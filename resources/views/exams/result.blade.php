<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Kết quả thi thử: {{ $attempt->exam->title }}
            </h2>
            <a href="{{ route('exams.show', $attempt->exam) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                ← Quay lại trang đề thi
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Score Card Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-8">
                    <div class="text-center mb-8">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-1">TỔNG ĐIỂM TOEIC</span>
                        <div class="text-6xl font-extrabold text-indigo-600 mb-2">
                            {{ $attempt->total_score }} <span class="text-2xl text-gray-400">/ 990</span>
                        </div>
                        <p class="text-sm text-gray-500">
                            Đúng <strong class="text-gray-900 font-semibold">{{ $attempt->total_correct }}</strong> / {{ $attempt->total_questions }} câu · Thời gian làm: {{ $attempt->formatted_time }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-6 bg-indigo-50 border border-indigo-100 rounded-xl text-center">
                            <span class="text-xs font-semibold text-indigo-600 uppercase tracking-wider block mb-1">🎧 Listening Score</span>
                            <span class="text-3xl font-bold text-indigo-900">{{ $attempt->listening_score }} / 495</span>
                            <p class="text-xs text-indigo-500 mt-2">
                                Phần nghe (Part 1 - 4)
                            </p>
                        </div>
                        <div class="p-6 bg-emerald-50 border border-emerald-100 rounded-xl text-center">
                            <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider block mb-1">📖 Reading Score</span>
                            <span class="text-3xl font-bold text-emerald-900">{{ $attempt->reading_score }} / 495</span>
                            <p class="text-xs text-emerald-500 mt-2">
                                Phần đọc (Part 5 - 7)
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Breakdown by Part -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 p-6">
                <h3 class="font-bold text-lg text-gray-900 mb-4">Chi tiết theo từng Part</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    @for($p = 1; $p <= 7; $p++)
                        @php
                            $correct = $partCorrect[$p] ?? 0;
                            $total = $partTotal[$p] ?? 0;
                            $percentage = $total > 0 ? round(($correct / $total) * 100) : 0;
                        @endphp
                        <div class="p-4 bg-gray-50 border rounded-lg text-center">
                            <span class="text-xs font-bold text-gray-500 block mb-1">Part {{ $p }}</span>
                            <span class="text-lg font-bold text-gray-950">{{ $correct }} / {{ $total }}</span>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="text-xs text-gray-400 block mt-1">{{ $percentage }}% đúng</span>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Question Review -->
            <h3 class="font-bold text-lg text-gray-900 mt-8 mb-2">Xem lại bài làm</h3>
            
            @foreach($attempt->userAnswers as $index => $userAnswer)
                @php
                    $question = $userAnswer->question;
                    $group = $question->questionGroup;
                    $isCorrect = $userAnswer->is_correct;
                @endphp
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4
                    @if($isCorrect) border-green-500 @else border-red-500 @endif">
                    <div class="p-6">
                        <!-- Question Header -->
                        <div class="flex items-center mb-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-bold text-sm mr-3
                                @if($isCorrect) bg-green-100 text-green-700 @else bg-red-100 text-red-700 @endif">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-sm text-gray-500">Câu {{ $question->question_number }} · Part {{ $group->part_id }}</span>
                            @if($isCorrect)
                                <span class="ml-2 text-xs font-semibold text-green-600">✓ Đúng</span>
                            @else
                                <span class="ml-2 text-xs font-semibold text-red-600">✗ Sai</span>
                            @endif
                        </div>

                        <!-- Audio -->
                        @if($group && $group->audio_path)
                            <div class="mb-3">
                                <audio controls class="w-full h-10">
                                    <source src="{{ Storage::url($group->audio_path) }}" type="audio/mpeg">
                                </audio>
                            </div>
                        @endif

                        <!-- Image -->
                        @if($group && $group->image_path)
                            <div class="mb-3">
                                <img src="{{ Storage::url($group->image_path) }}" alt="Question image" class="max-w-sm rounded-lg border">
                            </div>
                        @endif

                        <!-- Passage -->
                        @if($group && $group->passage)
                            <div class="mb-3 p-3 bg-gray-50 rounded-lg border-l-4 border-indigo-300">
                                <p class="text-sm text-gray-700 whitespace-pre-line font-serif">{{ $group->passage }}</p>
                            </div>
                        @endif

                        <!-- Question Content -->
                        @if($question->content)
                            <p class="text-gray-900 font-medium mb-3">{{ $question->content }}</p>
                        @endif

                        <!-- Answers -->
                        <div class="space-y-2">
                            @foreach($question->answers as $answer)
                                @php
                                    $isSelected = $userAnswer->selected_answer_id == $answer->id;
                                    $isCorrectAnswer = $answer->is_correct;
                                @endphp
                                <div class="flex items-center p-3 rounded-lg border
                                    @if($isCorrectAnswer) bg-green-50 border-green-300
                                    @elseif($isSelected && !$isCorrectAnswer) bg-red-50 border-red-300
                                    @else border-gray-200 @endif">
                                    <span class="text-sm">
                                        <span class="font-semibold
                                            @if($isCorrectAnswer) text-green-700
                                            @elseif($isSelected) text-red-700
                                            @else text-gray-700 @endif">
                                            {{ $answer->label }}.
                                        </span>
                                        <span class="@if($isCorrectAnswer) text-green-700 @elseif($isSelected) text-red-700 @else text-gray-600 @endif">
                                            {{ $answer->content }}
                                        </span>
                                        @if($isCorrectAnswer)
                                            <span class="ml-1 text-green-600 font-bold">✓</span>
                                        @endif
                                        @if($isSelected && !$isCorrectAnswer)
                                            <span class="ml-1 text-red-600 font-bold">✗ (Bạn chọn)</span>
                                        @endif
                                        @if($isSelected && $isCorrectAnswer)
                                            <span class="ml-1 text-green-600 text-xs">(Bạn chọn)</span>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Explanation -->
                        @if($question->explanation)
                            <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                                <p class="text-xs font-semibold text-blue-700 mb-1">💡 Giải thích:</p>
                                <p class="text-sm text-blue-800">{{ $question->explanation }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Actions -->
            <div class="flex justify-center pt-6">
                <a href="{{ route('exams.index') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 transition duration-150 shadow-md">
                    Xem các đề thi khác
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
