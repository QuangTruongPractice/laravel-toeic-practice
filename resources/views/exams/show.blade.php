<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $exam->title }}
            </h2>
            <a href="{{ route('exams.index') }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                ← Quay lại danh sách
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('error'))
                <div class="p-4 bg-red-100 text-red-700 rounded-md shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Exam Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="p-4 bg-indigo-50 rounded-lg text-center border border-indigo-100">
                            <span class="text-xs font-semibold text-indigo-600 uppercase tracking-widest block mb-1">Thời gian</span>
                            <span class="text-2xl font-bold text-indigo-900">{{ $exam->duration_minutes }} phút</span>
                        </div>
                        <div class="p-4 bg-emerald-50 rounded-lg text-center border border-emerald-100">
                            <span class="text-xs font-semibold text-emerald-600 uppercase tracking-widest block mb-1">Số câu hỏi</span>
                            <span class="text-2xl font-bold text-emerald-900">{{ $totalQuestions }} câu</span>
                        </div>
                        <div class="p-4 bg-amber-50 rounded-lg text-center border border-amber-100">
                            <span class="text-xs font-semibold text-amber-600 uppercase tracking-widest block mb-1">Năm phát hành</span>
                            <span class="text-2xl font-bold text-amber-900">{{ $exam->year }}</span>
                        </div>
                    </div>

                    <div class="prose max-w-none text-gray-600 mb-8">
                        <h4 class="font-semibold text-gray-900 mb-2">Mô tả đề thi:</h4>
                        <p class="whitespace-pre-line">{{ $exam->description ?: 'Luyện tập thi thử TOEIC chuẩn format. Đề thi bao gồm phần Listening (Part 1-4) và Reading (Part 5-7).' }}</p>
                    </div>

                    <div class="border-t pt-6 flex justify-center">
                        <form action="{{ route('exams.start', $exam) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-950 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-md">
                                Bắt đầu làm bài thi 🚀
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- History attempts -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="font-bold text-lg text-gray-900 mb-4">Lịch sử làm bài của bạn</h3>

                    @if($attempts->isEmpty())
                        <p class="text-gray-500 text-sm text-center py-6">Bạn chưa từng làm đề thi này. Hãy thử sức ngay!</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày thi</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Số câu đúng</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Điểm Listening</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Điểm Reading</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng điểm</th>
                                        <th scope="col" class="relative px-6 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($attempts as $attempt)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $attempt->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                                @if($attempt->isCompleted())
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Hoàn thành</span>
                                                @elseif($attempt->isInProgress())
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Đang làm</span>
                                                @else
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Đã hủy</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                                @if($attempt->isCompleted())
                                                    {{ $attempt->total_correct }} / {{ $attempt->total_questions }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-indigo-600">
                                                {{ $attempt->isCompleted() ? $attempt->listening_score : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-emerald-600">
                                                {{ $attempt->isCompleted() ? $attempt->reading_score : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900">
                                                {{ $attempt->isCompleted() ? $attempt->total_score : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                @if($attempt->isCompleted())
                                                    <a href="{{ route('exams.attempt.result', $attempt) }}" class="text-indigo-600 hover:text-indigo-900">Xem chi tiết</a>
                                                @elseif($attempt->isInProgress())
                                                    <a href="{{ route('exams.take', $exam) }}" class="text-yellow-600 hover:text-yellow-900 font-semibold">Tiếp tục thi</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
