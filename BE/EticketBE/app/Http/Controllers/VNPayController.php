<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Passenger;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VNPayController extends Controller
{
    public function createPayment(Request $request)
    {
        try {
            Log::info('VNPay createPayment request:', $request->all());

            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
            $vnp_Returnurl = "http://127.0.0.1:8000/api/vnpay/return";
            $vnp_TmnCode = "KHT30JML";
            $vnp_HashSecret = "YDKXVCGC7KL9KGJ8Y83JTORK8MWXCFSV";

            // Tạo mã đơn hàng
            $vnp_TxnRef = time() . "-" . Str::random(8);

            // Lưu thông tin đơn hàng vào bảng orders
            $order = new Order();
            $order->transaction_id = $vnp_TxnRef;
            $order->contact_name = $request->contact['name'] ?? 'Unknown';
            $order->contact_email = $request->contact['email'] ?? 'Unknown';
            $order->contact_phone = $request->contact['phone'] ?? 'Unknown';
            $order->total_amount = $request->amount ?? 0;
            $order->status = 'pending';
            $order->ticket_type = ($request->return && !empty($request->return['searchgadi']) && !empty($request->return['searchgaden'])) ? 'round-trip' : 'one-way';
            $order->save();
            Log::info('Order saved:', ['order_id' => $order->id, 'transaction_id' => $order->transaction_id]);

            // Lưu thông tin hành khách và ánh xạ ID hành khách
            $passengerIds = [];
            foreach ($request->passengers as $passenger) {
                $passengerRecord = new Passenger();
                $passengerRecord->order_id = $order->id;
                $passengerRecord->name = $passenger['name'] ?? 'Unknown';
                $passengerRecord->birthdate = $passenger['year'] . '-' . $passenger['month'] . '-' . $passenger['day'] ?? null;
                $passengerRecord->cccd = $passenger['idNumber'] ?? null;
                $passengerRecord->save();
                $passengerIds[] = $passengerRecord->id;
            }

            // Lưu chi tiết vé đi (one-way)
            $passengerIndex = 0;
            foreach ($request->departure['selectedSeatsByCar'] as $carName => $seatAssignments) {
                foreach ($seatAssignments as $seat) {
                    if ($passengerIndex >= count($request->passengers)) break;

                    $passenger = $request->passengers[$passengerIndex];

                    $orderDetail = new OrderDetail();
                    $orderDetail->order_id = $order->id;
                    $orderDetail->passenger_id = $passengerIds[$passengerIndex];
                    $orderDetail->trip_type = 'departure';
                    $orderDetail->ticket_type = $passenger['ticket_type'] ?? ($seat['ticketType'] === 'Người lớn' ? 'adult' : 'child');
                    $orderDetail->schedule_route = ($request->departure['searchgadi'] ?? 'Unknown') . ' - ' . ($request->departure['searchgaden'] ?? 'Unknown');
                    $orderDetail->car_name = $carName; // Lưu tên toa động
                    $orderDetail->departure_time = Carbon::createFromFormat('Y-m-d H:i', $request->departure['selectedDay'] . ' ' . $request->departure['departureTime'])->toDateTimeString();
                    $orderDetail->arrival_time = Carbon::createFromFormat('Y-m-d H:i', $request->departure['arrivalDate'] . ' ' . $request->departure['arrivalTime'])->toDateTimeString();
                    $orderDetail->train_code = $request->departure['trainCode'] ?? 'Unknown';
                    $orderDetail->train_type = $request->departure['traintau'] ?? 'Unknown';
                    $orderDetail->seat_number = $seat['sohieu'] ?? 'Unknown';
                    $orderDetail->price = $seat['gia'] ?? ($request->amount / count($request->passengers));
                    $orderDetail->save();
                    Log::info('OrderDetail (one-way) saved:', ['detail_id' => $orderDetail->id, 'car_name' => $carName]);

                    $passengerIndex++;
                }
            }

            // Lưu chi tiết vé về (nếu có)
            if ($request->return && !empty($request->return['searchgadi']) && !empty($request->return['searchgaden'])) {
                $passengerIndex = 0;
                foreach ($request->return['selectedSeatsByCar'] as $carName => $seatAssignments) {
                    foreach ($seatAssignments as $seat) {
                        if ($passengerIndex >= count($request->passengers)) break;

                        $passenger = $request->passengers[$passengerIndex];

                        $orderDetail = new OrderDetail();
                        $orderDetail->order_id = $order->id;
                        $orderDetail->passenger_id = $passengerIds[$passengerIndex];
                        $orderDetail->trip_type = 'return';
                        $orderDetail->ticket_type = $passenger['ticket_type'] ?? ($seat['ticketType'] === 'Người lớn' ? 'adult' : 'child');
                        $orderDetail->schedule_route = ($request->return['searchgadi'] ?? 'Unknown') . ' - ' . ($request->return['searchgaden'] ?? 'Unknown');
                        $orderDetail->car_name = $carName; // Lưu tên toa động
                        $orderDetail->departure_time = Carbon::createFromFormat('Y-m-d H:i', $request->return['selectedDay'] . ' ' . $request->return['departureTime'])->toDateTimeString();
                        $orderDetail->arrival_time = Carbon::createFromFormat('Y-m-d H:i', $request->return['arrivalDate'] . ' ' . $request->return['arrivalTime'])->toDateTimeString();
                        $orderDetail->train_code = $request->return['trainCode'] ?? 'Unknown';
                        $orderDetail->train_type = $request->return['traintau'] ?? 'Unknown';
                        $orderDetail->seat_number = $seat['sohieu'] ?? 'Unknown';
                        $orderDetail->price = $seat['gia'] ?? ($request->amount / count($request->passengers));
                        $orderDetail->save();
                        Log::info('OrderDetail (round-trip) saved:', ['detail_id' => $orderDetail->id, 'car_name' => $carName]);

                        $passengerIndex++;
                    }
                }
            }

            // Tạo dữ liệu gửi đến VNPay
            $vnp_OrderInfo = "Thanh toán vé tàu";
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $request->amount * 100;
            $vnp_Locale = 'vn';
            $vnp_BankCode = $request->bankCode ?? '';
            $vnp_IpAddr = $request->ip();

            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef
            );

            if (!empty($vnp_BankCode)) {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }

            $returnData = array(
                'code' => '00',
                'message' => 'success',
                'data' => $vnp_Url
            );

            return response()->json($returnData);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo thanh toán VNPay: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json([
                'code' => '99',
                'message' => 'Lỗi server: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function returnPayment(Request $request)
    {
        $vnp_HashSecret = "YDKXVCGC7KL9KGJ8Y83JTORK8MWXCFSV";
        $inputData = $request->all();

        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $hashData = "";
        $i = 0;

        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash == $vnp_SecureHash) {
            $order = Order::where('transaction_id', $inputData['vnp_TxnRef'])->first();

            if ($order) {
                if ($inputData['vnp_ResponseCode'] == '00') {
                    $order->status = 'completed';
                    $order->save();
                    $redirectUrl = env('FRONTEND_URL') . '/payment/result?vnp_TxnRef=' . $inputData['vnp_TxnRef'] . '&vnp_ResponseCode=' . $inputData['vnp_ResponseCode'];
                } else {
                    $order->status = 'failed';
                    $order->save();
                    $redirectUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/payment/result?vnp_ResponseCode=' . ($inputData['vnp_ResponseCode'] ?? '99') . '&status=failed';
                }
                return redirect()->to($redirectUrl);
            } else {
                return redirect()->to(env('FRONTEND_URL', 'http://localhost:5173') . '/payment/error');
            }
        } else {
            return redirect()->to(env('FRONTEND_URL', 'http://localhost:5173') . '/payment/error');
        }
    }
}