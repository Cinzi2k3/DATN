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
      <div v-for="row in rows" :key="`row-${row}`" class="seat-row" :class="{'top-rows': row <= 2, 'bottom-rows': row > 2}">
        <div 
          v-for="seat in getSeatsForRow(row)" 
          :key="`seat-${seat.sohieu}`" 
          class="seat-container"
          @click="toggleSeatSelection(seat.sohieu)"
        >
          <div 
            class="seat" 
            :class="{ 
              'selected': isSeatSelected(seat.sohieu),
              'unavailable': seat.trangthai === 'dadat'
            }"
          >
            <div class="seat-backrest"></div>
            <div class="seat-top-armrest"></div>
            <div class="seat-square">
              <div class="seat-number">{{ seat.sohieu }}</div>
              <div v-if="seat.trangthai === 'controng'" class="seat-price">{{ seat.gia || '372k' }}</div>
            </div>
            <div class="seat-bottom-armrest"></div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="seats && seats.length > 0" class="mt-4 text-center">
      <p>Số ghế đã chọn: {{ totalSelected }} / {{ totalTickets }}</p>
      <button
        class="btn btn-primary" 
        :disabled="totalSelected === 0 || totalSelected > totalTickets"
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
import { ElNotification } from 'element-plus';
import { defineProps, computed, defineEmits } from 'vue';

const props = defineProps({
  car: Object,
  seats: Array,
  totalTickets: { type: Number, default: 1 },
  selectedSeats: { type: Array, default: () => [] },
  totalSelected: Number,
  ticketDetails: Array,
});

const emit = defineEmits(['update:selected-seats', 'book']);

const rows = 4;
const columns = computed(() => Math.ceil((props.seats?.length || 0) / rows));
const seatsPerRow = computed(() => columns.value);

const getSeatsForRow = (row) => {
  const startIndex = (row - 1) * seatsPerRow.value;
  const endIndex = startIndex + seatsPerRow.value;
  return (props.seats || []).filter((_, index) => index >= startIndex && index < endIndex);
};

const isSeatSelected = (seatNumber) => {
  return props.selectedSeats.includes(seatNumber);
};

const toggleSeatSelection = (seatNumber) => {
  const seat = props.seats?.find(s => s.sohieu === seatNumber);
  if (!seat || seat.trangthai === 'dadat') return;

  const newSelectedSeats = [...props.selectedSeats];
  const index = newSelectedSeats.indexOf(seatNumber);

  if (index === -1) {
    if (props.totalSelected < props.totalTickets) {
      newSelectedSeats.push(seatNumber);
    } else {
      ElNotification.error(`Bạn chỉ có thể chọn tối đa ${props.totalTickets} ghế trên toàn tàu!`);
      return;
    }
  } else {
    newSelectedSeats.splice(index, 1);
  }

  emit('update:selected-seats', newSelectedSeats);
};

const bookTickets = () => {
  if (props.selectedSeats.length > 0) {
    emit('book', { carType: props.car.type, seats: props.selectedSeats }); // Truyền tên toa và danh sách ghế
    ElNotification.success('Đặt vé thành công!');
  }
};
</script>

<style scoped>
@import url(@/assets/css/seat.css);

</style>