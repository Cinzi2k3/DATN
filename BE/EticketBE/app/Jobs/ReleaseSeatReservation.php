<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Order;

class ReleaseSeatReservation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $macho;
    protected $malichtrinh;
    protected $transaction_id;

    public function __construct($macho, $malichtrinh, $transaction_id = null)
    {
        $this->macho = $macho;
        $this->malichtrinh = $malichtrinh;
        $this->transaction_id = $transaction_id;
    }

    public function handle(): void
    {
        try {
            // Kiểm tra bản ghi trong bảng datve
            $gheLichTrinh = DB::table('datve')
                ->where('macho', $this->macho)
                ->where('malichtrinh', $this->malichtrinh)
                ->first();

            if (!$gheLichTrinh) {
                Log::info('Không tìm thấy bản ghi trong datve', [
                    'macho' => $this->macho,
                    'malichtrinh' => $this->malichtrinh
                ]);
                return;
            }

            // Kiểm tra trạng thái thanh toán nếu có transaction_id
            if ($this->transaction_id) {
                $order = Order::where('transaction_id', $this->transaction_id)->first();
                if ($order && $order->status === 'completed') {
                    Log::info('Ghế đã được thanh toán, không hủy giữ chỗ', [
                        'macho' => $this->macho,
                        'malichtrinh' => $this->malichtrinh,
                        'transaction_id' => $this->transaction_id
                    ]);
                    return;
                }
            }

            // Nếu ghế ở trạng thái danggiu và đã hết hạn, xóa bản ghi
            if ($gheLichTrinh->trangthai === 'danggiu' && Carbon::now()->greaterThanOrEqualTo($gheLichTrinh->thoihan_giu)) {
                DB::table('datve')
                    ->where('macho', $this->macho)
                    ->where('malichtrinh', $this->malichtrinh)
                    ->delete();

                Log::info('Đã xóa bản ghi ghế khỏi datve (trở về trạng thái controng)', [
                    'macho' => $this->macho,
                    'malichtrinh' => $this->malichtrinh
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi xử lý Job ReleaseSeatReservation: ' . $e->getMessage(), [
                'macho' => $this->macho,
                'malichtrinh' => $this->malichtrinh
            ]);
        }
    }
}