<?php

namespace Database\Seeders;

use App\Models\Part;
use Illuminate\Database\Seeder;

class PartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parts = [
            ['part_number' => 1, 'section' => 'listening', 'name' => 'Photographs', 'directions' => 'In this part, you will see a picture and four statements. Choose the statement that best describes the picture.', 'description' => 'A group of people are doing something.', 'question_count' => 6],
            ['part_number' => 2, 'section' => 'listening', 'name' => 'Question-Response', 'directions' => 'A question or statement is spoken.', 'description' => 'A question or statement is spoken.', 'question_count' => 25],
            ['part_number' => 3, 'section' => 'listening', 'name' => 'Conversations', 'directions' => 'A conversation between two or more people.', 'description' => 'A conversation between two or more people.', 'question_count' => 39],
            ['part_number' => 4, 'section' => 'listening', 'name' => 'Talks', 'directions' => 'A talk or monologue.', 'description' => 'A talk or monologue.', 'question_count' => 30],
            ['part_number' => 5, 'section' => 'reading', 'name' => 'Incomplete Sentences', 'directions' => 'Complete each sentence with a word or phrase.', 'description' => 'Complete each sentence with a word or phrase.', 'question_count' => 30],
            ['part_number' => 6, 'section' => 'reading', 'name' => 'Text Completion', 'directions' => 'Complete each text with an appropriate sentence or phrase.', 'description' => 'Complete each text with an appropriate sentence or phrase.', 'question_count' => 16],
            ['part_number' => 7, 'section' => 'reading', 'name' => 'Reading Comprehension', 'directions' => 'Read each text and answer the questions that follow.', 'description' => 'Read each text and answer the questions that follow.', 'question_count' => 54],
        ];
        foreach ($parts as $partData) {
            Part::create($partData);
        }
    }
}
