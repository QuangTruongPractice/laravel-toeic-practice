<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\ExamAttempt;

class DashboardComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $cacheKey = "dashboard_stats_{$user->id}";
        $dashboardJson = null;

        try {
            $dashboardJson = Cache::get($cacheKey);
        } catch (\Throwable $exception) {
            Cache::forget($cacheKey);
        }

        if ($dashboardJson === null) {
            $dashboardJson = json_encode($this->buildDashboardData($user));
            Cache::put($cacheKey, $dashboardJson, now()->addMinutes(10));
        }

        $dashboard = json_decode($dashboardJson);

        if (!is_object($dashboard) || json_last_error() !== JSON_ERROR_NONE) {
            $dashboard = json_decode(json_encode($this->buildDashboardData($user)));
            Cache::put($cacheKey, json_encode($dashboard), now()->addMinutes(10));
        }

        $view->with((array) $dashboard);
    }

    private function buildDashboardData($user): array
    {
        // Get completed exam attempts
        $completedAttempts = ExamAttempt::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'asc')
            ->with('exam')
            ->get();

        $totalAttempts = $completedAttempts->count();

        // Calculate averages
        $avgTotalScore = $totalAttempts > 0 ? round($completedAttempts->avg('total_score')) : 0;
        $avgListeningScore = $totalAttempts > 0 ? round($completedAttempts->avg('listening_score')) : 0;
        $avgReadingScore = $totalAttempts > 0 ? round($completedAttempts->avg('reading_score')) : 0;

        // Total practice time in seconds
        $totalTimeSeconds = $completedAttempts->sum('time_spent_seconds');
        $totalHours = floor($totalTimeSeconds / 3600);
        $totalMinutes = floor(($totalTimeSeconds % 3600) / 60);
        $formattedTotalTime = $totalHours > 0 
            ? "{$totalHours}h {$totalMinutes}m" 
            : "{$totalMinutes}m";

        // Chart history (limit to last 10 completed attempts for progression chart)
        $chartAttempts = $completedAttempts->slice(max(0, $totalAttempts - 10));
        $chartData = [
            'labels' => $chartAttempts->map(function ($attempt) {
                return $attempt->exam->title ?? 'Exam';
            })->toArray(),
            'total_scores' => $chartAttempts->pluck('total_score')->toArray(),
            'listening_scores' => $chartAttempts->pluck('listening_score')->toArray(),
            'reading_scores' => $chartAttempts->pluck('reading_score')->toArray(),
        ];

        // Part by part analysis
        $partsPerformance = DB::table('user_answers')
            ->join('questions', 'user_answers.question_id', '=', 'questions.id')
            ->join('question_groups', 'questions.question_group_id', '=', 'question_groups.id')
            ->join('parts', 'question_groups.part_id', '=', 'parts.id')
            ->join('exam_attempts', 'user_answers.attempt_id', '=', 'exam_attempts.id')
            ->where('exam_attempts.user_id', $user->id)
            ->where('exam_attempts.status', 'completed')
            ->select(
                'parts.part_number',
                'parts.name as part_name',
                DB::raw('COUNT(user_answers.id) as total_answers'),
                DB::raw('SUM(CASE WHEN user_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers')
            )
            ->groupBy('parts.part_number', 'parts.name')
            ->orderBy('parts.part_number')
            ->get()
            ->map(function ($part) {
                return (object) [
                    'part_number' => $part->part_number,
                    'part_name' => $part->part_name,
                    'total_answers' => $part->total_answers,
                    'correct_answers' => $part->correct_answers,
                    'correct_rate' => $part->total_answers > 0 
                        ? round(($part->correct_answers / $part->total_answers) * 100, 1)
                        : 0,
                ];
            })
            ->toArray();

        $strongestPart = null;
        $weakestPart = null;

        if (!empty($partsPerformance)) {
            $attemptedParts = collect($partsPerformance)->filter(fn($part) => $part->total_answers > 0);

            if ($attemptedParts->isNotEmpty()) {
                $strongestPart = (object) $attemptedParts->sortByDesc('correct_rate')->first();
                $weakestPart = (object) $attemptedParts->sortBy('correct_rate')->first();
            }
        }

        $recentAttempts = ExamAttempt::where('user_id', $user->id)
            ->with('exam')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($attempt) {
                return (object) [
                    'id' => $attempt->id,
                    'exam_title' => $attempt->exam->title ?? 'N/A',
                    'status' => $attempt->status,
                    'created_at' => $attempt->created_at->toDateTimeString(),
                    'formatted_time' => $attempt->formatted_time,
                    'total_correct' => $attempt->total_correct,
                    'total_questions' => $attempt->total_questions,
                    'accuracy_percent' => $attempt->accuracy_percent,
                    'total_score' => $attempt->total_score,
                    'listening_score' => $attempt->listening_score,
                    'reading_score' => $attempt->reading_score,
                    'exam_id' => $attempt->exam_id,
                ];
            })
            ->toArray();

        return [
            'totalAttempts' => $totalAttempts,
            'avgTotalScore' => $avgTotalScore,
            'avgListeningScore' => $avgListeningScore,
            'avgReadingScore' => $avgReadingScore,
            'formattedTotalTime' => $formattedTotalTime,
            'chartData' => $chartData,
            'partsPerformance' => $partsPerformance,
            'strongestPart' => $strongestPart,
            'weakestPart' => $weakestPart,
            'recentAttempts' => $recentAttempts,
        ];
    }
}
