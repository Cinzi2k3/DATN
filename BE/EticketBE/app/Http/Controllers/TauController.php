<?php

namespace App\Http\Controllers;

use App\Models\Tau;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TauController extends Controller
{
    // Lấy danh sách tất cả tàu kèm thông tin loại tàu
    public function index(): JsonResponse
    {
        $tau = Tau::with('LoaiTau')->get();

        return response()->json([
            'success' => true,
            'data' => $tau->map(function ($item) {
                return [
                    'matau' => $item->matau,
                    'tentau' => $item->tentau,
                    'tenloaitau' => $item->LoaiTau->tenloaitau ?? null,
                ];
            }),
        ], 200);
    }

    // Lấy thông tin chi tiết một tàu kèm thông tin loại tàu
    public function show($id): JsonResponse
    {
        $tau = Tau::with('LoaiTau')->find($id);

        if (!$tau) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tàu!',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'matau' => $tau->matau,
                'tentau' => $tau->tentau,
                'tenloaitau' => $tau->LoaiTau->tenloaitau ?? null,
            ],
        ], 200);
    }

    // Thêm tàu mới
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'matau' => 'required|integer|unique:tau,matau',
            'maloaitau' => 'required|integer|exists:loaitau,maloaitau',
            'tentau' => 'required|string|max:255',
        ]);

        $tau = Tau::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thêm tàu thành công!',
            'data' => $tau,
        ], 201);
    }

    // Cập nhật thông tin tàu
    public function update(Request $request, $id): JsonResponse
    {
        $tau = Tau::find($id);

        if (!$tau) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tàu!',
            ], 404);
        }

        $validated = $request->validate([
            'maloaitau' => 'required|integer|exists:loaitau,maloaitau',
            'tentau' => 'required|string|max:255',
        ]);

        $tau->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật tàu thành công!',
            'data' => $tau,
        ], 200);
    }

    // Xóa tàu
    public function destroy($id): JsonResponse
    {
        $tau = Tau::find($id);

        if (!$tau) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tàu!',
            ], 404);
        }

        $tau->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa tàu thành công!',
        ], 200);
    }
}
