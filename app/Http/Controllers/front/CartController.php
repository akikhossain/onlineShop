<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $product = Product::with('product_images')->find($request->id);
        if ($product == null) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ]);
        }

        if (Cart::count() > 0) {
            $cartContent = Cart::content();
            $productAlreadyExist = false;
            foreach ($cartContent as $cartItem) {
                if ($cartItem->id == $product->id) {
                    $productAlreadyExist = true;
                }
            }
            if ($productAlreadyExist == false) {
                Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);


                $status = true;
                $message = $product->title . ' added in cart';
            } else {
                $status = false;
                $message = $product->title . ' already in cart';
            }
        } else {
            Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);
            $status = true;
            $message = $product->title . ' added in cart';
        }

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
        // Cart::add('293ad', 'Product 1', 1, 9.99);
    }

    public function cart()
    {
        // dd(Cart::content());
        $cartContent = Cart::content();
        // dd($cartContent);
        return view('Front.cart', compact('cartContent'));
    }
}
