<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProfile;
use Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5|confirmed',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        UserProfile::create([
            'user_id' =>$user->id,
        ]);

        return response()->json(['message' => 'Registration successful']);
    }

    public function login(Request $request)
{
    // Xác thực thông tin người dùng
    $credentials = $request->only('email', 'password');

    // Kiểm tra nếu email và mật khẩu hợp lệ
    if (Auth::attempt($credentials)) {
        return response()->json(['message' => 'Đăng nhập thành công']);
    } else {
        return response()->json(['message' => 'Thông tin đăng nhập không hợp lệ']);
    }
    
}






}
