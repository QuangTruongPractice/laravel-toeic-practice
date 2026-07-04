<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Jobs\ImportExamJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class ImportController extends Controller
{
    public function index()
    {
        $imports = Import::with(['admin', 'exam'])->latest()->paginate(10);
        return view('admin.imports.index', compact('imports'));
    }

    public function create()
    {
        return view('admin.imports.create');
    }

    public function store(Request $request)
    {
        // Tăng giới hạn bộ nhớ (RAM) và thời gian thực thi động để xử lý tệp nén lớn
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300'); // 5 phút

        $request->validate([
            'test_num' => 'required|integer|min:1|max:10',
            'listening_pdf' => 'required|file|mimes:pdf',
            'reading_pdf' => 'required|file|mimes:pdf',
            'audio_zip' => 'required|file|mimes:zip',
            'image_zip' => 'required|file|mimes:zip',
        ]);

        $testNum = $request->input('test_num');

        // Save uploaded PDFs and ZIPs to a temporary directory
        $uniqueId = uniqid('import_');
        $tempDir = storage_path("app/temp/{$uniqueId}");
        File::ensureDirectoryExists($tempDir);

        $listeningPdf = $request->file('listening_pdf');
        $readingPdf = $request->file('reading_pdf');
        $audioZip = $request->file('audio_zip');
        $imageZip = $request->file('image_zip');

        // Move files to temp directory using absolute paths
        $listeningPdf->move($tempDir, 'listening.pdf');
        $readingPdf->move($tempDir, 'reading.pdf');

        $fullListeningPath = $tempDir . DIRECTORY_SEPARATOR . 'listening.pdf';
        $fullReadingPath = $tempDir . DIRECTORY_SEPARATOR . 'reading.pdf';

        // Create the Import record
        $import = Import::create([
            'admin_id' => auth()->id(),
            'status' => 'pending',
            'pdf_path' => "temp/{$uniqueId}/listening.pdf",
            'audio_path' => $audioZip->getClientOriginalName()
        ]);

        try {
            // 1. Extract ZIP files to temporary directories
            $audioExtractDir = "{$tempDir}/audios";
            $imageExtractDir = "{$tempDir}/images";
            File::ensureDirectoryExists($audioExtractDir);
            File::ensureDirectoryExists($imageExtractDir);

            // Extract Audio
            $zip = new \ZipArchive();
            $openResult = $zip->open($audioZip->path());
            if ($openResult !== true) {
                throw new \Exception("Không thể mở tệp zip Audio (mã lỗi ZipArchive: {$openResult}).");
            }
            if (!$zip->extractTo($audioExtractDir)) {
                $zip->close();
                throw new \Exception("Giải nén tệp zip Audio thất bại. Kiểm tra quyền ghi thư mục hoặc cấu trúc file zip: {$audioExtractDir}");
            }
            $zip->close();
            $audioFileCount = count(File::allFiles($audioExtractDir));
            if ($audioFileCount === 0) {
                throw new \Exception("Giải nén Audio 'thành công' nhưng thư mục rỗng ({$audioExtractDir}). Kiểm tra lại file zip audio.");
            }

            // Extract Images
            $zip = new \ZipArchive();
            $openResult = $zip->open($imageZip->path());
            if ($openResult !== true) {
                throw new \Exception("Không thể mở tệp zip Hình ảnh (mã lỗi ZipArchive: {$openResult}).");
            }
            if (!$zip->extractTo($imageExtractDir)) {
                $zip->close();
                throw new \Exception("Giải nén tệp zip Hình ảnh thất bại. Kiểm tra quyền ghi thư mục hoặc cấu trúc file zip: {$imageExtractDir}");
            }
            $zip->close();
            $imageFileCount = count(File::allFiles($imageExtractDir));
            if ($imageFileCount === 0) {
                throw new \Exception("Giải nén Hình ảnh 'thành công' nhưng thư mục rỗng ({$imageExtractDir}). Kiểm tra lại file zip hình ảnh.");
            }

            // 2. Call Python FastAPI parser service
            $response = Http::attach(
                'listening_pdf', file_get_contents($fullListeningPath), 'listening.pdf'
            )->attach(
                'reading_pdf', file_get_contents($fullReadingPath), 'reading.pdf'
            )->post(env('APP_PYTHON', 'http://localhost:8080') . '/parse-exam', [
                'test_num' => $testNum
            ]);

            if ($response->failed()) {
                throw new \Exception("FastAPI parser service failed: " . $response->body());
            }

            $parsedData = $response->json();
            
            // Save parsed JSON to temp path
            $jsonTempPath = "{$tempDir}/parsed.json";
            File::put($jsonTempPath, json_encode($parsedData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            // 3. Dispatch the Import Job
            ImportExamJob::dispatch(
                $import->id,
                $testNum,
                $jsonTempPath,
                $audioExtractDir,
                $imageExtractDir
            );

            return redirect()->route('admin.imports.index')->with('success', 'Đã tải lên thành công. Đang tiến hành parse và import ngầm.');

        } catch (\Exception $e) {
            File::deleteDirectory($tempDir);
            $import->update([
                'status' => 'failed',
                'error_log' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => "Lỗi xử lý tệp nén hoặc kết nối với Python service: " . $e->getMessage()])->withInput();
        }
    }

    public function show(Import $import)
    {
        return view('admin.imports.show', compact('import'));
    }
}
