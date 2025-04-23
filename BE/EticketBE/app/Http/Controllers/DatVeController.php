<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Jobs\ReleaseSeatReservation;

class DatVeController extends Controller
{
    // Lưu trữ thông tin đặt vé
    public function DatVe(Request $request)
    {
        // Validate input
        $request->validate([
            'sohieu' => 'required|array',
            'malichtrinh' => 'required|integer',
            'matoa' => 'required',
        ]);

        $sohieu = $request->input('sohieu');
        $malichtrinh = $request->input('malichtrinh');
        $matoa = $request->input('matoa');

        Log::info('DatVe input:', [
            'sohieu' => $sohieu,
            'malichtrinh' => $malichtrinh,
            'matoa' => $matoa
        ]);

        try {
            DB::beginTransaction();

            foreach ($sohieu as $seatNumber) {
                // Lấy thông tin ghế từ bảng cho
                $seat = DB::table('cho')
                    ->where('sohieu', $seatNumber)
                    ->where('matoa', $matoa)
                    ->first();

                if (!$seat) {
                    throw new \Exception("Ghế với số hiệu $seatNumber không tồn tại trong toa $matoa.");
                }
                $macho = $seat->macho;

                // Kiểm tra trạng thái ghế trong bảng datve
                $gheLichTrinh = DB::table('datve')
                    ->where('macho', $macho)
                    ->where('malichtrinh', $malichtrinh)
                    ->first();

                if ($gheLichTrinh) {
                    if ($gheLichTrinh->trangthai === 'dadat') {
                        throw new \Exception("Ghế $seatNumber trong toa $matoa đã được đặt.");
                    } elseif ($gheLichTrinh->trangthai === 'danggiu' && Carbon::now()->lessThan($gheLichTrinh->thoihan_giu)) {
                        throw new \Exception("Ghế $seatNumber trong toa $matoa đang được giữ, vui lòng thử lại sau.");
                    } elseif ($gheLichTrinh->trangthai === 'danggiu' && Carbon::now()->greaterThanOrEqualTo($gheLichTrinh->thoihan_giu)) {
                        // Ghế hết hạn giữ, xoá ghế
                        DB::table('datve')
                            ->where('macho', $macho)
                            ->where('malichtrinh', $malichtrinh)
                            ->delete();
                    }
                }

                // Cập nhật hoặc thêm ghế vào trạng thái danggiu
                $thoihanGiu = Carbon::now('UTC')->addMinutes(3);
            DB::table('datve')->updateOrInsert(
                [
                    'macho' => $macho,
                    'malichtrinh' => $malichtrinh
                ],
                [
                    'trangthai' => 'danggiu',
                    'thoihan_giu' => $thoihanGiu->toDateTimeString(), // Lưu dưới dạng UTC
                ]
            );

            ReleaseSeatReservation::dispatch($macho, $malichtrinh)
                ->delay($thoihanGiu);
            }

            DB::commit();
            Log::info('Giữ ghế thành công', ['malichtrinh' => $malichtrinh, 'matoa' => $matoa]);
            return response()->json([
                'success' => true,
                'message' => 'Giữ ghế thành công, vui lòng thanh toán trong 15 phút'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi đặt vé: ' . $e->getMessage(), [
                'sohieu' => $sohieu,
                'malichtrinh' => $malichtrinh,
                'matoa' => $matoa
            ]);
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Có lỗi xảy ra trong quá trình đặt vé.'
            ], 400);
        }
    }
    // Lấy trạng thái ghế trong toa
    public function getSeatStatus(Request $request)
    {
        $malichtrinh = $request->query('malichtrinh');
        $matoa = $request->query('matoa');

        Log::info('getSeatStatus called:', [
            'malichtrinh' => $malichtrinh,
            'matoa' => $matoa
        ]);

        try {
            // Lấy tất cả ghế trong toa từ bảng cho
            $seats = DB::table('cho')
                ->where('matoa', $matoa)
                ->get(['sohieu', 'macho']);

            // Lấy trạng thái ghế từ bảng datve
            $seatStatuses = DB::table('datve')
                ->where('malichtrinh', $malichtrinh)
                ->whereIn('macho', $seats->pluck('macho'))
                ->get(['macho', 'trangthai'])
                ->keyBy('macho');

            // Tạo danh sách trạng thái ghế
            $statusData = $seats->map(function ($seat) use ($seatStatuses) {
                $status = $seatStatuses->has($seat->macho)
                    ? $seatStatuses[$seat->macho]->trangthai
                    : 'controng';

                return [
                    'sohieu' => $seat->sohieu,
                    'status' => $status
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $statusData
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy trạng thái ghế: ' . $e->getMessage(), [
                'malichtrinh' => $malichtrinh,
                'matoa' => $matoa
            ]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // Xoá trạng thái ghế khi trở về trang trước
    public function releaseSeats(Request $request)
    {
        // Validate input
        $request->validate([
            'seats' => 'required|array',
            'seats.*.sohieu' => 'required|string',
            'seats.*.malichtrinh' => 'required|integer',
            'seats.*.matoa' => 'required|integer',
        ]);

        $seats = $request->input('seats');

        Log::info('releaseSeats input:', ['seats' => $seats]);

        try {
            DB::beginTransaction();

            foreach ($seats as $seat) {
                $sohieu = $seat['sohieu'];
                $malichtrinh = $seat['malichtrinh'];
                $matoa = $seat['matoa'];

                // Lấy macho từ bảng cho
                $seatInfo = DB::table('cho')
                    ->where('sohieu', $sohieu)
                    ->where('matoa', $matoa)
                    ->first();

                if (!$seatInfo) {
                    Log::warning("Ghế với số hiệu $sohieu không tồn tại trong toa $matoa.");
                    continue; // Bỏ qua nếu ghế không tồn tại
                }

                // Xóa ghế khỏi bảng datve
                DB::table('datve')
                    ->where('macho', $seatInfo->macho)
                    ->where('malichtrinh', $malichtrinh)
                    ->delete();
            }

            DB::commit();
            Log::info('Xóa ghế thành công', ['seats' => $seats]);

            return response()->json([
                'success' => true,
                'message' => 'Ghế đã được xóa thành công.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xóa ghế: ' . $e->getMessage(), ['seats' => $seats]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Có lỗi xảy ra khi xóa ghế.'
            ], 500);
        }
    }
    public function checkReservation(Request $request)
    {
        try {
            $malichtrinh = $request->query('malichtrinh');
            $matoa = $request->query('matoa');

            $reservations = DB::table('datve')
                ->join('cho', 'datve.macho', '=', 'cho.macho')
                ->where('datve.malichtrinh', $malichtrinh)
                ->where('cho.matoa', $matoa)
                ->where('datve.thoihan_giu', '>', now('UTC'))
                ->select('datve.thoihan_giu')
                ->first();

            if ($reservations) {
                return response()->json([
                    'hasReservation' => true,
                    'thoihan_giu' => $reservations->thoihan_giu
                ]);
            }

            return response()->json([
                'hasReservation' => false
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi kiểm tra trạng thái đặt vé: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi kiểm tra trạng thái đặt vé.'
            ], 500);
        }
    }
}