```vue
<template>
  <el-card class="vnpay-card">
    <template #header>
      <div class="card-header">
        <h2>{{ $t('Phương thức thanh toán') }}</h2>
      </div>
    </template>
    
    <div class="payment-methods">
      <el-radio-group v-model="paymentMethod" class="payment-radio-group">
        <el-radio label="vnpay" border>
          <div class="payment-option">
            <span>{{ $t('Thanh toán qua VNPay') }}</span>
          </div>
        </el-radio>
      </el-radio-group>
    </div>
    
    <div class="vnpay-banks">
      <h3>{{ $t('Chọn ngân hàng thanh toán (tùy chọn)') }}</h3>
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
      <p>{{ $t('Thời gian còn lại để thanh toán') }}: {{ formatTime(timeLeft) }}</p>
    </div>
    <div class="countdown expired" v-else>
      <p>{{ $t('Thời gian giữ chỗ đã hết hạn. Vui lòng chọn lại chỗ') }}.</p>
    </div>
    
    <div class="payment-actions">
      <el-button @click="gotoback">
        <el-icon><ArrowLeft /></el-icon> 
        {{ $t('Quay lại') }}
      </el-button>
      <el-button 
        type="primary" 
        :loading="loading" 
        :disabled="timeLeft <= 0" 
        @click="processPayment"
      >
        {{ $t('Thanh toán') }} {{ formatTotalPrice(totalPrice) }}
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
import { useAuthStore } from '@/stores/authStore';
import { ElNotification } from 'element-plus';

const authStore = useAuthStore();
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
  const seatsToRelease = [];

  // Hàm helper để thu thập ghế từ dữ liệu chuyến
  const collectSeats = (tripData, tripType) => {
    if (!tripData || !tripData.selectedSeatsByCar || !tripData.malichtrinh) {
      console.warn(`Không có dữ liệu ghế cho chuyến ${tripType}`);
      return;
    }

    Object.entries(tripData.selectedSeatsByCar).forEach(([carName, seats]) => {
      // Tìm matoa từ seatMatoaMapping hoặc carOptions
      const matoa = tripData.seatMatoaMapping?.[carName] ||
        tripData.carOptions?.find((car) => car.type === carName)?.id;
      if (!matoa) {
        console.warn(`Không tìm thấy matoa cho carName: ${carName} trong chuyến ${tripType}`);
        return;
      }

      seats.forEach((seat) => {
        if (seat.sohieu) {
          seatsToRelease.push({
            sohieu: seat.sohieu,
            malichtrinh: tripData.malichtrinh,
            matoa: matoa,
          });
        }
      });
    });
  };

  // Thu thập ghế từ vé đi
  collectSeats(props.departureData, 'departure');

  // Thu thập ghế từ vé về (nếu có)
  if (props.returnData) {
    collectSeats(props.returnData, 'return');
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
    const success = navigator.sendBeacon('http://172.20.10.6:8000/api/release-seats', blob);
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
    const response = await axios.post(
      'http://172.20.10.6:8000/api/release-seats',
      { seats: seatsToRelease },
      { withCredentials: true }
    );
    if (response.data.success) {
      console.log('Xóa ghế thành công:', seatsToRelease);
    } else {
      console.error('API trả về lỗi khi xóa ghế:', response.data);
      ElNotification.error('Không thể xóa ghế: ' + (response.data.message || 'Lỗi không xác định'));
    }
  } catch (error) {
    console.error('Lỗi khi xóa ghế:', error.message);
    ElNotification.error('Lỗi khi xóa ghế: ' + error.message);
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
const timeLeft = ref(15 * 60);
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
const bookAgain = async () => {
  showTimeoutDialog.value = false;
  await releaseSeatsWithAxios(); // Xóa ghế trước khi chuyển hướng
  clearCountdown();
  localStorage.removeItem('bookingSession');
  router.push({ name: 'Home' });
};

// Xóa countdown
const clearCountdown = () => {
  if (countdownInterval) {
    clearInterval(countdownInterval);
    countdownInterval = null;
  }
  localStorage.removeItem('countdownTimeLeft');
  localStorage.removeItem('countdownServerTime');
  timeLeft.value = 0;
};

// Khởi tạo hoặc reset bộ đếm ngược
const startCountdown = (duration = 15 * 60) => {
  clearCountdown();
  timeLeft.value = duration;
  localStorage.setItem('countdownTimeLeft', duration.toString());
  localStorage.setItem('countdownServerTime', new Date().toISOString());

  countdownInterval = setInterval(() => {
    if (timeLeft.value > 0) {
      timeLeft.value -= 1;
      localStorage.setItem('countdownTimeLeft', timeLeft.value.toString());
    } else {
      clearCountdown();
      showTimeoutDialog.value = true;
      releaseSeatsWithAxios();
    }
  }, 1000);
};

// Kiểm tra trạng thái đặt vé và khởi tạo đếm ngược
const initializeCountdown = async () => {
  const totalDuration = 15 * 60; // 3 phút

  try {
    const response = await axios.get('http://172.20.10.6:8000/api/check-reservation', {
      params: {
        malichtrinh: props.departureData.malichtrinh,
        matoa: props.departureData.matoa
      },
      withCredentials: true
    });

    const { hasReservation, timeLeft: serverTimeLeft, serverTime } = response.data;

    console.log('Server response:', { hasReservation, serverTimeLeft, serverTime });

    if (hasReservation && serverTimeLeft > 0) {
      startCountdown(serverTimeLeft);
    } else {
      startCountdown(totalDuration);
    }
  } catch (error) {
    console.error('Lỗi khi kiểm tra trạng thái đặt vé:', error);
    startCountdown(totalDuration);
  }
};

// Khởi tạo đếm ngược khi component được mount
onMounted(() => {
  initializeCountdown();
});

// Dọn dẹp interval
onUnmounted(() => {
  clearCountdown();
});

// Gọi clearCountdown trong processPayment
const processPayment = async () => {
  if (paymentMethod.value === 'vnpay') {
    try {
      loading.value = true;
      const bookingData = {
        user_id: authStore.userId,
        departure: {
          ...props.departureData,
          seatMatoaMapping: props.departureData.seatMatoaMapping, // Thêm ánh xạ matoa
        },
        return: props.returnData
          ? {
              ...props.returnData,
              seatMatoaMapping: props.returnData.seatMatoaMapping || {},
            }
          : null,
        contact: props.contactInfo,
        passengers: props.passengerInfo,
        amount: totalPrice.value,
        bankCode: selectedBank.value,
      };
      const vnpayResponse = await axios.post('http://172.20.10.6:8000/api/vnpay/create', bookingData);

      if (vnpayResponse.data && vnpayResponse.data.data) {
        clearCountdown();
        isNavigatingToPayment.value = true;
        window.location.href = vnpayResponse.data.data;
      }
    } catch (error) {
      console.error('Lỗi khi xử lý thanh toán:', error);
      ElNotification.error('Đã xảy ra lỗi khi xử lý thanh toán. Vui lòng thử lại sau.');
    } finally {
      loading.value = false;
    }
  }
};
</script>

<style scoped>
@import url(@/assets/css/vnpaypayment.css);
</style>
```