<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatVeController extends Controller
{
    public function DatVe(Request $request)
    {
        // Validate input
        $request->validate([
            'sohieu' => 'required',
            'malichtrinh' => 'required|integer',
            'matoa' => 'required', // matoa có thể là int hoặc string, tùy thuộc vào bảng cho
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

            // Lấy macho từ bảng cho dựa trên sohieu và matoa
            $seat = DB::table('cho')
                ->where('sohieu', $sohieu)
                ->where('matoa', $matoa)
                ->first();

            if (!$seat) {
                throw new \Exception("Ghế với số hiệu $sohieu không tồn tại trong toa $matoa.");
            }
            $macho = $seat->macho;

            // Kiểm tra trạng thái ghế trong datve
            $gheLichTrinh = DB::table('datve')
                ->where('macho', $macho)
                ->where('malichtrinh', $malichtrinh)
                ->first();

            Log::info('Kiểm tra ghế:', [
                'sohieu' => $sohieu,
                'macho' => $macho,
                'matoa' => $matoa,
                'malichtrinh' => $malichtrinh,
                'gheLichTrinh' => $gheLichTrinh
            ]);

            if (!$gheLichTrinh) {
                DB::table('datve')->insert([
                    'macho' => $macho,
                    'malichtrinh' => $malichtrinh,
                    'trangthai' => 'dadat'
                ]);
            } elseif ($gheLichTrinh->trangthai === 'controng') {
                DB::table('datve')
                    ->where('macho', $macho)
                    ->where('malichtrinh', $malichtrinh)
                    ->update(['trangthai' => 'dadat']);
            } else {
                throw new \Exception("Ghế $sohieu trong toa $matoa đã được đặt.");
            }

            DB::commit();
            Log::info('Đặt vé thành công', ['malichtrinh' => $malichtrinh, 'matoa' => $matoa]);
            return response()->json(['success' => true, 'message' => 'Đặt vé thành công']);
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
}
