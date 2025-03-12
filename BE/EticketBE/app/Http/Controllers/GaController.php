<?php

namespace App\Http\Controllers;

use App\Models\Ga;
use Illuminate\Http\Request;

class GaController extends Controller
{
    // Lấy danh sách tất cả các ga và thông tin tuyến đường liên quan
    public function index()
    {
        $ga = Ga::with(['TuyenDuongDi', 'TuyenDuongDen'])->get();

        return response()->json([
            'success' => true,
            'data' => $ga->map(function ($g) {
                return [
                    'maga' => $g->maga,
                    'tenga' => $g->tenga,
                    'diachi' => $g->diachi,
                ];
            }),
        ]);
    }

    // Lấy thông tin chi tiết của một ga
    public function show($id)
    {
        $ga = Ga::with(['TuyenDuongDi', 'TuyenDuongDen'])->find($id);

        if (!$ga) {
            return response()->json(['success' => false, 'message' => 'Ga không tồn tại'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'maga' => $ga->maga,
                'tenga' => $ga->tenga,
                'diachi' => $ga->diachi,
            ],
        ]);
    }

    // Tạo một ga mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'maga' => 'required|unique:ga,maga',
            'tenga' => 'required|string|max:255',
            'diachi' => 'required|string|max:255',
        ]);

        $ga = Ga::create($validated);

        return response()->json(['success' => true, 'message' => 'Tạo ga thành công', 'data' => $ga], 201);
    }

    // Cập nhật thông tin một ga
    public function update(Request $request, $id)
    {
        $ga = Ga::find($id);

        if (!$ga) {
            return response()->json(['success' => false, 'message' => 'Ga không tồn tại'], 404);
        }

        $validated = $request->validate([
            'tenga' => 'sometimes|required|string|max:255',
            'diachi' => 'sometimes|required|string|max:255',
        ]);

        $ga->update($validated);

        return response()->json(['success' => true, 'message' => 'Cập nhật ga thành công', 'data' => $ga]);
    }

    // Xóa một ga
    public function destroy($id)
    {
        $ga = Ga::find($id);

        if (!$ga) {
            return response()->json(['success' => false, 'message' => 'Ga không tồn tại'], 404);
        }

        $ga->delete();

        return response()->json(['success' => true, 'message' => 'Xóa ga thành công']);
    }
}
