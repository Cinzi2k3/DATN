<?php

namespace App\Http\Controllers;

use App\Models\Toa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


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
    $malichtrinh = $request->query('malichtrinh');
    \Log::info('Request params:', ['trainCode' => $trainCode, 'malichtrinh' => $malichtrinh]);

    // Kiểm tra tham số bắt buộc
    if (!$trainCode || !$malichtrinh) {
        return response()->json([
            'success' => false,
            'message' => 'Thiếu trainCode hoặc malichtrinh'
        ], 400);
    }

    // Truy vấn các toa, lọc chỗ theo malichtrinh
    $query = Toa::with([
        'loaitoa',
        'tau',
        'cho' => function ($query) use ($malichtrinh) {
            $query->where('malichtrinh', $malichtrinh);
        }
    ]);

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

    // Lấy danh sách ghế đã đặt từ bảng datve
    $gheDaDat = DB::table('datve')
        ->where('malichtrinh', $malichtrinh)
        ->pluck('macho')
        ->toArray();

    return response()->json([
        'success' => true,
        'data' => $toa->map(function ($toa) use ($gheDaDat, $malichtrinh) {
            // Lọc lại chỗ để đảm bảo chỉ lấy đúng malichtrinh
            $cho = $toa->cho->filter(function ($seat) use ($malichtrinh) {
                return $seat->malichtrinh == $malichtrinh;
            });

            $socho = $toa->cho->count();
            $sochocon = $toa->cho->whereNotIn('macho', $gheDaDat)->count(); // Ghế trống (chưa đặt)
            $sochodadat = $toa->cho->whereIn('macho', $gheDaDat)->count(); // Ghế đã đặt

            // Cập nhật số chỗ trong toa (nếu cần)
            $toa->update(['socho' => $socho]);

            return [
                'matoa' => $toa->matoa,
                'tentoa' => $toa->tentoa,
                'tenloaitoa' => $toa->loaitoa->tenloaitoa ?? null,
                'tenloaitau' => $toa->tau->loaitau->tenloaitau ?? null,
                'tentau' => $toa->tau->tentau ?? null,
                'socho' => $socho,
                'sochocon' => $sochocon,
                'sochodadat' => $sochodadat,
                'cho' => $toa->cho->map(function ($seat) use ($gheDaDat) {
                    $trangthai = in_array($seat->macho, $gheDaDat) ? 'dadat' : 'controng';
                    return [
                        'macho' => $seat->macho,
                        'sohieu' => $seat->sohieu,
                        'trangthai' => $trangthai,
                        'tang' => $seat->tang,
                        'khoang' => $seat->khoang,
                        'gia' => $seat->gia,
                        'malichtrinh' => $seat->malichtrinh, // Thêm để debug
                    ];
                }),
            ];
        }),
    ], 200);
}

}