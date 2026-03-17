<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['sku' => 'ENG-001', 'name' => 'Oil Filter', 'category_id' => 1, 'price' => 12.99, 'cost_price' => 8.00, 'stock_quantity' => 50, 'description' => 'Premium oil filter for most vehicles'],
            ['sku' => 'ENG-002', 'name' => 'Spark Plug', 'category_id' => 1, 'price' => 8.99, 'cost_price' => 5.00, 'stock_quantity' => 100, 'description' => 'Iridium spark plug'],
            ['sku' => 'ENG-003', 'name' => 'Air Filter', 'category_id' => 1, 'price' => 15.99, 'cost_price' => 10.00, 'stock_quantity' => 45, 'description' => 'Engine air filter'],
            ['sku' => 'ENG-004', 'name' => 'Timing Belt', 'category_id' => 1, 'price' => 45.00, 'cost_price' => 30.00, 'stock_quantity' => 20, 'description' => 'Timing belt for most vehicles'],
            ['sku' => 'BRK-001', 'name' => 'Brake Pad Set', 'category_id' => 2, 'price' => 35.99, 'cost_price' => 22.00, 'stock_quantity' => 30, 'description' => 'Front brake pad set'],
            ['sku' => 'BRK-002', 'name' => 'Brake Disc', 'category_id' => 2, 'price' => 55.00, 'cost_price' => 38.00, 'stock_quantity' => 25, 'description' => 'Front brake disc'],
            ['sku' => 'BRK-003', 'name' => 'Brake Fluid', 'category_id' => 2, 'price' => 12.50, 'cost_price' => 7.50, 'stock_quantity' => 60, 'description' => 'DOT 4 brake fluid'],
            ['sku' => 'SUS-001', 'name' => 'Shock Absorber', 'category_id' => 3, 'price' => 89.99, 'cost_price' => 60.00, 'stock_quantity' => 15, 'description' => 'Gas shock absorber'],
            ['sku' => 'SUS-002', 'name' => 'Strut Assembly', 'category_id' => 3, 'price' => 145.00, 'cost_price' => 100.00, 'stock_quantity' => 10, 'description' => 'Complete strut assembly'],
            ['sku' => 'SUS-003', 'name' => 'Spring Set', 'category_id' => 3, 'price' => 120.00, 'cost_price' => 85.00, 'stock_quantity' => 12, 'description' => 'Coil spring set'],
            ['sku' => 'ELE-001', 'name' => 'Car Battery', 'category_id' => 4, 'price' => 95.00, 'cost_price' => 70.00, 'stock_quantity' => 20, 'description' => '12V 60AH car battery'],
            ['sku' => 'ELE-002', 'name' => 'Alternator', 'category_id' => 4, 'price' => 175.00, 'cost_price' => 120.00, 'stock_quantity' => 8, 'description' => '120A alternator'],
            ['sku' => 'ELE-003', 'name' => 'Starter Motor', 'category_id' => 4, 'price' => 150.00, 'cost_price' => 105.00, 'stock_quantity' => 10, 'description' => 'Electric starter motor'],
            ['sku' => 'ELE-004', 'name' => 'Headlight Bulb', 'category_id' => 4, 'price' => 18.99, 'cost_price' => 12.00, 'stock_quantity' => 80, 'description' => 'H7 headlight bulb'],
            ['sku' => 'BOD-001', 'name' => 'Side Mirror', 'category_id' => 5, 'price' => 45.00, 'cost_price' => 30.00, 'stock_quantity' => 15, 'description' => 'Left side mirror'],
            ['sku' => 'BOD-002', 'name' => 'Bumper', 'category_id' => 5, 'price' => 250.00, 'cost_price' => 180.00, 'stock_quantity' => 5, 'description' => 'Front bumper'],
            ['sku' => 'BOD-003', 'name' => 'Fender', 'category_id' => 5, 'price' => 120.00, 'cost_price' => 85.00, 'stock_quantity' => 8, 'description' => 'Front fender'],
            ['sku' => 'TRN-001', 'name' => 'Clutch Kit', 'category_id' => 6, 'price' => 185.00, 'cost_price' => 130.00, 'stock_quantity' => 12, 'description' => 'Complete clutch kit'],
            ['sku' => 'TRN-002', 'name' => 'Gear Oil', 'category_id' => 6, 'price' => 22.00, 'cost_price' => 15.00, 'stock_quantity' => 40, 'description' => 'SAE 80W-90 gear oil'],
            ['sku' => 'TRN-003', 'name' => 'CV Joint', 'category_id' => 6, 'price' => 65.00, 'cost_price' => 45.00, 'stock_quantity' => 18, 'description' => 'CV axle joint'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
