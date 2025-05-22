<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReportAdminController extends Controller
{
    public function statistics(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:schedule_route,user,time',
                'malichtrinh' => 'nullable|integer|exists:lichtrinh,malichtrinh',
                'user_id' => 'nullable|integer|exists:users,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'group_by' => 'nullable|in:week,month,year',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $query = Order::query()
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->selectRaw('SUM(order_details.price) as revenue');

            $type = $request->type;
            $statistics = [];

            if ($type === 'schedule_route') {
                // Log số lượng bản ghi
                Log::info('Orders count', ['count' => DB::table('orders')->count()]);
                Log::info('Order details count', ['count' => DB::table('order_details')->count()]);
                Log::info('Tuyen duong count', ['count' => DB::table('tuyenduong')->count()]);
                Log::info('Lich trinh count', ['count' => DB::table('lichtrinh')->count()]);
                Log::info('Ga count', ['count' => DB::table('ga')->count()]);

                // Log mẫu dữ liệu
                Log::info('Sample orders', [
                    'sample' => DB::table('orders')->select('id')->take(3)->get(),
                ]);
                Log::info('Sample order_details', [
                    'sample' => DB::table('order_details')->select('order_id', 'schedule_route', 'departure_time', 'arrival_time')->take(3)->get(),
                ]);
                Log::info('Sample tuyenduong', [
                    'sample' => DB::table('tuyenduong')->select('matuyenduong', 'gadi', 'gaden')->take(3)->get(),
                ]);
                Log::info('Sample ga', [
                    'sample' => DB::table('ga')->select('maga', 'tenga')->take(3)->get(),
                ]);
                Log::info('Sample lichtrinh', [
                    'sample' => DB::table('lichtrinh')->select('malichtrinh', 'matuyenduong', 'thoigiandi', 'thoigianden')->take(3)->get(),
                ]);

                // Thống kê theo mã lịch trình
                $query->selectRaw('lichtrinh.malichtrinh as label')
                    ->join('tuyenduong', function ($join) {
                        $join->join('ga as ga_from', 'tuyenduong.gadi', '=', 'ga_from.maga')
                             ->join('ga as ga_to', 'tuyenduong.gaden', '=', 'ga_to.maga')
                             ->whereRaw("REGEXP_REPLACE(LOWER(order_details.schedule_route), '[^a-z0-9-]', '') = REGEXP_REPLACE(LOWER(CONCAT(ga_from.tenga, '-', ga_to.tenga)), '[^a-z0-9-]', '')");
                    })
                    ->join('lichtrinh', function ($join) {
                        $join->on('lichtrinh.matuyenduong', '=', 'tuyenduong.matuyenduong')
                             ->whereRaw("DATE_FORMAT(lichtrinh.thoigiandi, '%Y-%m-%d %H:%i:%s') = DATE_FORMAT(order_details.departure_time, '%Y-%m-%d %H:%i:%s')")
                             ->whereRaw("DATE_FORMAT(lichtrinh.thoigianden, '%Y-%m-%d %H:%i:%s') = DATE_FORMAT(order_details.arrival_time, '%Y-%m-%d %H:%i:%s')");
                    })
                    ->groupBy('lichtrinh.malichtrinh')
                    ->when($request->malichtrinh, function ($q) use ($request) {
                        return $q->where('lichtrinh.malichtrinh', $request->malichtrinh);
                    });

                // Log số bản ghi sau từng bước join
                $tempQuery = clone $query;
                Log::info('Records after orders-order_details join', [
                    'count' => Order::query()
                        ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                        ->count()
                ]);
                Log::info('Records after tuyenduong-ga join', [
                    'count' => $tempQuery->count()
                ]);
                Log::info('Records after all joins', ['count' => $tempQuery->count()]);

                // Log truy vấn
                Log::info('Statistics query', [
                    'malichtrinh' => $request->malichtrinh,
                    'sql' => $query->toSql(),
                    'bindings' => $query->getBindings(),
                ]);

                $statistics = $query->get()->map(function ($item) {
                    return [
                        'label' => (string) $item->label,
                        'revenue' => $item->revenue ?? 0,
                    ];
                });

                // Log kết quả
                Log::info('Statistics result', ['statistics' => $statistics->toArray()]);
            } elseif ($type === 'user') {
                $query->selectRaw('users.name as label')
                    ->join('users', 'orders.user_id', '=', 'users.id')
                    ->groupBy('users.name')
                    ->when($request->user_id, function ($q) use ($request) {
                        return $q->where('orders.user_id', $request->user_id);
                    });
                $statistics = $query->get()->map(function ($item) {
                    return [
                        'label' => $item->label,
                        'revenue' => $item->revenue ?? 0,
                    ];
                });
            } elseif ($type === 'time') {
                $query->when($request->start_date && $request->end_date, function ($q) use ($request) {
                    return $q->whereBetween('orders.created_at', [$request->start_date, $request->end_date]);
                });

                $groupBy = $request->group_by ?? 'month';
                switch ($groupBy) {
                    case 'week':
                        $query->selectRaw("DATE_FORMAT(orders.created_at, '%Y-%u') as label");
                        $query->groupBy('label');
                        break;
                    case 'month':
                        $query->selectRaw("DATE_FORMAT(orders.created_at, '%Y-%m') as label");
                        $query->groupBy('label');
                        break;
                    case 'year':
                        $query->selectRaw("DATE_FORMAT(orders.created_at, '%Y') as label");
                        $query->groupBy('label');
                        break;
                }

                $statistics = $query->get()->map(function ($item) {
                    return [
                        'label' => $item->label,
                        'revenue' => $item->revenue ?? 0,
                    ];
                });
            }

            return response()->json([
                'success' => true,
                'type' => $type,
                'statistics' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi thống kê doanh thu: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Thống kê doanh thu thất bại',
            ], 500);
        }
    }
}