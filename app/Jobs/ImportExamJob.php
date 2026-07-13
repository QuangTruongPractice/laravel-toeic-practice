<?php

namespace App\Jobs;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\Import;
use App\Models\Part;
use App\Models\Question;
use App\Models\QuestionGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class ImportExamJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $importId;

    protected $testNum;

    protected $jsonPath;

    protected $audioSourceDir;

    protected $imageSourceDir;

    /**
     * Create a new job instance.
     */
    public function __construct(int $importId, int $testNum, string $jsonPath, string $audioSourceDir, string $imageSourceDir)
    {
        $this->importId = $importId;
        $this->testNum = $testNum;
        $this->jsonPath = $jsonPath;
        $this->audioSourceDir = $audioSourceDir;
        $this->imageSourceDir = $imageSourceDir;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $import = Import::find($this->importId);
        if (! $import) {
            return;
        }

        $import->update(['status' => 'processing']);

        if (! File::exists($this->jsonPath)) {
            $import->update([
                'status' => 'failed',
                'error_log' => "JSON file not found at temporary path: {$this->jsonPath}",
            ]);

            return;
        }

        try {
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key'    => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ],
                'url' => [
                    'secure' => true
                ]
            ]);
            $uploadApi = new UploadApi();

            $data = json_decode(File::get($this->jsonPath), true);
            if (! $data || ! isset($data['questions'])) {
                throw new \Exception("Invalid JSON format or missing 'questions' array.");
            }

            $questions = $data['questions'];

            DB::beginTransaction();

            // 1. Create or Find the Exam with draft status
            $examTitle = "ETS 2026 Test {$this->testNum}";
            $exam = Exam::updateOrCreate(
                ['slug' => Str::slug($examTitle)],
                [
                    'title' => $examTitle,
                    'description' => "ETS 2026 Practice Test {$this->testNum}",
                    'year' => 2026,
                    'status' => 'draft', // Draft status as requested by admin review flow
                    'duration_minutes' => 120,
                ]
            );

            // Clean up existing data for this exam
            $exam->questionGroups()->delete();

            $parts = Part::all()->keyBy('part_number');
            if ($parts->isEmpty()) {
                throw new \Exception('No parts found in the database. Please run database seeders first.');
            }

            $groupedQuestions = $this->groupQuestions($questions);
            $groupOrder = 1;
            $totalQuestionsCreated = 0;

            $missingAudios = [];
            $missingImages = [];

            foreach ($groupedQuestions as $groupKey => $groupData) {
                $partNum = $groupData['part'];
                $part = $parts->get($partNum);

                if (! $part) {
                    throw new \Exception("Part {$partNum} not found in the database.");
                }

                // Upload Audio File if it exists
                $dbAudioPath = null;
                if (! empty($groupData['audio_file'])) {
                    $audioFileName = basename($groupData['audio_file']);
                    $srcAudioPath = $this->findFileRecursive($this->audioSourceDir, $audioFileName);
                    if ($srcAudioPath && File::exists($srcAudioPath)) {
                        $uploaded = $uploadApi->upload($srcAudioPath, [
                            'folder' => "toeic/test_{$this->testNum}/audios",
                            'resource_type' => 'video'
                        ]);
                        $dbAudioPath = $uploaded['secure_url'];
                    } else {
                        $missingAudios[] = $audioFileName;
                    }
                }

                // Upload Image File if it exists
                $dbImagePath = null;
                if (! empty($groupData['image_file'])) {
                    $imageFileName = basename($groupData['image_file']);
                    $srcImagePath = $this->findFileRecursive($this->imageSourceDir, $imageFileName);
                    if ($srcImagePath && File::exists($srcImagePath)) {
                        $uploaded = $uploadApi->upload($srcImagePath, [
                            'folder' => "toeic/test_{$this->testNum}/images",
                            'resource_type' => 'image'
                        ]);
                        $dbImagePath = $uploaded['secure_url'];
                    } else {
                        $missingImages[] = $imageFileName;
                    }
                }

                // Create Question Group
                $questionGroup = QuestionGroup::create([
                    'exam_id' => $exam->id,
                    'part_id' => $part->id,
                    'passage' => $groupData['passage'],
                    'audio_path' => $dbAudioPath,
                    'image_path' => $dbImagePath,
                    'order_number' => $groupOrder++,
                ]);

                // Create Questions in this Group
                $orderInGroup = 1;
                foreach ($groupData['questions'] as $q) {
                    $question = Question::create([
                        'question_group_id' => $questionGroup->id,
                        'content' => $q['content'] ?: null,
                        'question_number' => $q['number'],
                        'order_in_group' => $orderInGroup++,
                        'explanation' => $q['explanation'] ?: null,
                    ]);

                    $totalQuestionsCreated++;

                    // Create Options/Answers
                    foreach ($q['options'] as $label => $optionContent) {
                        Answer::create([
                            'question_id' => $question->id,
                            'label' => $label,
                            'content' => $optionContent,
                            'is_correct' => (strtoupper($q['correct_answer']) === strtoupper($label)),
                        ]);
                    }
                }
            }

            DB::commit();

            // Create status log message if there are missing files
            $logMessage = null;
            if (! empty($missingAudios) || ! empty($missingImages)) {
                $logMessage = "Import completed but some files were missing:\n";
                if (! empty($missingAudios)) {
                    $logMessage .= '- Missing Audios ('.count($missingAudios).'): '.implode(', ', array_slice($missingAudios, 0, 10)).(count($missingAudios) > 10 ? '...' : '')."\n";
                }
                if (! empty($missingImages)) {
                    $logMessage .= '- Missing Images ('.count($missingImages).'): '.implode(', ', array_slice($missingImages, 0, 10)).(count($missingImages) > 10 ? '...' : '')."\n";
                }
            }

            // Update import status
            $import->update([
                'status' => 'completed',
                'exam_id' => $exam->id,
                'questions_created' => $totalQuestionsCreated,
                'error_log' => $logMessage,
            ]);

            Exam::clearCacheById($exam->id);
            Exam::clearPublishedListCache();

            // Clean up temporary uploads directory
            File::deleteDirectory(dirname($this->jsonPath));

        } catch (\Exception $e) {
            DB::rollBack();
            $import->update([
                'status' => 'failed',
                'error_log' => $e->getMessage()."\n".$e->getTraceAsString(),
            ]);
            // Clean up temporary uploads directory
            File::deleteDirectory(dirname($this->jsonPath));
        }
    }

    /**
     * Group questions by standard TOEIC parts and structures.
     */
    private function groupQuestions(array $questions): array
    {
        usort($questions, function ($a, $b) {
            return $a['number'] <=> $b['number'];
        });

        $grouped = [];

        foreach ($questions as $q) {
            $part = (int) $q['part'];
            $qNum = (int) $q['number'];

            $groupKey = null;
            $audioFile = $q['audio_filename'] ?? null;
            $imageFile = null;
            $passage = null;

            if ($part === 1) {
                $groupKey = "part1_{$qNum}";
                $imageFile = "part1_test{$this->testNum}_Q{$qNum}.png";
            } elseif ($part === 2) {
                $groupKey = "part2_{$qNum}";
            } elseif ($part === 3 || $part === 4) {
                if (! empty($audioFile)) {
                    $groupKey = 'part34_'.md5(basename($audioFile));
                } else {
                    $groupKey = "part34_manual_{$qNum}";
                }

                // Check for graphic images in Part 3 & 4 (use recursive search to handle subdirectories in ZIP)
                $possibleImage = "part34_test{$this->testNum}_Q{$qNum}.png";
                $possibleImageJpg = "part34_test{$this->testNum}_Q{$qNum}.jpg";

                if ($this->findFileRecursive($this->imageSourceDir, $possibleImage)) {
                    $imageFile = $possibleImage;
                } elseif ($this->findFileRecursive($this->imageSourceDir, $possibleImageJpg)) {
                    $imageFile = $possibleImageJpg;
                }
            } elseif ($part === 5) {
                $groupKey = "part5_{$qNum}";
            } elseif ($part === 6) {
                $start = 131 + (int) (($qNum - 131) / 4) * 4;
                $end = $start + 3;
                $groupKey = "part6_{$start}_{$end}";
                $possibleImage = "part6_test{$this->testNum}_Q{$start}-{$end}.png";
                if ($this->findFileRecursive($this->imageSourceDir, $possibleImage)) {
                    $imageFile = $possibleImage;
                }
            } elseif ($part === 7) {
                $ranges = [
                    [147, 148], [149, 150], [151, 152], [153, 154], [155, 157],
                    [158, 160], [161, 163], [164, 167], [168, 171], [172, 175],
                    [176, 180], [181, 185], [186, 190], [191, 195], [196, 200],
                ];

                $start = null;
                $end = null;
                foreach ($ranges as $r) {
                    if ($qNum >= $r[0] && $qNum <= $r[1]) {
                        $start = $r[0];
                        $end = $r[1];
                        break;
                    }
                }

                if ($start !== null) {
                    $groupKey = "part7_{$start}_{$end}";
                    // Try uppercase Q first, then lowercase q (files differ between tests)
                    $possibleImage = "part7_test{$this->testNum}_Q{$start}-{$end}.png";
                    $possibleImageLower = "part7_test{$this->testNum}_q{$start}-{$end}.png";
                    if ($this->findFileRecursive($this->imageSourceDir, $possibleImage)) {
                        $imageFile = $possibleImage;
                    } elseif ($this->findFileRecursive($this->imageSourceDir, $possibleImageLower)) {
                        $imageFile = $possibleImageLower;
                    }
                } else {
                    $groupKey = "part7_single_{$qNum}";
                }
            }

            if (! isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'part' => $part,
                    'audio_file' => $audioFile,
                    'image_file' => $imageFile,
                    'passage' => $passage,
                    'questions' => [],
                ];
            }

            if (! empty($imageFile) && empty($grouped[$groupKey]['image_file'])) {
                $grouped[$groupKey]['image_file'] = $imageFile;
            }

            $grouped[$groupKey]['questions'][] = $q;
        }

        return $grouped;
    }

    /**
     * Recursively search for a file name within a directory.
     */
    private function findFileRecursive(string $dir, string $fileName): ?string
    {
        if (! File::isDirectory($dir)) {
            return null;
        }

        // Direct check
        $directPath = $dir.DIRECTORY_SEPARATOR.$fileName;
        if (File::exists($directPath)) {
            return $directPath;
        }

        // Recursive check in subdirectories
        $files = File::allFiles($dir);
        foreach ($files as $file) {
            if ($file->getFilename() === $fileName) {
                return $file->getRealPath();
            }
        }

        return null;
    }
}
