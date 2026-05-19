<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use ErrorException;
use PhpParser\Node\Stmt\TryCatch;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $product = Product::all();
        return response()->json([
            'status' => true,
            'data' => $product
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create([
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => $request->slug,
            'thumbnail_url' => $request->thumbnail_url,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'is_featured' => $request->is_featured,
            'status' => $request->status
        ]);
        return response()->json([
            'message' => 'Thêm thành công',
            'data' => $product
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {

        return response()->json([
            'status' => true,
            'data' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update([
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => $request->slug,
            'thumbnail_url' => $request->thumbnail_url,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'is_featured' => $request->is_featured,
            'status' => $request->status
        ]);
        return response()->json([
            'message' => 'update thành công',
            'data' => $product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return response()->json([
                'message' => 'xóa thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
