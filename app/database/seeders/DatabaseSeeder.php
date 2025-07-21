<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Order;
use App\Models\OrderItem;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();
        Warehouse::factory(10)->create();
        Product::factory(10)->create();
        OrderItem::factory(10)->create();
        Stock::factory(10)->create();
        Order::factory(10)->create();
    }
}
