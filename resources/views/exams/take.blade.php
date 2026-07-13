<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Đang thi: {{ $exam->title }}
            </h2>
            <div class="flex items-center space-x-4">
                <button type="button" onclick="toggleNavPanel()" class="flex items-center space-x-2 bg-white border border-gray-300 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 shadow-sm transition cursor-pointer">
                    <span>🗺️ Bản đồ câu hỏi</span>
                </button>
                <div class="flex items-center space-x-4 bg-indigo-50 border border-indigo-200 px-4 py-2 rounded-lg">
                    <span class="text-sm font-semibold text-indigo-700">⏱️ Thời gian còn lại: </span>
                    <span id="timer" class="ml-1 font-mono text-xl font-bold text-indigo-900">--:--</span>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $durationSeconds = $exam->duration_minutes * 60;
        $remainingSeconds = $attempt->getRemainingTimeSeconds($durationSeconds);
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form id="examForm" action="{{ route('exams.submit', $exam) }}" method="POST">
                @csrf
                <input type="hidden" name="time_spent_seconds" id="time_spent_seconds" value="0">
                
                <div class="flex flex-col lg:flex-row gap-6 relative">
                    <!-- Left: Questions (Slide show mode by Group) -->
                    <div id="left-panel" class="w-full lg:w-3/4 transition-all duration-300 space-y-6">
                        <!-- Dynamic Part Header -->
                        <div class="bg-indigo-50 p-4 rounded-lg shadow-sm border border-indigo-150">
                            <h3 class="text-md font-bold text-indigo-900">
                                <span id="active-part-label">Part 1</span>: <span id="active-part-name">Photographs</span>
                            </h3>
                            <p id="active-part-desc" class="text-xs text-indigo-700 mt-1">
                                Listen and select the correct answer.
                            </p>
                        </div>

                        <!-- Slides Container -->
                        <div class="relative">
                            @php 
                                $groupSeq = 1; 
                                $questionSeq = 1;
                            @endphp
                            
                            @foreach($questionGroups->groupBy('part_id') as $partId => $groupsInPart)
                                @php $part = $groupsInPart->first()->part; @endphp

                                @foreach($groupsInPart as $group)
                                    @php 
                                        $groupQuestionsCount = count($group->questions);
                                        $startQSeq = $questionSeq;
                                        $endQSeq = $questionSeq + $groupQuestionsCount - 1;
                                    @endphp
                                    <div id="slide-{{ $groupSeq }}" 
                                         class="question-slide bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100"
                                         style="display: none; grid-template-columns: 1fr 1.2fr; gap: 1.5rem; align-items: start;"
                                         data-group-seq="{{ $groupSeq }}"
                                         data-start-q="{{ $startQSeq }}"
                                         data-end-q="{{ $endQSeq }}"
                                         data-part-num="{{ $part->part_number }}"
                                         data-part-name="{{ $part->name }}"
                                         data-part-desc="{{ $part->description }}">
                                        
                                        <!-- Left Column: Image (if present, else empty spacing) -->
                                        <div class="flex justify-center items-start min-h-[100px] border border-dashed border-gray-200 rounded-lg p-2 bg-gray-50/50">
                                            @if($group->image_url)
                                                <img src="{{ $group->image_url }}" alt="Hình ảnh câu hỏi" class="w-full rounded-lg border shadow-sm max-h-[400px] object-contain">
                                            @else
                                                <span class="text-xs text-gray-400 self-center">Không có hình ảnh</span>
                                            @endif
                                        </div>

                                        <!-- Right Column: Audio (hidden), Passage, and Question(s) -->
                                        <div class="space-y-4">
                                            <!-- Hidden Audio player for sequential auto-play -->
                                            @if($group->audio_url)
                                                <audio id="audio-slide-{{ $groupSeq }}" class="slide-audio" data-group-seq="{{ $groupSeq }}" style="display: none;">
                                                    <source src="{{ $group->audio_url }}" type="audio/mpeg">
                                                </audio>
                                            @endif

                                            <!-- Passage -->
                                            @if($group->passage)
                                                <div class="p-4 bg-gray-50 border-l-4 border-indigo-500 rounded-r-lg whitespace-pre-line text-sm text-gray-700 leading-relaxed font-serif">
                                                    {{ $group->passage }}
                                                </div>
                                            @endif

                                            <div class="space-y-6">
                                                @foreach($group->questions as $question)
                                                    <div id="q-{{ $question->id }}" class="question-block {{ !$loop->first ? 'border-t pt-4' : '' }}">
                                                        <div class="flex items-start justify-between mb-2">
                                                            <div class="flex items-start">
                                                                <span class="inline-flex items-center justify-center bg-gray-200 text-gray-800 font-bold text-xs w-6 h-6 rounded-full mr-2 mt-0.5">
                                                                    {{ $questionSeq }}
                                                                </span>
                                                                <p class="text-gray-900 font-medium pt-0.5">
                                                                    @if($part->part_number == 1 || $part->part_number == 2)
                                                                        (Chọn đáp án đúng)
                                                                    @else
                                                                        {{ $question->content ?: '(Chọn đáp án đúng)' }}
                                                                    @endif
                                                                </p>
                                                            </div>
                                                            <!-- Flag Button -->
                                                            <button type="button" 
                                                                    onclick="toggleFlag({{ $questionSeq }})"
                                                                    id="flag-btn-{{ $questionSeq }}"
                                                                    class="ml-2 text-gray-400 hover:text-red-500 transition duration-150 cursor-pointer flex items-center gap-1 text-xs"
                                                                    title="Đánh dấu câu hỏi này">
                                                                <span class="flag-icon">🏳️</span>
                                                            </button>
                                                        </div>

                                                        <!-- Answers -->
                                                        <div class="grid grid-cols-1 gap-3 mt-3">
                                                            @foreach($question->answers as $answer)
                                                                <label class="flex items-center p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition duration-150">
                                                                    <input type="radio" 
                                                                           name="answers[{{ $question->id }}]" 
                                                                           value="{{ $answer->id }}"
                                                                           data-q-id="{{ $question->id }}"
                                                                           data-q-seq="{{ $questionSeq }}"
                                                                           class="answer-radio h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                                                           @if(isset($savedAnswers[$question->id]) && $savedAnswers[$question->id] == $answer->id) checked @endif>
                                                                    <span class="ml-3 text-sm text-gray-700">
                                                                        <span class="font-bold mr-1 ml-2">{{ $answer->label }}.</span>
                                                                        @if($part->part_number != 1 && $part->part_number != 2)
                                                                            {{ $answer->content }}
                                                                        @endif
                                                                    </span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    @php $questionSeq++; @endphp
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @php $groupSeq++; @endphp
                                @endforeach
                            @endforeach
                        </div>

                        <!-- Navigation controls under active slide -->
                        <div class="flex justify-between items-center bg-white p-4 rounded-lg border shadow-sm">
                            <button type="button" onclick="changeSlide(-1)" id="prev-btn" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg text-sm transition cursor-pointer">
                                &larr; Câu trước
                            </button>
                            <span class="text-sm font-semibold text-gray-500">
                                Câu <span id="current-question-range" class="text-indigo-600 font-bold">1</span> / {{ $questionSeq - 1 }}
                            </span>
                            <button type="button" onclick="changeSlide(1)" id="next-btn" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition cursor-pointer">
                                Câu sau &rarr;
                            </button>
                        </div>
                    </div>

                    <!-- Right: Navigation Panel (Shown by default, toggleable) -->
                    <div id="nav-panel" class="w-full lg:w-1/4 transition-all duration-300">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 sticky top-6 max-h-[calc(100vh-8rem)] flex flex-col">
                            <h3 class="font-bold text-gray-900 text-md mb-3 pb-2 border-b">Bản đồ câu hỏi</h3>
                            
                            <div class="overflow-y-auto flex-grow pr-1 mb-4" style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 0.375rem; max-height: 380px;">
                                @for($i = 1; $i < $questionSeq; $i++)
                                    <button type="button" 
                                            id="nav-btn-{{ $i }}"
                                            onclick="jumpToQuestion({{ $i }})"
                                            class="nav-dot-btn text-xs font-bold rounded transition"
                                            style="width: 100%; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; border: 1px solid #e5e7eb; background-color: #f9fafb; color: #4b5563; cursor: pointer;">
                                        {{ $i }}
                                    </button>
                                @endfor
                            </div>

                            <div class="space-y-3 border-t pt-4">
                                <div class="flex justify-between text-xs text-gray-500">
                                    <span>Đã làm: <strong id="answered-count" class="text-indigo-600">0</strong>/{{ $questionSeq - 1 }}</span>
                                </div>
                                <button type="button" 
                                        onclick="confirmSubmit()"
                                        class="w-full inline-flex justify-center items-center px-4 py-3 bg-red-600 border border-transparent rounded-md font-bold text-sm text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 transition duration-150 shadow-sm cursor-pointer">
                                    Nộp bài thi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Modal for Submission Confirmation -->
    <div id="confirmModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        ⚠️
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Xác nhận nộp bài</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Bạn có chắc chắn muốn nộp bài không? Hãy kiểm tra kỹ tất cả các câu trả lời trước khi nộp.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="button" 
                            onclick="submitForm()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:col-start-2 sm:text-sm">
                        Đồng ý nộp bài
                    </button>
                    <button type="button" 
                            onclick="closeModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Autoplay Blocked Overlay -->
    <div id="autoplay-overlay" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white rounded-lg px-6 py-6 text-center overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full space-y-4">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 text-3xl">
                    🔊
                </div>
                <h3 class="text-lg leading-6 font-bold text-gray-900">Bắt đầu nghe bài thi</h3>
                <p class="text-sm text-gray-500">
                    Trình duyệt yêu cầu một tương tác để có thể tự động phát âm thanh bài nghe của bạn.
                </p>
                <button type="button" 
                        id="autoplay-btn"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-md px-4 py-3 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:text-sm cursor-pointer transition">
                    Bắt đầu phát âm thanh
                </button>
            </div>
        </div>
    </div>

    <script>
        const durationSeconds = {{ $durationSeconds }};
        const timerStorageKey = 'exam-timer-{{ $attempt->id }}';
        const currentGroupStorageKey = 'exam-current-group-{{ $attempt->id }}';
        const answersStorageKey = 'exam-answers-{{ $attempt->id }}';
        const startedAtTimestamp = {{ $attempt->started_at ? $attempt->started_at->getTimestamp() * 1000 : 0 }};
        const timerElement = document.getElementById('timer');
        const timeSpentInput = document.getElementById('time_spent_seconds');
        let deadlineTimestamp = parseInt(localStorage.getItem(timerStorageKey + '-deadline') || '0');
        let isSubmitting = false;

        if (!deadlineTimestamp) {
            deadlineTimestamp = startedAtTimestamp + (durationSeconds * 1000);
            localStorage.setItem(timerStorageKey + '-deadline', String(deadlineTimestamp));
        }

        let remainingSeconds = Math.max(0, Math.floor((deadlineTimestamp - Date.now()) / 1000));
        let elapsedSeconds = Math.min(durationSeconds, Math.floor((Date.now() - startedAtTimestamp) / 1000));

        function updateTimer() {
            if (isSubmitting) {
                return;
            }

            const now = Date.now();
            remainingSeconds = Math.max(0, Math.floor((deadlineTimestamp - now) / 1000));
            elapsedSeconds = Math.min(durationSeconds, Math.floor((now - startedAtTimestamp) / 1000));

            if (remainingSeconds <= 0) {
                timerElement.innerText = '00:00';
                timeSpentInput.value = String(durationSeconds);
                localStorage.removeItem(timerStorageKey + '-deadline');
                isSubmitting = true;
                submitForm();
                return;
            }

            const minutes = Math.floor(remainingSeconds / 60);
            const seconds = remainingSeconds % 60;

            timerElement.innerText =
                (minutes < 10 ? '0' + minutes : minutes) + ':' +
                (seconds < 10 ? '0' + seconds : seconds);

            timeSpentInput.value = String(elapsedSeconds);
        }

        window.setInterval(updateTimer, 1000);
        updateTimer();

        // Paging Slide State (Group-level paging)
        let currentGroupSeq = parseInt(localStorage.getItem(currentGroupStorageKey) || '1');
        const totalGroups = {{ $groupSeq - 1 }};
        let activeAudio = null;
        let isListeningMode = true;
        const flaggedQuestions = new Set();

        function toggleFlag(qSeq) {
            const btn = document.getElementById('flag-btn-' + qSeq);
            const icon = btn.querySelector('.flag-icon');
            const navBtn = document.getElementById('nav-btn-' + qSeq);
            
            if (flaggedQuestions.has(qSeq)) {
                flaggedQuestions.delete(qSeq);
                icon.innerText = '🏳️';
                btn.classList.remove('text-red-500');
                btn.classList.add('text-gray-400');
            } else {
                flaggedQuestions.add(qSeq);
                icon.innerText = '🚩';
                btn.classList.remove('text-gray-400');
                btn.classList.add('text-red-500');
            }
            updateAnswersCount();
        }

        function showSlide(groupSeq) {
            // Validate group sequence bounds
            if (groupSeq < 1) groupSeq = 1;
            if (groupSeq > totalGroups) groupSeq = totalGroups;
            currentGroupSeq = groupSeq;
            localStorage.setItem(currentGroupStorageKey, String(currentGroupSeq));

            // Stop any currently playing audio
            if (activeAudio) {
                activeAudio.pause();
                activeAudio.currentTime = 0;
                activeAudio = null;
            }

            // Hide all slides using inline style
            document.querySelectorAll('.question-slide').forEach(slide => {
                slide.style.display = 'none';
            });

            // Show active slide using inline style (grid)
            const activeSlide = document.getElementById('slide-' + groupSeq);
            if (activeSlide) {
                activeSlide.style.display = 'grid';

                // Update Dynamic Part Header
                const partNum = parseInt(activeSlide.getAttribute('data-part-num'));
                const partName = activeSlide.getAttribute('data-part-name');
                const partDesc = activeSlide.getAttribute('data-part-desc');
                
                document.getElementById('active-part-label').innerText = 'Part ' + partNum;
                document.getElementById('active-part-name').innerText = partName;
                document.getElementById('active-part-desc').innerText = partDesc;

                // Update Question Range Label
                const startQ = parseInt(activeSlide.getAttribute('data-start-q'));
                const endQ = parseInt(activeSlide.getAttribute('data-end-q'));
                if (startQ === endQ) {
                    document.getElementById('current-question-range').innerText = startQ;
                } else {
                    document.getElementById('current-question-range').innerText = startQ + ' - ' + endQ;
                }

                // Manage controls depending on Listening vs Reading
                if (partNum <= 4) {
                    isListeningMode = true;
                    document.getElementById('prev-btn').style.display = 'none';
                    document.getElementById('next-btn').style.display = 'none';
                } else {
                    isListeningMode = false;
                    document.getElementById('prev-btn').style.display = 'inline-flex';
                    document.getElementById('next-btn').style.display = 'inline-flex';
                    
                    // Update navigation buttons status
                    document.getElementById('prev-btn').disabled = (groupSeq === 1);
                    document.getElementById('prev-btn').style.opacity = (groupSeq === 1) ? '0.5' : '1';
                    document.getElementById('next-btn').innerText = (groupSeq === totalGroups) ? 'Nộp bài' : 'Câu sau →';
                }

                // Play audio if present
                const audio = activeSlide.querySelector('.slide-audio');
                if (audio) {
                    activeAudio = audio;
                    
                    // Force refresh audio load state on refresh/slide load to guarantee replay
                    audio.load();

                    audio.play().then(() => {
                        audio.onended = function() {
                            // Automatically transition to the next group slide when audio finishes
                            if (currentGroupSeq < totalGroups) {
                                // Temporarily bypass listening mode check to allow automatic transition
                                isListeningMode = false;
                                changeSlide(1);
                                isListeningMode = (parseInt(document.getElementById('slide-' + currentGroupSeq).getAttribute('data-part-num')) <= 4);
                            }
                        };
                    }).catch(err => {
                        console.log("Audio autoplay blocked by browser policy. Showing overlay.", err);
                        const overlay = document.getElementById('autoplay-overlay');
                        if (overlay) {
                            overlay.classList.remove('hidden');
                            const btn = document.getElementById('autoplay-btn');
                            btn.onclick = function() {
                                audio.play().then(() => {
                                    overlay.classList.add('hidden');
                                    audio.onended = function() {
                                        if (currentGroupSeq < totalGroups) {
                                            isListeningMode = false;
                                            changeSlide(1);
                                            isListeningMode = (parseInt(document.getElementById('slide-' + currentGroupSeq).getAttribute('data-part-num')) <= 4);
                                        }
                                    };
                                }).catch(e => console.log("Play failed on overlay click: ", e));
                            };
                        }
                    });
                }

                // Highlight corresponding nav buttons in the map
                document.querySelectorAll('.nav-dot-btn').forEach(btn => {
                    btn.style.outline = 'none';
                });
                for (let q = startQ; q <= endQ; q++) {
                    const navBtn = document.getElementById('nav-btn-' + q);
                    if (navBtn) {
                        navBtn.style.outline = '3px solid #4f46e5';
                    }
                }
            }
        }

        function changeSlide(offset) {
            if (isListeningMode) {
                return; // Do not allow manual navigation in listening mode
            }
            const nextGroupSeq = currentGroupSeq + offset;
            if (nextGroupSeq > totalGroups) {
                confirmSubmit();
            } else {
                showSlide(nextGroupSeq);
            }
        }

        function jumpToQuestion(qSeq) {
            if (isListeningMode) {
                alert("Bạn không thể chuyển đổi câu hỏi thủ công trong khi đang làm phần thi nghe (Listening)!");
                return;
            }
            // Find which group slide contains this question sequence
            const slides = document.querySelectorAll('.question-slide');
            for (let slide of slides) {
                const startQ = parseInt(slide.getAttribute('data-start-q'));
                const endQ = parseInt(slide.getAttribute('data-end-q'));
                const groupSeq = parseInt(slide.getAttribute('data-group-seq'));
                
                if (qSeq >= startQ && qSeq <= endQ) {
                    showSlide(groupSeq);
                    break;
                }
            }
        }

        function toggleNavPanel() {
            const panel = document.getElementById('nav-panel');
            const leftPanel = document.getElementById('left-panel');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                leftPanel.classList.remove('w-full');
                leftPanel.classList.add('lg:w-3/4');
            } else {
                panel.classList.add('hidden');
                leftPanel.classList.remove('lg:w-3/4');
                leftPanel.classList.add('w-full');
            }
        }

        // Update navigation side dots on load/check
        function updateAnswersCount() {
            let radios = document.querySelectorAll('.answer-radio');
            let answeredQIds = new Set();
            
            // First reset all buttons to default style
            document.querySelectorAll('.nav-dot-btn').forEach(btn => {
                const seq = parseInt(btn.id.replace('nav-btn-', ''));
                if (flaggedQuestions.has(seq)) {
                    btn.style.backgroundColor = '#fee2e2'; // Light red/pink
                    btn.style.color = '#ef4444'; // Red text
                    btn.style.borderColor = '#fca5a5';
                } else {
                    btn.style.backgroundColor = '#f9fafb';
                    btn.style.color = '#4b5563';
                    btn.style.borderColor = '#e5e7eb';
                }
            });
            
            radios.forEach(radio => {
                let seq = parseInt(radio.getAttribute('data-q-seq'));
                let navBtn = document.getElementById('nav-btn-' + seq);
                if (radio.checked) {
                    answeredQIds.add(radio.getAttribute('data-q-id'));
                    if (navBtn) {
                        if (flaggedQuestions.has(seq)) {
                            // If flagged and answered
                            navBtn.style.backgroundColor = '#ef4444'; // Solid red
                            navBtn.style.color = '#ffffff';
                            navBtn.style.borderColor = '#b91c1c';
                        } else {
                            navBtn.style.backgroundColor = '#4f46e5';
                            navBtn.style.color = '#ffffff';
                            navBtn.style.borderColor = '#4338ca';
                        }
                    }
                }
            });

            document.getElementById('answered-count').innerText = answeredQIds.size;
        }

        function saveAnswers() {
            const answers = {};
            document.querySelectorAll('.answer-radio:checked').forEach(radio => {
                answers[radio.getAttribute('data-q-id')] = radio.value;
            });
            localStorage.setItem(answersStorageKey, JSON.stringify(answers));
        }

        function restoreAnswers() {
            const savedAnswers = localStorage.getItem(answersStorageKey);
            if (!savedAnswers) {
                return;
            }

            try {
                const parsedAnswers = JSON.parse(savedAnswers);
                document.querySelectorAll('.answer-radio').forEach(radio => {
                    const qId = radio.getAttribute('data-q-id');
                    if (parsedAnswers[qId] && radio.value === parsedAnswers[qId]) {
                        radio.checked = true;
                    }
                });
            } catch (e) {
                console.log('Failed to restore answers', e);
            }
        }

        document.querySelectorAll('.answer-radio').forEach(radio => {
            radio.addEventListener('change', () => {
                saveAnswers();
                updateAnswersCount();
            });
        });

        // Initialize first slide and counts on page load
        document.addEventListener('DOMContentLoaded', () => {
            restoreAnswers();
            showSlide(currentGroupSeq);
            updateAnswersCount();
        });

        // Modal triggers
        function confirmSubmit() {
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('confirmModal').classList.add('hidden');
        }

        function submitForm() {
            saveAnswers();
            localStorage.removeItem(timerStorageKey + '-deadline');
            localStorage.removeItem(currentGroupStorageKey);
            localStorage.removeItem(answersStorageKey);
            document.getElementById('examForm').submit();
        }
    </script>
</x-app-layout>
