<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Quản lý câu hỏi:') }} {{ $exam->title }}
            </h2>
            <a href="{{ route('admin.exams.show', $exam) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                &larr; Xem thông tin đề
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-green-100 text-green-700 rounded-md shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @foreach($parts as $part)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center border-b pb-3 mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-950">Part {{ $part->part_number }}: {{ $part->name }}</h3>
                                <p class="text-xs text-gray-500">{{ $part->description }}</p>
                            </div>
                            <a href="{{ route('admin.exams.question-groups.create', [$exam, $part]) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition duration-150">
                                + Thêm Nhóm câu hỏi
                            </a>
                        </div>

                        @php
                            $groups = $questionGroups->get($part->id) ?? collect();
                        @endphp

                        @if($groups->isEmpty())
                            <div class="text-center py-4 text-sm text-gray-500">
                                Chưa có câu hỏi nào cho Part này.
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($groups as $group)
                                    <div class="border rounded-lg p-4 bg-gray-50/50">
                                        <div class="flex justify-between items-start mb-3">
                                            <div class="space-y-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-800">
                                                    Nhóm #{{ $group->order_number }}
                                                </span>
                                                @if($group->audio_path)
                                                    <audio controls class="mt-2 h-8 w-full max-w-md">
                                                        <source src="{{ Storage::url($group->audio_path) }}" type="audio/mpeg">
                                                    </audio>
                                                @endif
                                                @if($group->image_path)
                                                    <img src="{{ Storage::url($group->image_path) }}" alt="Image" class="mt-2 w-64">
                                                @endif
                                                @if($group->passage)
                                                    <div class="text-xs text-gray-600 line-clamp-1 italic">📄 Passage: "{{ Str::limit($group->passage, 60) }}"</div>
                                                @endif
                                            </div>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.exams.question-groups.edit', [$exam, $group]) }}" class="text-xs text-yellow-600 hover:underline">Sửa</a>
                                                <form action="{{ route('admin.exams.question-groups.destroy', [$exam, $group]) }}" method="POST" onsubmit="return confirm('Xóa nhóm câu hỏi này sẽ xóa sạch tất cả câu hỏi và đáp án đi kèm. Tiếp tục?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs text-red-600 hover:underline">Xóa</button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Questions List inside Group -->
                                        <div class="ml-4 pl-4 border-l-2 border-indigo-100 space-y-3">
                                            @foreach($group->questions as $question)
                                                <div>
                                                    <div class="text-sm font-semibold text-gray-900">
                                                        Câu {{ $question->question_number }}: {{ $question->content ?? '(Không có tiêu đề/Nội dung nghe)' }}
                                                    </div>
                                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-1.5 ml-2">
                                                        @foreach($question->answers as $answer)
                                                            <div class="text-xs @if($answer->is_correct) font-bold text-green-700 @else text-gray-600 @endif">
                                                                {{ $answer->label }}. {{ $answer->content }}
                                                                @if($answer->is_correct)
                                                                    <span class="text-xs text-green-600">✓</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    @if($question->explanation)
                                                        <div class="text-xs text-gray-500 mt-1 italic ml-2">
                                                            Giải thích: {{ $question->explanation }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
