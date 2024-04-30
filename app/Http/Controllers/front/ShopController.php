<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;

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
        $product = Product::where('slug', $slug)->with('product_images')->first();
        // dd($product);
        if ($product == null) {
            // return view('Front.product', compact('product'));
            abort(404);
        } else {
            // return redirect()->route('shop');
            return view('Front.product', compact('product'));
        }
    }
}
