<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Получить список товаров с остатками
     * @param Request $request
     * @return JsonResponse
     */
    public function getProductsWhithStock(Request $request): JsonResponse
    {
        $products = Product::with(('stocks'))->paginate($request->per_page ?? 10);
        return response()->json($products);
    }
}
