<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Part;
use App\Models\QuestionGroup;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    /**
     * Hiển thị danh sách câu hỏi phân theo Part của 1 đề thi
     */
    public function index(Exam $exam)
    {
        $parts = Part::orderBy('part_number')->get();
        
        // Lấy toàn bộ nhóm câu hỏi kèm câu hỏi và đáp án của đề thi
        $questionGroups = QuestionGroup::where('exam_id', $exam->id)
            ->with(['questions.answers'])
            ->orderBy('order_number')
            ->get()
            ->groupBy('part_id');

        return view('admin.exams.questions.index', compact('exam', 'parts', 'questionGroups'));
    }

    /**
     * Hiển thị form tạo nhóm câu hỏi cho 1 Part cụ thể
     */
    public function create(Exam $exam, Part $part)
    {
        return view('admin.exams.questions.create', compact('exam', 'part'));
    }

    /**
     * Lưu nhóm câu hỏi mới kèm theo câu hỏi và câu trả lời
     */
    public function store(Request $request, Exam $exam, Part $part)
    {
        $request->validate([
            'passage' => 'nullable|string',
            'audio' => 'nullable|file|mimes:mp3,wav|max:10240',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'questions' => 'required|array|min:1',
            'questions.*.question_number' => 'required|integer|between:1,200',
            'questions.*.content' => 'nullable|string',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.answers' => 'required|array|min:3|max:4',
            'questions.*.answers.*.label' => 'required|string|in:A,B,C,D',
            'questions.*.answers.*.content' => 'required|string',
            'questions.*.correct_answer' => 'required|string|in:A,B,C,D',
        ]);

        DB::transaction(function () use ($request, $exam, $part) {
            // 1. Xử lý upload file (Lesson 4 sẽ tối ưu hơn, hiện tại lưu tạm public)
            $audioPath = null;
            if ($request->hasFile('audio')) {
                $audioPath = $request->file('audio')->store('audios', 'public');
            }

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('images', 'public');
            }

            // Lấy thứ tự nhóm câu hỏi tiếp theo
            $nextOrder = QuestionGroup::where('exam_id', $exam->id)->max('order_number') + 1;

            // 2. Tạo Question Group
            $group = QuestionGroup::create([
                'exam_id' => $exam->id,
                'part_id' => $part->id,
                'passage' => $request->passage,
                'audio_path' => $audioPath,
                'image_path' => $imagePath,
                'order_number' => $nextOrder,
            ]);

            // 3. Tạo các Question và Answer đi kèm
            foreach ($request->questions as $index => $qData) {
                $question = Question::create([
                    'question_group_id' => $group->id,
                    'content' => $qData['content'] ?? null,
                    'question_number' => $qData['question_number'],
                    'order_in_group' => $index + 1,
                    'explanation' => $qData['explanation'] ?? null,
                ]);

                foreach ($qData['answers'] as $aData) {
                    Answer::create([
                        'question_id' => $question->id,
                        'label' => $aData['label'],
                        'content' => $aData['content'],
                        'is_correct' => $aData['label'] === $qData['correct_answer'],
                    ]);
                }
            }
        });

        Exam::clearCacheById($exam->id);

        return redirect()
            ->route('admin.exams.questions.index', $exam)
            ->with('success', 'Nhóm câu hỏi đã được tạo thành công!');
    }

    /**
     * Show form chỉnh sửa nhóm câu hỏi
     */
    public function edit(Exam $exam, QuestionGroup $questionGroup)
    {
        $questionGroup->load('questions.answers');
        $part = $questionGroup->part;
        return view('admin.exams.questions.edit', compact('exam', 'questionGroup', 'part'));
    }

    /**
     * Cập nhật nhóm câu hỏi
     */
    public function update(Request $request, Exam $exam, QuestionGroup $questionGroup)
    {
        $request->validate([
            'passage' => 'nullable|string',
            'audio' => 'nullable|file|mimes:mp3,wav|max:10240',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'questions' => 'required|array|min:1',
            'questions.*.id' => 'nullable|exists:questions,id',
            'questions.*.question_number' => 'required|integer|between:1,200',
            'questions.*.content' => 'nullable|string',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.answers' => 'required|array|min:3|max:4',
            'questions.*.answers.*.id' => 'nullable|exists:answers,id',
            'questions.*.answers.*.label' => 'required|string|in:A,B,C,D',
            'questions.*.answers.*.content' => 'required|string',
            'questions.*.correct_answer' => 'required|string|in:A,B,C,D',
        ]);

        DB::transaction(function () use ($request, $questionGroup) {
            // 1. Cập nhật Question Group
            if ($request->hasFile('audio')) {
                if ($questionGroup->audio_path) {
                    Storage::disk('public')->delete($questionGroup->audio_path);
                }
                $questionGroup->audio_path = $request->file('audio')->store('audios', 'public');
            }
            if ($request->hasFile('image')) {
                if ($questionGroup->image_path) {
                    Storage::disk('public')->delete($questionGroup->image_path);
                }
                $questionGroup->image_path = $request->file('image')->store('images', 'public');
            }
            $questionGroup->passage = $request->passage;
            $questionGroup->save();

            // Lấy IDs câu hỏi cũ để xóa nếu bị admin xóa bớt trong form
            $submittedQuestionIds = collect($request->questions)->pluck('id')->filter()->toArray();
            Question::where('question_group_id', $questionGroup->id)
                ->whereNotIn('id', $submittedQuestionIds)
                ->delete();

            // 2. Lưu/Cập nhật các câu hỏi
            foreach ($request->questions as $index => $qData) {
                $question = Question::updateOrCreate(
                    ['id' => $qData['id'] ?? null],
                    [
                        'question_group_id' => $questionGroup->id,
                        'content' => $qData['content'] ?? null,
                        'question_number' => $qData['question_number'],
                        'order_in_group' => $index + 1,
                        'explanation' => $qData['explanation'] ?? null,
                    ]
                );

                // Lấy IDs câu trả lời cũ để xóa
                $submittedAnswerIds = collect($qData['answers'])->pluck('id')->filter()->toArray();
                Answer::where('question_id', $question->id)
                    ->whereNotIn('id', $submittedAnswerIds)
                    ->delete();

                foreach ($qData['answers'] as $aData) {
                    Answer::updateOrCreate(
                        ['id' => $aData['id'] ?? null],
                        [
                            'question_id' => $question->id,
                            'label' => $aData['label'],
                            'content' => $aData['content'],
                            'is_correct' => $aData['label'] === $qData['correct_answer'],
                        ]
                    );
                }
            }
        });

        Exam::clearCacheById($exam->id);

        return redirect()
            ->route('admin.exams.questions.index', $exam)
            ->with('success', 'Nhóm câu hỏi đã được cập nhật thành công!');
    }

    /**
     * Xóa nhóm câu hỏi
     */
    public function destroy(Exam $exam, QuestionGroup $questionGroup)
    {
        // Cascade delete sẽ xóa sạch questions + answers tương ứng
        if ($questionGroup->audio_path) {
            Storage::disk('public')->delete($questionGroup->audio_path);
        }
        if ($questionGroup->image_path) {
            Storage::disk('public')->delete($questionGroup->image_path);
        }
        $questionGroup->delete();
        Exam::clearCacheById($exam->id);

        return redirect()
            ->route('admin.exams.questions.index', $exam)
            ->with('success', 'Nhóm câu hỏi đã được xóa thành công!');
    }
}
