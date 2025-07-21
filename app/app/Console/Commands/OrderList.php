<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class OrderList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:order-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $arguments = [
        'warehouse_id' => null,
        'status' => null,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::all();
        $this->info('Список заказов:');
        foreach ($orders as $order) {
            echo $order->id . ' - ' . $order->customer . ' - ' . $order->warehouse->name . ' - ' . $order->status . ' - ' . $order->completed_at . PHP_EOL;
        }
    }
}
