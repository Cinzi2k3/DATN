<?php

namespace App\Http\Controllers;

use App\Models\LoaiToa;
use Illuminate\Http\Request;

class LoaiToaController extends Controller
{
    public function getLoaiToa()
    {
        // Lấy tất cả dữ liệu từ bảng loaitoa
        $loaitoa = LoaiToa::all();

        // Trả về dữ liệu dưới dạng JSON
        return response()->json($loaitoa);
    }
}
