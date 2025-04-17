<template>
  <div class="payment-result">
      <div class="result-container">
          <el-card class="result-card">
              <div v-if="paymentStatus === 'success'" class="success-result">
                  <div class="icon-wrapper success">
                      <i class="el-icon-check-circle"></i>
                  </div>
                  <h1>Thanh toán thành công!</h1>
                  <p>Cảm ơn bạn đã đặt vé. Mã đơn hàng của bạn là: {{ transactionId }}</p>
                  <p>Loại vé: {{ bookingData.ticket_type === 'one-way' ? 'Một chiều' : 'Khứ hồi' }}</p>
                  <p>Vé điện tử sẽ được gửi đến email của bạn trong vòng 15 phút.</p>

                  <div class="ticket-info" v-if="bookingData">
                      <h3>Thông tin vé</h3>

                      <!-- Vé một chiều -->
                      <div class="ticket-details" v-if="departureData?.length">
                          <h4>Vé đi (Một chiều)</h4>
                          <div v-for="(detail, index) in departureData" :key="`departure-${index}`">
                              <p><strong>Tuyến đường:</strong> {{ detail.schedule_route }}</p>
                              <p><strong>Thời gian:</strong> {{ detail.departureTime }} đến {{ detail.arrivalTime }}</p>
                              <p><strong>Mã tàu:</strong> {{ detail.train_code }}</p>
                              <p><strong>Loại tàu:</strong> {{ detail.train_type }}</p>
                              <p><strong>Toa:</strong> {{ detail.car_name }}</p>
                              <p><strong>Số ghế:</strong> {{ detail.seat_number }}</p>
                              <p><strong>Giá vé:</strong> {{ formatTotalPrice(detail.price) }}</p>
                              <p><strong>Hành khách:</strong> {{ detail.passenger?.name || 'Không có thông tin' }} ({{ detail.passenger?.cccd || 'N/A' }})</p>
                          </div>
                      </div>

                      <!-- Vé khứ hồi -->
                      <div class="ticket-details" v-if="returnData?.length">
                          <h4>Vé về (Khứ hồi)</h4>
                          <div v-for="(detail, index) in returnData" :key="`return-${index}`">
                              <p><strong>Tuyến đường:</strong> {{ detail.schedule_route }}</p>
                              <p><strong>Thời gian:</strong> {{ detail.departureTime }} đến {{ detail.arrivalTime }}</p>
                              <p><strong>Mã tàu:</strong> {{ detail.train_code }}</p>
                              <p><strong>Loại tàu:</strong> {{ detail.train_type }}</p>
                              <p><strong>Toa:</strong> {{ detail.car_name }}</p>
                              <p><strong>Số ghế:</strong> {{ detail.seat_number }}</p>
                              <p><strong>Giá vé:</strong> {{ formatTotalPrice(detail.price) }}</p>
                              <p><strong>Hành khách:</strong> {{ detail.passenger?.name || 'Không có thông tin' }} ({{ detail.passenger?.cccd || 'N/A' }})</p>
                          </div>
                      </div>

                      <!-- Tổng tiền -->
                      <div class="ticket-details">
                          <p><strong>Tổng tiền:</strong> {{ formatTotalPrice(bookingData.total_amount) }}</p>
                      </div>
                  </div>
                  <div v-else>
                      <p>Không có thông tin vé.</p>
                  </div>

                  <div class="actions">
                      <el-button type="primary" @click="goToHome">Về trang chủ</el-button>
                      <el-button @click="printTicket">In vé</el-button>
                  </div>
              </div>

              <div v-else-if="paymentStatus === 'failed'" class="failed-result">
                  <div class="icon-wrapper failed">
                      <i class="el-icon-close-circle"></i>
                  </div>
                  <h1>Thanh toán thất bại</h1>
                  <p>Rất tiếc, thanh toán của bạn không thành công.</p>
                  <p>Mã lỗi: {{ errorCode }}</p>
                  <div class="actions">
                      <el-button @click="tryAgain">Thử lại</el-button>
                      <el-button @click="goToHome">Về trang chủ</el-button>
                  </div>
              </div>

              <div v-else class="processing-result">
                  <div class="icon-wrapper processing">
                      <i class="el-icon-loading"></i>
                  </div>
                  <h1>Đang xử lý thanh toán</h1>
                  <p>Vui lòng đợi trong khi chúng tôi xác nhận thanh toán của bạn.</p>
              </div>
          </el-card>
      </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useFormatPrice } from '@/composables/useFormatprice';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const { formatTotalPrice } = useFormatPrice();

const paymentStatus = ref('processing');
const transactionId = ref('');
const errorCode = ref('');
const bookingData = ref(null);

const departureData = computed(() => {
  if (bookingData.value?.details) {
      return bookingData.value.details.departure || [];
  }
  return [];
});
const returnData = computed(() => {
  if (bookingData.value?.details) {
      return bookingData.value.details.return || [];
  }
  return [];
});

onMounted(async () => {
  const vnpTxnRef = route.query.vnp_TxnRef;
  const vnpResponseCode = route.query.vnp_ResponseCode;

  if (!vnpTxnRef) {
      paymentStatus.value = 'failed';
      errorCode.value = 'Không tìm thấy mã giao dịch';
      return;
  }

  transactionId.value = vnpTxnRef;

  if (vnpResponseCode === '00') {
      paymentStatus.value = 'success';

      try {
          const response = await axios.get(`/orders/${vnpTxnRef}`);
          console.log('API response:', response.data);
          bookingData.value = response.data.bookingData;
      } catch (error) {
          console.error('Lỗi khi lấy dữ liệu đơn hàng:', error.response?.data || error.message);
          paymentStatus.value = 'failed';
          errorCode.value = error.response?.data?.error || 'Lỗi tải dữ liệu đơn hàng';
      }
  } else {
      paymentStatus.value = 'failed';
      errorCode.value = vnpResponseCode || 'Lỗi không xác định';
  }
});

const goToHome = () => {
  router.push('/');
};

const tryAgain = () => {
  router.go(-1);
};

const printTicket = () => {
  window.print();
};
</script>

<style scoped>
.payment-result {
  background-color: #f2f7fa;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 0;
}

.result-container {
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
}

.result-card {
  padding: 20px;
}

.icon-wrapper {
  font-size: 64px;
  margin-bottom: 20px;
  text-align: center;
}

.success-result,
.failed-result,
.processing-result {
  text-align: center;
  padding: 20px 0;
}

.success .el-icon-check-circle {
  color: #67c23a;
}

.failed .el-icon-close-circle {
  color: #f56c6c;
}

.processing .el-icon-loading {
  color: #409eff;
}

h1 {
  font-size: 28px;
  margin-bottom: 20px;
  color: #303133;
}

p {
  color: #606266;
  margin-bottom: 10px;
}

.ticket-info {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 20px;
  margin: 30px 0;
  text-align: left;
}

.ticket-info h3 {
  margin-top: 0;
  color: #104c8a;
  font-size: 18px;
  margin-bottom: 15px;
}

.ticket-info h4 {
  color: #104c8a;
  font-size: 16px;
  margin: 20px 0 10px;
}

.ticket-details {
  margin-bottom: 20px;
  padding: 10px;
  border-bottom: 1px solid #e8ecef;
}

.ticket-details:last-child {
  border-bottom: none;
}

.ticket-details p {
  margin-bottom: 8px;
}

.actions {
  margin-top: 30px;
  display: flex;
  justify-content: center;
  gap: 15px;
}

@media print {
  .actions {
      display: none;
  }

  .ticket-info {
      background-color: transparent;
      border: none;
  }
}
</style>