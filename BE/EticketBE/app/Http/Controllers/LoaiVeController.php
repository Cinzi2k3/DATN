<?php

namespace App\Http\Controllers;

use App\Models\LoaiVe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoaiVeController extends Controller
{
    // Lấy danh sách tất cả loại vé
    public function index(): JsonResponse
    {
        $loaive = LoaiVe::all();

        return response()->json([
            'success' => true,
            'data' => $loaive
        ], 200);
    }

    // Lấy thông tin loại vé cụ thể theo mã loại vé (maloaive)
    public function show($id): JsonResponse
    {
        $loaive = LoaiVe::find($id);

        if (!$loaive) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy loại vé!'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $loaive
        ], 200);
    }

    // Thêm loại vé mới
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'maloaive' => 'required|integer|unique:loaive,maloaive',
            'tenloaive' => 'required|string|max:255',
        ]);

        $loaive = LoaiVe::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thêm loại vé thành công!',
            'data' => $loaive
        ], 201);
    }

    // Cập nhật thông tin loại vé
    public function update(Request $request, $id): JsonResponse
    {
        $loaive = LoaiVe::find($id);

        if (!$loaive) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy loại vé!'
            ], 404);
        }

        $validated = $request->validate([
            'tenloaive' => 'required|string|max:255',
        ]);

        $loaive->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật loại vé thành công!',
            'data' => $loaive
        ], 200);
    }

    // Xóa loại vé
    public function destroy($id): JsonResponse
    {
        $loaive = LoaiVe::find($id);

        if (!$loaive) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy loại vé!'
            ], 404);
        }

        $loaive->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa loại vé thành công!'
        ], 200);
    }
}
