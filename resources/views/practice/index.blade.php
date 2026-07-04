<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Luyện tập TOEIC theo Part') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-md shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($parts as $part)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200">
                        <div class="p-6">
                            <div class="flex items-start gap-4 mb-4">
                                <span class="flex-shrink-0 inline-flex items-center justify-center w-11 h-11 rounded-full text-sm font-bold
                                    @if($part->isListening()) bg-indigo-100 text-indigo-700 @else bg-emerald-100 text-emerald-700 @endif">
                                    {{ $part->part_number }}
                                </span>
                                <div class="min-w-0">
                                    <h3 class="text-base sm:text-lg font-bold text-gray-900 truncate">Part {{ $part->part_number }}: {{ $part->name }}</h3>
                                    <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full
                                        @if($part->isListening()) bg-indigo-50 text-indigo-600 @else bg-emerald-50 text-emerald-600 @endif">
                                        {{ ucfirst($part->section) }} · {{ $part->question_count }} câu
                                    </span>
                                </div>
                            </div>

                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $part->description }}</p>

                            <!-- Form chọn số câu và bắt đầu -->
                            <form action="{{ route('practice.start', $part) }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Số lượng câu hỏi</label>
                                    <select name="num_questions" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="5">5 câu</option>
                                        <option value="10" selected>10 câu</option>
                                        <option value="20">20 câu</option>
                                        <option value="{{ $part->question_count }}">Tất cả ({{ $part->question_count }} câu)</option>
                                    </select>
                                </div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition duration-150">
                                    Bắt đầu luyện tập
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
