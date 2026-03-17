<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Engine Parts', 'description' => 'Components related to engine functionality'],
            ['name' => 'Brake System', 'description' => 'Brakes, pads, discs, and related parts'],
            ['name' => 'Suspension', 'description' => 'Shocks, struts, springs, and suspension parts'],
            ['name' => 'Electrical', 'description' => 'Batteries, alternators, starters, and wiring'],
            ['name' => 'Body Parts', 'description' => 'Fenders, doors, bumpers, and body panels'],
            ['name' => 'Transmission', 'description' => 'Gearboxes, clutches, and drivetrain parts'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
