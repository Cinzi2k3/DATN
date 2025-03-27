<template>
  <div class="seat-selection">
    <h3 class="text-center mb-4">{{ car.type }}</h3>
    <div v-if="!seats" class="text-center">
      <p>Đang tải dữ liệu ghế...</p>
    </div>
    <div v-else-if="seats.length === 0" class="text-center">
      <p>Không có ghế nào trong toa này.</p>
    </div>
    <div v-else class="seat-grid">
      <!-- Tạo 4 hàng cố định -->
      <div v-for="row in rows" :key="`row-${row}`" class="seat-row" :class="{'top-rows': row <= 2, 'bottom-rows': row > 2}">
        <!-- Lặp qua danh sách ghế đã được lọc theo hàng -->
        <div 
          v-for="seat in getSeatsForRow(row)" 
          :key="`seat-${seat.macho}`" 
          class="seat-container"
          @click="toggleSeatSelection(seat.sohieu)"
        >
          <div 
            class="seat" 
            :class="{ 
              'selected': isSeatSelected(seat.sohieu),
              'unavailable': seat.trangthai === 'Đã đặt'
            }"
          >
            <div class="seat-backrest"></div>
            <div class="seat-top-armrest"></div>
            <div class="seat-square">
              <div class="seat-number">{{ seat.sohieu }}</div>
              <div v-if="seat.trangthai === 'Trống'" class="seat-price">{{ seat.gia || '372k' }}</div>
            </div>
            <div class="seat-bottom-armrest"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Hiển thị số ghế đã chọn và nút đặt vé -->
    <div v-if="seats && seats.length > 0" class="mt-4 text-center">
      <p>Số ghế đã chọn: {{ selectedSeats.length }} / {{ totalTickets }}</p>
      <button
        class="btn btn-primary" 
        :disabled="selectedSeats.length === 0 || selectedSeats.length > totalTickets"
        @click="bookTickets"
      >
        Đặt vé
      </button>
    </div>

    <div class="legend mt-4 d-flex justify-content-center">
      <div class="d-flex align-items-center me-4">
        <div class="legend-item available">
          <div class="legend-backrest"></div>
          <div class="legend-top"></div>
          <div class="legend-square"></div>
          <div class="legend-bottom"></div>
        </div>
        <span class="ms-2">Ghế trống</span>
      </div>
      <div class="d-flex align-items-center me-4">
        <div class="legend-item selected">
          <div class="legend-backrest"></div>
          <div class="legend-top"></div>
          <div class="legend-square"></div>
          <div class="legend-bottom"></div>
        </div>
        <span class="ms-2">Ghế đang chọn</span>
      </div>
      <div class="d-flex align-items-center">
        <div class="legend-item unavailable">
          <div class="legend-backrest"></div>
          <div class="legend-top"></div>
          <div class="legend-square"></div>
          <div class="legend-bottom"></div>
        </div>
        <span class="ms-2">Ghế đã đặt</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ElMessage, ElNotification } from 'element-plus';
import { ref, defineProps, computed, defineEmits } from 'vue';

const props = defineProps({
  car: Object,
  seats: Array, // Danh sách ghế từ API
  totalTickets: { // Thêm lại prop totalTickets
    type: Number,
    default: 1,
  },
});

const emit = defineEmits(['book']); // Định nghĩa emit cho sự kiện đặt vé
const selectedSeats = ref([]); // Danh sách ghế được chọn

// Số hàng cố định
const rows = 4;

// Tính số cột động dựa trên số lượng ghế
const columns = computed(() => {
  return Math.ceil(props.seats.length / rows);
});

// Tính số ghế trên mỗi hàng
const seatsPerRow = computed(() => {
  return columns.value;
});

// Lấy danh sách ghế cho từng hàng
const getSeatsForRow = (row) => {
  const startIndex = (row - 1) * seatsPerRow.value;
  const endIndex = startIndex + seatsPerRow.value;
  return props.seats.filter((seat, index) => {
    return index >= startIndex && index < endIndex;
  });
};

// Kiểm tra ghế có được chọn không
const isSeatSelected = (seatNumber) => {
  return selectedSeats.value.includes(seatNumber);
};

// Chọn/bỏ chọn ghế với giới hạn số lượng
const toggleSeatSelection = (seatNumber) => {
    console.log("Selected seats:", selectedSeats.value);
    const seat = props.seats.find(s => s.sohieu === seatNumber);
  if (!seat || seat.trangthai === 'Đã đặt') return;

  const index = selectedSeats.value.indexOf(seatNumber);
  if (index === -1) {
    if (selectedSeats.value.length < props.totalTickets) {
      selectedSeats.value.push(seatNumber);
    } else {
      ElNotification.error(`Bạn chỉ có thể chọn tối đa ${props.totalTickets} ghế!`);
    }
  } else {
    selectedSeats.value.splice(index, 1);
  }
};

// Xử lý đặt vé
const bookTickets = () => {
  if (selectedSeats.value.length > 0) {
    emit('book', selectedSeats.value); // Emit danh sách ghế đã chọn lên cha
    ElNotification.success('Đặt vé thành công!');
  }
};
</script>

<style scoped>
@import url(@/assets/css/seat.css);

</style>