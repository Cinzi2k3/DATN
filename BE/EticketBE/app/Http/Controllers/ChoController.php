<?php

namespace App\Http\Controllers;

use App\Models\Cho;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChoController extends Controller
{
    // Lấy danh sách tất cả chỗ
    public function index(): JsonResponse
    {
        $cho = Cho::with(['LoaiCho', 'Toa', 'Tau'])->get();

        return response()->json([
            'success' => true,
            'data' => $cho->map(function ($item) {
                return [
                    'macho' => $item->macho,
                    'sohieu' => $item->sohieu,
                    'tentoa' => $item->Toa->tentoa ?? null,
                    'tentau' => $item->Tau->tentau ?? null,
                    'tang' => $item->tang,
                    'khoang' => $item->khoang,
                    'gia' => $item->gia ?? null,
                ];
            })
        ], 200);
    }

    // Lấy thông tin chỗ cụ thể theo mã chỗ (macho)
    public function show($id): JsonResponse
    {
        $cho = Cho::with(['LoaiCho', 'Toa', 'Tau'])->find($id);

        if (!$cho) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy chỗ!'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $cho
        ], 200);
    }

    // Thêm chỗ mới
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'macho' => 'required|integer|unique:cho,macho',
            'maloaicho' => 'required|integer|exists:loaicho,maloaicho',
            'matoa' => 'required|integer|exists:toa,matoa',
            'matau' => 'required|integer|exists:tau,matau',
            'sohieu' => 'required|string|max:255',
            'trangthai' => 'required|string|max:50',
        ]);

        $cho = Cho::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thêm chỗ thành công!',
            'data' => $cho
        ], 201);
    }

    // Cập nhật thông tin chỗ
    public function update(Request $request, $id): JsonResponse
{
    $cho = Cho::find($id);

    if (!$cho) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy chỗ!'
        ], 404);
    }

    $validated = $request->validate([
        'maloaicho' => 'sometimes|required|integer|exists:loaicho,maloaicho',
        'matoa' => 'sometimes|required|integer|exists:toa,matoa',
        'matau' => 'sometimes|required|integer|exists:tau,matau',
        'sohieu' => 'sometimes|required|string|max:255',
        'trangthai' => 'sometimes|required|string|max:50',
        'gia' => 'sometimes|required|numeric|min:0', // Thêm validation cho gia
    ]);

    $cho->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'Cập nhật chỗ thành công!',
        'data' => $cho
    ], 200);
}

    // Xóa chỗ
    public function destroy($id): JsonResponse
    {
        $cho = Cho::find($id);

        if (!$cho) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy chỗ!'
            ], 404);
        }

        $cho->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa chỗ thành công!'
        ], 200);
    }
}
