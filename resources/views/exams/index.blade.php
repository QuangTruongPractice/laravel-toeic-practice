<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Thi thử TOEIC Full Test') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-md shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form method="GET" action="{{ route('exams.index') }}" class="mb-6">
                <label for="q" class="sr-only">Tìm đề thi</label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input
                        id="q"
                        name="q"
                        type="search"
                        value="{{ old('q', $query ?? '') }}"
                        placeholder="Tìm đề thi theo tên, mô tả hoặc năm..."
                        class="min-w-0 flex-1 rounded-md border border-gray-300 bg-white py-3 px-4 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    >
                    <button type="submit" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        Tìm kiếm
                    </button>
                </div>
            </form>

            @if(!empty($query))
                <div class="mb-4 text-sm text-gray-600">
                    Kết quả tìm kiếm cho "<strong>{{ e($query) }}</strong>": {{ $exams->count() }} đề thi
                </div>
            @endif

            @if($exams->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
                    @if(!empty($query))
                        <p class="text-gray-500 text-lg mb-4">Không tìm thấy đề thi nào với từ khóa "{{ e($query) }}".</p>
                        <p class="text-sm text-gray-400">Thử điều chỉnh lại từ khóa tìm kiếm hoặc để trống để xem tất cả đề thi.</p>
                    @else
                        <p class="text-gray-500 text-lg mb-4">Hiện tại chưa có đề thi nào được kích hoạt.</p>
                        <p class="text-sm text-gray-400">Vui lòng đăng nhập bằng tài khoản Admin để thêm đề thi mới.</p>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($exams as $exam)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="font-bold text-lg text-gray-900 mb-1">{{ $exam->title }}</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            Năm {{ $exam->year }}
                                        </span>
                                    </div>
                                    <span class="text-sm text-gray-500 font-medium">
                                        ⏱️ {{ $exam->duration_minutes }} phút
                                    </span>
                                </div>

                                <p class="text-sm text-gray-600 mb-6 line-clamp-3">
                                    {{ $exam->description ?: 'Luyện tập kỹ năng làm đề TOEIC 200 câu hỏi chuẩn format mới nhất.' }}
                                </p>

                                <a href="{{ route('exams.show', $exam) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition duration-150">
                                    Chi tiết đề thi
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
