<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get all products",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="product_code", type="string", example="P001"),
     *                 @OA\Property(property="product_name", type="string", example="Paracetamol 500mg"),
     *                 @OA\Property(property="generic_name", type="string", example="Paracetamol"),
     *                 @OA\Property(property="strip_size", type="integer", example=10),
     *                 @OA\Property(property="box_size", type="integer", example=10),
     *                 @OA\Property(property="carton_size", type="integer", example=100),
     *                 @OA\Property(property="hsn_code", type="string", example="3004"),
     *                 @OA\Property(property="mrp", type="string", example="20.00"),
     *                 @OA\Property(property="ptr", type="string", example="15.00"),
     *                 @OA\Property(property="pts", type="string", example="12.00"),
     *                 @OA\Property(property="gst", type="string", example="12.00"),
     *                 @OA\Property(property="net_amount", type="string", example="18.00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }
}
