<?php

namespace App\Http\Controllers;

use App\Models\Ve;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VeController extends Controller
{
    // Lấy danh sách tất cả vé
    public function index(): JsonResponse
    {
        $ve = Ve::with(['LoaiVe', 'LichTrinh', 'NguoiDung', 'Cho'])->get();

        return response()->json([
            'success' => true,
            'data' => $ve->map(function ($item) {
                return [
                    'mave' => $item->mave,
                    'maloaive' => $item->maloaive,
                    'malichtrinh' => $item->malichtrinh,
                    'manguoidung' => $item->manguoidung,
                    'macho' => $item->macho,
                    'thoihan' => $item->thoihan,
                    'trangthai' => $item->trangthai,
                    'tenloai' => $item->LoaiVe->tenloaive ?? null,
                    'tenlichtrinh' => $item->LichTrinh->tenlichtrinh ?? null,
                    'tennguoidung' => $item->NguoiDung->tendangnhap ?? null,
                    'tencho' => $item->Cho->tencho ?? null,
                ];
            })
        ], 200);
    }

    // Lấy thông tin vé cụ thể theo mã vé (mave)
    public function show($id): JsonResponse
    {
        $ve = Ve::find($id);

        if (!$ve) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy vé!'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ve
        ], 200);
    }

    // Thêm vé mới
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mave' => 'required|integer|unique:ve,mave',
            'maloaive' => 'required|integer|exists:loai_ve,maloaive',
            'malichtrinh' => 'required|integer|exists:lich_trinh,malichtrinh',
            'manguoidung' => 'required|integer|exists:nguoi_dung,manguoidung',
            'macho' => 'required|integer|exists:cho,macho',
            'thoihan' => 'required|date',
            'trangthai' => 'required|string',
        ]);

        $ve = Ve::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thêm vé thành công!',
            'data' => $ve
        ], 201);
    }

    // Cập nhật thông tin vé
    public function update(Request $request, $id): JsonResponse
    {
        $ve = Ve::find($id);

        if (!$ve) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy vé!'
            ], 404);
        }

        $validated = $request->validate([
            'maloaive' => 'sometimes|required|integer|exists:loai_ve,maloaive',
            'malichtrinh' => 'sometimes|required|integer|exists:lich_trinh,malichtrinh',
            'manguoidung' => 'sometimes|required|integer|exists:nguoi_dung,manguoidung',
            'macho' => 'sometimes|required|integer|exists:cho,macho',
            'thoihan' => 'sometimes|required|date',
            'trangthai' => 'sometimes|required|string',
        ]);

        $ve->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật vé thành công!',
            'data' => $ve
        ], 200);
    }

    // Xóa vé
    public function destroy($id): JsonResponse
    {
        $ve = Ve::find($id);

        if (!$ve) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy vé!'
            ], 404);
        }

        $ve->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa vé thành công!'
        ], 200);
    }
}
