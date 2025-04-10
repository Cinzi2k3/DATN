<template>
  <el-dialog
    :model-value="visible"
    @close="closeDialog"
    @update:visible="updateVisible"
    @opened="setInitialScrollPosition"
    width="80%"
  >
    <div class="train-booking-header">
      <div class="route-container">
        <div class="route-info">
          <span>{{ searchgadi }} → {{ searchgaden }} | {{ selectedDay }}</span>
        </div>
        <h2 class="train-title">{{ traintau }} {{ trainCode }}</h2>
      </div>
      <div class="car-selection-wrapper">
        <div class="car-selection" ref="carSelectionRef">
          <div class="car-options">
            <div
              v-for="(car, index) in carOptions"
              :key="car.id"
              class="car-option"
              :class="{ active: selectedCar === index }"
              @click="selectCar(index)"
            >
              <div class="car-type">{{ car.type }}</div>
              <div class="seat-info">{{ car.seatInfo }}</div>
            </div>
            <img
              class="image-head"
              src="https://res.ivivu.com/train/images/trainlist/head-train-desktop.svg"
              alt="Train head"
            />
          </div>
        </div>
      </div>
    </div>
    <keep-alive>
      <Seat
        v-if="currentCarType === 'Toa ghế ngồi'"
        :car="carOptions[selectedCar]"
        :seats="seatData?.cho || []"
        :total-tickets="totalTickets"
        :ticket-details="ticketDetails"
        :selected-seats="selectedSeatsByCar[carOptions[selectedCar]?.type] || []"
        :total-selected="totalSelectedSeats"
        @update:selected-seats="updateSelectedSeats(carOptions[selectedCar]?.type, $event)"
        @book="handleBook"
      />
      <Bed
        v-else-if="currentCarType === 'Toa giường nằm'"
        :car="carOptions[selectedCar]"
        :beds="seatData?.cho || []"
        :total-tickets="totalTickets"
        :ticket-details="ticketDetails"
        :selected-beds="selectedSeatsByCar[carOptions[selectedCar]?.type] || []"
        :total-selected="totalSelectedSeats"
        @update:selected-beds="updateSelectedSeats(carOptions[selectedCar]?.type, $event)"
        @book="handleBook"
      />
    </keep-alive>
  </el-dialog>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from "vue";
import axios from "axios";
import Seat from "@/components/Seat.vue";
import Bed from "@/components/Bed.vue";
import { useRouter } from "vue-router";
import { ElNotification } from "element-plus";

// Refs
const carOptions = ref([]);
const selectedCar = ref(0);
const carSelectionRef = ref(null);
const currentCarType = ref("");
const seatData = ref(null);
const selectedSeatsByCar = ref({});
const router = useRouter();

// Props và Emits
const props = defineProps({
  visible: Boolean,
  trainCode: String,
  traintau: String,
  searchgadi: String,
  searchgaden: String,
  selectedDay: String,
  totalTickets: { type: Number, default: 1 },
  ticketDetails: Array,
  departureTime: String,
  arrivalTime: String,
  malichtrinh: String,
  isReturnTrip: Boolean, // Thêm prop để biết đây là vé khứ hồi
});

const emits = defineEmits(["update:visible", "submit", "proceed-to-return"]);

// Computed
const totalSelectedSeats = computed(() =>
  Object.values(selectedSeatsByCar.value).reduce((total, seats) => total + (seats?.length || 0), 0)
);

// Methods
const closeDialog = () => {
  selectedSeatsByCar.value = {};
  emits("update:visible", false);
};

const updateVisible = (value) => emits("update:visible", value);

const fetchTrainCars = async () => {
  try {
    if (!props.malichtrinh) {
      console.error("malichtrinh is not provided!");
      return;
    }
    const { data } = await axios.get(`/chotoa?trainCode=${props.trainCode}&malichtrinh=${props.malichtrinh}`);
    if (data.success && Array.isArray(data.data)) {
      carOptions.value = data.data
        .map((car) => ({
          id: car.matoa,
          tentau: car.tentau,
          type: `${car.tentoa}: ${car.tenloaitoa}`,
          seatInfo: `Còn ${car.sochocon} chỗ trống`,
          tenloaitoa: car.tenloaitoa,
          cho: car.cho || [],
        }))
        .sort((a, b) => b.id - a.id);

      const toa1Index = carOptions.value.findIndex((car) => car.type.includes("Toa 1:"));
      selectedCar.value = toa1Index !== -1 ? toa1Index : 0;
      selectCar(selectedCar.value);
      await nextTick();
      setInitialScrollPosition();
    } else {
      console.error("Invalid API data:", data);
    }
  } catch (error) {
    console.error("API fetch error:", error);
  }
};

const selectCar = (index) => {
  selectedCar.value = index;
  const car = carOptions.value[index];
  if (car) {
    currentCarType.value = car.tenloaitoa;
    seatData.value = car;
    selectedSeatsByCar.value[car.type] = selectedSeatsByCar.value[car.type] || [];
  }
};

const setInitialScrollPosition = () => {
  const container = carSelectionRef.value;
  if (container) {
    const imageHead = container.querySelector(".image-head");
    if (imageHead) {
      container.scrollLeft = imageHead.offsetLeft - container.offsetWidth + imageHead.offsetWidth;
    } else {
      container.scrollLeft = container.scrollWidth;
    }
  }
};

const updateSelectedSeats = (carType, newSelected) => {
  if (carType) selectedSeatsByCar.value[carType] = newSelected;
};

const handleBook = (selectedSeats) => {
  const bookingData = {
    trainCode: props.trainCode,
    traintau: props.traintau,
    searchgadi: props.searchgadi,
    searchgaden: props.searchgaden,
    selectedDay: props.selectedDay,
    ticketDetails: props.ticketDetails,
    selectedSeatsByCar: selectedSeatsByCar.value,
    totalTickets: props.totalTickets,
    departureTime: props.departureTime,
    arrivalTime: props.arrivalTime,
    malichtrinh: props.malichtrinh,
  };

  emits("submit", bookingData); // Phát sự kiện submit để parent xử lý

  if (props.isReturnTrip) {
    // Nếu là vé khứ hồi, chuyển sang bước chọn vé ngày về
    emits("proceed-to-return");
  } else {
    // Nếu là vé một chiều, chuyển hướng sang trang Pay
    router.push({
      name: "Payment",
      query: { data: JSON.stringify(bookingData) },
    });
    ElNotification.success("Đặt vé thành công ! Vui lòng thanh toán để hoàn tất !")
    closeDialog();
  }
};

// Lifecycle
onMounted(() => {
  fetchTrainCars();
});

watch(
  () => props.malichtrinh,
  (newMalichtrinh, oldMalichtrinh) => {
    if (newMalichtrinh !== oldMalichtrinh) {
      fetchTrainCars();
    }
  }
);
</script>

<style scoped>
@import url("@/assets/css/boking.css");
</style>