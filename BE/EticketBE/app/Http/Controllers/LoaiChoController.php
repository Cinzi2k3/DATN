<?php

namespace App\Http\Controllers;

use App\Models\LoaiCho;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoaiChoController extends Controller
{
    // Lấy danh sách tất cả loại chỗ
    public function index(): JsonResponse
    {
        $loaicho = LoaiCho::with(['LoaiToa', 'Cho'])->get();

        return response()->json([
            'success' => true,
            'data' => $loaicho->map(function ($item) {
                return [
                    'maloaicho' => $item->maloaicho,
                    'tenloaicho' => $item->tenloaicho,
                    'loaitoa' => $item->LoaiToa ? $item->LoaiToa->tenloaitoa : null,
                ];
            })
        ], 200);
    }

    // Lấy thông tin loại chỗ cụ thể theo mã loại chỗ (maloaicho)
    public function show($id): JsonResponse
    {
        $loaicho = LoaiCho::with(['LoaiToa', 'Cho'])->find($id);

        if (!$loaicho) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy loại chỗ!'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $loaicho
        ], 200);
    }

    // Thêm loại chỗ mới
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'maloaicho' => 'required|integer|unique:loaicho,maloaicho',
            'tenloaicho' => 'required|string|max:255',
            'maloaitoa' => 'required|integer|exists:loaitoa,maloaitoa',
        ]);

        $loaicho = LoaiCho::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thêm loại chỗ thành công!',
            'data' => $loaicho
        ], 201);
    }

    // Cập nhật thông tin loại chỗ
    public function update(Request $request, $id): JsonResponse
    {
        $loaicho = LoaiCho::find($id);

        if (!$loaicho) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy loại chỗ!'
            ], 404);
        }

        $validated = $request->validate([
            'tenloaicho' => 'sometimes|required|string|max:255',
            'maloaitoa' => 'sometimes|required|integer|exists:loaitoa,maloaitoa',
        ]);

        $loaicho->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật loại chỗ thành công!',
            'data' => $loaicho
        ], 200);
    }

    // Xóa loại chỗ
    public function destroy($id): JsonResponse
    {
        $loaicho = LoaiCho::find($id);

        if (!$loaicho) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy loại chỗ!'
            ], 404);
        }

        $loaicho->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa loại chỗ thành công!'
        ], 200);
    }
}
