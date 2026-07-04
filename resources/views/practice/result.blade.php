<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Kết quả: Part {{ $practiceResult['part_number'] }} - {{ $practiceResult['part_name'] }}
            </h2>
            <a href="{{ route('practice.index') }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                ← Luyện tập tiếp
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Score Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-center">
                        <div class="text-5xl font-bold mb-2
                            @if($practiceResult['percentage'] >= 80) text-green-600
                            @elseif($practiceResult['percentage'] >= 60) text-yellow-600
                            @else text-red-600 @endif">
                            {{ $practiceResult['percentage'] }}%
                        </div>
                        <p class="text-gray-600 text-lg">
                            Đúng <span class="font-bold text-gray-900">{{ $practiceResult['correct'] }}</span> / {{ $practiceResult['total'] }} câu
                        </p>
                        <p class="text-xs text-gray-400 mt-2">
                            Bắt đầu: {{ $practiceResult['started_at'] }} · Hoàn thành: {{ $practiceResult['completed_at'] }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Detailed Results -->
            @foreach($practiceResult['results'] as $index => $result)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4
                    @if($result['is_correct']) border-green-500 @else border-red-500 @endif">
                    <div class="p-6">
                        <!-- Question Header -->
                        <div class="flex items-center mb-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-bold text-sm mr-3
                                @if($result['is_correct']) bg-green-100 text-green-700 @else bg-red-100 text-red-700 @endif">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-sm text-gray-500">Câu {{ $result['question_number'] }}</span>
                            @if($result['is_correct'])
                                <span class="ml-2 text-xs font-semibold text-green-600">✓ Đúng</span>
                            @else
                                <span class="ml-2 text-xs font-semibold text-red-600">✗ Sai</span>
                            @endif
                        </div>

                        <!-- Audio -->
                        @if($result['group'] && $result['group']['audio_path'])
                            <div class="mb-3">
                                <audio controls class="w-full h-10">
                                    <source src="{{ Storage::url($result['group']['audio_path']) }}" type="audio/mpeg">
                                </audio>
                            </div>
                        @endif

                        <!-- Image -->
                        @if($result['group'] && $result['group']['image_path'])
                            <div class="mb-3">
                                <img src="{{ Storage::url($result['group']['image_path']) }}" alt="Question image" class="max-w-sm rounded-lg border">
                            </div>
                        @endif

                        <!-- Passage -->
                        @if($result['group'] && $result['group']['passage'])
                            <div class="mb-3 p-3 bg-gray-50 rounded-lg border-l-4 border-indigo-300">
                                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $result['group']['passage'] }}</p>
                            </div>
                        @endif

                        <!-- Question Content -->
                        @if($result['content'])
                            <p class="text-gray-900 font-medium mb-3">{{ $result['content'] }}</p>
                        @endif

                        <!-- Answers with correct/wrong highlighting -->
                        <div class="space-y-2">
                            @foreach($result['answers'] as $answer)
                                @php
                                    $isSelected = $result['selected_answer_id'] == $answer['id'];
                                    $isCorrectAnswer = $answer['is_correct'];
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
                                            {{ $answer['label'] }}.
                                        </span>
                                        <span class="@if($isCorrectAnswer) text-green-700 @elseif($isSelected) text-red-700 @else text-gray-600 @endif">
                                            {{ $answer['content'] }}
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
                        @if($result['explanation'])
                            <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                                <p class="text-xs font-semibold text-blue-700 mb-1">💡 Giải thích:</p>
                                <p class="text-sm text-blue-800">{{ $result['explanation'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Actions -->
            <div class="flex justify-center space-x-4">
                <a href="{{ route('practice.index') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 transition duration-150">
                    Luyện tập tiếp
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
