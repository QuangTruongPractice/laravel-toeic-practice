<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\Question;
use Illuminate\Http\Request;

class PracticeController extends Controller
{
    /**
     * Trang chọn Part để luyện tập (7 cards)
     */
    public function index()
    {
        $parts = Part::orderBy('part_number')->get();

        return view('practice.index', compact('parts'));
    }

    /**
     * Nhận lựa chọn Part + số câu, random câu hỏi, lưu vào session, chuyển sang trang làm bài
     */
    public function start(Request $request, Part $part)
    {
        $request->validate([
            'num_questions' => 'required|integer|min:1|max:200',
        ]);

        $numQuestions = $request->num_questions;

        // Lấy random câu hỏi theo Part, kèm đáp án và thông tin group (audio, image, passage)
        $questions = Question::whereHas('questionGroup', function ($query) use ($part) {
            $query->where('part_id', $part->id);
        })
            ->with(['answers', 'questionGroup'])
            ->inRandomOrder()
            ->take($numQuestions)
            ->get();

        if ($questions->isEmpty()) {
            return redirect()->route('practice.index')
                ->with('error', 'Chưa có câu hỏi nào cho Part '.$part->part_number.'. Hãy nhờ Admin thêm đề thi!');
        }

        // Lưu thông tin phiên luyện tập vào session
        session([
            'practice' => [
                'part_id' => $part->id,
                'part_number' => $part->part_number,
                'part_name' => $part->name,
                'question_ids' => $questions->pluck('id')->toArray(),
                'started_at' => now()->toDateTimeString(),
            ],
        ]);

        return redirect()->route('practice.show');
    }

    /**
     * Hiển thị trang làm bài luyện tập
     */
    public function show()
    {
        $practice = session('practice');

        if (! $practice) {
            return redirect()->route('practice.index')
                ->with('error', 'Không tìm thấy phiên luyện tập. Vui lòng chọn lại Part.');
        }

        $questions = Question::whereIn('id', $practice['question_ids'])
            ->with(['answers', 'questionGroup'])
            ->get();

        return view('practice.show', compact('practice', 'questions'));
    }

    /**
     * Nhận câu trả lời, so sánh với đáp án đúng, tính kết quả, lưu vào session
     */
    public function submit(Request $request)
    {
        $practice = session('practice');

        if (! $practice) {
            return redirect()->route('practice.index')
                ->with('error', 'Phiên luyện tập đã hết hạn.');
        }

        $answers = $request->input('answers', []); // ['question_id' => 'answer_id', ...]

        $questions = Question::whereIn('id', $practice['question_ids'])
            ->with(['answers', 'questionGroup'])
            ->get();

        $results = [];
        $correct = 0;
        $total = $questions->count();

        foreach ($questions as $question) {
            $selectedAnswerId = $answers[$question->id] ?? null;
            $correctAnswer = $question->answers->firstWhere('is_correct', true);
            $selectedAnswer = $selectedAnswerId ? $question->answers->firstWhere('id', $selectedAnswerId) : null;
            $isCorrect = $selectedAnswer && $selectedAnswer->is_correct;

            if ($isCorrect) {
                $correct++;
            }

            $results[] = [
                'question_id' => $question->id,
                'question_number' => $question->question_number,
                'content' => $question->content,
                'explanation' => $question->explanation,
                'group' => $question->questionGroup,
                'answers' => $question->answers,
                'selected_answer_id' => $selectedAnswerId ? (int) $selectedAnswerId : null,
                'correct_answer_id' => $correctAnswer?->id,
                'correct_answer_label' => $correctAnswer?->label,
                'is_correct' => $isCorrect,
            ];
        }

        // Lưu kết quả vào session
        session([
            'practice_result' => [
                'part_number' => $practice['part_number'],
                'part_name' => $practice['part_name'],
                'correct' => $correct,
                'total' => $total,
                'percentage' => $total > 0 ? round(($correct / $total) * 100) : 0,
                'results' => $results,
                'started_at' => $practice['started_at'],
                'completed_at' => now()->toDateTimeString(),
            ],
        ]);

        // Xóa phiên luyện tập
        session()->forget('practice');

        return redirect()->route('practice.result');
    }

    /**
     * Hiển thị kết quả luyện tập
     */
    public function result()
    {
        $practiceResult = session('practice_result');

        if (! $practiceResult) {
            return redirect()->route('practice.index')
                ->with('error', 'Không tìm thấy kết quả. Vui lòng làm bài lại.');
        }

        return view('practice.result', compact('practiceResult'));
    }
}
