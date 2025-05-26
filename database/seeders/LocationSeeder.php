<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            ['name' => 'Downtown Conference Center', 'latitude' => 33.5138, 'longitude' => 36.2765],
            ['name' => 'City Sports Hall', 'latitude' => 33.5155, 'longitude' => 36.2990],
            ['name' => 'Open-Air Theater', 'latitude' => 33.5182, 'longitude' => 36.2755],
            ['name' => 'Technology Park', 'latitude' => 33.5200, 'longitude' => 36.2800],
            ['name' => 'Green Square Garden', 'latitude' => 33.5120, 'longitude' => 36.2680],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
