<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach ([4 => 1, 13 => 3] as $examId => $testNum) {
    $total    = DB::table('question_groups')->where('exam_id', $examId)->count();
    $hasAudio = DB::table('question_groups')->where('exam_id', $examId)->whereNotNull('audio_path')->count();
    $hasImage = DB::table('question_groups')->where('exam_id', $examId)->whereNotNull('image_path')->count();
    echo "exam_id={$examId} (Test {$testNum}): total_groups={$total}  with_audio={$hasAudio}  with_image={$hasImage}\n";

    // Sample check
    $sample = DB::table('question_groups')->where('exam_id', $examId)->whereNotNull('audio_path')->first(['audio_path', 'image_path']);
    if ($sample) {
        echo "  Sample audio_path: {$sample->audio_path}\n";
    }
}
