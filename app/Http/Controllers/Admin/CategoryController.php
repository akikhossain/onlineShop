<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->paginate(10);
        // dd($categories);
        return view('Admin.category.list', compact('categories'));
    }

    public function create()
    {
        return view('Admin.category.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:categories',
        ]);
        if ($validator->passes()) {

            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->save();

            // $request->Session()->flash('success', 'Category added Successfully');

            return response()->json([
                'status' => true,
                'message' => 'Category added Successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit()
    {
        return view('viewName');
    }

    public function update()
    {
        return view('viewName');
    }

    public function delete()
    {
        return view('viewName');
    }
}
