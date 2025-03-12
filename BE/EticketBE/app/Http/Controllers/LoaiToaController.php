<?php

namespace App\Http\Controllers;

use App\Models\LoaiToa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoaiToaController extends Controller
{
    // Lấy danh sách tất cả loại toa
    public function index(): JsonResponse
    {
        $loaitoa = LoaiToa::all();
        return response()->json([
            'success' => true,
            'data' => $loaitoa
        ], 200);
    }

    // Lấy thông tin loại toa cụ thể theo mã loại toa (maloaitoa)
    public function show($id): JsonResponse
    {
        $loaitoa = LoaiToa::find($id);

        if (!$loaitoa) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy loại toa!'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $loaitoa
        ], 200);
    }

    // Thêm loại toa mới
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'maloaitoa' => 'required|integer|unique:loaitoa,maloaitoa',
            'tenloaitoa' => 'required|string|max:255',
        ]);

        $loaitoa = LoaiToa::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thêm loại toa thành công!',
            'data' => $loaitoa
        ], 201);
    }

    // Sửa loại toa
    public function update(Request $request, $id): JsonResponse
    {
        $loaitoa = LoaiToa::find($id);

        if (!$loaitoa) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy loại toa!'
            ], 404);
        }

        $validated = $request->validate([
            'tenloaitoa' => 'required|string|max:255',
        ]);

        $loaitoa->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật loại toa thành công!',
            'data' => $loaitoa
        ], 200);
    }

    // Xóa loại toa
    public function destroy($id): JsonResponse
    {
        $loaitoa = LoaiToa::find($id);

        if (!$loaitoa) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy loại toa!'
            ], 404);
        }

        $loaitoa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa loại toa thành công!'
        ], 200);
    }
}
