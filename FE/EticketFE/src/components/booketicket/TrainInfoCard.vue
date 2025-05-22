<template>
  <div v-if="searchPerformed" class="container">
    <div class="container2">
      <div class="container3">
        <el-row :gutter="20" style="align-items: center">
          <!-- Phần thông tin tàu -->
          <el-col :span="6">
            <p style="font-size: 20px;">{{ $t(traintau) }}</p>
            <h3>{{ trainCode }}</h3>
            <div class="cho">
              <p class="cho1">{{ $t('Còn') }} {{ availableSeats }} {{ $t('chỗ') }}</p>
            </div>
          </el-col>

          <!-- Phần thông tin ga đi -->
          <el-col :span="4" class="text-center">
            <h3 class="ga">{{ searchgadi }}</h3>
            <p class="time">{{ departureTime }}</p>
            <p class="date">{{ selectedDay }}</p>
          </el-col>

          <!-- Mũi tên giữa ga đi và ga đến -->
          <el-col :span="4" class="text-center1">
            <h3>{{ duration }}</h3>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 100 20"
              width="120"
              height="20"
            >
              <line x1="0" y1="10" x2="90" y2="10" stroke="black" stroke-width="2" />
              <polygon points="90,5 100,10 90,15" fill="black" />
            </svg>
          </el-col>

          <!-- Phần thông tin ga đến -->
          <el-col :span="4" class="text-center">
            <h3 class="ga">{{ searchgaden }}</h3>
            <p class="time">{{ arrivalTime }}</p>
            <p class="date">{{ arrivalDate }}</p>
          </el-col>

          <!-- Phần giá vé -->
          <el-col :span="6">
            <div class="price">
              <el-button class="hover-button" @click="openDialog">
                <h4>{{ $t('Đặt') }}</h4>
              </el-button>
              <h2 class="ticket-price">{{ ticketPrice }}</h2>
            </div>
          </el-col>
        </el-row>
      </div>
    </div>
    <Boking
      v-model:visible="dialogVisible"
      @submit="handleSubmit"
      :trainCode="trainCode"
      :traintau="traintau"
      :searchgadi="searchgadi"
      :searchgaden="searchgaden"
      :selectedDay="selectedDay"
      :arrivalDate="arrivalDate"
      :totalTickets="totalTickets"
      :ticketDetails="ticketDetails"
      :departureTime="departureTime"
      :arrivalTime="arrivalTime"
      :malichtrinh="malichtrinh"
      :isReturnTrip="isReturnTrip"
      @proceed-to-return="proceedToReturn"
    />
  </div>
</template>

<script setup>
import { ref } from "vue";
import Boking from "./Boking.vue";

const dialogVisible = ref(false);

const props = defineProps({
  searchPerformed: Boolean,
  searchgadi: String,
  searchgaden: String,
  selectedDay: String,
  traintau: String,
  trainCode: String,
  availableSeats: Number,
  departureTime: String,
  duration: String,
  arrivalTime: String,
  arrivalDate: String,
  ticketPrice: String,
  totalTickets: Number,
  ticketDetails: Array,
  malichtrinh: String,
  isReturnTrip: Boolean, // Thêm prop để biết đây là vé khứ hồi
});

const emit = defineEmits(["select-train", "proceed-to-return"]);

const openDialog = () => {
  dialogVisible.value = true;
};

const handleSubmit = (formData) => {
  console.log("Form Data Submitted:", formData);
  // Phát sự kiện để báo cho parent biết tàu đã được chọn
  emit("select-train", { ...props, ...formData });
};

const proceedToReturn = () => {
  emit("proceed-to-return"); // Phát sự kiện để chuyển sang lịch trình ngày về
};
</script>

<style scoped>
@import url(@/assets/css/traininfocard.css);
</style>