<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('details')->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function show($txnRef)
    {
        try {
            $order = Order::where('transaction_id', $txnRef)
                ->with('details.passenger')
                ->firstOrFail();

            return response()->json([
                'bookingData' => [
                    'id' => $order->id,
                    'transaction_id' => $order->transaction_id,
                    'contact_name' => $order->contact_name,
                    'contact_email' => $order->contact_email,
                    'contact_phone' => $order->contact_phone,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'checkin' => $order->checkin,
                    'ticket_type' => $order->ticket_type,
                    'details' => $order->details->map(function ($detail) {
                        return [
                            'trip_type' => $detail->trip_type,
                            'ticket_type' => $detail->ticket_type,
                            'schedule_route' => $detail->schedule_route,
                            'departureTime' => $detail->departure_time instanceof \Carbon\Carbon
                                ? $detail->departure_time->format('Y-m-d H:i')
                                : $detail->departure_time,
                            'arrivalTime' => $detail->arrival_time instanceof \Carbon\Carbon
                                ? $detail->arrival_time->format('Y-m-d H:i')
                                : $detail->arrival_time,
                            'train_code' => $detail->train_code,
                            'train_type' => $detail->train_type,
                            'car_name' => $detail->car_name ?? 'Không xác định',
                            'seat_number' => $detail->seat_number,
                            'price' => $detail->price,
                            'passenger' => $detail->passenger ? [
                                'name' => $detail->passenger->name,
                                'birthdate' => $detail->passenger->birthdate,
                                'cccd' => $detail->passenger->cccd,
                            ] : null,
                        ];
                    })->groupBy('trip_type')->mapWithKeys(function ($group, $key) {
                        return [$key === 'departure' ? 'departure' : 'return' => $group];
                    })->toArray(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy đơn hàng: ' . $e->getMessage(), ['txnRef' => $txnRef]);
            return response()->json(['error' => 'Không tìm thấy đơn hàng'], 404);
        }
    }
}