<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
{
    try {
        $users = User::with('profile')->get();
        return response()->json([
            'success' => true,
            'users' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->profile->phone_number,
                    'address' => $user->profile->address,
                    'birth_date' => $user->profile->birth_date,
                ];
            }),
        ]);
    } catch (\Exception $e) {
        Log::error('Lỗi khi lấy danh sách người dùng: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Lấy danh sách người dùng thất bại',
        ], 500);
    }
}
    public function getUserByEmail(Request $request)
    {
        $email = $request->query('email');
    
        // Tìm user theo email
        $user = User::with('profile') ->where('email', $email)->first();

        if ($user) {
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->profile->phone_number,
                    'address' => $user->profile->address,
                    'birth_date' => $user->profile->birth_date,
                ],
            ]);
        }
    
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy người dùng với email đã cung cấp',
        ]);
    }
    public function updateProfile(Request $request)
    {
        Log::info('Yêu cầu update nhận được:', $request->all());

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $request->user_id,
            'phone_number' => 'sometimes|string|max:20',
            'address' => 'sometimes|string',
            'birth_date' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $user = User::find($request->user_id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Người dùng không tồn tại'], 404);
            }

            // Cập nhật bảng users
            if ($request->has('name')) {
                $user->name = $request->name;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            $user->save();

            // Cập nhật bảng user_profiles
            $profile = $user->profile;
            if (!$profile) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy hồ sơ người dùng'], 404);
            }

            $profile->update($request->only(['phone_number', 'address', 'birth_date']));

            return response()->json(['success' => true, 'message' => 'Cập nhật thông tin thành công']);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật hồ sơ: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Cập nhật thất bại: ' . $e->getMessage()], 500);
        }
    }
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::find($request->user_id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Người dùng không tồn tại'
                ], 404);
            }

            // Kiểm tra mật khẩu hiện tại
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu hiện tại không đúng'
                ], 401);
            }

            // Cập nhật mật khẩu mới
            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Đổi mật khẩu thành công'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi đổi mật khẩu: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đổi mật khẩu thất bại: ' . $e->getMessage()
            ], 500);
        }
    }
    

}
