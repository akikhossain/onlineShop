<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::leftJoin('users', 'users.id', 'orders.user_id')
            ->select('orders.*', 'users.name', 'users.phone')
            ->latest('orders.created_at');

        if (request()->has('keyword') && request()->keyword != '') {
            $keyword = '%' . request()->keyword . '%';
            $orders->where(function ($query) use ($keyword) {
                $query->where('users.name', 'like', $keyword)
                    ->orWhere('users.email', 'like', $keyword)
                    ->orWhere('orders.id', 'like', $keyword);
            });
        }

        $orders = $orders->paginate(10);

        return view('Admin.order.list', compact('orders'));
    }


    public function detail($orderId)
    {
        $order = Order::select('orders.*', 'countries.name as countryName')
            ->where('orders.id', $orderId)
            ->leftJoin('countries', 'countries.id', 'orders.country_id')
            ->first();

        $orderItems = OrderItem::where('order_id', $orderId)->get();
        return view('Admin.order.detail', compact('order', 'orderItems'));
    }

    public function changeStatusForm(Request $request, $orderId)
    {
        $order = Order::find($orderId);
        $order->status = $request->status;
        $order->shipped_date = $request->shipped_date;
        $order->save();

        session()->flash('success', 'Order status updated successfully');
        return response()->json([
            'status' => true,
            'message' => 'Order status updated successfully'
        ]);
    }

    public function sendInvoiceEmail(Request $request, $orderId)
    {
        // echo 'Email sent successfully';
        orderEmail($orderId, $request->userType);
        $message = 'Email sent successfully';
        session()->flash('success', $message);
        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }
}
