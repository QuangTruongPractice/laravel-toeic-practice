<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import Đề Thi TOEIC (Tự Động Hóa)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded shadow-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.imports.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div>
                            <label for="test_num" class="block text-sm font-medium text-gray-700">Đề số mấy (Test Number)</label>
                            <select name="test_num" id="test_num" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('test_num') == $i ? 'selected' : '' }}>Test {{ $i }}</option>
                                @endfor
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Giúp định dạng tên file audio, hình ảnh và tạo tên đề thi (ETS 2026 Test X).</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="listening_pdf" class="block text-sm font-medium text-gray-700">File PDF Listening (SCRIPT AND KEY)</label>
                                <input type="file" name="listening_pdf" id="listening_pdf" accept=".pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                            </div>
                            <div>
                                <label for="reading_pdf" class="block text-sm font-medium text-gray-700">File PDF Reading (KEY)</label>
                                <input type="file" name="reading_pdf" id="reading_pdf" accept=".pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="audio_zip" class="block text-sm font-medium text-gray-700">Tệp ZIP chứa Audio (.zip)</label>
                                <input type="file" name="audio_zip" id="audio_zip" accept=".zip" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                                <p class="mt-1 text-xs text-gray-500">Tải lên tệp nén .zip chứa tất cả file âm thanh (.mp3) của đề thi này.</p>
                            </div>
                            <div>
                                <label for="image_zip" class="block text-sm font-medium text-gray-700">Tệp ZIP chứa Hình ảnh (.zip)</label>
                                <input type="file" name="image_zip" id="image_zip" accept=".zip" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                                <p class="mt-1 text-xs text-gray-500">Tải lên tệp nén .zip chứa tất cả hình ảnh minh họa của đề thi này.</p>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('admin.imports.index') }}" class="bg-white border border-gray-300 rounded-md py-2 px-4 inline-flex justify-center text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Quay lại
                            </a>
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Bắt đầu Parse & Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
