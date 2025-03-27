<?php

namespace App\Http\Controllers;

use App\Models\Toa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ToaController extends Controller
{
    // Lấy danh sách tất cả toa
    public function index(Request $request): JsonResponse
{
    $trainCode = $request->query('trainCode');
    $query = Toa::with(['loaitoa', 'tau']);

    if ($trainCode) {
        $query->whereHas('tau', function ($q) use ($trainCode) {
            $q->where('tentau', $trainCode);
        });
    }

    $toa = $query->get();

    return response()->json([
        'success' => true,
        'data' => $toa->map(function ($item) {
            // Tính toán số ghế trống, ghế đã đặt
            $socho = $item->cho->count();
            $sochocon = $item->cho->whereIn('trangthai', ['Trống', 0])->count();
            $sochodadat = $item->cho->whereNotIn('trangthai', ['Trống', 0])->count();

            return [
                'matoa' => $item->matoa,
                'tentoa' => $item->tentoa,
                'tentau' => $item->tau->tentau ?? null,
                'tenloaitoa' => $item->loaitoa->tenloaitoa ?? null,
                'sotang' => $item->sotang,
                'socho' => $socho,
                'sochocon' => $sochocon,
                'sochodadat' => $sochodadat,
            ];
        }),
    ], 200);
}


public function chotoa(Request $request): JsonResponse
{
    $trainCode = $request->query('trainCode');
    $query = Toa::with(['loaitoa', 'tau', 'cho']);  // Đảm bảo đã bao gồm quan hệ với ghế `cho`

    if ($trainCode) {
        $query->whereHas('tau', function ($q) use ($trainCode) {
            $q->where('tentau', $trainCode);
        });
    }

    $toa = $query->get();

    if ($toa->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy dữ liệu toa!'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $toa->map(function ($toa) {
            // Tính toán số ghế trống, ghế đã đặt
            $socho = $toa->cho->count();
            $sochocon = $toa->cho->whereIn('trangthai', ['Trống', 0])->count();
            $sochodadat = $toa->cho->whereNotIn('trangthai', ['Trống', 0])->count();

            return [
                'matoa' => $toa->matoa,
                'tentoa' => $toa->tentoa,
                'tenloaitoa' => $toa->loaitoa->tenloaitoa ?? null,
                'tenloaitau' => $toa->tau->loaitau->tenloaitau ?? null,
                'tentau' => $toa->tau->tentau ?? null,
                'socho' => $socho,  // Tổng số ghế
                'sochocon' => $sochocon,  // Số ghế trống
                'sochodadat' => $sochodadat,  // Số ghế đã đặt
                'cho' => $toa->cho->map(function ($seat) {
                    return [
                        'macho' => $seat->macho,
                        'sohieu' => $seat->sohieu,
                        'trangthai' => $seat->trangthai,
                        'tang' => $seat->tang,
                        'khoang' => $seat->khoang,
                    ];
                }),
            ];
        }),
    ], 200);
}


public function getSeatsByToa($matoa): JsonResponse
{
    $cho = \App\Models\Cho::where('matoa', $matoa)->get();

    if ($cho->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy ghế nào!'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $cho->map(function ($seat) {
            return [
                'macho' => $seat->macho,
                'sohieu' => $seat->sohieu,
                'trangthai' => $seat->trangthai,
                'tang' => $seat->tang,
                'khoang' => $seat->khoang,
            ];
        }),
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
