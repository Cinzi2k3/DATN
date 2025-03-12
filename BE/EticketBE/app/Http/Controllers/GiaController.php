<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Gia;
use Illuminate\Http\Request;
use Validator;

class GiaController extends Controller
{
    // Lấy danh sách giá
    public function index()
    {
        $gias = Gia::with([ 'Toa', 'LichTrinh', 'PhanLoai', 'LoaiVe'])->get(); // Lấy tất cả giá và các quan hệ liên quan

        return response()->json([
            'success' => true,
            'data' => $gias
        ], 200);
    }

    // Lấy thông tin giá theo ID
    public function show($id)
    {
        $gia = Gia::with([ 'Toa', 'LichTrinh', 'PhanLoai', 'LoaiVe'])->find($id);

        if (!$gia) {
            return response()->json(['message' => 'Giá không tồn tại!'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $gia
        ], 200);
    }

    // Thêm mới giá
    public function store(Request $request)
    {
        // Validate dữ liệu nhập vào
        $validator = Validator::make($request->all(), [
            'matoa' => 'required|exists:toa,matoa',
            'malichtrinh' => 'required|exists:lichtrinh,malichtrinh',
            'maphanloai' => 'required|exists:phanloai,maphanloai',
            'maloaive' => 'required|exists:loaive,maloaive',
            'gia' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Tạo mới giá
        $gia = Gia::create([
            'matoa' => $request->matoa,
            'malichtrinh' => $request->malichtrinh,
            'maphanloai' => $request->maphanloai,
            'maloaive' => $request->maloaive,
            'gia' => $request->gia,
        ]);

        return response()->json([
            'success' => true,
            'data' => $gia
        ], 201);
    }

    // Cập nhật giá
    public function update(Request $request, $id)
    {
        $gia = Gia::find($id);

        if (!$gia) {
            return response()->json(['message' => 'Giá không tồn tại!'], 404);
        }

        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'matoa' => 'sometimes|required|exists:toa,matoa',
            'malichtrinh' => 'sometimes|required|exists:lichtrinh,malichtrinh',
            'maphanloai' => 'sometimes|required|exists:phanloai,maphanloai',
            'maloaive' => 'sometimes|required|exists:loaive,maloaive',
            'gia' => 'sometimes|required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Cập nhật giá
        $gia->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $gia
        ], 200);
    }

    // Xóa giá
    public function destroy($id)
    {
        $gia = Gia::find($id);

        if (!$gia) {
            return response()->json(['message' => 'Giá không tồn tại!'], 404);
        }

        $gia->delete(); // Xóa giá

        return response()->json(['message' => 'Giá đã được xóa!'], 200);
    }
}
