<template>
  <el-dialog
    :model-value="visible"
    @close="closeDialog"
    @update:visible="updateVisible"
    @opened="onDialogOpened"
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
            />
          </div>
        </div>
      </div>
    </div>
    <Seat
        v-if="currentCarType === 'Toa ghế ngồi'"
        :car="carOptions[selectedCar]"
        :seats="seatData.cho || []"
        :totalTickets="totalTickets"
        :ticketDetails="ticketDetails"
      />
    <Bed
      v-else-if="currentCarType === 'Toa giường nằm'"
      :car="carOptions[selectedCar]"
      :beds="seatData.cho || []"
      :totalTickets="totalTickets"
      :ticketDetails="ticketDetails"
    />
  </el-dialog>
</template>

<script setup>
import { ref, onMounted, defineProps, onBeforeMount } from "vue";
import axios from "axios";
import Bed from "@/components/Bed.vue";
import Seat from "@/components/Seat.vue";

const carOptions = ref([]);
const selectedCar = ref(0);
const carSelectionRef = ref(null);
const currentCarType = ref(""); // Loại toa hiện tại (ghế ngồi hoặc giường nằm)
const seatData = ref(null);

const props = defineProps({
  visible: Boolean,
  trainCode: String,
  traintau: String,
  searchgadi: String,
  searchgaden: String,
  selectedDay: String,
  totalTickets: Number,
  ticketDetails: Array,
});

const emits = defineEmits(["update:visible"]); // Định nghĩa sự kiện

const closeDialog = () => {
  emits("update:visible", false); // Phát sự kiện để đóng dialog
};

const updateVisible = (value) => {
  emits("update:visible", value); // Phát sự kiện khi dialog thay đổi trạng thái
};

const initializeDialog = () => {
  if (carOptions.value.length > 0) {
    selectCar(selectedCar.value);
  }
};

// Hàm gọi API để lấy dữ liệu toa tàu
const fetchTrainCars = async () => {
  try {
    const response = await axios.get(`/chotoa?trainCode=${props.trainCode}`);
    const result = response.data;
    console.log(`Request URL: /toa?trainCode=${props.trainCode}`);

    if (result.success && Array.isArray(result.data)) {
      carOptions.value = result.data.map((car) => ({
        id: car.matoa,
        tentau: car.tentau,
        type: `${car.tentoa}: ${car.tenloaitoa}`,
        seatInfo: `Còn ${car.sochocon} chỗ trống`,
        tenloaitoa: car.tenloaitoa, // Thêm trường loại toa
        cho: car.cho || [], // Thêm trường danh sách ghế
      }));
      console.log(carOptions.value);

      // Sắp xếp danh sách toa tàu theo `matoa`
      carOptions.value.sort((a, b) => b.id - a.id);

      // Đặt toa 1 làm mặc định
      const toa1Index = carOptions.value.findIndex((car) =>
        car.type.includes("Toa 1:")
      );
      if (toa1Index !== -1) {
        selectedCar.value = toa1Index;
        setTimeout(() => scrollToSelectedCar(), 100);
      }
    } else {
      console.error("API trả về dữ liệu không hợp lệ");
    }
  } catch (error) {
    console.error("Lỗi khi gọi API:", error);
  }
};

const selectCar = (index) => {
  selectedCar.value = index; // Cập nhật chỉ số toa được chọn
  currentCarType.value = carOptions.value[index].tenloaitoa; // Lấy loại toa từ danh sách
  scrollToSelectedCar(index);
  seatData.value = carOptions.value[index];
};

const scrollToSelectedCar = (index = null) => {
  const container = carSelectionRef.value;
  if (container) {
    if (index !== null) {
      // Cuộn đến mục cụ thể
      const carElement = document.querySelectorAll(".car-option")[index];
      if (carElement) {
        const scrollLeft =
          carElement.offsetLeft +
          carElement.offsetWidth -
          container.offsetWidth;
        container.scrollTo({ left: scrollLeft, behavior: "smooth" });
      }
    } else {
      // Cuộn toàn bộ nội dung sang bên phải
      container.scrollTo({ left: container.scrollWidth, behavior: "smooth" });
    }
  }
};
const onDialogOpened = () => {
  initializeDialog(); // Gọi hàm khởi tạo
  scrollToSelectedCar(); // Cuộn tới toa được chọn
};


onMounted(() => {
  fetchTrainCars();
});
</script>

<style scoped>
@import url(@/assets/css/boking.css);

</style>