<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function showChangePassword()
    {
        return view('Admin.user.changePassword');
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:5',
            'confirm_password' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $admin = User::where('id', Auth::guard('admin')->user()->id)->first();
        if (!Hash::check($request->old_password, $admin->password)) {
            session()->flash('error', 'Old password is incorrect');
            return response()->json([
                'status' => true,
                'errors' => $validator->errors()
            ]);
        }

        User::where('id', Auth::guard('admin')->user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        session()->flash('success', 'Password changed successfully');
        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully'
        ]);
    }
}
