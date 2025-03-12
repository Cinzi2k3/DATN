<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ThanhToan;
use Illuminate\Http\Request;
use Validator;

class ThanhToanController extends Controller
{
    // Lấy danh sách thanh toán
    public function index()
    {
        $thanhToans = ThanhToan::with(['Ve', 'NguoiDung'])->get(); // Lấy tất cả thanh toán với các quan hệ liên quan

        return response()->json([
            'success' => true,
            'data' => $thanhToans
        ], 200);
    }

    // Lấy thông tin thanh toán theo ID
    public function show($id)
    {
        $thanhToan = ThanhToan::with(['Ve', 'NguoiDung'])->find($id);

        if (!$thanhToan) {
            return response()->json(['message' => 'Thanh toán không tồn tại!'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $thanhToan
        ], 200);
    }

    // Thêm mới thanh toán
    public function store(Request $request)
    {
        // Validate dữ liệu nhập vào
        $validator = Validator::make($request->all(), [
            'mave' => 'required|exists:ve,mave',  // Kiểm tra nếu vé tồn tại
            'manguoidung' => 'required|exists:nguoidung,manguoidung',  // Kiểm tra nếu người dùng tồn tại
            'sotien' => 'required|numeric|min:1',  // Kiểm tra số tiền thanh toán
            'phuongthuc' => 'required|string',  // Kiểm tra phương thức thanh toán
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Tạo mới thanh toán
        $thanhToan = ThanhToan::create([
            'mave' => $request->mave,
            'manguoidung' => $request->manguoidung,
            'sotien' => $request->sotien,
            'phuongthuc' => $request->phuongthuc,
        ]);

        return response()->json([
            'success' => true,
            'data' => $thanhToan
        ], 201);
    }

    // Cập nhật thông tin thanh toán
    public function update(Request $request, $id)
    {
        $thanhToan = ThanhToan::find($id);

        if (!$thanhToan) {
            return response()->json(['message' => 'Thanh toán không tồn tại!'], 404);
        }

        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'mave' => 'sometimes|required|exists:ve,mave',  // Kiểm tra nếu vé tồn tại
            'manguoidung' => 'sometimes|required|exists:nguoidung,manguoidung',  // Kiểm tra nếu người dùng tồn tại
            'sotien' => 'sometimes|required|numeric|min:1',  // Kiểm tra số tiền thanh toán
            'phuongthuc' => 'sometimes|required|string',  // Kiểm tra phương thức thanh toán
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Cập nhật thanh toán
        $thanhToan->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $thanhToan
        ], 200);
    }

    // Xóa thanh toán
    public function destroy($id)
    {
        $thanhToan = ThanhToan::find($id);

        if (!$thanhToan) {
            return response()->json(['message' => 'Thanh toán không tồn tại!'], 404);
        }

        $thanhToan->delete(); // Xóa thanh toán

        return response()->json(['message' => 'Thanh toán đã được xóa!'], 200);
    }
}
