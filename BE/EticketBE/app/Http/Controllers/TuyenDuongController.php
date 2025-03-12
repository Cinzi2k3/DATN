<?php

namespace App\Http\Controllers;

use App\Models\TuyenDuong;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TuyenDuongController extends Controller
{
    // Lấy danh sách tất cả tuyến đường
    public function index(): JsonResponse
    {
        $tuyenDuong = TuyenDuong::with(['gaDi', 'gaDen'])->get();

        return response()->json([
            'success' => true,
            'data' => $tuyenDuong->map(function ($item) {
                return [
                    'matuyenduong' => $item->matuyenduong,
                    'magadi' => $item->gaDi->tenga ?? null,
                    'magaden' => $item->gaDen->tenga ?? null,
                    'khoangcach' => $item->khoangcach,
                ];
            })
        ], 200);
    }

    // Lấy thông tin tuyến đường cụ thể theo mã tuyến đường (matuyenduong)
    public function show($id): JsonResponse
    {
        $tuyenDuong = TuyenDuong::with(['gaDi', 'gaDen'])->find($id);

        if (!$tuyenDuong) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tuyến đường!'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'matuyenduong' => $tuyenDuong->matuyenduong,
                'magadi' => $tuyenDuong->gaDi->tenga ?? null,
                'magaden' => $tuyenDuong->gaDen->tenga ?? null,
                'khoangcach' => $tuyenDuong->khoangcach,
            ]
        ], 200);
    }

    // Thêm tuyến đường mới
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'magadi' => 'required|integer|exists:ga,maga',
            'magaden' => 'required|integer|exists:ga,maga',
            'khoangcach' => 'required|numeric|min:0',
        ]);

        $tuyenDuong = TuyenDuong::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thêm tuyến đường thành công!',
            'data' => $tuyenDuong
        ], 201);
    }

    // Cập nhật thông tin tuyến đường
    public function update(Request $request, $id): JsonResponse
    {
        $tuyenDuong = TuyenDuong::find($id);

        if (!$tuyenDuong) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tuyến đường!'
            ], 404);
        }

        $validated = $request->validate([
            'magadi' => 'required|integer|exists:ga,maga',
            'magaden' => 'required|integer|exists:ga,maga',
            'khoangcach' => 'sometimes|required|numeric|min:0',
        ]);

        $tuyenDuong->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật tuyến đường thành công!',
            'data' => $tuyenDuong
        ], 200);
    }

    // Xóa tuyến đường
    public function destroy($id): JsonResponse
    {
        $tuyenDuong = TuyenDuong::find($id);

        if (!$tuyenDuong) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tuyến đường!'
            ], 404);
        }

        $tuyenDuong->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa tuyến đường thành công!'
        ], 200);
    }
}