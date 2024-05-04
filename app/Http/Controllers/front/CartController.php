<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CustomerAdress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
                $message = '<strong>' . $product->title . '</strong> added in your cart successfully.';
                session()->flash('success', $message);
            } else {
                $status = false;
                $message = $product->title . ' already in cart';
            }
        } else {
            Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);
            $status = true;
            $message = '<strong>' . $product->title . '</strong> added in your cart successfully.';
            session()->flash('success', $message);
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

    public function updateCart(Request $request)
    {
        $rowId = $request->rowId;
        $qty = $request->qty;

        // check qty available in stock
        $itemInfo = Cart::get($rowId);
        $product = Product::find($itemInfo->id);
        if ($product->track_qty == 'Yes') {
            if ($qty <= $product->qty) {
                Cart::update($rowId, $qty);
                $message = 'Cart Updated Successfully';
                $status = true;
                session()->flash('success', $message);
            } else {
                $message = 'Sorry, Requested Quantity (' . $qty . ') not available in stock';
                $status = false;
                session()->flash('error', $message);
            }
        } else {
            Cart::update($rowId, $qty);
            $message = 'Cart Updated Successfully';
            $status = true;
            session()->flash('success', $message);
        }
        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function deleteItem(Request $request)
    {
        $rowId = $request->rowId;
        $itemInfo = Cart::get($rowId);
        if ($itemInfo == null) {
            $errorMessage = 'Item not found';
            session()->flash('success',  $errorMessage);
            return response()->json([
                'status' => false,
                'message' => $errorMessage
            ]);
        }

        Cart::remove($request->rowId);
        $message = 'Item removed from the cart';
        session()->flash('success', $message);
        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }

    public function checkOut()
    {

        // if cart item is empty
        if (Cart::count() == 0) {
            return redirect()->route('front.cart');
        }
        // if user is not logged in
        if (Auth::check() == false) {
            if (!session()->has('url.intended')) {
                session(['url.intended' => url()->current()]);
            }
            return redirect()->route('account.login');
        }

        session()->forget('url.intended');


        $customerAddress = CustomerAdress::where('user_id', Auth::user()->id)->first();

        $countries = Country::orderBy('name', 'ASC')->get();
        return view('Front.checkout', compact('countries', 'customerAddress'));
    }

    public function processCheckout(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'country' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'mobile' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $user = Auth::user();
        CustomerAdress::updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'first_name' => request()->first_name,
                'last_name' => request()->last_name,
                'email' => request()->email,
                'country_id' => request()->country,
                'address' => request()->address,
                'city' => request()->city,
                'state' => request()->state,
                'zip' => request()->zip,
                'mobile' => request()->mobile,
                'apartment' => request()->apartment,
            ]
        );


        // step 3: store data in order table
        if ($request->payment_method == 'cod') {
            $shipping = 0;
            $discount = 0;
            $subTotal = Cart::subtotal(2, '.', '');
            $grandTotal = $subTotal + $shipping;



            $order = new Order();
            $order->subtotal = $subTotal;
            $order->shipping = $shipping;
            $order->grand_total = $grandTotal;
            $order->user_id = $user->id;



            $order->first_name = request()->first_name;
            $order->last_name = request()->last_name;
            $order->email = request()->email;
            $order->country_id = request()->country;
            $order->address = request()->address;
            $order->city = request()->city;
            $order->state = request()->state;
            $order->zip = request()->zip;
            $order->mobile = request()->mobile;
            $order->notes = request()->order_notes;
            $order->apartment = request()->apartment;
            $order->save();


            // step 4: store data in order item table
            foreach (Cart::content() as $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $item->id;
                $orderItem->name = $item->name;
                $orderItem->qty = $item->qty;
                $orderItem->price = $item->price;
                $orderItem->total = $item->price * $item->qty;
                $orderItem->save();
            }

            session()->flash('success', 'Order placed successfully');
            Cart::destroy();
            return response()->json([
                'status' => true,
                'orderId' => $order->id,
                'message' => 'Order placed successfully'
            ]);
        }
    }

    public function thankyou($id)
    {
        return view('Front.thankyou', compact('id'));
    }
}
