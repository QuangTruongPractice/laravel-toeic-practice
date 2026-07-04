<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Chi tiết Import Đề Thi #{{ $import->id }}
            </h2>
            <a href="{{ route('admin.imports.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-semibold">
                &larr; Quay lại danh sách
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Thông tin tiến trình</h3>
                            <table class="min-w-full text-sm">
                                <tbody>
                                    <tr class="border-b">
                                        <td class="py-2 text-gray-500 font-medium w-1/3">Trạng thái</td>
                                        <td class="py-2">
                                            @if ($import->status === 'pending')
                                                <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Chờ xử lý
                                                </span>
                                            @elseif ($import->status === 'processing')
                                                <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 animate-pulse">
                                                    Đang xử lý (Parse PDF & copy file)
                                                </span>
                                            @elseif ($import->status === 'completed')
                                                <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Thành công
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Lỗi
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="py-2 text-gray-500 font-medium">Người thực hiện</td>
                                        <td class="py-2">{{ $import->admin->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="py-2 text-gray-500 font-medium">Đề thi đích</td>
                                        <td class="py-2">
                                            @if($import->exam)
                                                <a href="{{ route('admin.exams.show', $import->exam) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                    {{ $import->exam->title }}
                                                </a>
                                                <span class="text-xs text-gray-500 block mt-1">(Đang ở trạng thái DRAFT - cần review trước khi publish)</span>
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="py-2 text-gray-500 font-medium">Số câu tạo thành công</td>
                                        <td class="py-2 font-bold">{{ $import->questions_created }} / 200 câu</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-gray-500 font-medium">Ngày khởi tạo</td>
                                        <td class="py-2">{{ $import->created_at->format('H:i:s d/m/Y') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Thông tin file nguồn</h3>
                            <table class="min-w-full text-sm">
                                <tbody>
                                    <tr class="border-b">
                                        <td class="py-2 text-gray-500 font-medium w-1/3">Tệp PDF nguồn</td>
                                        <td class="py-2 text-gray-700 break-all">{{ basename($import->pdf_path) }}</td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="py-2 text-gray-500 font-medium">Nguồn Audio</td>
                                        <td class="py-2 text-gray-700 break-all">{{ $import->audio_path }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($import->status === 'failed')
                        <div class="mt-6">
                            <h3 class="text-lg font-medium text-red-900 border-b border-red-200 pb-2 mb-4">Chi tiết lỗi (Error Log)</h3>
                            <div class="bg-red-50 p-4 rounded-md border border-red-200 font-mono text-xs text-red-800 whitespace-pre-wrap max-h-96 overflow-y-auto">
                                {{ $import->error_log ?? 'Không có thông tin chi tiết.' }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
