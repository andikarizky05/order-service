<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Create Orders
        foreach (range(1, 200) as $i) { // 200 orders
            DB::table('orders')->insert([
                'user_id' => $faker->numberBetween(1, 50), // asumsi user id 1-50
                'total_amount' => $faker->randomFloat(2, 50, 5000),
                'status' => $faker->randomElement(['pending', 'completed', 'cancelled']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // Create Order Items
        foreach (range(1, 500) as $i) { // 500 order items
            DB::table('order_items')->insert([
                'order_id' => $faker->numberBetween(1, 200),
                'product_id' => $faker->numberBetween(1, 100), // asumsi product id 1-100
                'quantity' => $faker->numberBetween(1, 5),
                'price' => $faker->randomFloat(2, 10, 2000),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
