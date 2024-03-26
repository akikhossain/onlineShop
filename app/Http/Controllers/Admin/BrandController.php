<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function create()
    {
        return view('Admin.Brand.create');
    }

    public function index()
    {
        $brands = Brand::latest('id');
        if (!empty(request()->get('keyword'))) {
            $brands = $brands->where('name', 'like', '%' . request()->get('keyword') . '%');
        }
        $brands = $brands->paginate(10);
        return view('Admin.Brand.list', compact('brands'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:brands',
            'status' => 'required',
        ]);

        if ($validator->passes()) {

            $brand = new Brand();
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;

            $brand->save();

            session()->flash('success', 'Brand Created Successfully');
            return response()->json([
                'status' => true,
                'message' => 'Brand Created Successfully'
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
        $brand = Brand::find($id);
        if (empty($brand)) {
            session()->flash('error', 'Brand not found');
            return redirect()->route('brands.list');
        }
        return view('Admin.Brand.edit', compact('brand'));
    }

    public function update($id, Request $request)
    {
        $brand = Brand::find($id);
        if (empty($brand)) {
            session()->flash('error', 'Brand not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
            ]);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $brand->id . ',id',
            // 'slug' => 'required|unique:brands',
            'status' => 'required',
        ]);

        if ($validator->passes()) {

            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;

            $brand->save();

            session()->flash('success', 'Brand Updated Successfully');
            return response()->json([
                'status' => true,
                'message' => 'Brand Updated Successfully'
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
        $brand = Brand::find($id);
        if (empty($brand)) {
            session()->flash('error', 'Brand not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
            ]);
        }
        $brand->delete();
        session()->flash('success', 'Brand Deleted Successfully');
        return response()->json([
            'status' => true,
            'message' => 'Brand Deleted Successfully'
        ]);
    }
}
