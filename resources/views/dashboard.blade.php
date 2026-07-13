<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Bảng điều khiển học tập') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('practice.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    Luyện tập Part
                </a>
                <a href="{{ route('exams.index') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:border-emerald-900 focus:ring ring-emerald-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    Thi thử Full Test
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Statistics Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Total Attempts Card -->
                <div class="bg-white overflow-hidden shadow-md sm:rounded-2xl border border-gray-100 p-6 flex items-center space-x-5 transition-all duration-300 hover:shadow-lg">
                    <div class="p-3.5 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <div class="ml-2">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Bài thi đã làm</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalAttempts }}</p>
                    </div>
                </div>

                <!-- Avg TOEIC Score Card -->
                <div class="bg-white overflow-hidden shadow-md sm:rounded-2xl border border-gray-100 p-6 flex items-center space-x-5 transition-all duration-300 hover:shadow-lg">
                    <div class="p-3.5 bg-indigo-50 text-indigo-600 rounded-xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div class="ml-2">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Điểm TB (Composer)</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ $avgTotalScore }} <span class="text-xs text-gray-400 font-normal">/ 990</span>
                        </p>
                        @if($totalAttempts > 0)
                            <p class="text-xs text-gray-500 mt-0.5">LC: {{ $avgListeningScore }} | RC: {{ $avgReadingScore }}</p>
                        @endif
                    </div>
                </div>

                <!-- User Accessor Score Card -->
                <div class="bg-white overflow-hidden shadow-md sm:rounded-2xl border border-gray-100 p-6 flex items-center space-x-5 transition-all duration-300 hover:shadow-lg">
                    <div class="p-3.5 bg-violet-50 text-violet-600 rounded-xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="ml-2">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Điểm TB (Accessor)</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ Auth::user()->average_score }} <span class="text-xs text-gray-400 font-normal">/ 990</span>
                        </p>
                    </div>
                </div>

                <!-- Total Study Time Card -->
                <div class="bg-white overflow-hidden shadow-md sm:rounded-2xl border border-gray-100 p-6 flex items-center space-x-5 transition-all duration-300 hover:shadow-lg">
                    <div class="p-3.5 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="ml-2">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Tổng thời gian thi</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $formattedTotalTime }}</p>
                    </div>
                </div>
            </div>

            @if($totalAttempts === 0)
                <!-- Empty State -->
                <div class="bg-white overflow-hidden shadow-md sm:rounded-2xl border border-gray-100 p-12 text-center">
                    <div class="max-w-md mx-auto">
                        <div class="inline-flex p-4 bg-indigo-50 text-indigo-600 rounded-full mb-6">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Chào mừng bạn đến với TOEIC Practice Platform!</h3>
                        <p class="text-gray-500 mb-8">Bạn chưa thực hiện bài thi thử nào. Hãy làm bài thi thử đầu tiên để xem thống kê phân tích điểm chi tiết tại đây!</p>
                        <div class="flex justify-center space-x-4">
                            <a href="{{ route('exams.index') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition duration-150 shadow-sm">
                                Bắt đầu thi thử ngay
                            </a>
                            <a href="{{ route('practice.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition duration-150">
                                Luyện tập theo Part
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- Main Analytics Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Score Progress Chart (Chart.js) -->
                    <div class="bg-white overflow-hidden shadow-md sm:rounded-2xl border border-gray-100 p-6 lg:col-span-2 flex flex-col">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Tiến trình điểm số</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Biểu đồ hiển thị kết quả tối đa 10 lần thi gần nhất</p>
                            </div>
                            <span class="text-xs px-2.5 py-1 bg-gray-100 text-gray-600 font-semibold rounded-full">Line Chart</span>
                        </div>
                        <div class="relative flex-grow min-h-[300px]">
                            <canvas id="scoreHistoryChart"></canvas>
                        </div>
                    </div>

                    <!-- Strengths and Weaknesses Card -->
                    <div class="bg-white overflow-hidden shadow-md sm:rounded-2xl border border-gray-100 p-6 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Phân tích kỹ năng</h3>
                            
                            <!-- Strongest Part -->
                            @if($strongestPart)
                                <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100 mb-4">
                                    <div class="flex items-center space-x-2 text-emerald-800 font-bold text-sm mb-1.5">
                                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                                        <span>Phần mạnh nhất: Part {{ $strongestPart->part_number }}</span>
                                    </div>
                                    <p class="text-xs text-emerald-700 font-semibold mb-1">{{ $strongestPart->part_name }}</p>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-xs text-emerald-600">Tỷ lệ chính xác:</span>
                                        <span class="text-sm font-bold text-emerald-800">{{ $strongestPart->correct_rate }}%</span>
                                    </div>
                                    <div class="w-full bg-emerald-200 rounded-full h-1.5 mt-1.5">
                                        <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ $strongestPart->correct_rate }}%"></div>
                                    </div>
                                </div>
                            @endif

                            <!-- Weakest Part -->
                            @if($weakestPart)
                                <div class="p-4 bg-rose-50 rounded-xl border border-rose-100">
                                    <div class="flex items-center space-x-2 text-rose-800 font-bold text-sm mb-1.5">
                                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        <span>Cần cải thiện: Part {{ $weakestPart->part_number }}</span>
                                    </div>
                                    <p class="text-xs text-rose-700 font-semibold mb-1">{{ $weakestPart->part_name }}</p>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-xs text-rose-600">Tỷ lệ chính xác:</span>
                                        <span class="text-sm font-bold text-rose-800">{{ $weakestPart->correct_rate }}%</span>
                                    </div>
                                    <div class="w-full bg-rose-200 rounded-full h-1.5 mt-1.5">
                                        <div class="bg-rose-500 h-1.5 rounded-full" style="width: {{ $weakestPart->correct_rate }}%"></div>
                                    </div>
                                    <div class="mt-3 text-xs text-rose-600 leading-relaxed border-t border-rose-100 pt-2.5">
                                        <strong>Gợi ý ôn luyện:</strong> 
                                        @if($weakestPart->part_number == 1)
                                            Tập trung nghe kỹ danh từ và động từ miêu tả hành động trong hình ảnh. Tránh bẫy tương đồng phát âm.
                                        @elseif($weakestPart->part_number == 2)
                                            Chú ý từ để hỏi (Who, Where, When, Why, How, What). Phản xạ nhanh với câu trả lời gián tiếp.
                                        @elseif($weakestPart->part_number == 3 || $weakestPart->part_number == 4)
                                            Đọc trước câu hỏi và các lựa chọn đáp án trước khi đoạn băng phát. Tận dụng thời gian nghỉ giữa các bài nghe.
                                        @elseif($weakestPart->part_number == 5)
                                            Hệ thống lại các chủ điểm ngữ pháp cốt lõi (Tenses, Word form, Preposition, Conjunction) và từ vựng thông dụng.
                                        @elseif($weakestPart->part_number == 6)
                                            Phân tích mối quan hệ ngữ cảnh giữa các câu liền kề để điền từ hoặc chọn câu phù hợp nhất vào chỗ trống.
                                        @elseif($weakestPart->part_number == 7)
                                            Rèn luyện kỹ năng đọc lướt (Skimming) để tìm ý chính và đọc quét (Scanning) để tìm thông tin chi tiết nhanh chóng.
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($weakestPart)
                            <div class="mt-6">
                                <a href="{{ route('practice.index') }}" class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-indigo-600 text-sm font-semibold rounded-xl text-indigo-600 bg-white hover:bg-indigo-50 transition duration-150">
                                    Luyện tập Part {{ $weakestPart->part_number }} ngay
                                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Parts performance details Table -->
                <div class="bg-white overflow-hidden shadow-md sm:rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Chi tiết độ chính xác theo từng Part</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($partsPerformance as $part)
                            <div class="flex items-center justify-between p-3.5 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex items-center space-x-4">
                                    <span class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-sm bg-indigo-50 text-indigo-600">P{{ $part->part_number }}</span>
                                    <div class="ml-2">
                                        <p class="text-xs font-semibold text-gray-700 leading-tight">{{ $part->part_name }}</p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">Đúng {{ $part->correct_answers }}/{{ $part->total_answers }} câu đã làm</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm font-bold {{ $part->correct_rate >= 75 ? 'text-emerald-600' : ($part->correct_rate >= 50 ? 'text-amber-600' : 'text-rose-500') }}">{{ $part->correct_rate }}%</span>
                                    <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full {{ $part->correct_rate >= 75 ? 'bg-emerald-500' : ($part->correct_rate >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $part->correct_rate }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Recent Attempts History Table -->
                <div class="bg-white overflow-hidden shadow-md sm:rounded-2xl border border-gray-100 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-900">Lịch sử làm bài gần đây</h3>
                        <a href="{{ route('exams.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold flex items-center">
                            Xem tất cả đề thi
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                    <th class="pb-3">Đề thi</th>
                                    <th class="pb-3">Ngày làm bài</th>
                                    <th class="pb-3">Thời gian làm</th>
                                    <th class="pb-3">Số câu đúng</th>
                                    <th class="pb-3">Điểm</th>
                                    <th class="pb-3 text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                                @foreach($recentAttempts as $attempt)
                                    <tr>
                                        <td class="py-4 font-semibold text-gray-900">
                                            {{ $attempt->exam_title ?? 'N/A' }}
                                            <span class="ml-1.5 px-2 py-0.5 text-[10px] rounded-full font-bold {{ $attempt->status === 'completed' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                                                {{ $attempt->status === 'completed' ? 'Đã hoàn thành' : 'Đang làm' }}
                                            </span>
                                        </td>
                                        <td class="py-4 text-gray-500">
                                            @php
                                                try {
                                                    $createdAt = \Illuminate\Support\Carbon::parse($attempt->created_at);
                                                } catch (\Throwable $e) {
                                                    $createdAt = \Illuminate\Support\Carbon::createFromFormat('d/m/Y H:i', $attempt->created_at);
                                                }
                                            @endphp
                                            {{ $createdAt->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-4 text-gray-500">
                                            {{ $attempt->formatted_time }}
                                        </td>
                                        <td class="py-4 text-gray-500">
                                            {{ $attempt->total_correct }}/{{ $attempt->total_questions }}
                                            @if($attempt->total_questions > 0)
                                                <span class="text-xs text-gray-400">({{ $attempt->accuracy_percent }}%)</span>
                                            @endif
                                        </td>
                                        <td class="py-4 font-bold text-indigo-600">
                                            @if($attempt->status === 'completed')
                                                {{ $attempt->total_score }}
                                                <span class="text-[10px] text-gray-400 font-normal block">L: {{ $attempt->listening_score }} | R: {{ $attempt->reading_score }}</span>
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td class="py-4 text-right">
                                            @if($attempt->status === 'completed')
                                                <a href="{{ route('exams.attempt.result', $attempt->id) }}" class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors duration-150">
                                                    Chi tiết
                                                </a>
                                            @else
                                                <a href="{{ route('exams.take', $attempt->exam_id) }}" class="inline-flex items-center text-xs font-semibold text-white bg-amber-500 hover:bg-amber-600 px-3 py-1.5 rounded-lg transition-colors duration-150">
                                                    Làm tiếp
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>

    @if($totalAttempts > 0)
        <!-- Chart.js CDN and initialization -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('scoreHistoryChart').getContext('2d');
                
                const chartData = @json($chartData);
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [
                            {
                                label: 'Tổng Điểm TOEIC',
                                data: chartData.total_scores,
                                borderColor: 'rgb(79, 70, 229)',
                                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.3,
                                pointRadius: 4,
                                pointBackgroundColor: 'rgb(79, 70, 229)'
                            },
                            {
                                label: 'Điểm Listening',
                                data: chartData.listening_scores,
                                borderColor: 'rgb(14, 165, 233)',
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                borderDash: [5, 5],
                                tension: 0.3,
                                pointRadius: 3,
                                pointBackgroundColor: 'rgb(14, 165, 233)'
                            },
                            {
                                label: 'Điểm Reading',
                                data: chartData.reading_scores,
                                borderColor: 'rgb(244, 63, 94)',
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                borderDash: [5, 5],
                                tension: 0.3,
                                pointRadius: 3,
                                pointBackgroundColor: 'rgb(244, 63, 94)'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    font: {
                                        family: 'Figtree, sans-serif',
                                        weight: '600'
                                    }
                                }
                            },
                            tooltip: {
                                padding: 12,
                                cornerRadius: 8,
                                titleFont: {
                                    family: 'Figtree, sans-serif',
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    family: 'Figtree, sans-serif'
                                }
                            }
                        },
                        scales: {
                            y: {
                                min: 0,
                                max: 990,
                                ticks: {
                                    stepSize: 100,
                                    font: {
                                        family: 'Figtree, sans-serif'
                                    }
                                },
                                grid: {
                                    color: 'rgba(243, 244, 246, 1)'
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        family: 'Figtree, sans-serif'
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endif
</x-app-layout>
