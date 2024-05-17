<?php

use App\Models\Category;
use App\Models\Order;
use App\Mail\orderEmail;
use App\Models\Country;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Mail;

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

function orderEmail($orderId)
{
    $order = Order::where('id', $orderId)->with('items')->first();
    $mailData = [
        'subject' => 'thanks for your order',
        'order' => $order,
    ];
    Mail::to($order->email)->send(new OrderEmail($mailData));
    // dd($order);
}

function getCountryInfo($countryId)
{
    return Country::where('id', $countryId)->first();
}
