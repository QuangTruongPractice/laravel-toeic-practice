<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Chỉnh sửa nhóm câu hỏi:') }} {{ $exam->title }} - Part {{ $part->part_number }}
            </h2>
            <a href="{{ route('admin.exams.questions.index', $exam) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                &larr; Quay lại danh sách câu hỏi
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="questionForm()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.exams.question-groups.update', [$exam, $questionGroup]) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                        @csrf
                        @method('PUT')

                        <!-- Group Info -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                            <h3 class="font-bold text-gray-700">Thông tin nhóm câu hỏi (Question Group)</h3>
                            
                            @if($part->part_number >= 1 && $part->part_number <= 4)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tệp âm thanh (Audio - Part 1-4)</label>
                                    @if($questionGroup->audio_path)
                                        <div class="mt-1 mb-2 text-sm text-gray-600 flex items-center space-x-2">
                                            <span>🔊 Hiện tại: <a href="{{ Storage::url($questionGroup->audio_path) }}" target="_blank" class="text-indigo-600 hover:underline">Nghe thử</a></span>
                                        </div>
                                    @endif
                                    <input type="file" name="audio" accept="audio/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-750 hover:file:bg-indigo-100">
                                </div>
                            @endif

                            @if($part->part_number == 1)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Hình ảnh (Image - Part 1)</label>
                                    @if($questionGroup->image_path)
                                        <div class="mt-1 mb-2 text-sm text-gray-600">
                                            <img src="{{ Storage::url($questionGroup->image_path) }}" alt="Preview image" class="h-20 object-contain rounded border">
                                        </div>
                                    @endif
                                    <input type="file" name="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-750 hover:file:bg-indigo-100">
                                </div>
                            @endif

                            @if(in_array($part->part_number, [3, 4, 6, 7]))
                                <div>
                                    <label for="passage" class="block text-sm font-medium text-gray-700">Đoạn văn / Đoạn hội thoại (Passage - Part 3,4,6,7)</label>
                                    <textarea name="passage" id="passage" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('passage', $questionGroup->passage) }}</textarea>
                                </div>
                            @endif
                        </div>

                        <!-- Questions Section -->
                        <div class="space-y-6">
                            <div class="flex justify-between items-center border-b pb-2">
                                <h3 class="text-lg font-bold text-gray-800">Danh sách câu hỏi</h3>
                                <button type="button" @click="addQuestion()" class="inline-flex items-center px-3 py-1 bg-emerald-600 text-white rounded text-xs font-semibold uppercase tracking-wider hover:bg-emerald-700">
                                    + Thêm câu hỏi
                                </button>
                            </div>

                            <template x-for="(question, qIndex) in questions" :key="qIndex">
                                <div class="border rounded-lg p-5 bg-white shadow-sm space-y-4 relative">
                                    <button type="button" @click="removeQuestion(qIndex)" x-show="questions.length > 1" class="absolute top-4 right-4 text-red-500 hover:text-red-700 font-medium text-xs">
                                        Xóa câu này
                                    </button>

                                    <!-- Lưu ID câu hỏi cũ -->
                                    <input type="hidden" :name="`questions[${qIndex}][id]`" x-model="question.id">

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Số thứ tự câu (1-200)</label>
                                            <input type="number" :name="`questions[${qIndex}][question_number]`" required x-model="question.question_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700">Nội dung câu hỏi (Không bắt buộc với Part 1-2)</label>
                                            <input type="text" :name="`questions[${qIndex}][content]`" x-model="question.content" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                    </div>

                                    <!-- Answers Block -->
                                    <div class="space-y-3">
                                        <label class="block text-sm font-medium text-gray-700">Các đáp án lựa chọn</label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <template x-for="(ans, aIndex) in question.answers" :key="aIndex">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-bold text-gray-700 text-sm" x-text="ans.label + '.'"></span>
                                                    <!-- Lưu ID đáp án cũ -->
                                                    <input type="hidden" :name="`questions[${qIndex}][answers][${aIndex}][id]`" x-model="ans.id">
                                                    <input type="hidden" :name="`questions[${qIndex}][answers][${aIndex}][label]`" :value="ans.label">
                                                    <input type="text" :name="`questions[${qIndex}][answers][${aIndex}][content]`" required x-model="ans.content" placeholder="Nội dung đáp án" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Correct Answer -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Đáp án đúng</label>
                                            <select :name="`questions[${qIndex}][correct_answer]`" required x-model="question.correct_answer" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <template x-for="ans in question.answers" :key="ans.label">
                                                    <option :value="ans.label" x-text="'Đáp án ' + ans.label"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Giải thích đáp án (Optional)</label>
                                            <input type="text" :name="`questions[${qIndex}][explanation]`" x-model="question.explanation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4 border-t">
                            <button type="submit" class="inline-flex items-center px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition duration-150">
                                Cập nhật nhóm câu hỏi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function questionForm() {
            const partNum = {{ $part->part_number }};
            
            // Format existing data to Alpine.js structure
            const getInitialAnswers = () => {
                if (partNum === 2) {
                    return [
                        { label: 'A', content: '' },
                        { label: 'B', content: '' },
                        { label: 'C', content: '' }
                    ];
                }
                return [
                    { label: 'A', content: '' },
                    { label: 'B', content: '' },
                    { label: 'C', content: '' },
                    { label: 'D', content: '' }
                ];
            };

            // Parse existing questions from Laravel DB
            const rawQuestions = @json($questionGroup->questions);
            const questions = rawQuestions.map(q => {
                // Find correct answer label
                const correctAns = q.answers.find(a => a.is_correct);
                const correctLabel = correctAns ? correctAns.label : 'A';

                return {
                    id: q.id,
                    question_number: q.question_number,
                    content: q.content || '',
                    explanation: q.explanation || '',
                    answers: q.answers.map(a => ({
                        id: a.id,
                        label: a.label,
                        content: a.content
                    })),
                    correct_answer: correctLabel
                };
            });

            if (questions.length === 0) {
                questions.push({
                    question_number: '',
                    content: '',
                    explanation: '',
                    answers: getInitialAnswers(),
                    correct_answer: 'A'
                });
            }

            return {
                questions: questions,
                addQuestion() {
                    this.questions.push({
                        question_number: '',
                        content: '',
                        explanation: '',
                        answers: getInitialAnswers(),
                        correct_answer: 'A'
                    });
                },
                removeQuestion(index) {
                    this.questions.splice(index, 1);
                }
            };
        }
    </script>
</x-app-layout>
