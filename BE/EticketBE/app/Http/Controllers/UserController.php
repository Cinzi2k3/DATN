<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function getUserByEmail(Request $request)
    {
        $email = $request->query('email');
    
        // Tìm user theo email
        $user = User::where('email', $email)->first();
    
        if ($user) {
            return response()->json([
                'success' => true,
                'user' => [
                    'name' => $user->name,
                ],
            ]);
        }
    
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy người dùng với email đã cung cấp',
        ]);
    }
    

}
