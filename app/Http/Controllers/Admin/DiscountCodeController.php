<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class DiscountCodeController extends Controller
{
    public function index(Request $request)
    {
        $discountCoupons = DiscountCoupon::latest();
        if (!empty($request->get('keyword'))) {
            $discountCoupons = $discountCoupons->where('name', 'like', '%' . $request->get('keyword') . '%');
            $discountCoupons = $discountCoupons->orWhere('code', 'like', '%' . $request->get('keyword') . '%');
        }
        $discountCoupons = $discountCoupons->paginate(10);
        return view('Admin.coupon.list', compact('discountCoupons'));
    }

    public function create()
    {
        return view('Admin.coupon.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'discount_amount' => 'required|numeric',
            'type' => 'required',
            'status' => 'required',
        ]);

        if ($validator->passes()) {
            // start date must be greater than current date
            if (!empty($request->starts_at)) {
                $now = Carbon::now();
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at);
                if ($startAt->lte($now) == true) {
                    return response()->json([
                        'status' => false,
                        'message' => ['starts_at' => 'Start date must be greater than current date']
                    ]);
                }
            }

            // end date must be greater than start date
            if (!empty($request->starts_at) && !empty($request->expires_at)) {
                $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->expires_at);
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at);
                if ($expiresAt->lte($startAt)) {
                    return response()->json([
                        'status' => false,
                        'message' => ['expires_at' => 'Expiry date must be greater than start date']
                    ]);
                }
            }

            $discountCode = new DiscountCoupon();
            $discountCode->code = $request->code;
            $discountCode->name = $request->name;
            $discountCode->description = $request->description;
            $discountCode->discount_amount = $request->discount_amount;
            $discountCode->type = $request->type;
            $discountCode->status = $request->status;
            $discountCode->max_uses = $request->max_uses;
            $discountCode->max_uses_user = $request->max_uses_user;
            $discountCode->min_amount = $request->min_amount;
            $discountCode->starts_at = $request->starts_at;
            $discountCode->expires_at = $request->expires_at;
            $discountCode->save();

            session()->flash('success', 'Discount Coupon code created successfully');
            return response()->json([
                'status' => true,
                'message' => 'Discount Coupon code created successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }
    }

    public function edit(Request $request, $id)
    {
        $discountCoupon = DiscountCoupon::find($id);

        if (empty($discountCoupon)) {
            return redirect()->route('coupon.list');
        }
        return view('Admin.coupon.edit', compact('discountCoupon'));
    }

    public function update(Request $request, $id)
    {
        $discountCode = DiscountCoupon::find($id);
        if (empty($discountCode)) {
            session()->flash('error', 'Discount Coupon code not found');
            return response()->json([
                'status' => true,
                'message' => 'Discount Coupon code not found'
            ]);
        }
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'discount_amount' => 'required|numeric',
            'type' => 'required',
            'status' => 'required',
        ]);

        if ($validator->passes()) {
            // start date must be greater than current date
            // if (!empty($request->starts_at)) {
            //     $now = Carbon::now();
            //     $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at);
            //     if ($startAt->lte($now) == true) {
            //         return response()->json([
            //             'status' => false,
            //             'message' => ['starts_at' => 'Start date must be greater than current date']
            //         ]);
            //     }
            // }

            // end date must be greater than start date
            if (!empty($request->starts_at) && !empty($request->expires_at)) {
                $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->expires_at);
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at);
                if ($expiresAt->lte($startAt)) {
                    return response()->json([
                        'status' => false,
                        'message' => ['expires_at' => 'Expiry date must be greater than start date']
                    ]);
                }
            }
            $discountCode->code = $request->code;
            $discountCode->name = $request->name;
            $discountCode->description = $request->description;
            $discountCode->discount_amount = $request->discount_amount;
            $discountCode->type = $request->type;
            $discountCode->status = $request->status;
            $discountCode->max_uses = $request->max_uses;
            $discountCode->max_uses_user = $request->max_uses_user;
            $discountCode->min_amount = $request->min_amount;
            $discountCode->starts_at = $request->starts_at;
            $discountCode->expires_at = $request->expires_at;
            $discountCode->save();

            session()->flash('success', 'Discount Coupon code updated successfully');
            return response()->json([
                'status' => true,
                'message' => 'Discount Coupon code updated successfully'

            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }
    }

    public function destroy(Request $request, $id)
    {
        $discountCode = DiscountCoupon::find($id);
        if (empty($discountCode)) {
            session()->flash('error', 'Discount Coupon code not found');
            return response()->json([
                'status' => true,
                'message' => 'Discount Coupon code not found'
            ]);
        }
        $discountCode->delete();
        session()->flash('success', 'Discount Coupon code deleted successfully');
        return response()->json([
            'status' => true,
            'message' => 'Discount Coupon code deleted successfully'
        ]);
    }
}
