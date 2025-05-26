<template>
  <div class="seat-selection">
    <h3 class="text-center mb-4">{{ $t(car.type) }}</h3>
    <div v-if="!seats" class="text-center">
      <p>Đang tải dữ liệu ghế...</p>
    </div>
    <div v-else-if="seats.length === 0" class="text-center">
      <p>Không có ghế nào trong toa này.</p>
    </div>
    <div v-else class="seat-grid">
      <div
        v-for="row in rows"
        :key="`row-${row}`"
        class="seat-row"
        :class="{ 'top-rows': row <= 2, 'bottom-rows': row > 2 }"
      >
        <div
          v-for="seat in getSeatsForRow(row)"
          :key="`seat-${seat.sohieu}`"
          class="seat-container"
          @click="toggleSelection(seat.sohieu, seats, selectedSeats)"
        >
          <div
            class="seat"
            :class="{
              selected: isSeatSelected(seat.sohieu),
              unavailable: seat.trangthai === 'dadat',
              danggiu: seat.trangthai === 'danggiu',

            }"
          >
            <div class="seat-backrest"></div>
            <div class="seat-top-armrest"></div>
            <div class="seat-square">
              <div class="seat-number">{{ seat.sohieu }}</div>
              <div v-if="seat.trangthai === 'controng'" class="seat-price">
                {{ formatPrice(seat.gia) }}
              </div>
            </div>
            <div class="seat-bottom-armrest"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="ticket-buttons mt-5 d-flex justify-content-center flex-wrap">
      <button
        v-for="(ticket, index) in ticketList"
        :key="`ticket-${index}`"
        class="btn ticket-btn"
        :class="{ 'btn-primary': currentTicketIndex === index, 'btn-outline-primary': currentTicketIndex !== index }"
        @click="selectTicket(index)"
      >
        {{ $t(ticket.type) }} {{ ticket.ticketNumber }}
        <span v-if="ticket.sohieu"> ({{ ticket.sohieu }} - {{ $t(ticket.carType) }})</span>
        <span
          v-if="ticketDetails.find(detail => detail.type === ticket.type)?.discount > 0"
          class="discount-badge">
            -{{ ticketDetails.find(detail => detail.type === ticket.type).discount }}%
        </span>
      </button>
    </div>

    <div class="d-flex justify-content-between mt-1">
      <div class="legend mt-4 d-flex justify-content-center">
        <div class="d-flex align-items-center me-4">
          <div class="legend-item available">
            <div class="legend-backrest"></div>
            <div class="legend-top"></div>
            <div class="legend-square"></div>
            <div class="legend-bottom"></div>
          </div>
          <span class="ms-2">{{ $t('Ghế trống') }}</span>
        </div>
        <div class="d-flex align-items-center me-4">
          <div class="legend-item selected">
            <div class="legend-backrest"></div>
            <div class="legend-top"></div>
            <div class="legend-square"></div>
            <div class="legend-bottom"></div>
          </div>
          <span class="ms-2">{{ $t('Ghế đang chọn') }}</span>
        </div>
        <div class="d-flex align-items-center me-4">
          <div class="legend-item dang-giu">
            <div class="legend-backrest"></div>
            <div class="legend-top"></div>
            <div class="legend-square"></div>
            <div class="legend-bottom"></div>
          </div>
          <span class="ms-2">{{ $t('Ghế đang giữ') }}</span>
        </div>
        <div class="d-flex align-items-center">
          <div class="legend-item unavailable">
            <div class="legend-backrest"></div>
            <div class="legend-top"></div>
            <div class="legend-square"></div>
            <div class="legend-bottom"></div>
          </div>
          <span class="ms-2">{{ $t('Ghế đã đặt') }}</span>
        </div>
      </div>

      <div v-if="seats && seats.length > 0" class="mt-4 text-center d-flex">
        <div class="d-flex flex-column align-items-end">
          <span>{{ $t('Đã chọn') }} : <strong class="fs-6">{{ totalSelected }} / {{ totalTickets }}</strong></span>
          <div v-if="totalPrice === 0" class="textseat">{{ $t('Quý khách vui lòng chọn chỗ trống ở trên tương ứng loại vé') }}</div>
          <span v-else-if="totalPrice > 0" class="fw-bold fs-6">{{ $t('Tổng tiền') }} : <span class="fs-4 text-primary">{{ formatTotalPrice(totalPrice) }}</span></span>
        </div>
        <button
          class="btn btn-primary ms-3"
          :disabled="totalSelected !== totalTickets"
          @click="bookTickets(selectedSeats, totalPrice)"
        >
          {{ $t('Đặt vé') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import { ElMessage } from "element-plus";
import { useFormatPrice } from "@/composables/useFormatPrice";
import { useTicketSelection } from "@/composables/useTicketSelection";
import { useTotalPrice } from "@/composables/useTotalPrice";

const { formatPrice, formatTotalPrice } = useFormatPrice();
const router = useRouter();

const props = defineProps({
  car: Object,
  seats: Array,
  seatStatus: Array,
  totalTickets: { type: Number, default: 1 },
  selectedSeats: { type: Array, default: () => [] },
  totalSelected: Number,
  ticketDetails: Array,
  selectedSeatsByCar: Object,
  malichtrinh: [String, Number] // Thêm malichtrinh
});

const emit = defineEmits(["update:selected-seats", "book"]);
const { ticketList, currentTicketIndex, selectTicket, toggleSelection, bookTickets } =
  useTicketSelection(props, emit, {
    itemType: "Ghế",
    updateEvent: "update:selected-seats",
  });

const { totalPrice } = useTotalPrice(props);

const rows = 4;
const columns = computed(() => Math.ceil((props.seats?.length || 0) / rows));
const seatsPerRow = computed(() => columns.value);

const getSeatsForRow = (row) => {
  const startIndex = (row - 1) * seatsPerRow.value;
  const endIndex = startIndex + seatsPerRow.value;
  return (props.seats || []).filter(
    (_, index) => index >= startIndex && index < endIndex
  );
};

const isSeatSelected = (seatNumber) => {
  return props.selectedSeats.some((seat) => seat.sohieu === seatNumber);
};

const updateSeatStatus = () => {
  if (props.seatStatus && props.seats) {
    props.seats.forEach((seat) => {
      const status = props.seatStatus.find((s) => s.sohieu === seat.sohieu)?.status || 'controng';
      seat.trangthai = status;
    });
  }
};

watch(
  () => props.seatStatus,
  () => {
    updateSeatStatus();
  },
  { deep: true }
);

onMounted(() => {
  updateSeatStatus();
});
</script>

<style scoped>
@import url("@/assets/css/seat.css");
</style>