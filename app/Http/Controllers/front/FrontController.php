<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index()
    {
        $products = Product::where('is_featured', 'Yes')
            ->orderBy('id', 'DESC')
            ->where('status', 1)
            ->get();
        $latestProducts = Product::orderBy('id', 'DESC')
            ->where('status', 1)
            ->take(4)
            ->get();
        return view('Front.home', compact('products', 'latestProducts'));
    }
}
