<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\Stock;
use DB;
use Illuminate\Http\JsonResponse;
use Request;
use Validator;

class OrderController extends Controller
{
    /**
     * Получить список заказов
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['warehouse', 'items.product'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->warehouse_id, fn($q, $id) => $q->where('warehouse_id', $id))
            ->when($request->customer, fn($q, $customer) => $q->where('customer', 'like', "%$customer%"));

        $orders = $query->paginate($request->per_page ?? 10);
        return response()->json($orders);
    }


    /**
     * Создание заказа
     * @param \App\Http\Requests\StoreOrderRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $request->validated();

        $order = DB::transaction(function () use ($request) {
            $order = Order::create([
                'customer' => $request->customer,
                'warehouse_id' => $request->warehouse_id,
                'status' => Order::STATUS_ACTIVE,
            ]);

            Product::find($request->items[0]['product_id'])->stock->decrement('stock', $request->items[0]['count']);
            StockProductHistory::create([
                'stock' => Product::find($request->items[0]['product_id'])->stocks()->where('warehouse_id', $request->warehouse_id)->first()->id,
                'product_id' => $request->items[0]['product_id'],
                'warehouse_id' => $request->warehouse_id,
                'quantity' => $request->items[0]['count'],
                'type' => 'product_out',
            ]);

            foreach ($request->items as $item) {
                $order->items()->create($item);
            }

            return $order;
        });

        return response()->json($order->load('items'), 201);
    }

    /**
     * Редактирование заказа
     * @param \App\Http\Requests\UpdateOrderRequest $request
     * @param \App\Models\Order $order
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $request->validated();

        if ($order->status !== Order::STATUS_ACTIVE) {
            return response()->json(['error' => 'Только активные заказы можно редактировать'], 400);
        }

        DB::transaction(function () use ($request, $order) {
            if ($request->has('customer')) {
                $order->customer = $request->customer;
            }

            if ($request->has('warehouse_id')) {
                $order->warehouse_id = $request->warehouse_id;
            }

            $order->save();


            $stock = Product::find($request->items[0]['product_id'])->stocks()->where('warehouse_id', $request->warehouse_id)->first();
            $stock->increment('stock', $request->items[0]['count']);
            StockProductHistory::create([
                'stock' => $stock->id,
                'product_id' => $request->items[0]['product_id'],
                'warehouse_id' => $request->warehouse_id,
                'quantity' => $request->items[0]['count'],
                'type' => 'product_in',
            ]);

            if ($request->has('items')) {
                $order->items()->delete();
                foreach ($request->items as $item) {
                    $order->items()->create($item);
                }
            }
        });

        return response()->json($order->load('items', 'warehouse'));
    }

    /**
     * Завершение заказа
     * @param \App\Models\Order $order
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function complete(Order $order): JsonResponse
    {
        if ($order->status !== Order::STATUS_ACTIVE) {
            return response()->json(['error' => 'Заказ не активен'], 400);
        }
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                Stock::where('warehouse_id', $order->warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->decrement('stock', $item->count);
                StockProductHistory::create([
                    'stock' => Product::find($item->product_id)->stocks()->where('warehouse_id', $order->warehouse_id)->first()->id,
                    'product_id' => $item->product_id,
                    'warehouse_id' => $order->warehouse_id,
                    'quantity' => $item->count,
                    'type' => 'order_completed',
                ]);
            }

            $order->update([
                'status' => Order::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Заказ завершен']);
    }

    /**
     * Отмена заказа
     * @param \App\Models\Order $order
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function cancel(Order $order): JsonResponse
    {
        if ($order->status === Order::STATUS_CANCELED) {
            return response()->json(['error' => 'Заказ уже отменен'], 400);
        }

        DB::transaction(function () use ($order) {
            if ($order->status === Order::STATUS_COMPLETED) {
                foreach ($order->items as $item) {
                    Stock::where('warehouse_id', $order->warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->increment('stock', $item->count);
                    StockProductHistory::create([
                        'stock' => Product::find($item->product_id)->stocks()->where('warehouse_id', $order->warehouse_id)->first()->id,
                        'product_id' => $item->product_id,
                        'warehouse_id' => $order->warehouse_id,
                        'quantity' => $item->count,
                        'type' => 'order_canceled',
                    ]);
                }
            }

            $order->update(['status' => Order::STATUS_CANCELED]);
        });

        return response()->json(['message' => 'Заказ отменен']);
    }

    /**
     * Возобновление заказа
     * @param \App\Models\Order $order
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function resume(Order $order): JsonResponse
    {
        if ($order->status !== Order::STATUS_CANCELED) {
            return response()->json(['error' => 'Только отмененные заказы можно возобновить'], 400);
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $stock = Stock::firstOrCreate(
                    ['warehouse_id' => $order->warehouse_id, 'product_id' => $item->product_id],
                    ['stock' => 0]
                );
                StockProductHistory::create([
                    'stock' => Product::find($item->product_id)->stocks()->where('warehouse_id', $order->warehouse_id)->first()->id,
                    'product_id' => $item->product_id,
                    'warehouse_id' => $order->warehouse_id,
                    'quantity' => $item->count,
                    'type' => 'order_resumed',
                ]);

                if ($stock->stock < $item->count) {
                    throw new \Exception("Недостаточно товара {$item->product_id} на складе");
                }

                $stock->decrement('stock', $item->count);
            }

            $order->update(['status' => Order::STATUS_ACTIVE]);
        });

        return response()->json(['message' => 'Заказ возобновлен']);
    }
}
