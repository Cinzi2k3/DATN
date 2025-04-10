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
    $malichtrinh = $request->query('malichtrinh'); // Lấy mã lịch trình
    \Log::info('malichtrinh received:', ['malichtrinh' => $malichtrinh]);

    // Truy vấn các toa, bao gồm cả quan hệ với loaitoa, tau và cho (ghế)
    $query = Toa::with(['loaitoa', 'tau', 'cho']);

    // Lọc theo mã tàu nếu có
    if ($trainCode) {
        $query->whereHas('tau', function ($q) use ($trainCode) {
            $q->where('tentau', $trainCode);
        });
    }

    $toa = $query->get();

    // Nếu không tìm thấy toa, trả về thông báo lỗi
    if ($toa->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy dữ liệu toa!'
        ], 404);
    }

    // Lấy danh sách ghế đã đặt từ bảng `datve` cho mã lịch trình (malichtrinh)
    $gheDaDat = DB::table('datve')
        ->where('malichtrinh', $malichtrinh)
        ->pluck('macho') // Lấy danh sách mã ghế đã đặt
        ->toArray();
        

    // Trả về dữ liệu theo định dạng yêu cầu
    return response()->json([
        'success' => true,
        'data' => $toa->map(function ($toa) use ($gheDaDat) {
            // Tính toán số ghế trống và ghế đã đặt
            $socho = $toa->cho->count();
            $sochocon = $toa->cho->whereNotIn('macho', $gheDaDat)->count(); // Ghế trống (chưa đặt)
            $sochodadat = $toa->cho->whereIn('macho', $gheDaDat)->count(); // Ghế đã đặt

            $toa->update([
                'socho' => $socho,
            ]);

            return [
                'matoa' => $toa->matoa,
                'tentoa' => $toa->tentoa,
                'tenloaitoa' => $toa->loaitoa->tenloaitoa ?? null,
                'tenloaitau' => $toa->tau->loaitau->tenloaitau ?? null,
                'tentau' => $toa->tau->tentau ?? null,
                'socho' => $socho,  // Tổng số ghế
                'sochocon' => $sochocon,  // Số ghế trống
                'sochodadat' => $sochodadat,  // Số ghế đã đặt
                'cho' => $toa->cho->map(function ($seat) use ($gheDaDat) {
                    // Kiểm tra trạng thái ghế từ bảng `datve`
                    $trangthai = in_array($seat->macho, $gheDaDat) ? 'dadat' : 'controng';

                    return [
                        'macho' => $seat->macho,
                        'sohieu' => $seat->sohieu,
                        'trangthai' => $trangthai,  // Trạng thái ghế
                        'tang' => $seat->tang,
                        'khoang' => $seat->khoang,
                        'gia' => $seat->gia,
                    ];
                }),
            ];
        }),
    ], 200);
}

}