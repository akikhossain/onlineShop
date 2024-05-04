<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ShippingCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingController extends Controller
{
    public function create()
    {
        $countries = Country::get();
        $shippingCharges = ShippingCharge::select('shipping_charges.*', 'countries.name')
            ->leftJoin('countries', 'countries.id', 'shipping_charges.country_id')->get();
        return view('Admin.shipping.create', compact('countries', 'shippingCharges'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country' => 'required',
            'amount' => 'required|numeric',
        ]);

        if ($validator->passes()) {
            $count = ShippingCharge::where('country_id', $request->country)->count();
            if ($count > 0) {
                session()->flash('error', 'Shipping already exists for this country');
                return response()->json([
                    'status' => true,
                ]);
            }
            $shipping = new ShippingCharge();
            $shipping->country_id = $request->country;
            $shipping->amount = $request->amount;
            $shipping->save();


            session()->flash('success', 'Shipping created successfully');
            return response()->json([
                'status' => true,
                'message' => 'Shipping created successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ]);
        }
    }

    public function edit($id)
    {
        $shipping = ShippingCharge::find($id);
        // return response()->json([
        //     'status' => true,
        //     'shipping' => $shipping
        // ]);

        $countries = Country::get();
        return view('Admin.shipping.edit', compact('countries', 'shipping'));
    }

    public function update($id, Request $request)
    {
        $shipping = ShippingCharge::find($id);
        $validator = Validator::make($request->all(), [
            'country' => 'required',
            'amount' => 'required|numeric',
        ]);

        if ($validator->passes()) {
            if (!$shipping) {
                session()->flash('error', 'Shipping not found');
                return response()->json([
                    'status' => true,
                ]);
            }
            $shipping->country_id = $request->country;
            $shipping->amount = $request->amount;
            $shipping->save();


            session()->flash('success', 'Shipping Updated successfully');
            return response()->json([
                'status' => true,
                'message' => 'Shipping Updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ]);
        }
    }

    public function destroy($id)
    {
        $shipping = ShippingCharge::find($id);
        if (!$shipping) {
            session()->flash('error', 'Shipping not found');
            return response()->json([
                'status' => true,
            ]);
        }
        $shipping->delete();
        session()->flash('success', 'Shipping deleted successfully');
        return response()->json([
            'status' => true,
            'message' => 'Shipping deleted successfully'
        ]);
    }
}
