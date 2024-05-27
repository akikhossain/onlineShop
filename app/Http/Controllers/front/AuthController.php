<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CustomerAdress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login()
    {
        return view('Front.account.login');
    }

    public function register()
    {
        return view('Front.account.register');
    }

    public function processRegister(Request $request)
    {
        // return view('viewName');
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->passes()) {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->save();

            session()->flash('success', 'You have been registered successfully');

            return response()->json([
                'status' => true,
                // 'message' => 'User created successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->passes()) {

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {

                if (session()->has('url.intended')) {
                    return redirect(session()->get('url.intended'));
                }

                // Authentication passed...
                return redirect()->route('account.profile')
                    ->with('success', 'You have been logged in');
            } else {
                return redirect()->route('account.login')
                    ->withInput($request->only('email'))
                    ->with('error', 'Either an Email or a Password is Incorrect');
            }
        } else {
            // session()->flash('error', 'Invalid credentials');
            return redirect()->route('account.login')
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }
    }

    public function profile()
    {
        $userId = Auth::user()->id;
        $countries = Country::orderBy('name', 'ASC')->get();
        $user = User::where('id', $userId)->first();
        $address = CustomerAdress::where('user_id', $userId)->first();
        return view('Front.account.profile', compact('user', 'countries', 'address'));
    }

    public function updateProfile(Request $request)
    {
        $userId = Auth::user()->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $userId . ',id',
            'phone' => 'required',
        ]);

        if ($validator->passes()) {
            $user = User::find($userId);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->save();

            session()->flash('success', 'Profile updated successfully');
            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function updateAddress(Request $request)
    {
        $userId = Auth::user()->id;
        $validator = Validator::make(request()->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'country_id' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'mobile' => 'required',
        ]);
        if ($validator->passes()) {

            CustomerAdress::updateOrCreate(
                ['user_id' => $userId],
                [
                    'user_id' => $userId,
                    'first_name' => request()->first_name,
                    'last_name' => request()->last_name,
                    'email' => request()->email,
                    'country_id' => request()->country_id,
                    'address' => request()->address,
                    'city' => request()->city,
                    'state' => request()->state,
                    'zip' => request()->zip,
                    'mobile' => request()->mobile,
                    'apartment' => request()->apartment,
                ]
            );

            session()->flash('success', 'Address updated successfully');
            return response()->json([
                'status' => true,
                'message' => 'Address updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('account.login')
            ->with('success', 'You have been logged out');
    }

    public function orders()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
            ->orderBy('created_at', 'DESC')
            ->get();
        return view('Front.account.orders', compact('orders'));
    }

    public function orderDetail($id)
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)
            ->where('id', $id)
            ->first();
        $orderItems = OrderItem::where('order_id', $order->id)
            ->get();
        $orderItemsCount = OrderItem::where('order_id', $order->id)
            ->count();
        return view('Front.account.orderDetail', compact('order', 'orderItems', 'orderItemsCount'));
    }

    public function wishlist()
    {
        $wishlists = Wishlist::where('user_id', Auth::user()->id)
            ->with('product')
            ->get();
        return view('Front.account.wishlist', compact('wishlists'));
    }

    public function removeWishlist()
    {
        $wishlist = Wishlist::where('user_id', Auth::user()->id)
            ->where('product_id', request()->id)
            ->first();

        if ($wishlist == null) {
            session()->flash('error', 'Product not found in wishlist');
            return response()->json([
                'status' => false,
                'message' => 'Product not found in wishlist'
            ]);
        }
        $wishlist->delete();
        session()->flash('success', 'Product removed from wishlist');
        return response()->json([
            'status' => true,
            'message' => 'Product removed from wishlist'
        ]);
    }

    public function showChangePassword()
    {
        return view('Front.account.changePassword');
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:5',
            'confirm_password' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $user = User::select('id', 'password')->where('id', Auth::user()->id)->first();
        if (!Hash::check($request->old_password, $user->password)) {
            session()->flash('error', 'Old password is incorrect');
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        User::where('id', Auth::user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        session()->flash('success', 'Password changed successfully');
        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully'
        ]);
    }
}
