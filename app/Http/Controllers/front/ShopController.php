<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductRating;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    public function index(Request $request, $categorySlug = null, $subCategorySlug = null)
    {

        $categorySelected = '';
        $subCategorySelected = '';
        $brandsArray = [];

        if (!empty($request->get('brand'))) {
            $brandsArray = explode(',', $request->get('brand'));
        }


        $categories = Category::orderBy('name', 'ASC')->with('sub_category')->where('status', 1)->get();
        $brands = Brand::orderBy('name', 'ASC')->where('status', 1)->get();
        $products = Product::where('status', 1);


        // Apply Filter here
        if (!empty($categorySlug)) {
            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                $products = $products->where('category_id', $category->id);
                $categorySelected = $category->id;
            }
        }
        if (!empty($subCategorySlug)) {
            $subCategory = SubCategory::where('slug', $subCategorySlug)->first();
            if ($subCategory) {
                $products = $products->where('sub_category_id', $subCategory->id);
                $subCategorySelected = $subCategory->id;
            }
        }

        if (!empty($request->get('brand'))) {
            $brandsArray = explode(',', $request->get('brand'));
            $products = $products->whereIn('brand_id', $brandsArray);
        }

        if (($request->get('price_max') != '' && $request->get('price_min') != '')) {
            if ($request->get('price_max') == 200000) {
                $products = $products->whereBetween('price', [intval($request->get('price_min')), 1000000]);
            } else {
                $products = $products->whereBetween('price', [intval($request->get('price_min')), intval($request->get('price_max'))]);
            }
        }

        $priceMax = (intval($request->get('price_max')) == 0) ? 1000 : intval($request->get('price_max'));
        $priceMin = intval($request->get('price_min'));
        $sort = $request->get('sort');


        if (!empty($request->get('search'))) {
            $products = $products->where('title', 'like', '%' . $request->get('search') . '%');
        }

        if ($request->get('sort') != '') {
            if ($request->get('sort') == 'latest') {
                $products = $products->orderBy('id', 'DESC');
            } else if ($request->get('sort') == 'price_asc') {
                $products = $products->orderBy('price', 'ASC');
            } else {
                $products = $products->orderBy('price', 'DESC');
            }
        } else {
            $products = $products->orderBy('id', 'DESC');
        }
        $products = $products->paginate(12);
        return view('Front.shop', compact('categories', 'brands', 'products', 'categorySelected', 'subCategorySelected', 'brandsArray', 'priceMax', 'priceMin', 'sort'));
    }

    public function product($slug)
    {
        $product = Product::where('slug', $slug)
            ->withCount('productRatings')
            ->withSum('productRatings', 'rating')
            ->with(['product_images', 'productRatings'])->first();
        // dd($product);
        if ($product == null) {
            abort(404);
        }

        $relatedProducts = [];
        if ($product->related_products != '') {
            $productArray = explode(',', $product->related_products);
            $relatedProducts = Product::whereIn('id', $productArray)->where('status', 1)->get();
        }

        // "product_ratings_count" => 1
        // "product_ratings_sum_rating" => 5.0

        // rating calculation
        $rating = 0;
        $avgRatingPer = 0;
        if ($product->product_ratings_count > 0) {
            $rating = number_format(($product->product_ratings_sum_rating / $product->product_ratings_count), 1);
            $avgRatingPer = ($rating / 5) * 100;
        }

        // return redirect()->route('shop');
        return view('Front.product', compact('product', 'relatedProducts', 'rating', 'avgRatingPer'));
    }

    public function saveRating($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:5',
            'email' => 'required|email',
            'comment' => 'required|min:10',
            'rating' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $count = ProductRating::where('email', $request->email)->count();
        if ($count > 0) {
            session()->flash('error', 'You have already submitted a review');
            return response()->json([
                'status' => true,
                'message' => 'You have already submitted a review'
            ]);
        }

        $productRating = new ProductRating();
        $productRating->product_id = $id;
        $productRating->username = $request->name;
        $productRating->email = $request->email;
        $productRating->comment = $request->comment;
        $productRating->rating = $request->rating;
        $productRating->status = 0;
        $productRating->save();

        session()->flash('success', 'Your review has been submitted successfully');
        return response()->json([
            'status' => true,
            'message' => 'Your review has been submitted successfully'
        ]);
    }
}
