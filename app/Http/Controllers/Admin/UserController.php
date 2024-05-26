<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest();
        if (!empty(request()->get('keyword'))) {
            $users = $users->where('name', 'like', '%' . request()->get('keyword') . '%')
                ->orWhere('email', 'like', '%' . request()->get('keyword') . '%');
        }
        $users = $users->paginate(10);
        return view('Admin.user.list', compact('users'));
    }

    public function create()
    {
        return view('Admin.user.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'status' => 'required',
            'phone' => 'required',
        ]);
        if ($validator->passes()) {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->status = $request->status;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->save();

            session()->flash('success', 'User created successfully');
            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
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
        $user = User::find($id);
        if ($user == null) {
            session()->flash('error', 'User not found');
            return redirect()->route('users.list');
        }
        return view('Admin.user.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if ($user == null) {
            session()->flash('error', 'User not found');
            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
            ]);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id . ',id',
            'status' => 'required',
            'phone' => 'required',
        ]);
        if ($validator->passes()) {

            $user->name = $request->name;
            $user->email = $request->email;
            $user->status = $request->status;
            $user->phone = $request->phone;
            if ($request->password != '') {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            session()->flash('success', 'User updated successfully');
            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
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
        $user = User::find($id);
        if ($user == null) {
            session()->flash('error', 'User not found');
            return response()->json([
                'status' => true,
                'message' => 'User not found',
            ]);
        }
        $user->delete();
        session()->flash('success', 'User deleted successfully');
        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully',
        ]);
    }
}
