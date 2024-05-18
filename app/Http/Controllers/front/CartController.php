<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CustomerAdress;
use App\Models\DiscountCoupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingCharge;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            session()->flash('success', $errorMessage);
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

        $discount = 0;
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

        $subTotal = Cart::subtotal(2, '.', '');

        if (session()->has('code')) {
            $code = session()->get('code');
            if ($code->type == 'percent') {
                $discount = ($code->discount_amount / 100) * $subTotal;
            } else {
                $discount = $code->discount_amount;
            }
        }


        // Calculate shipping charges
        if ($customerAddress != '') {
            $userCountry = $customerAddress->country_id;
            $shippingInfo = ShippingCharge::where('country_id', $userCountry)->first();
            // echo $shippingInfo->amount;
            $totalQty = 0;
            $totalShipping = 0;
            $grandTotal = 0;
            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }
            if ($shippingInfo != null) {
                $totalShipping = $shippingInfo->amount;
                $grandTotal = ($subTotal - $discount) + $totalShipping;
            } else {
                $totalShipping = 0;
                $grandTotal = ($subTotal - $discount);
            }
        } else {
            $totalShipping = 0;
            $grandTotal = ($subTotal - $discount);
        }

        return view('Front.checkout', compact('countries', 'customerAddress', 'totalShipping', 'grandTotal', 'discount'));
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

            $discountCode = null;
            $promoCode = '';
            $shipping = 0;
            $discount = 0;
            $subTotal = Cart::subtotal(2, '.', '');
            if (session()->has('code')) {
                $code = session()->get('code');
                if ($code->type == 'percent') {
                    $discount = ($code->discount_amount / 100) * $subTotal;
                } else {
                    $discount = $code->discount_amount;
                }
                $discountCode = $code->id;
                $promoCode = $code->code;
            }

            $shippingInfo = ShippingCharge::where('country_id', $request->country)->first();
            $totalQty = 0;
            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }

            if ($shippingInfo != null) {
                $shipping = $shippingInfo->amount;
                $grandTotal = ($subTotal - $discount) + $shipping;
            } else {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();
                $shipping = $shippingInfo->amount;
                $grandTotal = ($subTotal - $discount) + $shipping;
            }


            $order = new Order();
            $order->subtotal = $subTotal;
            $order->shipping = $shipping;
            $order->grand_total = $grandTotal;
            $order->discount = $discount;
            $order->payment_status = 'not paid';
            $order->status = 'pending';
            $order->coupon_code_id = $discountCode;
            $order->coupon_code = $promoCode;
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

            // send email to user
            orderEmail($order->id, 'customer');

            session()->flash('success', 'Order placed successfully');
            Cart::destroy();
            session()->forget('code');
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

    public function getOrderSummery(Request $request)
    {
        $subTotal = Cart::subtotal(2, '.', '');
        $discount = 0;
        $discountString = '';

        // apply discount code here
        if (session()->has('code')) {
            $code = session()->get('code');
            if ($code->type == 'percent') {
                $discount = ($code->discount_amount / 100) * $subTotal;
            } else {
                $discount = $code->discount_amount;
            }
            $discountString = '  <div class="mt-4 d-flex align-content-center gap-1" id="discount-response">
            <strong class="fs-5">' . session()->get('code')->code . '</strong>
            <button class="btn btn-sm btn-danger" id="remove-discount"><i class="fa fa-times"></i></button>
            </div>';
        }

        if ($request->country_id > 0) {

            $shippingInfo = ShippingCharge::where('country_id', $request->country_id)->first();

            $totalQty = 0;
            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }
            if ($shippingInfo != null) {
                $shippingCharge = $shippingInfo->amount;
                $grandTotal = ($subTotal - $discount) + $shippingCharge;

                return response()->json([
                    'status' => true,
                    'shippingCharge' => number_format($shippingCharge, 2),
                    'discount' => number_format($discount, 2),
                    'discountString' => $discountString,
                    'grandTotal' => number_format($grandTotal, 2)
                ]);
            } else {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();
                $shippingCharge = $shippingInfo->amount;
                $grandTotal = ($subTotal - $discount) + $shippingCharge;
                return response()->json([
                    'status' => true,
                    'shippingCharge' => number_format($shippingCharge, 2),
                    'discount' => number_format($discount, 2),
                    'discountString' => $discountString,
                    'grandTotal' => number_format($grandTotal, 2)
                ]);
            }
        } else {
            return response()->json([
                'status' => true,
                'grandTotal' => number_format(($subTotal - $discount), 2),
                'discount' => number_format($discount, 2),
                'discountString' => $discountString,
                'shippingCharge' => number_format(0, 2)
            ]);
        }
    }

    public function applyDiscount(Request $request)
    {
        $code = DiscountCoupon::where('code', $request->code)->first();
        if ($code == null) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Coupon Code'
            ]);
        }

        // check if coupon date is valid or not
        $now = Carbon::now();
        // echo $now->format('Y-m-d H:i:s');
        if ($code->starts_at != '') {
            $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $code->starts_at);
            if ($now->lt($startDate)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Coupon code is not valid yet'
                ]);
            }
        }

        if ($code->expires_at != '') {
            $endDate = Carbon::createFromFormat('Y-m-d H:i:s', $code->expires_at);
            if ($now->gt($endDate)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Coupon code is not valid yet'
                ]);
            }
        }

        // max uses of coupon check
        if ($code->max_uses > 0) {
            $couponUsed = Order::where('coupon_code_id', $code->id)->count();
            if ($couponUsed >= $code->max_uses) {
                return response()->json([
                    'status' => false,
                    'message' => 'Coupon code is expired'
                ]);
            }
        }

        // max uses per user check
        if ($code->max_uses_user > 0) {
            $couponUsedByUser = Order::where('coupon_code_id', $code->id)->where('user_id', Auth::user()->id)->count();
            if ($couponUsedByUser >= $code->max_uses_user) {
                return response()->json([
                    'status' => false,
                    'message' => 'You have already used this coupon code'
                ]);
            }
        }

        // Minimum Amount Condition check
        $subTotal = Cart::subtotal(2, '.', '');
        if ($code->min_amount > 0) {
            if ($subTotal < $code->min_amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Minimum amount should be $' . $code->min_amount . ' to apply this coupon code.'
                ]);
            }
        }

        session()->put('code', $code);
        return $this->getOrderSummery($request);
    }

    public function removeCoupon(Request $request)
    {
        session()->forget('code');
        return $this->getOrderSummery($request);
    }
}
