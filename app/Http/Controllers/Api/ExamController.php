<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\UserAnswer;
use App\Http\Resources\ExamResource;
use App\Http\Resources\ExamDetailResource;
use App\Http\Resources\AttemptResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    /**
     * Display a listing of published exams.
     */
    public function index()
    {
        $exams = Exam::published()->orderBy('created_at', 'desc')->get();
        return ExamResource::collection($exams);
    }

    /**
     * Display the specified exam with nested question groups, questions and answers.
     */
    public function show(Exam $exam)
    {
        if ($exam->status !== 'published') {
            return response()->json(['message' => 'Đề thi chưa được công bố.'], 403);
        }

        $exam->load([
            'questionGroups.part',
            'questionGroups.questions.answers'
        ]);

        return new ExamDetailResource($exam);
    }

    /**
     * Start a new exam attempt.
     */
    public function startAttempt(Request $request, Exam $exam)
    {
        if ($exam->status !== 'published') {
            return response()->json(['message' => 'Đề thi chưa được công bố.'], 403);
        }

        $user = $request->user();

        // Check if there is an in-progress attempt for this exam
        $existingAttempt = ExamAttempt::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existingAttempt) {
            return response()->json([
                'message' => 'Bạn có lượt làm bài đang dở dang.',
                'attempt' => new AttemptResource($existingAttempt),
            ]);
        }

        // Count total questions in the exam
        $totalQuestions = Question::whereHas('questionGroup', function ($q) use ($exam) {
            $q->where('exam_id', $exam->id);
        })->count();

        if ($totalQuestions === 0) {
            return response()->json(['message' => 'Đề thi này chưa có câu hỏi nào.'], 400);
        }

        $attempt = ExamAttempt::create([
            'user_id' => $user->id,
            'exam_id' => $exam->id,
            'mode' => 'full_test',
            'status' => 'in_progress',
            'total_questions' => $totalQuestions,
            'started_at' => now(),
        ]);

        return response()->json([
            'message' => 'Bắt đầu lượt làm bài mới thành công.',
            'attempt' => new AttemptResource($attempt),
        ], 201);
    }

    /**
     * Submit an exam attempt.
     */
    public function submitAttempt(Request $request, ExamAttempt $attempt)
    {
        $user = $request->user();

        if ($attempt->user_id !== $user->id) {
            return response()->json(['message' => 'Bạn không có quyền nộp bài cho lượt làm bài này.'], 403);
        }

        if ($attempt->status !== 'in_progress') {
            return response()->json(['message' => 'Lượt làm bài đã được nộp hoặc không hợp lệ.'], 400);
        }

        $request->validate([
            'answers' => ['array'],
            'answers.*' => ['nullable', 'integer'], // question_id => selected_answer_id
            'time_spent_seconds' => ['required', 'integer', 'min:0'],
        ]);

        $submittedAnswers = $request->input('answers', []);
        $timeSpentSeconds = $request->input('time_spent_seconds', 0);

        // Fetch all questions with answers & part information
        $questions = Question::whereHas('questionGroup', function ($q) use ($attempt) {
            $q->where('exam_id', $attempt->exam_id);
        })->with(['answers', 'questionGroup.part'])->get();

        $totalQuestions = $questions->count();
        $totalCorrect = 0;
        $listeningCorrect = 0;
        $readingCorrect = 0;

        $userAnswersData = [];

        foreach ($questions as $question) {
            $selectedAnswerId = $submittedAnswers[$question->id] ?? null;
            $correctAnswer = $question->answers->firstWhere('is_correct', true);
            $isCorrect = $selectedAnswerId && $correctAnswer && ((int)$selectedAnswerId === (int)$correctAnswer->id);

            if ($isCorrect) {
                $totalCorrect++;
                if (in_array($question->questionGroup->part_id, [1, 2, 3, 4])) {
                    $listeningCorrect++;
                } else {
                    $readingCorrect++;
                }
            }

            $userAnswersData[] = [
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'selected_answer_id' => $selectedAnswerId ? (int)$selectedAnswerId : null,
                'is_correct' => $isCorrect,
            ];
        }

        $listeningScore = $this->calculateToeicScore($listeningCorrect, 'listening');
        $readingScore = $this->calculateToeicScore($readingCorrect, 'reading');
        $totalScore = $listeningScore + $readingScore;

        DB::transaction(function () use ($attempt, $userAnswersData, $totalCorrect, $totalQuestions, $listeningScore, $readingScore, $totalScore, $timeSpentSeconds) {
            // Delete old answers if any
            UserAnswer::where('attempt_id', $attempt->id)->delete();

            // Insert user answers
            UserAnswer::insert($userAnswersData);

            // Update exam attempt status
            $attempt->update([
                'status' => 'completed',
                'listening_score' => $listeningScore,
                'reading_score' => $readingScore,
                'total_score' => $totalScore,
                'total_correct' => $totalCorrect,
                'total_questions' => $totalQuestions,
                'time_spent_seconds' => $timeSpentSeconds,
                'completed_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'Nộp bài thi thành công.',
            'attempt' => new AttemptResource($attempt->fresh()),
        ]);
    }

    /**
     * Calculate approximate TOEIC score.
     */
    private function calculateToeicScore($correctCount, $section)
    {
        if ($correctCount <= 0) return 5;
        if ($correctCount >= 100) return 495;

        $score = 5 + ($correctCount * 4.9);
        return min(495, max(5, round($score / 5) * 5));
    }
}
