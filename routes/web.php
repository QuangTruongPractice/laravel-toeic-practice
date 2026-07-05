<?php

use App\Http\Controllers\Admin\ExamController as AdminExamController;
use App\Http\Controllers\Admin\ImportController as AdminImportController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController as UserExamController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Practice Mode (cho tất cả user đã đăng nhập)
Route::middleware('auth')->prefix('practice')->name('practice.')->group(function () {
    Route::get('/', [PracticeController::class, 'index'])->name('index');
    Route::post('/{part}/start', [PracticeController::class, 'start'])->name('start');
    Route::get('/session', [PracticeController::class, 'show'])->name('show');
    Route::post('/submit', [PracticeController::class, 'submit'])->name('submit');
    Route::get('/result', [PracticeController::class, 'result'])->name('result');
});

// Exam Mode (cho tất cả user đã đăng nhập)
Route::middleware('auth')->prefix('exams')->name('exams.')->group(function () {
    Route::get('/', [UserExamController::class, 'index'])->name('index');
    Route::get('/{exam}', [UserExamController::class, 'show'])->name('show');
    Route::post('/{exam}/start', [UserExamController::class, 'start'])->name('start');
    Route::get('/{exam}/take', [UserExamController::class, 'take'])->name('take');
    Route::post('/{exam}/submit', [UserExamController::class, 'submit'])->name('submit');
    Route::get('/attempts/{attempt}', [UserExamController::class, 'attemptResult'])->name('attempt.result');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('exams', AdminExamController::class);
    Route::resource('imports', AdminImportController::class);

    // Quản lý câu hỏi theo đề thi (Phương án A)
    Route::get('exams/{exam}/questions', [QuestionController::class, 'index'])->name('exams.questions.index');
    Route::get('exams/{exam}/parts/{part}/question-groups/create', [QuestionController::class, 'create'])->name('exams.question-groups.create');
    Route::post('exams/{exam}/parts/{part}/question-groups', [QuestionController::class, 'store'])->name('exams.question-groups.store');
    Route::get('exams/{exam}/question-groups/{question_group}/edit', [QuestionController::class, 'edit'])->name('exams.question-groups.edit');
    Route::put('exams/{exam}/question-groups/{question_group}', [QuestionController::class, 'update'])->name('exams.question-groups.update');
    Route::delete('exams/{exam}/question-groups/{question_group}', [QuestionController::class, 'destroy'])->name('exams.question-groups.destroy');
});

require __DIR__.'/auth.php';
