<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Chi tiết đề thi: ') }} {{ $exam->title }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('admin.exams.edit', $exam) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 transition ease-in-out duration-150">
                    Chỉnh sửa đề
                </a>
                <a href="{{ route('admin.exams.index') }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                    &larr; Quay lại danh sách
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Exam Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold border-b pb-2 mb-4 text-gray-700">Thông tin chung</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <span class="text-xs text-gray-500 block uppercase font-semibold">Năm</span>
                            <span class="text-sm font-medium text-gray-900">{{ $exam->year }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 block uppercase font-semibold">Thời gian làm bài</span>
                            <span class="text-sm font-medium text-gray-900">{{ $exam->duration_minutes }} phút</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 block uppercase font-semibold">Trạng thái</span>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($exam->status === 'published') bg-green-100 text-green-800 
                                @elseif($exam->status === 'draft') bg-yellow-100 text-yellow-800 
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($exam->status) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 block uppercase font-semibold">Đường dẫn tĩnh (Slug)</span>
                            <span class="text-sm text-gray-600 font-mono">{{ $exam->slug }}</span>
                        </div>
                    </div>
                    @if($exam->description)
                        <div class="mt-4">
                            <span class="text-xs text-gray-500 block uppercase font-semibold">Mô tả</span>
                            <p class="text-sm text-gray-700 mt-1">{{ $exam->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Question Groups Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Cấu trúc câu hỏi</h3>
                        <a href="{{ route('admin.exams.questions.index', $exam) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition ease-in-out duration-150">
                            Quản lý câu hỏi (Part 1 - 7)
                        </a>
                    </div>
                    <div class="text-gray-600">
                        Nhấp vào nút trên để quản lý các câu hỏi, đoạn văn nghe/đọc, hình ảnh và tệp âm thanh tương ứng với từng phần thi (Part 1 - Part 7) của đề thi này.
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
