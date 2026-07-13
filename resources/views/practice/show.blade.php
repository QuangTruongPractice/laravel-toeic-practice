<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Luyện tập Part {{ $practice['part_number'] }}: {{ $practice['part_name'] }}
            </h2>
            <div class="flex items-center space-x-3">
                @if(in_array($practice['part_number'], [1, 2]))
                    <button type="button" id="global-toggle-answers" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                        Hiện tất cả đáp án
                    </button>
                @endif
                <span class="text-sm text-gray-500">{{ $questions->count() }} câu hỏi</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('practice.submit') }}" method="POST" class="space-y-6">
                @csrf

                @foreach($questions as $index => $question)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <!-- Question Header -->
                            <div class="flex items-center mb-4">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold text-sm mr-3">
                                    {{ $index + 1 }}
                                </span>
                                <span class="text-sm text-gray-500">Câu {{ $question->question_number }}</span>
                                @if(in_array($practice['part_number'], [1, 2]))
                                    <button type="button" class="toggle-single-answer ml-auto text-xs text-indigo-600 hover:text-indigo-900 font-semibold focus:outline-none bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded-md transition-colors duration-150" data-question-id="{{ $question->id }}">
                                        Hiện đáp án
                                    </button>
                                @endif
                            </div>

                            <!-- Audio Player (Part 1-4) -->
                            @if($question->questionGroup->audio_url)
                                <div class="mb-4">
                                    <audio controls class="w-full h-10">
                                        <source src="{{ $question->questionGroup->audio_url }}" type="audio/mpeg">
                                    </audio>
                                </div>
                            @endif

                            <!-- Image (Part 1) -->
                            @if($question->questionGroup->image_url)
                                <div class="mb-4 flex justify-center">
                                    <img src="{{ $question->questionGroup->image_url }}" alt="Question image" class="max-w-md rounded-lg border">
                                </div>
                            @endif

                            <!-- Passage (Part 3,4,6,7) -->
                            @if($question->questionGroup->passage)
                                <div class="mb-4 p-4 bg-gray-50 rounded-lg border-l-4 border-indigo-300">
                                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $question->questionGroup->passage }}</p>
                                </div>
                            @endif

                            <!-- Question Content -->
                            @if($question->content)
                                <p class="text-gray-900 font-medium mb-4">{{ $question->content }}</p>
                            @endif

                            <!-- Answer Options -->
                            <div class="space-y-2">
                                @foreach($question->answers as $answer)
                                    <label class="flex items-center p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-indigo-50 hover:border-indigo-300 transition-colors duration-150">
                                        <input type="radio"
                                               name="answers[{{ $question->id }}]"
                                               value="{{ $answer->id }}"
                                               class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                        <span class="ml-3 text-sm">
                                            <span class="font-semibold text-gray-700">{{ $answer->label }}.</span>
                                            <span class="answer-content text-gray-600 @if(in_array($practice['part_number'], [1, 2])) hidden @endif" data-question-id="{{ $question->id }}">
                                                {{ $answer->content }}
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Submit Button -->
                <div class="flex justify-between items-center bg-white p-6 rounded-lg shadow-sm">
                    <a href="{{ route('practice.index') }}" class="text-gray-600 hover:text-gray-900 font-medium text-sm">
                        ← Hủy và quay lại
                    </a>
                    <button type="submit" onclick="return confirm('Bạn có chắc chắn muốn nộp bài?')" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 transition duration-150">
                        Nộp bài
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(in_array($practice['part_number'], [1, 2]))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const globalToggleBtn = document.getElementById('global-toggle-answers');
                const singleToggleBtns = document.querySelectorAll('.toggle-single-answer');
                const answerContents = document.querySelectorAll('.answer-content');
                
                let allShown = false;

                if (globalToggleBtn) {
                    globalToggleBtn.addEventListener('click', function () {
                        allShown = !allShown;
                        
                        answerContents.forEach(el => {
                            if (allShown) {
                                el.classList.remove('hidden');
                            } else {
                                el.classList.add('hidden');
                            }
                        });

                        singleToggleBtns.forEach(btn => {
                            btn.textContent = allShown ? 'Ẩn đáp án' : 'Hiện đáp án';
                        });

                        globalToggleBtn.textContent = allShown ? 'Ẩn tất cả đáp án' : 'Hiện tất cả đáp án';
                    });
                }

                singleToggleBtns.forEach(btn => {
                    btn.addEventListener('click', function () {
                        const questionId = this.getAttribute('data-question-id');
                        const relatedContents = document.querySelectorAll(`.answer-content[data-question-id="${questionId}"]`);
                        if (relatedContents.length === 0) return;
                        
                        const isHidden = relatedContents[0].classList.contains('hidden');
                        
                        relatedContents.forEach(el => {
                            if (isHidden) {
                                el.classList.remove('hidden');
                            } else {
                                el.classList.add('hidden');
                            }
                        });

                        this.textContent = isHidden ? 'Ẩn đáp án' : 'Hiện đáp án';
                    });
                });
            });
        </script>
    @endif
</x-app-layout>
