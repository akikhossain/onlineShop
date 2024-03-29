<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;


class ProductController extends Controller
{

    public function index()
    {
        return view('Admin.products.list');
    }
    public function create()
    {
        $categories = Category::orderBy('name', 'asc')->get();
        $brands = Brand::orderBy('name', 'asc')->get();
        return view('Admin.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        // dd($request->image_array);

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

            // if ($request->hasFile('image')) {
            //     $image = $request->file('image');
            //     $name = time() . '.' . $image->getClientOriginalExtension();
            //     $destinationPath = public_path('/uploads/products');
            //     $image->move($destinationPath, $name);
            //     $product->image = $name;
            //     $product->save();
            // }

            // save gallery picture

            if (!empty($request->image_array)) {
                foreach ($request->image_array as $key => $temp_image_id) {

                    $tempImageInfo = TempImage::find($temp_image_id);
                    $extArray = explode('.', $tempImageInfo->name);
                    $ext = last($extArray); // get like jpg, png, gif etc
                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = 'NULL';
                    $productImage->save();

                    $imageName = $product->id . '-' . $productImage->id . '-' . time() . '.' . $ext;
                    $productImage->image = $imageName;
                    $productImage->save();

                    // move image to product image folder
                    // Large Image
                    $sourcePath = public_path() . '//temp/' . $tempImageInfo->name;
                    $destPath = public_path() . '/uploads/products/large/' . $tempImageInfo->name;
                    $image = Image::make($sourcePath);
                    $image->resize(1400, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->save($destPath);

                    // Small Image

                    $destPath = public_path() . '/uploads/products/small/' . $tempImageInfo->name;
                    $image = Image::make($sourcePath);
                    $image->fit(300, 300);
                    $image->save($destPath);
                }
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
