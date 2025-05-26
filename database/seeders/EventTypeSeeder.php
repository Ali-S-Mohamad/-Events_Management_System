<?php

namespace Database\Seeders;

use App\Models\EventType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Workshop',
            'Conference',
            'Concert',
            'Seminar',
            'Sports Event',
            'Art Exhibition',
            'Tech Meetup',
        ];

        foreach ($types as $name) {
            EventType::create(['name' => $name]);
        }
    }
}
