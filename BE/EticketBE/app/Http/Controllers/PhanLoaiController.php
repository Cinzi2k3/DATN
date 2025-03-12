<?php

namespace App\Http\Controllers;

use App\Models\PhanLoai;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhanLoaiController extends Controller
{
    // Lấy danh sách tất cả phân loại
    public function index(): JsonResponse
    {
        $phanloais = PhanLoai::all();

        return response()->json([
            'success' => true,
            'data' => $phanloais
        ], 200);
    }

    // Lấy thông tin chi tiết của phân loại theo mã phân loại
    public function show($id): JsonResponse
    {
        $phanloai = PhanLoai::find($id);

        if (!$phanloai) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phân loại!'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $phanloai
        ], 200);
    }

    // Thêm phân loại mới
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'maphanloai' => 'required|integer|unique:phanloai,maphanloai',
            'tenphanloai' => 'required|string|max:255',
        ]);

        $phanloai = PhanLoai::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thêm phân loại thành công!',
            'data' => $phanloai
        ], 201);
    }

    // Cập nhật phân loại
    public function update(Request $request, $id): JsonResponse
    {
        $phanloai = PhanLoai::find($id);

        if (!$phanloai) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phân loại!'
            ], 404);
        }

        $validated = $request->validate([
            'tenphanloai' => 'required|string|max:255',
        ]);

        $phanloai->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật phân loại thành công!',
            'data' => $phanloai
        ], 200);
    }

    // Xóa phân loại
    public function destroy($id): JsonResponse
    {
        $phanloai = PhanLoai::find($id);

        if (!$phanloai) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phân loại!'
            ], 404);
        }

        $phanloai->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa phân loại thành công!'
        ], 200);
    }
}
