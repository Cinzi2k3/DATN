<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use Illuminate\Http\Request;
use Validator;

class DonHangController extends Controller
{
    // Lấy danh sách đơn hàng
    public function index()
    {
        $donHangs = DonHang::with(['NguoiDung', 'Ve'])->get(); // Lấy tất cả đơn hàng với các quan hệ liên quan
        return response()->json([
            'success' => true,
            'data' => $donHangs
        ], 200);
    }

    // Lấy thông tin đơn hàng theo ID
    public function show($id)
    {
        $donHang = DonHang::with(['NguoiDung', 'Ve'])->find($id);

        if (!$donHang) {
            return response()->json(['message' => 'Đơn hàng không tồn tại!'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $donHang
        ], 200);
    }

    // Thêm mới đơn hàng
    public function store(Request $request)
    {
        // Validate dữ liệu nhập vào
        $validator = Validator::make($request->all(), [
            'manguoidung' => 'required|exists:nguoidung,manguoidung',  // Kiểm tra nếu người dùng tồn tại
            'mave' => 'required|exists:ve,mave',  // Kiểm tra nếu vé tồn tại
            'ngaydat' => 'required|date',  // Kiểm tra nếu ngày đặt hợp lệ
            'soluongve' => 'required|integer|min:1',  // Kiểm tra số lượng vé
            'phuongthuc' => 'required|string',  // Kiểm tra phương thức thanh toán
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Tạo mới đơn hàng
        $donHang = DonHang::create([
            'manguoidung' => $request->manguoidung,
            'mave' => $request->mave,
            'ngaydat' => $request->ngaydat,
            'soluongve' => $request->soluongve,
            'phuongthuc' => $request->phuongthuc,
        ]);

        return response()->json([
            'success' => true,
            'data' => $donHang
        ], 201);
    }

    // Cập nhật thông tin đơn hàng
    public function update(Request $request, $id)
    {
        $donHang = DonHang::find($id);

        if (!$donHang) {
            return response()->json(['message' => 'Đơn hàng không tồn tại!'], 404);
        }

        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'manguoidung' => 'sometimes|required|exists:nguoidung,manguoidung',  // Kiểm tra nếu người dùng tồn tại
            'mave' => 'sometimes|required|exists:ve,mave',  // Kiểm tra nếu vé tồn tại
            'ngaydat' => 'sometimes|required|date',  // Kiểm tra nếu ngày đặt hợp lệ
            'soluongve' => 'sometimes|required|integer|min:1',  // Kiểm tra số lượng vé
            'phuongthuc' => 'sometimes|required|string',  // Kiểm tra phương thức thanh toán
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Cập nhật đơn hàng
        $donHang->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $donHang
        ], 200);
    }

    // Xóa đơn hàng
    public function destroy($id)
    {
        $donHang = DonHang::find($id);

        if (!$donHang) {
            return response()->json(['message' => 'Đơn hàng không tồn tại!'], 404);
        }

        $donHang->delete(); // Xóa đơn hàng

        return response()->json(['message' => 'Đơn hàng đã được xóa!'], 200);
    }
}
