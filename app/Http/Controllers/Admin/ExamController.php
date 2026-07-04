<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $exams = Exam::all();
        return view('admin.exams.index', compact('exams'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.exams.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:exams,slug',
            'description' => 'nullable|string',
            'year' => 'required|integer|min:1900|max:2100',
            'duration_minutes' => 'required|integer|min:1',
            'status' => 'required|in:draft,published,archived',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']) . '-' . uniqid();
        }

        Exam::create($data);
        Exam::clearPublishedListCache();

        return redirect()->route('admin.exams.index')->with('success', 'Đề thi đã được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Exam $exam)
    {
        return view('admin.exams.show', compact('exam'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exam $exam)
    {
        return view('admin.exams.edit', compact('exam'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:exams,slug,' . $exam->id,
            'description' => 'nullable|string',
            'year' => 'required|integer|min:1900|max:2100',
            'duration_minutes' => 'required|integer|min:1',
            'status' => 'required|in:draft,published,archived',
        ]);

        $exam->update($data);
        Exam::clearCacheById($exam->id);
        Exam::clearPublishedListCache();

        return redirect()->route('admin.exams.index')->with('success', 'Đề thi đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exam $exam)
    {
        $examId = $exam->id;
        $exam->delete();
        Exam::clearCacheById($examId);
        Exam::clearPublishedListCache();

        return redirect()->route('admin.exams.index');
    }
}
