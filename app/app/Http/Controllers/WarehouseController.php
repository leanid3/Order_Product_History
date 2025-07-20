<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;

class WarehouseController extends Controller
{
    /**
     * Получить список складов
     */
    public function index()
    {
        $warehouses = Warehouse::with('stocks')->get();
        return response()->json($warehouses);
    }

}
