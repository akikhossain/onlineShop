<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request)
    {

        $categories = Category::latest();
        if (!empty($request->get('keyword'))) {
            $categories = $categories->where('name', 'like', '%' . $request->get('keyword') . '%');
        }
        $categories = $categories->paginate(10);
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
            $category->showHome = $request->showHome;
            $category->save();


            // save image here

            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id  . '.' . $ext;

                $sPath = public_path() . '//temp/' . $tempImage->name;
                $dPath = public_path() . '/uploads/category/' . $newImageName;
                File::copy($sPath, $dPath);

                // image thumb

                // $dPath = public_path() . '/uploads/category/thumb/' . $newImageName;
                // $image = Image::make($sPath);
                // $image->resize(450, 600);
                // $image->save($dPath);

                $category->image = $newImageName;
                $category->save();
            }

            session()->flash('success', 'Category added Successfully');
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

    public function edit($categoryId, Request $request)
    {
        $category = Category::find($categoryId);
        if (empty($category)) {
            session()->flash('error', 'Category not found');
            return redirect()->route('categories.list');
        }
        return view('Admin.category.edit', compact('category'));
    }


    public function update($categoryId, Request $request)
    {
        $category = Category::find($categoryId);
        if (empty($category)) {
            session()->flash('error', 'Category not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Category not found'
            ]);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $category->id . ',id',
        ]);
        if ($validator->passes()) {
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->showHome = $request->showHome;
            $category->save();

            $oldImage = $category->image;

            // save image here

            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id . '-' . time() . '.' . $ext;

                $sPath = public_path() . '//temp/' . $tempImage->name;
                $dPath = public_path() . '/uploads/category/' . $newImageName;
                File::copy($sPath, $dPath);

                // image thumb


                // $dPath = public_path() . '/uploads/category/thumb/' . $newImageName;
                // $image = Image::make($sPath);
                // $image->resize(450, 600);
                // $image->save($dPath);

                $category->image = $newImageName;
                $category->save();

                // delete temp image
                File::delete(public_path() . '/uploads/category/' . $oldImage);
                // File::delete(public_path() . '/uploads/category/thumb/' . $category->image);

            }

            session()->flash('success', 'Category updated Successfully');
            return response()->json([
                'status' => true,
                'message' => 'Category updated Successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()

            ]);
        }
    }

    public function destroy($categoryId)
    {
        $category = Category::find($categoryId);
        if (empty($category)) {

            session()->flash('error', 'Category not found');
            return response()->json([
                'status' => true,
                'message' => 'Category not found'
            ]);
        }

        File::delete(public_path() . '/uploads/category/' . $category->image);
        // File::delete(public_path() . '/uploads/category/thumb/' . $category->image);
        $category->delete();

        session()->flash('success', 'Category deleted Successfully');

        return response()->json([
            'status' => true,
            'message' => 'Category deleted Successfully'
        ]);
    }
}
