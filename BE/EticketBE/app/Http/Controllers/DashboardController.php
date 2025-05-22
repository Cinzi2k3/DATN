<?php

// app/Http/Controllers/Admin/DashboardController.php
namespace App\Http\Controllers;

use App\Http\Controllers;
use App\Models\Order;
use App\Models\LichTrinh;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        $range = $request->query('range', 'month');
        $startDate = match ($range) {
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };
        $endDate = match ($range) {
            'week' => Carbon::now()->endOfWeek(),
            'month' => Carbon::now()->endOfMonth(),
            'year' => Carbon::now()->endOfYear(),
            default => Carbon::now()->endOfMonth(),
        };

        // Doanh thu
        $revenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        // Số đơn hàng
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])->count();

        // Người dùng mới
        $newUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();

        // Tổng số ghế từ lịch trình
        $totalSeats = LichTrinh::whereBetween('thoigiandi', [$startDate, $endDate])
            ->with(['tau.toa'])
            ->get()
            ->sum(function ($LichTrinh) {
                return $LichTrinh->tau ? $LichTrinh->tau->toa->sum('socho') : 0;
            });

        // Số ghế đã đặt (mỗi OrderDetail là một ghế)
        $bookedSeats = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('details')
            ->get()
            ->sum(function ($order) {
                return $order->details->count();
            });

        // Tỷ lệ lấp đầy
        $occupancyRate = $totalSeats ? ($bookedSeats / $totalSeats) * 100 : 0;

        // Tính trend so với khoảng trước
        $previousStartDate = match ($range) {
            'week' => Carbon::now()->subWeek()->startOfWeek(),
            'month' => Carbon::now()->subMonth()->startOfMonth(),
            'year' => Carbon::now()->subYear()->startOfYear(),
            default => Carbon::now()->subMonth()->startOfMonth(),
        };
        $previousEndDate = match ($range) {
            'week' => Carbon::now()->subWeek()->endOfWeek(),
            'month' => Carbon::now()->subMonth()->endOfMonth(),
            'year' => Carbon::now()->subYear()->endOfYear(),
            default => Carbon::now()->subMonth()->endOfMonth(),
        };

        $lastRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->sum('total_amount');
        $revenueTrend = $lastRevenue ? (($revenue - $lastRevenue) / $lastRevenue * 100) : 0;

        $lastOrders = Order::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        $ordersTrend = $lastOrders ? (($orders - $lastOrders) / $lastOrders * 100) : 0;

        $lastNewUsers = User::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        $newUsersTrend = $lastNewUsers ? (($newUsers - $lastNewUsers) / $lastNewUsers * 100) : 0;

        $lastBookedSeats = Order::where('status', 'completed')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->with('details')
            ->get()
            ->sum(function ($order) {
                return $order->details->count();
            });
        $lastOccupancyRate = $totalSeats ? ($lastBookedSeats / $totalSeats) * 100 : 0;
        $occupancyTrend = $lastOccupancyRate ? ($occupancyRate - $lastOccupancyRate) : 0;

        return response()->json([
            'revenue' => $revenue,
            'orders' => $orders,
            'newUsers' => $newUsers,
            'occupancyRate' => round($occupancyRate, 1),
            'revenueTrend' => round($revenueTrend, 1),
            'ordersTrend' => round($ordersTrend, 1),
            'newUsersTrend' => round($newUsersTrend, 1),
            'occupancyTrend' => round($occupancyTrend, 1),
        ]);
    }

    public function getRevenue(Request $request)
    {
        $range = $request->query('range', 'month');
        $startDate = match ($range) {
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        $labels = [];
        $data = [];
        if ($range === 'week') {
            for ($i = 0; $i < 7; $i++) {
                $date = $startDate->copy()->addDays($i);
                $labels[] = $date->format('d/m');
                $data[] = Order::where('status', 'completed')
                    ->whereDate('created_at', $date)
                    ->sum('total_amount') / 1000000;
            }
        } elseif ($range === 'year') {
            for ($i = 0; $i < 12; $i++) {
                $date = $startDate->copy()->addMonths($i);
                $labels[] = 'T' . ($i + 1);
                $data[] = Order::where('status', 'completed')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('total_amount') / 1000000;
            }
        } else {
            $days = $startDate->daysInMonth;
            for ($i = 0; $i < $days; $i++) {
                $date = $startDate->copy()->addDays($i);
                $labels[] = $date->format('d/m');
                $data[] = Order::where('status', 'completed')
                    ->whereDate('created_at', $date)
                    ->sum('total_amount') / 1000000;
            }
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Doanh thu',
                    'data' => $data,
                    'borderColor' => '#409EFF',
                    'backgroundColor' => 'rgba(64, 158, 255, 0.1)',
                ],
            ],
        ]);
    }

    public function getOccupancy(Request $request)
    {
        $range = $request->query('range', 'month');
        $startDate = match ($range) {
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };
        $endDate = match ($range) {
            'week' => Carbon::now()->endOfWeek(),
            'month' => Carbon::now()->endOfMonth(),
            'year' => Carbon::now()->endOfYear(),
            default => Carbon::now()->endOfMonth(),
        };

        // Tổng số ghế từ lịch trình
        $totalSeats = LichTrinh::whereBetween('thoigiandi', [$startDate, $endDate])
            ->with(['tau.toa'])
            ->get()
            ->sum(function ($LichTrinh) {
                return $LichTrinh->tau ? $LichTrinh->tau->toa->sum('socho') : 0;
            });

        // Số ghế đã đặt
        $booked = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('details')
            ->get()
            ->sum(function ($order) {
                return $order->details->count();    
            });

        // Ghế bảo trì (giả lập, cần thay bằng logic thực nếu có)
        $maintenance = 0; // Có thể query từ bảng Carriage nếu có trạng thái bảo trì
        $available = $totalSeats - $booked - $maintenance;

        return response()->json([
            'labels' => ['Đã đặt', 'Còn trống', 'Bảo trì'],
            'datasets' => [
                [
                    'data' => [$booked, $available, $maintenance],
                    'backgroundColor' => ['#67C23A', '#409EFF', '#E6A23C'],
                ],
            ],
        ]);
    }

    public function getRecentOrders()
    {
        $orders = Order::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->transaction_id,
                    'customer' => $order->contact_name ?? $order->user->name,
                    'email' => $order->contact_email ?? $order->user->email,
                    'phone' => $order->contact_phone ?? $order->user->profile->phone_number,
                    'date' => $order->created_at->format('d/m/Y'),
                    'amount' => number_format($order->total_amount) . 'đ',
                    'status' => match ($order->status) {
                        'completed' => 'Hoàn thành',
                        'pending' => 'Chờ xử lý',
                        default => 'Không xác định',
                    },
                ];
            });

        return response()->json($orders);
    }
}