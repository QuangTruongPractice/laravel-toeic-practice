<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Luyện tập Part {{ $practice['part_number'] }}: {{ $practice['part_name'] }}
            </h2>
            <span class="text-sm text-gray-500">{{ $questions->count() }} câu hỏi</span>
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
                            </div>

                            <!-- Audio Player (Part 1-4) -->
                            @if($question->questionGroup->audio_path)
                                <div class="mb-4">
                                    <audio controls class="w-full h-10">
                                        <source src="{{ Storage::url($question->questionGroup->audio_path) }}" type="audio/mpeg">
                                    </audio>
                                </div>
                            @endif

                            <!-- Image (Part 1) -->
                            @if($question->questionGroup->image_path)
                                <div class="mb-4">
                                    <img src="{{ Storage::url($question->questionGroup->image_path) }}" alt="Question image" class="max-w-md rounded-lg border">
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
                                            <span class="text-gray-600">{{ $answer->content }}</span>
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
</x-app-layout>
