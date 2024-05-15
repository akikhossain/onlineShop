<?php

use App\Models\Category;
use App\Models\ProductImage;

function getCategories()
{
    return Category::orderBy('name', 'asc')
        ->with('sub_category')
        ->orderBy('id', 'DESC')
        ->where('showHome', 'Yes')
        ->where('status', 1)
        ->get();
}

function getProductImage($productId)
{
    return ProductImage::where('product_id', $productId)->first();
}
