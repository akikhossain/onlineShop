<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class SubCategoryController extends Controller
{

    public function index(Request $request)
    {
        $subCategories = SubCategory::select('sub_categories.*', 'categories.name as categoryName')
            ->latest('sub_categories.id')
            ->leftJoin('categories', 'categories.id', '=', 'sub_categories.category_id');

        if (!empty($request->get('keyword'))) {
            $subCategories =  $subCategories->where('sub_categories.name', 'like', '%' . $request->get('keyword') . '%');
            $subCategories =  $subCategories->orWhere('categories.name', 'like', '%' . $request->get('keyword') . '%');
        }
        $subCategories =  $subCategories->paginate(10);
        // dd($categories);
        return view('Admin.sub_category.list', compact('subCategories'));
    }
    public function create()
    {
        $categories = Category::orderBy("name", "asc")->get();
        return view('admin.sub_category.create', compact('categories'));
    }
    public function store(Request $request)
    {
        $validator = FacadesValidator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'category' => 'required',
            'status' => 'required',
        ]);

        if ($validator->passes()) {
            $subCategory = new SubCategory();
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            session()->flash('success', ' Sub Category added Successfully');
            return response()->json([
                'status' => true,
                'message' => 'Sub Category added Successfully'
            ]);
        } else {
            return response([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function edit($id, Request $request)
    {
        $subCategory = SubCategory::find($id);
        if (!$subCategory) {
            session()->flash('error', 'Sub Category not found');
            return redirect()->route('sub-categories.list');
        }
        $categories = Category::orderBy("name", "asc")->get();
        return view('Admin.sub_category.edit', compact('subCategory', 'categories'));
    }

    public function update($id, Request $request)
    {
        $subCategory = SubCategory::find($id);
        if (!$subCategory) {
            session()->flash('error', 'Sub Category not found');
            return response()->json([
                'status' => false,
                'notFound' => true
            ]);
        }
        $validator = FacadesValidator::make($request->all(), [
            'name' => 'required',
            // 'slug' => 'required|unique:sub_categories',
            'slug' => 'required|unique:sub_categories,slug,' . $subCategory->id . ',id',
            'category' => 'required',
            'status' => 'required',
        ]);

        if ($validator->passes()) {

            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            session()->flash('success', ' Sub Category Updated Successfully');
            return response()->json([
                'status' => true,
                'message' => 'Sub Category updated Successfully'
            ]);
        } else {
            return response([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function destroy($id, Request $request)
    {
        $subCategory = SubCategory::find($id);
        if (!$subCategory) {
            session()->flash('error', 'Sub Category not found');
            return response()->json([
                'status' => false,
                'notFound' => true
            ]);
        }
        $subCategory->delete();
        session()->flash('success', 'Sub Category Deleted Successfully');
        return response()->json([
            'status' => true,
            'message' => 'Sub Category Deleted Successfully'
        ]);
    }
}
