<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Получить историю остатков товара
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return JsonResponse
     */
    public function getStockHistory(Request $request, Product $product): JsonResponse
    {
        $stockHistory = StockProductHistory::where('product_id', $product->id)->paginate($request->per_page ?? 10);
        return response()->json($stockHistory);
    }
}
