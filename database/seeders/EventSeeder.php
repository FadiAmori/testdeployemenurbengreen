<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event\Event; // Your nested model
use Illuminate\Support\Facades\File;
use Carbon\Carbon; // For dates

class EventSeeder extends Seeder
{
    public function run()
    {
        $csvPath = base_path('urban_ml_service/datasets/urban_events_details.csv');
        if (!File::exists($csvPath)) {
            $this->command->error("CSV not found at $csvPath");
            return;
        }

        $lines = File::lines($csvPath)->toArray();
        $header = str_getcsv(array_shift($lines)); // Skip header

        $events = [];
        foreach ($lines as $line) {
            $row = str_getcsv($line);
            if (count($row) < 3) continue;

            // Find column indices
            $eventIdIndex = array_search('event_id', $header) ?? 0;
            $titleIndex = array_search('title', $header) ?? 1;
            $locationIndex = array_search('location', $header) ?? 2;
            $categoryIndex = array_search('category', $header) ?? 3;
            $plantStepIndex = array_search('plant_step', $header) ?? 4;
            // Add more if CSV has them, e.g., descriptionIndex = array_search('description', $header) ?? 5;

            $events[] = [
                'title' => trim($row[$titleIndex]),
                'location' => trim($row[$locationIndex]),
                'category' => trim($row[$categoryIndex] ?? 'General'),
                'plant_step' => trim($row[$plantStepIndex] ?? 'N/A'),
                'description' => 'Event generated from ML dataset for recommendations.', // Placeholder
                'event_date' => Carbon::now()->addDays(rand(1, 30)), // Random future date; adjust if CSV has dates
                'is_published' => true,
                'user_id' => 1, // Default organizer (change if needed)
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert in batches (safe for 200)
        collect($events)->chunk(50)->each(function ($chunk) {
            Event::insert($chunk->toArray());
        });

        $this->command->info("Seeded " . count($events) . " real events from CSV!");
    }
}