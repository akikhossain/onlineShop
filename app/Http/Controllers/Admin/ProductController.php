<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SubCategory;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;


class ProductController extends Controller
{

    public function index(Request $request)
    {
        $products = Product::latest('id')->with('product_images');
        if (!empty($request->get('keyword'))) {
            $products = $products->where('title', 'like', '%' . $request->get('keyword') . '%');
        }
        $products = $products->paginate(10);
        // dd($products);
        return view('Admin.products.list', compact('products'));
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
            'price' => 'required | numeric',
            'sku' => 'required | unique:products',
            'track_qty' => 'required | in:Yes,No',
            'is_featured' => 'required | in:Yes,No',

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
            $product->short_description = $request->short_description;
            $product->shipping_returns = $request->shipping_returns;
            $product->related_products = (!empty($request->related_products)) ? implode(',', $request->related_products) : '';
            $product->save();

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
                    $destPath = public_path() . '/uploads/products/large/' . $imageName;
                    $image = Image::make($sourcePath);
                    $image->resize(1400, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->save($destPath);

                    // Small Image

                    $destPath = public_path() . '/uploads/products/small/' . $imageName;
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

    public function edit($productId)
    {
        $product = Product::find($productId);
        if (empty($product)) {
            session()->flash('error', 'Product not found');
            return redirect()->route('products.list');
        }

        $productImages = ProductImage::where('product_id', $product->id)->get();
        $categories = Category::orderBy('name', 'asc')->get();
        $subCategories = SubCategory::where('category_id', $product->category_id)->get();
        // dd($subCategories);
        $brands = Brand::orderBy('name', 'asc')->get();

        // fetch related products
        $relatedProducts = [];
        if ($product->related_products != '') {
            $productArray = explode(',', $product->related_products);
            $relatedProducts = Product::whereIn('id', $productArray)->with('product_images')->get();
        }


        return view('Admin.products.edit', compact('product', 'categories', 'brands', 'subCategories', 'productImages', 'relatedProducts'));
    }

    public function update($id, Request $request)
    {
        $product = Product::find($id);

        $rules = [
            'title' => 'required',
            'slug' => 'required | unique:products,slug,' . $product->id . ',id',
            'category' => 'required',
            'price' => 'required | numeric',
            'sku' => 'required | unique:products,sku,' . $product->id . ',id',
            'track_qty' => 'required | in:Yes,No',
            'is_featured' => 'required | in:Yes,No',
        ];

        if (!empty($request->track_qty) && $request->track_qty == 'Yes') {
            $rules['qty'] = 'required | numeric';
        }
        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
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
            $product->short_description = $request->short_description;
            $product->shipping_returns = $request->shipping_returns;
            $product->related_products = (!empty($request->related_products)) ? implode(',', $request->related_products) : '';
            $product->save();

            session()->flash('success', 'Product Updated successfully');
            return response()->json([
                'status' => true,
                'message' => 'Product Updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($id, Request $request)
    {
        $product = Product::find($id);
        if (empty($product)) {
            session()->flash('error', 'Product not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
            ]);
        }

        $productImages = ProductImage::where('product_id', $id)->get();
        if (!empty($productImages)) {
            foreach ($productImages as $productImage) {
                File::delete(public_path() . '/uploads/products/large/' . $productImage->image);
                File::delete(public_path() . '/uploads/products/small/' . $productImage->image);
            }

            ProductImage::where('product_id', $id)->delete();
        }
        $product->delete();

        session()->flash('success', 'Product deleted successfully');
        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    public function getProducts(Request $request)
    {
        $tempProduct = [];
        if ($request->term != '') {
            $products = Product::where('title', 'like', '%' . $request->term . '%')->get();
            if ($products != null) {
                foreach ($products as $product) {
                    $tempProduct[] = array('id' => $product->id, 'text' => $product->title);
                }
            }
        }
        // print_r($tempProduct);
        return response()->json([
            'tags' => $tempProduct,
            'status' => true,
        ]);
    }
}
