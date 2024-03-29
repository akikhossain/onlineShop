<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function create()
    {
        $categories = Category::orderBy('name', 'asc')->get();
        $brands = Brand::orderBy('name', 'asc')->get();
        return view('Admin.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
            'slug' => 'required | unique:products',
            'category' => 'required',
            // 'brand_id' => 'required',
            'price' => 'required | numeric',
            // 'quantity' => 'required',
            'sku' => 'required | unique:products',
            'track_qty' => 'required | in:Yes,No',
            'is_featured' => 'required | in:Yes,No',
            // 'description' => 'required',
            // 'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        if (!empty($request->track_qty) && $request->track_qty == 'Yes') {
            $rules['qty'] = 'required | numeric';
        }
        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $product = new Product();
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->category_id = $request->category;
            $product->barcode = $request->barcode;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->track_qty = $request->track_qty;
            $product->is_featured = $request->is_featured;
            $product->description = $request->description;
            $product->qty = $request->qty;
            $product->save();

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $name = time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/uploads/products');
                $image->move($destinationPath, $name);
                $product->image = $name;
                $product->save();
            }

            session()->flash('success', 'Product added successfully');
            return response()->json([
                'status' => true,
                'message' => 'Product added successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
}
