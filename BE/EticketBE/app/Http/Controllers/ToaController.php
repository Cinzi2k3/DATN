<?php

namespace App\Http\Controllers;

use App\Models\Toa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ToaController extends Controller
{
    // Lấy danh sách tất cả toa
    public function index(): JsonResponse
    {
        $toa = Toa::with(['loaitoa'])->get();

        return response()->json([
            'success' => true,
            'data' => $toa->map(function ($item) {
                return [
                    'matoa' => $item->matoa,
                    'tentoa' => $item->tentoa,
                    'tenloaitoa' => $item->loaitoa->tenloaitoa ?? null,
                    'sotang'=>$item->sotang,
                    'socho'=>$item->socho,
                    'sochocon'=>$item->sochocon,
                    'sochodadat'=>$item->sochodadat,
                ];
            })
        ], 200);
    }

    // Lấy thông tin toa cụ thể theo mã toa (matoa)
    public function show($id): JsonResponse
    {
        $toa = Toa::find($id);

        if (!$toa) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy toa!'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $toa
        ], 200);
    }

    // Thêm toa mới
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'matoa' => 'required|integer|unique:toa,matoa',
            'maloaitoa' => 'required|integer|exists:loaitoa,maloaitoa',
            'tentoa' => 'required|string|max:255',
            'sotang' => 'required|integer|min:1',
            'socho' => 'required|integer|min:1',
            'sochocon' => 'required|integer|min:1',
            'sochodadat' => 'required|integer|min:1',
        ]);

        $toa = Toa::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thêm toa thành công!',
            'data' => $toa
        ], 201);
    }

    // Cập nhật thông tin toa
    public function update(Request $request, $id): JsonResponse
    {
        $toa = Toa::find($id);

        if (!$toa) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy toa!'
            ], 404);
        }

        $validated = $request->validate([
            'maloaitoa' => 'required|integer|exists:loaitoa,maloaitoa',
            'tentoa' => 'required|string|max:255',
            'sotang' => 'sometimes|required|integer|min:1',
            'socho' => 'sometimes|required|integer|min:1',
            'sochocon' => 'sometimes|required|integer|min:1',
            'sochodadat' => 'sometimes|required|integer|min:1',
            
        ]);

        $toa->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật toa thành công!',
            'data' => $toa
        ], 200);
    }

    // Xóa toa
    public function destroy($id): JsonResponse
    {
        $toa = Toa::find($id);

        if (!$toa) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy toa!'
            ], 404);
        }

        $toa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa toa thành công!'
        ], 200);
    }
}
