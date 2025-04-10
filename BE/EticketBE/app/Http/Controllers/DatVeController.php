<?php

namespace App\Http\Controllers;

use App\Models\DatVe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DatVeController extends Controller
{
    // Lấy danh sách tất cả các ga và thông tin tuyến đường liên quan
    public function DatVe(Request $request) {
        $macho = $request->input('macho');
        $malichtrinh = $request->input('malichtrinh');
    
        $gheLichTrinh = DB::table('datve')
            ->where('macho', $macho)
            ->where('malichtrinh', $malichtrinh)
            ->first();
    
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
            return response()->json(['error' => 'Ghế đã được đặt'], 400);
        }
    
        return response()->json(['success' => true]);
    }


}
