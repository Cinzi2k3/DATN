<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function sendResetCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email không hợp lệ hoặc không tồn tại trong hệ thống',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();
            
            // Generate random 6-digit code
            $resetCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store reset code with expiration (30 minutes)
            $user->password_reset_token = $resetCode;
            $user->password_reset_expires = Carbon::now()->addMinutes(30);
            $user->save();

            // Send email with reset code
            Mail::send([], ['code' => $resetCode, 'user' => $user], function ($message) use ($user, $resetCode) {
                $message->to($user->email)
                        ->subject('Mã xác nhận đặt lại mật khẩu')
                        ->html($this->getEmailContent($user, $resetCode), 'text/html');
            });

            return response()->json([
                'success' => true,
                'message' => 'Mã xác nhận đã được gửi đến email của bạn'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi gửi mã xác nhận: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi mã xác nhận: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getEmailContent($user, $code)
    {
        return '
            <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
                <h2 style="color: #333;">Mã xác nhận đặt lại mật khẩu</h2>
                <p>Xin chào ' . htmlspecialchars($user->name) . ',</p>
                <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn. Đây là mã xác nhận của bạn:</p>
                <h3 style="color: #007bff;">' . htmlspecialchars($code) . '</h3>
                <p>Mã này có hiệu lực trong 30 phút. Vui lòng sử dụng mã này để đặt lại mật khẩu của bạn.</p>
                <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
                <p>Trân trọng,<br>Đội ngũ hỗ trợ</p>
            </div>
        ';
    }

    public function verifyResetCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
            'new_password' => 'required|string|min:4|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)
                       ->where('password_reset_token', $request->code)
                       ->where('password_reset_expires', '>', Carbon::now())
                       ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã xác nhận không hợp lệ hoặc đã hết hạn'
                ], 400);
            }

            // Update password
            $user->password = bcrypt($request->new_password);
            $user->password_reset_token = null;
            $user->password_reset_expires = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Đặt lại mật khẩu thành công'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi đặt lại mật khẩu: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể đặt lại mật khẩu: ' . $e->getMessage()
            ], 500);
        }
    }
}