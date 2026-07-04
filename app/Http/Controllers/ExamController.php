<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\UserAnswer;
use App\Models\Question;
use App\Models\QuestionGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ExamController extends Controller
{
    /**
     * Danh sách các đề thi
     */
    public function index(Request $request)
    {
        $query = $request->input('q');

        if ($query) {
            $keyword = trim($query);

            $exams = Exam::published()
                ->where(function ($q) use ($keyword) {
                    $q->where('title', 'LIKE', "%{$keyword}%")
                      ->orWhere('description', 'LIKE', "%{$keyword}%")
                      ->orWhere('slug', 'LIKE', "%{$keyword}%")
                      ->orWhere('year', 'LIKE', "%{$keyword}%")
                      ->orWhere('status', 'LIKE', "%{$keyword}%" );
                })
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $cachedExams = Cache::remember(Exam::publishedListCacheKey(), now()->addMinutes(30), function () {
                return Exam::published()
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->toArray();
            });

            $exams = Exam::hydrate($cachedExams);
        }

        return view('exams.index', compact('exams', 'query'));
    }

    /**
     * Chi tiết đề thi và lịch sử làm bài
     */
    public function show(Exam $exam)
    {
        // Nếu đề thi chưa được công bố và user không phải là admin thì chặn quyền truy cập
        if ($exam->status !== 'published' && (!Auth::user() || !Auth::user()->isAdmin())) {
            abort(404, 'Đề thi chưa được công bố.');
        }

        $attempts = ExamAttempt::where('user_id', Auth::id())
            ->where('exam_id', $exam->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Lấy số câu hỏi trong đề
        $examMeta = Cache::remember($exam->detailCacheKey(), now()->addMinutes(30), function () use ($exam) {
            return [
                'total_questions' => Question::whereHas('questionGroup', function ($q) use ($exam) {
                    $q->where('exam_id', $exam->id);
                })->count(),
            ];
        });

        $totalQuestions = $examMeta['total_questions'];

        return view('exams.show', compact('exam', 'attempts', 'totalQuestions'));
    }

    /**
     * Bắt đầu một lượt làm bài mới
     */
    public function start(Exam $exam)
    {
        // Chặn user thường vào làm bài nháp (draft)
        if ($exam->status !== 'published' && (!Auth::user() || !Auth::user()->isAdmin())) {
            abort(403, 'Đề thi chưa được công bố.');
        }

        // Kiểm tra xem có lượt làm bài nào đang dở dang không (in_progress)
        $existingAttempt = ExamAttempt::where('user_id', Auth::id())
            ->where('exam_id', $exam->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existingAttempt) {
            return redirect()->route('exams.take', $exam);
        }

        // Đếm tổng số câu hỏi của đề thi
        $totalQuestions = Question::whereHas('questionGroup', function ($q) use ($exam) {
            $q->where('exam_id', $exam->id);
        })->count();

        if ($totalQuestions === 0) {
            return redirect()->route('exams.show', $exam)
                ->with('error', 'Đề thi này chưa có câu hỏi nào. Hãy quay lại sau!');
        }

        // Tạo attempt mới
        $attempt = ExamAttempt::create([
            'user_id' => Auth::id(),
            'exam_id' => $exam->id,
            'mode' => 'full_test',
            'status' => 'in_progress',
            'total_questions' => $totalQuestions,
            'started_at' => now(),
        ]);

        return redirect()->route('exams.take', $exam);
    }

    /**
     * Trang làm bài thi
     */
    public function take(Exam $exam)
    {
        $attempt = ExamAttempt::where('user_id', Auth::id())
            ->where('exam_id', $exam->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$attempt) {
            return redirect()->route('exams.show', $exam)
                ->with('error', 'Bạn chưa bắt đầu lượt làm bài này.');
        }

        // Lấy tất cả QuestionGroups thuộc về đề này, kèm questions và answers
        $cachedQuestionGroups = Cache::remember($exam->questionGroupsCacheKey(), now()->addMinutes(30), function () use ($exam) {
            return QuestionGroup::where('exam_id', $exam->id)
                ->with(['part', 'questions.answers'])
                ->orderBy('part_id')
                ->orderBy('order_number')
                ->get()
                ->toArray();
        });

        $questionGroups = $this->hydrateCachedQuestionGroups($cachedQuestionGroups);

        // Lấy danh sách câu trả lời tạm thời của user đã lưu (cho tính năng auto-save sau này nếu cần)
        $savedAnswers = UserAnswer::where('attempt_id', $attempt->id)
            ->pluck('selected_answer_id', 'question_id')
            ->toArray();

        return view('exams.take', compact('exam', 'attempt', 'questionGroups', 'savedAnswers'));
    }

    private function hydrateCachedQuestionGroups(array $groups)
    {
        return collect($groups)->map(function ($group) {
            $group = (object) $group;
            $group->part = isset($group->part) ? (object) $group->part : null;
            $group->questions = collect($group->questions ?? [])->map(function ($question) {
                $question = (object) $question;
                $question->answers = collect($question->answers ?? [])->map(fn ($answer) => (object) $answer);
                return $question;
            });
            return $group;
        });
    }

    /**
     * Nộp bài thi
     */
    public function submit(Request $request, Exam $exam)
    {
        $attempt = ExamAttempt::where('user_id', Auth::id())
            ->where('exam_id', $exam->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$attempt) {
            return redirect()->route('exams.show', $exam)
                ->with('error', 'Lượt thi không tồn tại hoặc đã nộp.');
        }

        $submittedAnswers = $request->input('answers', []); // [question_id => selected_answer_id]
        $timeSpentSeconds = $request->input('time_spent_seconds', 0);

        // Lấy tất cả câu hỏi của đề
        $questions = Question::whereHas('questionGroup', function ($q) use ($exam) {
            $q->where('exam_id', $exam->id);
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
                // Phân biệt listening (Part 1-4) và reading (Part 5-7)
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

        // Tính điểm TOEIC
        $listeningScore = $this->calculateToeicScore($listeningCorrect, 'listening');
        $readingScore = $this->calculateToeicScore($readingCorrect, 'reading');
        $totalScore = $listeningScore + $readingScore;

        DB::transaction(function () use ($attempt, $userAnswersData, $totalCorrect, $totalQuestions, $listeningScore, $readingScore, $totalScore, $timeSpentSeconds) {
            // Xóa câu trả lời cũ nếu có (để tránh trùng lặp)
            UserAnswer::where('attempt_id', $attempt->id)->delete();

            // Insert câu trả lời của user
            UserAnswer::insert($userAnswersData);

            // Cập nhật trạng thái lượt làm bài
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

        return redirect()->route('exams.attempt.result', $attempt->id);
    }

    /**
     * Kết quả chi tiết lần thi
     */
    public function attemptResult(ExamAttempt $attempt)
    {
        // Bảo mật: chỉ cho phép chính chủ nhân hoặc admin xem kết quả
        if ($attempt->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'Bạn không có quyền xem kết quả này.');
        }

        $attempt->load(['exam', 'userAnswers.question.questionGroup.part', 'userAnswers.question.answers']);

        // Nhóm câu trả lời theo Part để hiển thị breakdown
        $partCorrect = [];
        $partTotal = [];

        foreach ($attempt->userAnswers as $userAnswer) {
            $partId = $userAnswer->question->questionGroup->part_id;
            $partTotal[$partId] = ($partTotal[$partId] ?? 0) + 1;
            if ($userAnswer->is_correct) {
                $partCorrect[$partId] = ($partCorrect[$partId] ?? 0) + 1;
            }
        }

        return view('exams.result', compact('attempt', 'partCorrect', 'partTotal'));
    }

    /**
     * Quy đổi điểm TOEIC chuẩn (gần đúng)
     */
    private function calculateToeicScore($correctCount, $section)
    {
        if ($correctCount <= 0) return 5;
        if ($correctCount >= 100) return 495;

        // Công thức xấp xỉ tuyến tính và bo tròn về bội số của 5
        $score = 5 + ($correctCount * 4.9);
        return min(495, max(5, round($score / 5) * 5));
    }
}
