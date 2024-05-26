<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $pages = Page::latest();
        if (!empty($request->get('keyword'))) {
            $pages = $pages->where('name', 'like', '%' . $request->get('keyword') . '%');
        }
        $pages = $pages->paginate(10);
        return view('Admin.page.list', compact('pages'));
    }

    public function create()
    {

        return view('Admin.page.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required',
        ]);
        if ($validator->passes()) {
            $page = new Page();
            $page->name = $request->name;
            $page->slug = $request->slug;
            $page->content = $request->content;
            $page->save();

            session()->flash('success', 'Page created successfully');
            return response()->json([
                'status' => true,
                'message' => 'Page created successfully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ]);
        }
    }

    public function edit(Request $request, $id)
    {
        $page = Page::find($id);
        if ($page == null) {
            session()->flash('error', 'Page not found');
            return redirect()->route('pages.list');
        }
        return view('Admin.page.edit', compact('page'));
    }

    public function update(Request $request, $id)
    {
        $page = Page::find($id);
        if (!$page) {
            session()->flash('error', 'Page not found');
            return redirect()->route('pages.list');
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required',
        ]);
        if ($validator->passes()) {
            $page->name = $request->name;
            $page->slug = $request->slug;
            $page->content = $request->content;
            $page->save();

            session()->flash('success', 'Page updated successfully');
            return response()->json([
                'status' => true,
                'message' => 'Page updated successfully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ]);
        }
    }

    public function destroy(Request $request, $id)
    {
        $page = Page::find($id);
        if ($page == null) {
            session()->flash('error', 'Page not found');
            return redirect()->route('pages.list');
        }
        $page->delete();
        session()->flash('success', 'Page deleted successfully');
        return response()->json([
            'status' => true,
            'message' => 'Page deleted successfully',
        ]);
    }
}
