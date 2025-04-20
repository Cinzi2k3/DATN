<template>
  <div class="container">
    <h3 class="text-center mb-4">{{ car.type }}</h3>
    <div class="car-container">
      <div v-if="!beds" class="text-center">
        <p>Đang tải dữ liệu giường...</p>
      </div>
      <div v-else-if="beds.length === 0" class="text-center">
        <p>Không có dữ liệu giường để hiển thị.</p>
      </div>
      <div v-else class="car mb-5">
        <div class="compartment-labels row">
          <div class="col-2"></div>
          <div
            v-for="compartment in numCompartments"
            :key="`compartment-${compartment}`"
            class="col compartment-label"
          >
            Khoang {{ compartment }}
          </div>
        </div>
        <div
          v-for="tier in rows"
          :key="`tier-${tier}`"
          class="bed-row row align-items-center"
        >
          <div class="col-2 row-label">Tầng {{ rows + 1 - tier }}</div>
          <div
            v-for="compartment in numCompartments"
            :key="`compartment-${tier}-${compartment}`"
            class="col compartment d-flex align-items-center"
          >
            <div
              v-for="bed in getBedsForCompartment(rows + 1 - tier, compartment)"
              :key="`bed-${bed.sohieu}`"
              class="bed-container"
              @click="toggleSelection(bed.sohieu, beds, selectedBeds)"
            >
              <div
                class="bed"
                :class="{
                  selected: isBedSelected(bed.sohieu),
                  unavailable: bed.trangthai === 'dadat',
                  danggiu: bed.trangthai === 'danggiu',
                }"
              >
                <div class="bed-number">{{ bed.sohieu }}</div>
                <div v-if="bed.trangthai === 'controng'" class="bed-price">
                  {{ formatPrice(bed.gia) }}
                </div>
              </div>
            </div>
            <div v-if="compartment < numCompartments" class="divider"></div>
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
        {{ ticket.type }} {{ ticket.ticketNumber }}
        <span v-if="ticket.sohieu"> ({{ ticket.sohieu }} - {{ ticket.carType }})</span>
        <span v-if="ticket.type === 'Trẻ em'" class="discount-badge"> -25%</span>
      </button>
    </div>

    <div class="d-flex justify-content-between mt-4">
      <div class="legend mt-4 d-flex justify-content-center">
        <div class="d-flex align-items-center me-4">
          <div class="beds available"></div>
          <span class="ms-2">Giường trống</span>
        </div>
        <div class="d-flex align-items-center me-4">
          <div class="beds selected"></div>
          <span class="ms-2">Giường đang chọn</span>
        </div>
        <div class="d-flex align-items-center me-4">
          <div class="beds dang-giu"></div>
          <span class="ms-2">Giường đang giữ</span>
        </div>
        <div class="d-flex align-items-center">
          <div class="beds unavailable"></div>
          <span class="ms-2">Giường đã đặt</span>
        </div>
      </div>
      <div v-if="beds && beds.length > 0" class="mt-4 text-center d-flex">
        <div class="d-flex flex-column align-items-end">
          <span>Đã chọn : <strong class="fs-6">{{ totalSelected }} / {{ totalTickets }} chỗ</strong></span>
          <div v-if="totalPrice === 0" class="textbed">Quý khách vui lòng chọn chỗ trống ở trên tương ứng loại vé</div>
          <span v-else-if="totalPrice > 0" class="fw-bold fs-6">Tổng tiền : <span class="fs-4 text-primary">{{ formatTotalPrice(totalPrice) }}</span></span>
        </div>
        <button
          class="btn btn-primary ms-3"
          :disabled="totalSelected !== totalTickets"
          @click="bookTickets(selectedBeds, totalPrice)"
        >
          Đặt vé
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, watch, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { ElNotification } from "element-plus";
import { useFormatPrice } from "@/composables/useFormatPrice";
import { useTicketSelection } from "@/composables/useTicketSelection";
import { useTotalPrice } from "@/composables/useTotalPrice";

const { formatPrice, formatTotalPrice } = useFormatPrice();
const router = useRouter();

const props = defineProps({
  car: Object,
  beds: Array,
  seatStatus: Array,
  totalTickets: { type: Number, default: 1 },
  selectedBeds: { type: Array, default: () => [] },
  totalSelected: Number,
  ticketDetails: Array,
  selectedSeatsByCar: Object,
  malichtrinh: [String, Number]
});

const emit = defineEmits(["update:selected-beds", "book"]);
const { ticketList, currentTicketIndex, selectTicket, toggleSelection, bookTickets } =
  useTicketSelection(props, emit, {
    itemType: "Giường",
    updateEvent: "update:selected-beds",
  });

const { totalPrice } = useTotalPrice(props);

const rows = 2;
const numCompartments = computed(() => {
  if (!props.beds || props.beds.length === 0) return 0;
  return Math.max(...props.beds.map((bed) => parseInt(bed.khoang) || 0));
});

const getBedsForCompartment = (tang, khoang) => {
  return (props.beds || [])
    .filter(
      (bed) => parseInt(bed.tang) === tang && parseInt(bed.khoang) === khoang
    )
    .sort((a, b) => a.sohieu.localeCompare(b.sohieu));
};

const isBedSelected = (bedNumber) => {
  return props.selectedBeds.some((bed) => bed.sohieu === bedNumber);
};

const updateBedStatus = () => {
  if (props.seatStatus && props.beds) {
    props.beds.forEach((bed) => {
      const newStatus = props.seatStatus.find((s) => s.sohieu === bed.sohieu)?.status || 'controng';
      bed.trangthai = newStatus;
    });
  }
};

watch(
  () => props.seatStatus,
  () => {
    updateBedStatus();
  },
  { deep: true }
);

onMounted(() => {
  updateBedStatus();
});

onUnmounted(() => {
});
</script>

<style scoped>
@import url("@/assets/css/bed.css");

</style>