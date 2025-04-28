<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\PendingUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users|unique:pending_users',
            'password' => 'required|string|min:4|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create pending user
        $verificationCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $pendingUser = PendingUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'verification_code' => $verificationCode,
        ]);

        // Send verification email
        try {
            Mail::send([], ['code' => $verificationCode, 'user' => $pendingUser], function ($message) use ($pendingUser, $verificationCode) {
                $message->to($pendingUser->email)
                        ->subject('Mã xác nhận đăng ký')
                        ->html($this->getVerificationEmailContent($pendingUser, $verificationCode), 'text/html');
            });
        } catch (\Exception $e) {
            Log::error('Lỗi khi gửi email xác nhận: ' . $e->getMessage());
            $pendingUser->delete();
            return response()->json([
                'message' => 'Không thể gửi email xác nhận. Vui lòng thử lại.'
            ], 500);
        }

        return response()->json([
            'message' => 'Đăng ký thành công. Vui lòng kiểm tra email để xác nhận.',
            'email' => $pendingUser->email
        ]);
    }

    private function getVerificationEmailContent($user, $code)
    {
        return '
            <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
                <h2 style="color: #333;">Xác nhận đăng ký tài khoản</h2>
                <p>Xin chào ' . htmlspecialchars($user->name) . ',</p>
                <p>Cảm ơn bạn đã đăng ký. Đây là mã xác nhận của bạn:</p>
                <h3 style="color: #007bff;">' . htmlspecialchars($code) . '</h3>
                <p>Mã này có hiệu lực trong 30 phút. Vui lòng nhập mã này để hoàn tất đăng ký.</p>
                <p>Nếu bạn không đăng ký, vui lòng bỏ qua email này.</p>
                <p>Trân trọng,<br>Đội ngũ hỗ trợ</p>
            </div>
        ';
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:pending_users,email',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $pendingUser = PendingUser::where('email', $request->email)
                                 ->where('verification_code', $request->code)
                                 ->first();

        if (!$pendingUser) {
            return response()->json([
                'message' => 'Mã xác nhận không hợp lệ'
            ], 400);
        }

        // Create user in users table
        $user = User::create([
            'name' => $pendingUser->name,
            'email' => $pendingUser->email,
            'password' => $pendingUser->password,
            'email_verified_at' => Carbon::now(),
        ]);

        UserProfile::create([
            'user_id' => $user->id,
        ]);

        // Delete pending user
        $pendingUser->delete();

        return response()->json([
            'message' => 'Xác nhận email thành công. Bạn có thể đăng nhập.'
        ]);
    }

    public function login(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Attempt to authenticate
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            return response()->json([
                'message' => 'Đăng nhập thành công',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        }

        return response()->json([
            'message' => 'Email hoặc mật khẩu không đúng'
        ], 401);
    }
}