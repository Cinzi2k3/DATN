<?php

namespace App\Http\Controllers;

use App\Models\SupportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupportRequestController extends Controller
{
    // Lưu yêu cầu hỗ trợ từ người dùng
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $supportRequest = SupportRequest::create([
            'email' => $request->email,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Yêu cầu hỗ trợ đã được gửi thành công',
            'data' => $supportRequest
        ], 201);
    }

    // Lấy danh sách yêu cầu hỗ trợ cho admin
    public function index()
    {
        $requests = SupportRequest::orderBy('created_at', 'asc')->get();
        return response()->json($requests);
    }

    // Cập nhật trạng thái yêu cầu hỗ trợ
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,resolved',
            'admin_note' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $supportRequest = SupportRequest::findOrFail($id);
        $supportRequest->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note
        ]);

        return response()->json([
            'message' => 'Cập nhật yêu cầu hỗ trợ thành công',
            'data' => $supportRequest
        ]);
    }
}
