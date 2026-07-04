<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Exam;
use App\Models\Part;
use App\Models\QuestionGroup;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportToeicExam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-toeic-exam 
                            {--file=parser-service/test_1_parsed.json : Path to the parsed JSON file}
                            {--test=1 : The test number (e.g. 1, 2, 3...)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import parsed TOEIC exam questions, answers, and copy images/audio to public storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = base_path($this->option('file'));
        $testNum = (int)$this->option('test');

        if (!File::exists($filePath)) {
            $this->error("JSON file not found at: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Reading JSON from: {$filePath}...");
        $data = json_decode(File::get($filePath), true);

        if (!$data || !isset($data['questions'])) {
            $this->error("Invalid JSON format or missing 'questions' array.");
            return Command::FAILURE;
        }

        $questions = $data['questions'];
        $this->info("Found " . count($questions) . " questions in JSON.");

        // Resolve source paths
        $sourceAudioDir = base_path("ETS-2026/AUDIO ETS 2026/Audio-3");
        $sourceAssetsDir = base_path("parser-service/app/assets");

        // Target storage paths (within public storage)
        $targetAudioDir = storage_path("app/public/audios/test_{$testNum}");
        $targetImageDir = storage_path("app/public/images/test_{$testNum}");

        // Create target directories if they don't exist
        File::ensureDirectoryExists($targetAudioDir);
        File::ensureDirectoryExists($targetImageDir);

        $this->info("Assets will be copied to:");
        $this->info("- Audios: {$targetAudioDir}");
        $this->info("- Images: {$targetImageDir}");

        DB::beginTransaction();

        try {
            // 1. Create or Find the Exam
            $examTitle = "ETS 2026 Test {$testNum}";
            $exam = Exam::updateOrCreate(
                ['slug' => Str::slug($examTitle)],
                [
                    'title' => $examTitle,
                    'description' => "ETS 2026 Practice Test {$testNum}",
                    'year' => 2026,
                    'status' => 'published',
                    'duration_minutes' => 120
                ]
            );

            // Clean up existing data for this exam to allow re-importing cleanly
            $exam->questionGroups()->delete();

            // 2. Fetch Parts mapped by part_number
            $parts = Part::all()->keyBy('part_number');
            if ($parts->isEmpty()) {
                $this->error("No parts found in the database. Please run database seeders first.");
                DB::rollBack();
                return Command::FAILURE;
            }

            // 3. Group the questions
            $groupedQuestions = $this->groupQuestions($questions, $testNum, $sourceAssetsDir);

            $groupOrder = 1;

            foreach ($groupedQuestions as $groupKey => $groupData) {
                $partNum = $groupData['part'];
                $part = $parts->get($partNum);

                if (!$part) {
                    $this->error("Part {$partNum} not found in the database.");
                    DB::rollBack();
                    return Command::FAILURE;
                }

                // Copy Audio File if it exists
                $dbAudioPath = null;
                if (!empty($groupData['audio_file'])) {
                    $audioFileName = basename($groupData['audio_file']);
                    $srcAudioPath = "{$sourceAudioDir}/{$audioFileName}";
                    if (File::exists($srcAudioPath)) {
                        File::copy($srcAudioPath, "{$targetAudioDir}/{$audioFileName}");
                        $dbAudioPath = "audios/test_{$testNum}/{$audioFileName}";
                    } else {
                        $this->warn("Audio file not found: {$srcAudioPath}");
                    }
                }

                // Copy Image File if it exists
                $dbImagePath = null;
                if (!empty($groupData['image_file'])) {
                    $imageFileName = basename($groupData['image_file']);
                    $srcImagePath = "{$sourceAssetsDir}/{$imageFileName}";
                    if (File::exists($srcImagePath)) {
                        File::copy($srcImagePath, "{$targetImageDir}/{$imageFileName}");
                        $dbImagePath = "images/test_{$testNum}/{$imageFileName}";
                    } else {
                        $this->warn("Image file not found: {$srcImagePath}");
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
            $this->info("Successfully imported ETS 2026 Test {$testNum}!");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error occurred: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Group questions by standard TOEIC parts and structures.
     */
    private function groupQuestions(array $questions, int $testNum, string $sourceAssetsDir): array
    {
        // Sort questions by their number
        usort($questions, function ($a, $b) {
            return $a['number'] <=> $b['number'];
        });

        $grouped = [];

        foreach ($questions as $q) {
            $part = (int)$q['part'];
            $qNum = (int)$q['number'];

            $groupKey = null;
            $audioFile = $q['audio_path'] ?? null;
            $imageFile = null;
            $passage = null;

            if ($part === 1) {
                // Part 1: Each question is its own group. Has audio & image.
                $groupKey = "part1_{$qNum}";
                // Resolve Part 1 Image name
                $imageFile = "part1_test{$testNum}_Q{$qNum}.png";
            } elseif ($part === 2) {
                // Part 2: Each question is its own group. Has audio.
                $groupKey = "part2_{$qNum}";
            } elseif ($part === 3 || $part === 4) {
                // Part 3 & 4: Grouped by audio file.
                if (!empty($audioFile)) {
                    $groupKey = "part34_" . md5(basename($audioFile));
                } else {
                    $groupKey = "part34_manual_{$qNum}";
                }

                // Check if there is an image for graphic questions in this group
                // Standard graphic question numbers: 62, 65, 68, 95, 98 (usually)
                // Let's check if part34_test{testNum}_Q{qNum}.png exists
                $possibleImage = "part34_test{$testNum}_Q{$qNum}.png";
                if (File::exists("{$sourceAssetsDir}/{$possibleImage}")) {
                    $imageFile = $possibleImage;
                } else {
                    // Try .jpg
                    $possibleImageJpg = "part34_test{$testNum}_Q{$qNum}.jpg";
                    if (File::exists("{$sourceAssetsDir}/{$possibleImageJpg}")) {
                        $imageFile = $possibleImageJpg;
                    }
                }
            } elseif ($part === 5) {
                // Part 5: Each question is its own group.
                $groupKey = "part5_{$qNum}";
            } elseif ($part === 6) {
                // Part 6: Grouped by ranges (131-134, 135-138, 139-142, 143-146)
                $start = 131 + (int)(($qNum - 131) / 4) * 4;
                $end = $start + 3;
                $groupKey = "part6_{$start}_{$end}";
                // Check for image
                $possibleImage = "part6_test{$testNum}_Q{$start}-{$end}.png";
                if (File::exists("{$sourceAssetsDir}/{$possibleImage}")) {
                    $imageFile = $possibleImage;
                }
            } elseif ($part === 7) {
                // Part 7: Predefined standard ranges
                $ranges = [
                    [147, 148], [149, 150], [151, 152], [153, 154], [155, 157],
                    [158, 160], [161, 163], [164, 167], [168, 171], [172, 175],
                    [176, 180], [181, 185], [186, 190], [191, 195], [196, 200]
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
                    // Check for image (could be uppercase Q or lowercase q)
                    $possibleImage = "part7_test{$testNum}_Q{$start}-{$end}.png";
                    $possibleImageLower = "part7_test{$testNum}_q{$start}-{$end}.png";
                    if (File::exists("{$sourceAssetsDir}/{$possibleImage}")) {
                        $imageFile = $possibleImage;
                    } elseif (File::exists("{$sourceAssetsDir}/{$possibleImageLower}")) {
                        $imageFile = $possibleImageLower;
                    }
                } else {
                    $groupKey = "part7_single_{$qNum}";
                }
            }

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'part' => $part,
                    'audio_file' => $audioFile,
                    'image_file' => $imageFile,
                    'passage' => $passage,
                    'questions' => [],
                ];
            }

            // If we found an image for graphic questions in Part 3/4, attach it to the group
            if (!empty($imageFile) && empty($grouped[$groupKey]['image_file'])) {
                $grouped[$groupKey]['image_file'] = $imageFile;
            }

            $grouped[$groupKey]['questions'][] = $q;
        }

        return $grouped;
    }
}
