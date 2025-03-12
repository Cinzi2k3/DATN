<?php

namespace App\Http\Controllers;

use App\Models\DatVe;
use Illuminate\Http\Request;

class DatVeController extends Controller
{
    // Lấy danh sách tất cả các ga và thông tin tuyến đường liên quan
    public function datVe(Request $request)
{
    // Lấy thông tin từ request, ví dụ như mã tàu, mã toa, số lượng vé đặt
    $maTau = $request->input('maTau');
    $maToa = $request->input('maToa');
    $soLuongDat = $request->input('soLuongDat');

    // Tìm toa tương ứng
    $toa = Toa::find($maToa);

    if (!$toa) {
        return response()->json(['error' => 'Toa không tồn tại'], 404);
    }

    // Kiểm tra xem số ghế còn lại trong toa có đủ không
    if ($toa->gheCon() < $soLuongDat) {
        return response()->json(['error' => 'Không đủ ghế'], 400);
    }

    // Cập nhật số ghế đã đặt
    $toa->so_ghe_da_dat += $soLuongDat;
    $toa->save(); // Lưu thay đổi vào cơ sở dữ liệu

    // Tính lại số ghế còn lại trong tàu
    $tongSoGheCon = $toa->Tau->toas->sum(function ($toa) {
        return $toa->gheCon();  // Gọi phương thức gheCon để tính số ghế còn lại trong toa
    });

    // Cập nhật số ghế còn lại trong tàu (nếu cần)
    $toa->Tau->so_ghe_con = $tongSoGheCon;
    $toa->Tau->save(); // Lưu thay đổi vào cơ sở dữ liệu

    return response()->json([
        'success' => true,
        'message' => 'Đặt vé thành công',
        'so_ghe_con_tau' => $tongSoGheCon,  // Trả về số ghế còn lại trong tàu
    ]);
}


}
