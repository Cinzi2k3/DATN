<?php

namespace App\Http\Controllers;

use App\Models\LichTrinh;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class LichTrinhController extends Controller
{
    // Lấy danh sách tất cả lịch trình
    public function index(Request $request): JsonResponse
{
    // Xây dựng query
    $query = LichTrinh::with(['Tau.LoaiTau', 'TuyenDuong.GaDi', 'TuyenDuong.GaDen', 'Gia', 'Tau.Toa']);
    
    // Lọc theo ga đi
    if ($request->has('gadi') && !empty($request->gadi)) {
        $query->whereHas('TuyenDuong.GaDi', function ($q) use ($request) {
            $q->where('tenga', 'like', '%' . $request->gadi . '%');
        });
    }
    
    // Lọc theo ga đến
    if ($request->has('gaden') && !empty($request->gaden)) {
        $query->whereHas('TuyenDuong.GaDen', function ($q) use ($request) {
            $q->where('tenga', 'like', '%' . $request->gaden . '%');
        });
    }
    
    // Lọc theo ngày đi
    if ($request->has('ngaydi') && !empty($request->ngaydi)) {
        $query->whereDate('thoigiandi', $request->ngaydi);
    }
    
    // Thực hiện query
    $lichTrinhs = $query->get();
    
    // Dùng phương thức map để xử lý các lịch trình
    $data = $lichTrinhs->map(function ($lichTrinh) {
        // Tính tổng số ghế của tàu (từ tất cả các toa)
        $tongSoGhe = $lichTrinh->Tau->Toa->sum('socho');

        // Đếm số ghế đã đặt cho lịch trình này từ bảng ghe_lichtrinh
        $soGheDaDat = DB::table('datve')
            ->where('malichtrinh', $lichTrinh->malichtrinh)
            ->count();

        // Tính số ghế còn lại
        $tongSoGheCon = $tongSoGhe - $soGheDaDat;
        
        // Tính thời gian di chuyển
        $thoiGianDi = Carbon::parse($lichTrinh->thoigiandi);
        $thoiGianDen = Carbon::parse($lichTrinh->thoigianden);
        $soGioDiChuyen = $thoiGianDi->diffInHours($thoiGianDen);
        $soPhutDiChuyen = $thoiGianDi->diffInMinutes($thoiGianDen) % 60;
        $thoiGianDiChuyen = $soGioDiChuyen . 'h' . $soPhutDiChuyen . 'p';
        
        return [
            'malichtrinh' => $lichTrinh->malichtrinh,
            'tentau' => $lichTrinh->Tau->tentau ?? 'Không xác định',
            'tenloaitau' => $lichTrinh->Tau->LoaiTau->tenloaitau ?? 'Không xác định',
            'gadi' => $lichTrinh->TuyenDuong->GaDi->tenga ?? 'Không xác định',
            'gaden' => $lichTrinh->TuyenDuong->GaDen->tenga ?? 'Không xác định',
            'ngaydi' => Carbon::parse($lichTrinh->thoigiandi)->format('Y-m-d'),
            'ngayden' => Carbon::parse($lichTrinh->thoigianden)->format('Y-m-d'),
            'giodi' => Carbon::parse($lichTrinh->thoigiandi)->format('H:i'),
            'gioden' => Carbon::parse($lichTrinh->thoigianden)->format('H:i'),
            'thoigiandichuyen' => $thoiGianDiChuyen,
            'gia' => number_format($lichTrinh->Gia->gia ?? 0, 0, ',', '.') ,
            'sochocon' => $tongSoGheCon,
        ];
    });
    
    return response()->json([
        'success' => true,
        'data' => $data,
    ]);
}


    // Lấy thông tin lịch trình cụ thể theo mã
    public function show($id): JsonResponse
{
    // Lấy lịch trình theo ID với đầy đủ quan hệ
    $lichTrinh = LichTrinh::with(['Tau.LoaiTau', 'TuyenDuong.GaDi', 'TuyenDuong.GaDen', 'Gia', 'Tau.Toa'])->find($id);

    // Kiểm tra nếu không tìm thấy lịch trình
    if (!$lichTrinh) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy lịch trình!'
        ], 404);
    }

    // Tính tổng số ghế còn lại trong tàu
    $tongSoGheCon = $lichTrinh->Tau->Toa->sum(function ($toa) {
        return $toa->sochocon(); 
    });

    // Tính thời gian di chuyển
    $thoiGianDi = Carbon::parse($lichTrinh->thoigiandi);
    $thoiGianDen = Carbon::parse($lichTrinh->thoigianden);
    $soGioDiChuyen = $thoiGianDi->diffInHours($thoiGianDen);
    $soPhutDiChuyen = $thoiGianDi->diffInMinutes($thoiGianDen) % 60;
    $thoiGianDiChuyen = $soGioDiChuyen . 'h' . $soPhutDiChuyen . 'p';

    // Chuẩn bị dữ liệu trả về
    $data = [
        'malichtrinh' => $lichTrinh->malichtrinh,
        'tentau' => $lichTrinh->Tau->tentau ?? 'Không xác định',
        'tenloaitau' => $lichTrinh->Tau->LoaiTau->tenloaitau ?? 'Không xác định',
        'gadi' => $lichTrinh->TuyenDuong->GaDi->tenga ?? 'Không xác định',
        'gaden' => $lichTrinh->TuyenDuong->GaDen->tenga ?? 'Không xác định',
        'ngaydi' => Carbon::parse($lichTrinh->thoigiandi)->format('Y-m-d'),
        'ngayden' => Carbon::parse($lichTrinh->thoigianden)->format('Y-m-d'),
        'giodi' => Carbon::parse($lichTrinh->thoigiandi)->format('H:i'),
        'gioden' => Carbon::parse($lichTrinh->thoigianden)->format('H:i'),
        'thoigiandichuyen' => $thoiGianDiChuyen,
        'gia' => number_format($lichTrinh->Gia->gia ?? 0, 0, ',', '.'),
        'sochocon' => $tongSoGheCon,
    ];

    return response()->json([
        'success' => true,
        'data' => $data,
    ], 200);
}

    // Thêm lịch trình mới
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'matau' => 'required|integer|exists:tau,matau',
            'matuyenduong' => 'required|integer|exists:tuyenduong,matuyenduong',
            'thoigiandi' => 'required|date_format:Y-m-d H:i:s',
            'thoigianden' => 'required|date_format:Y-m-d H:i:s|after:thoigiandi',
        ]);

        $lichtrinh = LichTrinh::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thêm lịch trình thành công!',
            'data' => [
                'malichtrinh' => $lichtrinh->malichtrinh,
                'thoigiandi' => Carbon::parse($lichtrinh->thoigiandi)->format('H:i d/m/Y'),
                'thoigianden' => Carbon::parse($lichtrinh->thoigianden)->format('H:i d/m/Y'),
            ]
        ], 201);
    }

    // Cập nhật lịch trình
    public function update(Request $request, $id): JsonResponse
    {
        $lichtrinh = LichTrinh::find($id);

        if (!$lichtrinh) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy lịch trình!'
            ], 404);
        }

        $validated = $request->validate([
            'matau' => 'required|integer|exists:tau,matau',
            'matuyenduong' => 'required|integer|exists:tuyenduong,matuyenduong',
            'thoigiandi' => 'required|date_format:Y-m-d H:i:s',
            'thoigianden' => 'required|date_format:Y-m-d H:i:s|after:thoigiandi',
        ]);

        $lichtrinh->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật lịch trình thành công!',
            'data' => [
                'malichtrinh' => $lichtrinh->malichtrinh,
                'thoigiandi' => Carbon::parse($lichtrinh->thoigiandi)->format('H:i d/m/Y'),
                'thoigianden' => Carbon::parse($lichtrinh->thoigianden)->format('H:i d/m/Y'),
            ]
        ], 200);
    }

    // Xóa lịch trình
    public function destroy($id): JsonResponse
    {
        $lichtrinh = LichTrinh::find($id);

        if (!$lichtrinh) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy lịch trình!'
            ], 404);
        }

        $lichtrinh->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa lịch trình thành công!'
        ], 200);
    }
}
