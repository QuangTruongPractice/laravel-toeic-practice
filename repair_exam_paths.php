<?php

use Illuminate\Contracts\Console\Kernel;

/**
 * Repair script v2: Fix audio_path for Part 3/4 groups using question-range naming
 * Audio naming convention: E26-T{test}-{firstQ}-{lastQ}.mp3
 *
 * Usage: php repair_exam_paths.php <exam_id> <test_num>
 */
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$examId = (int) ($argv[1] ?? 13);
$testNum = (int) ($argv[2] ?? 3);

echo "=== Repairing Part 3/4 audio for exam_id={$examId}, test_num={$testNum} ===\n\n";

$audioStorageDir = storage_path("app/public/audios/test_{$testNum}");
$testPrefix = 'E26-T'.str_pad($testNum, 2, '0', STR_PAD_LEFT);

// Index all audio files by filename
$audioFiles = glob("{$audioStorageDir}/*.mp3");
$audioIndex = [];
foreach ($audioFiles as $f) {
    $audioIndex[basename($f)] = true;
}
echo 'Audio files available: '.count($audioIndex)."\n\n";

$parts = DB::table('parts')->get()->keyBy('id');

// Get all groups for this exam that have NULL audio_path and are Part 3 or 4
$groups = DB::table('question_groups')
    ->where('exam_id', $examId)
    ->whereNull('audio_path')
    ->get(['id', 'part_id', 'order_number']);

$updated = 0;
$missing = [];

foreach ($groups as $group) {
    $partNum = $parts[$group->part_id]->part_number ?? null;

    if (! in_array($partNum, [3, 4])) {
        continue; // Only fix Part 3 & 4 here
    }

    // Get all question numbers for this group
    $qNums = DB::table('questions')
        ->where('question_group_id', $group->id)
        ->orderBy('question_number')
        ->pluck('question_number');

    $firstQ = $qNums->first();
    $lastQ = $qNums->last();

    if (! $firstQ) {
        continue;
    }

    // Try audio file with question range naming: E26-T03-32-34.mp3
    $audioFileName = "{$testPrefix}-{$firstQ}-{$lastQ}.mp3";

    if (isset($audioIndex[$audioFileName])) {
        DB::table('question_groups')->where('id', $group->id)->update([
            'audio_path' => "audios/test_{$testNum}/{$audioFileName}",
        ]);
        echo "  Group#{$group->order_number} (part{$partNum}) Q{$firstQ}-{$lastQ}: {$audioFileName}\n";
        $updated++;
    } else {
        // Try single-number format (Part 2 fallback, should not happen for Part 3/4)
        $missing[] = "Group#{$group->order_number} (part{$partNum}) Q{$firstQ}-{$lastQ}: expected {$audioFileName}";
    }
}

echo "\n=== Summary ===\n";
echo "Updated: {$updated}\n";
if (! empty($missing)) {
    echo "\nStill missing (".count($missing)."):\n";
    foreach ($missing as $m) {
        echo "  - {$m}\n";
    }
}
