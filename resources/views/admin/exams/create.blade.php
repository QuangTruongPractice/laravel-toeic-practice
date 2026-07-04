<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tạo đề thi TOEIC mới') }}
            </h2>
            <a href="{{ route('admin.exams.index') }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                &larr; Quay lại danh sách
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-indigo-900">Muốn thêm đề thi tự động?</p>
                            <p class="text-sm text-indigo-700">Bạn có thể dùng trang import để xử lý đề thi từ file/đường dẫn.</p>
                        </div>
                        <a href="{{ route('admin.imports.index') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            ➜ Đi tới trang import đề
                        </a>
                    </div>

                    <form action="{{ route('admin.exams.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Tiêu đề đề thi</label>
                            <input type="text" name="title" id="title" required value="{{ old('title') }}" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700">Slug (Đường dẫn tĩnh - Để trống sẽ tự tạo)</label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug') }}" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Mô tả</label>
                            <textarea name="description" id="description" rows="3" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="year" class="block text-sm font-medium text-gray-700">Năm đề thi</label>
                                <input type="number" name="year" id="year" required value="{{ old('year', date('Y')) }}" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('year')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="duration_minutes" class="block text-sm font-medium text-gray-700">Thời gian làm bài (Phút)</label>
                                <input type="number" name="duration_minutes" id="duration_minutes" required value="{{ old('duration_minutes', 120) }}" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('duration_minutes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Trạng thái</label>
                                <select name="status" id="status" required 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Nháp (Draft)</option>
                                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Xuất bản (Published)</option>
                                    <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Lưu trữ (Archived)</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Lưu đề thi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
