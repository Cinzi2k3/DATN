<template>
  <el-card class="vnpay-card">
    <template #header>
      <div class="card-header">
        <h2>Phương thức thanh toán</h2>
      </div>
    </template>
    
    <div class="payment-methods">
      <el-radio-group v-model="paymentMethod" class="payment-radio-group">
        <el-radio label="vnpay" border>
          <div class="payment-option">
            <span>Thanh toán qua VNPay</span>
          </div>
        </el-radio>
      </el-radio-group>
    </div>
    
    <div class="vnpay-banks">
      <h3>Chọn ngân hàng thanh toán (tùy chọn)</h3>
      <div class="bank-list">
        <el-radio-group v-model="selectedBank" class="bank-radio-group">
          <el-radio label="" border>VNPay</el-radio>
          <el-radio label="NCB" border>NCB</el-radio>
          <el-radio label="BIDV" border>BIDV</el-radio>
          <el-radio label="VCB" border>Vietcombank</el-radio>
          <el-radio label="TCB" border>Techcombank</el-radio>
        </el-radio-group>
      </div>
    </div>
    
    <div class="countdown" v-if="timeLeft > 0">
      <p>Thời gian còn lại để thanh toán: {{ formatTime(timeLeft) }}</p>
    </div>
    <div class="countdown expired" v-else>
      <p>Thời gian giữ ghế đã hết hạn. Vui lòng chọn lại ghế.</p>
    </div>
    
    <div class="payment-actions">
      <el-button @click="gotoback">
        <el-icon><ArrowLeft /></el-icon> Quay lại
      </el-button>
      <el-button 
        type="primary" 
        :loading="loading" 
        :disabled="timeLeft <= 0" 
        @click="processPayment"
      >
        Thanh toán {{ formatTotalPrice(totalPrice) }}
        <el-icon class="el-icon--right"><ArrowRight /></el-icon>
      </el-button>
    </div>

    <!-- Dialog thông báo hết giờ -->
    <el-dialog
      title="Thông báo hết thời gian giữ vé"
      v-model="showTimeoutDialog"
      width="30%"
      center
      :close-on-click-modal="false"
      :close-on-press-escape="false"
      :show-close="false"
      class="timeout-dialog"
    >
      <div class="dialog-content">
        <p>Thời gian giữ vé của bạn đã hết. Vui lòng đặt vé lại để tiếp tục.</p>
      </div>
      <template #footer>
        <div class="dialog-footer">
          <el-button class="book-again-button" @click="bookAgain">Đặt vé lại</el-button>
        </div>
      </template>
    </el-dialog>
  </el-card>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { ArrowRight, ArrowLeft } from '@element-plus/icons-vue';
import { useFormatPrice } from '@/composables/useFormatprice';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const props = defineProps({
  departureData: { 
    type: Object, 
    required: true 
  },
  returnData: { 
    type: Object, 
    default: null 
  },
  contactInfo: {
    type: Object,
    required: true
  },
  passengerInfo: {
    type: Array,
    required: true
  }
});

// State cho dialog
const showTimeoutDialog = ref(false);

// Cờ để kiểm tra xem có đang chuyển hướng đến thanh toán không
const isNavigatingToPayment = ref(false);

// Hàm chuẩn bị dữ liệu ghế để xóa
const prepareSeatsToRelease = () => {
  if (!props.departureData || !props.departureData.selectedSeatsByCar) {
    console.warn('Không có dữ liệu ghế đi để xóa.');
    return [];
  }

  const departureSeats = props.departureData.selectedSeatsByCar || {};
  const returnSeats = props.returnData?.selectedSeatsByCar || {};
  const seatsToRelease = [];

  Object.keys(departureSeats).forEach(carName => {
    departureSeats[carName].forEach(seat => {
      if (seat.sohieu && props.departureData.malichtrinh && props.departureData.matoa) {
        seatsToRelease.push({
          sohieu: seat.sohieu,
          malichtrinh: props.departureData.malichtrinh,
          matoa: props.departureData.matoa
        });
      }
    });
  });

  if (props.returnData && props.returnData.malichtrinh && props.returnData.matoa) {
    Object.keys(returnSeats).forEach(carName => {
      returnSeats[carName].forEach(seat => {
        if (seat.sohieu) {
          seatsToRelease.push({
            sohieu: seat.sohieu,
            malichtrinh: props.returnData.malichtrinh,
            matoa: props.returnData.matoa
          });
        }
      });
    });
  }

  return seatsToRelease;
};

// Hàm xóa ghế bằng sendBeacon
const releaseSeatsWithBeacon = () => {
  const seatsToRelease = prepareSeatsToRelease();
  if (seatsToRelease.length === 0) {
    console.warn('Không có ghế nào để xóa.');
    return;
  }

  try {
    const data = JSON.stringify({ seats: seatsToRelease });
    const blob = new Blob([data], { type: 'application/json' });
    const success = navigator.sendBeacon('http://192.168.0.105:8000/api/release-seats', blob);
    if (!success) {
      console.error('Gửi sendBeacon thất bại.');
    } else {
      console.log('Yêu cầu xóa ghế đã được gửi:', seatsToRelease);
    }
  } catch (error) {
    console.error('Lỗi khi gửi yêu cầu xóa ghế:', error);
  }
};

// Hàm xóa ghế bằng axios
const releaseSeatsWithAxios = async () => {
  const seatsToRelease = prepareSeatsToRelease();
  if (seatsToRelease.length === 0) {
    console.warn('Không có ghế nào để xóa.');
    return;
  }

  try {
    await axios.post(
      'http://192.168.0.105:8000/api/release-seats',
      { seats: seatsToRelease },
      { withCredentials: true }
    );
    console.log('Xóa ghế thành công:', seatsToRelease);
  } catch (error) {
    console.error('Lỗi khi xóa ghế:', error);
  }
};

// Xử lý sự kiện trước khi rời khỏi trang
onMounted(() => {
  window.history.pushState(null, null, window.location.href);
  window.addEventListener('popstate', () => {
    if (!isNavigatingToPayment.value) {
      releaseSeatsWithBeacon();
      clearCountdown();
    }
  });

  window.addEventListener('beforeunload', () => {
    if (!isNavigatingToPayment.value) {
      releaseSeatsWithBeacon();
      clearCountdown();
    }
  });
});

// Dọn dẹp sự kiện
onUnmounted(() => {
  window.removeEventListener('popstate', releaseSeatsWithBeacon);
  window.removeEventListener('beforeunload', releaseSeatsWithBeacon);
  if (!isNavigatingToPayment.value) {
    releaseSeatsWithBeacon();
    clearCountdown();
  }
});

// Xử lý nút "Quay lại"
const gotoback = async () => {
  await releaseSeatsWithAxios();
  clearCountdown();
  window.history.back();
};

const { formatTotalPrice } = useFormatPrice();
const paymentMethod = ref('vnpay');
const selectedBank = ref('');
const loading = ref(false);
const timeLeft = ref(3 * 60);
let countdownInterval = null;

const totalPrice = computed(() => {
  const departurePrice = props.departureData?.totalPrice || 0;
  const returnPrice = props.returnData?.totalPrice || 0;
  return departurePrice + returnPrice;
});

// Format thời gian còn lại
const formatTime = (seconds) => {
  const minutes = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
};

// Xử lý nút "Đặt vé lại"
const bookAgain = () => {
  showTimeoutDialog.value = false;
  clearCountdown();
  localStorage.removeItem('bookingSession');
  router.push({ name: 'Home' });
};

// Xóa countdown
const clearCountdown = () => {
  localStorage.removeItem('countdownStartTime');
};

// Kiểm tra trạng thái đặt vé và khởi tạo đếm ngược
const initializeCountdown = async () => {
  const totalDuration = 3 * 60;
 // Kiểm tra xem có ghế nào đang được giữ không
  try {
    const response = await axios.get('http://192.168.0.105:8000/api/check-reservation', {
      params: {
        malichtrinh: props.departureData.malichtrinh,
        matoa: props.departureData.matoa
      },
      withCredentials: true
    });

    const { hasReservation, thoihan_giu } = response.data;

    if (!hasReservation) {
            // Không có ghế nào đang được giữ, reset đếm ngược
      clearCountdown();
      localStorage.setItem('countdownStartTime', Math.floor(Date.now() / 1000).toString());
      timeLeft.value = totalDuration;
    } else {
      // Có ghế đang được giữ, tính thời gian còn lại từ backend
      const now = Math.floor(Date.now() / 1000);
      const remaining = Math.max(0, Math.floor((new Date(thoihan_giu).getTime() / 1000) - now));
      timeLeft.value = remaining;

      if (remaining <= 0) {
        clearCountdown();
        showTimeoutDialog.value = true;
        releaseSeatsWithAxios();
        return;
      }
    }
  } catch (error) {
    console.error('Lỗi khi kiểm tra trạng thái đặt vé:', error);
    clearCountdown();
    localStorage.setItem('countdownStartTime', Math.floor(Date.now() / 1000).toString());
    timeLeft.value = totalDuration;
  }
  // Chạy bộ đếm ngược
  countdownInterval = setInterval(() => {
    if (timeLeft.value > 0) {
      timeLeft.value -= 1;
    } else {
      clearInterval(countdownInterval);
      clearCountdown();
      showTimeoutDialog.value = true;
      releaseSeatsWithAxios();
    }
  }, 1000);
};

// Khởi tạo đếm ngược khi component được mount
onMounted(() => {
  initializeCountdown();
});

// Dọn dẹp interval
onUnmounted(() => {
  clearInterval(countdownInterval);
});

// Gọi clearCountdown trong processPayment
const processPayment = async () => {
  if (paymentMethod.value === 'vnpay') {
    try {
      loading.value = true;

      const bookingData = {
        departure: props.departureData,
        return: props.returnData,
        contact: props.contactInfo,
        passengers: props.passengerInfo,
        amount: totalPrice.value,
        bankCode: selectedBank.value
      };

      const vnpayResponse = await axios.post('http://192.168.0.105:8000/api/vnpay/create', bookingData);

      if (vnpayResponse.data && vnpayResponse.data.data) {
        localStorage.setItem('bookingData', JSON.stringify(bookingData));
        clearCountdown();
        isNavigatingToPayment.value = true; // Đặt cờ trước khi chuyển hướng
        window.location.href = vnpayResponse.data.data;
      }
    } catch (error) {
      console.error('Lỗi khi xử lý thanh toán:', error);
      ElMessage.error('Đã xảy ra lỗi khi xử lý thanh toán. Vui lòng thử lại sau.');
    } finally {
      loading.value = false;
    }
  }
};
</script>

<style scoped>
.vnpay-card {
  margin-bottom: 20px;
}

.card-header h2 {
  margin: 0;
  font-size: 18px;
  color: #104c8a;
}

.payment-methods {
  margin-bottom: 20px;
}

.payment-radio-group {
  display: flex;
  gap: 10px;
  width: 100%;
}

.payment-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 5px;
}

.vnpay-banks {
  margin-top: 20px;
  margin-bottom: 20px;
}

.bank-list {
  margin-top: 10px;
}

.bank-radio-group {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.payment-actions {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

.countdown {
  margin-bottom: 20px;
  font-weight: bold;
  color: #104c8a;
}

.countdown.expired {
  color: red;
}

.timeout-dialog :deep(.el-dialog) {
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border-top: 4px solid #ff6200;
  background: #ffffff;
  overflow: hidden;
}

.timeout-dialog :deep(.el-dialog__header) {
  padding: 15px;
  margin: 0;
  border-bottom: 1px solid #e8e8e8;
}

.timeout-dialog :deep(.el-dialog__title) {
  color: #333;
  font-size: 18px;
  font-weight: 700;
}

.timeout-dialog :deep(.el-dialog__body) {
  padding: 20px;
  text-align: center;
}

.dialog-content p {
  margin: 0;
  font-size: 14px;
  color: #666;
  line-height: 1.5;
}

.dialog-footer {
  display: flex;
  justify-content: center;
  padding: 15px;
}

.book-again-button {
  background: #ff6200;
  border: none;
  color: #ffffff;
  border-radius: 4px;
  padding: 10px 30px;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.book-again-button:hover {
  background: #ff8533;
}

.book-again-button:active {
  background: #e65c00;
}
</style>