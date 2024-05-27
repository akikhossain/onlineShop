<?php

use App\Models\Category;
use App\Models\Order;
use App\Mail\orderEmail;
use App\Models\Country;
use App\Models\Page;
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

function orderEmail($orderId, $userType = "customer")
{
    $order = Order::where('id', $orderId)->with('items')->first();
    if ($userType == 'customer') {
        $subject = 'Thanks for your order';
        $email = $order->email;
    } else {
        $subject = 'New Order Received';
        $email = env('ADMIN_EMAIL');
    }
    $mailData = [
        'subject' => $subject,
        'order' => $order,
        'userType' => $userType,
    ];
    Mail::to($email)->send(new OrderEmail($mailData));
    // dd($order);
}

function getCountryInfo($countryId)
{
    return Country::where('id', $countryId)->first();
}

function staticPages()
{
    $pages = Page::orderBy('name', 'ASC')->get();
    return $pages;
}
