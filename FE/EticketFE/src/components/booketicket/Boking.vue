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
        <h2 class="train-title">{{ $t(traintau) }} {{ trainCode }}</h2>
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
              <div class="car-type">{{ $t(car.type) }}</div>
              <div class="seat-info">{{ $t('Còn') }} {{ $t(car.seatInfo) }} {{ $t('chỗ trống') }}</div>
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
        :seat-status="seatStatus"
        :total-tickets="totalTickets"
        :ticket-details="ticketDetails"
        :selected-seats="selectedSeatsByCar[carOptions[selectedCar]?.type] || []"
        :total-selected="totalSelectedSeats"
        :selected-seats-by-car="selectedSeatsByCar"
        @update:selected-seats="updateSelectedSeats(carOptions[selectedCar]?.type, $event)"
        @book="handleBook"
      />
      <Bed
        v-else-if="currentCarType === 'Toa giường nằm'"
        :car="carOptions[selectedCar]"
        :beds="seatData?.cho || []"
        :seat-status="seatStatus"
        :total-tickets="totalTickets"
        :ticket-details="ticketDetails"
        :selected-beds="selectedSeatsByCar[carOptions[selectedCar]?.type] || []"
        :total-selected="totalSelectedSeats"
        :selected-seats-by-car="selectedSeatsByCar"
        @update:selected-beds="updateSelectedSeats(carOptions[selectedCar]?.type, $event)"
        @book="handleBook"
      />
    </keep-alive>
  </el-dialog>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from "vue";
import axios from "axios";
import Seat from "./Seat.vue";
import Bed from "./Bed.vue";
import { useRouter } from "vue-router";
import { ElNotification } from "element-plus";

// Refs
const carOptions = ref([]);
const selectedCar = ref(0);
const carSelectionRef = ref(null);
const currentCarType = ref("");
const seatData = ref(null);
const seatStatus = ref([]); // Lưu trạng thái ghế
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
  arrivalDate: String,
  totalTickets: { type: Number, default: 1 },
  ticketDetails: Array,
  departureTime: String,
  arrivalTime: String,
  malichtrinh: String,
  isReturnTrip: Boolean,
});

const emits = defineEmits(["update:visible", "submit", "proceed-to-return"]);

// Computed
const totalSelectedSeats = computed(() =>
  Object.values(selectedSeatsByCar.value).reduce((total, seats) => total + (seats?.length || 0), 0)
);

const totalPrice = computed(() => {
  let total = 0;
  Object.values(selectedSeatsByCar.value).forEach((seats) => {
    seats.forEach((seat) => {
      if (seat.gia) {
        total += Number(seat.gia);
      }
    });
  });
  return total;
});

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
          seatInfo: car.sochocon,
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

const fetchSeatStatus = async (matoa) => {
  try {
    const { data } = await axios.get(`/seat-status?malichtrinh=${props.malichtrinh}&matoa=${matoa}`);
    if (data.success) {
      seatStatus.value = data.data;
    }
  } catch (error) {
    console.error("Lỗi khi lấy trạng thái ghế:", error);
  }
};

const selectCar = async (index) => {
  selectedCar.value = index;
  const car = carOptions.value[index];
  if (car) {
    currentCarType.value = car.tenloaitoa;
    seatData.value = car;
    selectedSeatsByCar.value[car.type] = selectedSeatsByCar.value[car.type] || [];
    // Lấy trạng thái ghế khi chọn toa
    await fetchSeatStatus(car.id);
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
  if (!carType) return;

  const updatedSeatsByCar = { ...selectedSeatsByCar.value };

  // Kiểm tra trùng lặp ghế
  const seatNumberSet = new Set(newSelected.map((seat) => seat.sohieu));
  if (seatNumberSet.size !== newSelected.length) {
    ElNotification.error("Không thể chọn cùng một ghế cho nhiều vé trong cùng toa!");
    return;
  }

  // Kiểm tra ràng buộc vé trẻ em
  const hasChild = newSelected.some((seat) => seat.ticketType === "Trẻ em");
  const hasAdult = newSelected.some((seat) => seat.ticketType === "Người lớn");
  if (hasChild && !hasAdult) {
    ElNotification.error("Vé trẻ em phải được chọn trong toa có vé người lớn!");
    return;
  }

  updatedSeatsByCar[carType] = newSelected;

  Object.keys(updatedSeatsByCar).forEach((otherCarType) => {
    if (otherCarType !== carType) {
      updatedSeatsByCar[otherCarType] = updatedSeatsByCar[otherCarType].filter(
        (seat) =>
          !newSelected.some(
            (newSeat) =>
              newSeat.ticketType === seat.ticketType &&
              newSeat.ticketNumber === seat.ticketNumber
          )
      );
    }
  });

  selectedSeatsByCar.value = updatedSeatsByCar;
};

const handleBook = async () => {
  // Kiểm tra ràng buộc vé trẻ em
  for (const [carType, selections] of Object.entries(selectedSeatsByCar.value)) {
    const hasChild = selections.some((seat) => seat.ticketType === "Trẻ em");
    const hasAdult = selections.some((seat) => seat.ticketType === "Người lớn");
    if (hasChild && !hasAdult) {
      ElNotification.error("Vé trẻ em phải được chọn trong toa có vé người lớn!");
      return;
    }
  }

  const bookingData = {
    trainCode: props.trainCode,
    traintau: props.traintau,
    searchgadi: props.searchgadi,
    searchgaden: props.searchgaden,
    selectedDay: props.selectedDay,
    arrivalDate: props.arrivalDate,
    ticketDetails: props.ticketDetails,
    selectedSeatsByCar: selectedSeatsByCar.value,
    totalTickets: props.totalTickets,
    departureTime: props.departureTime,
    arrivalTime: props.arrivalTime,
    malichtrinh: props.malichtrinh,
    matoa: carOptions.value[selectedCar.value].id,
    totalPrice: totalPrice.value,
  };

  try {
    const response = await axios.post("/datve", {
      sohieu: selectedSeatsByCar.value[carOptions.value[selectedCar.value].type].map(
        (seat) => seat.sohieu
      ),
      malichtrinh: props.malichtrinh,
      matoa: carOptions.value[selectedCar.value].id,
    });

    if (response.data.success) {
      ElNotification.success("Giữ ghế thành công! Vui lòng thanh toán trong 15 phút.");

      // Tải lại dữ liệu ghế
      await fetchTrainCars();
      await fetchSeatStatus(carOptions.value[selectedCar.value].id);

      // Emit dữ liệu đặt vé
      emits("submit", bookingData);
      if (props.isReturnTrip) {
        emits("proceed-to-return");
      } else {
        router.push({
          name: "Payment",
          query: { data: JSON.stringify(bookingData) },
        });
        closeDialog();
      }
    } else {
      ElNotification.error(response.data.error || "Giữ ghế thất bại!");
    }
  } catch (error) {
    ElNotification.error("Lỗi khi giữ ghế: " + error.message);
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