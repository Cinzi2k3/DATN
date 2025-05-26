<template>
  <div v-if="loading" class="loading-overlay">
    <div class="spinner"></div>
  </div>
  <div style="justify-items: center">
    <div style="width: 1170px">
      <div style="background-color: #e5eaf3">
        <el-row :gutter="20">
          <div style="
              width: 380px;
              margin: 15px 15px 15px 25px;
              background-color: #fff;
            ">
            <el-col :span="24" style="display: flex; align-items: center; margin-top: 10px">
              <div style="flex: 1; margin-right: 10px">
                <label style="opacity: 0.5; margin-left: 10px">{{ $t('Ga đi') }}:</label>
                <el-select v-model="gadi" :placeholder="$t('Chọn ga đi')" style="width: 100%">
                  <el-option v-for="station in stations" :key="station.maga" :label="station.tenga"
                    :value="station.tenga" />
                </el-select>
              </div>
              <el-icon style="font-size: 25px; cursor: pointer; margin: 0 10px" @click="swapStations">
                <Refresh />
              </el-icon>
              <div style="flex: 1">
                <label style="opacity: 0.5; margin-left: 10px">{{ $t('Ga đến') }}:</label>
                <el-select v-model="gaden" :placeholder="$t('Chọn ga đến')" style="width: 100%">
                  <el-option v-for="station in stations" :key="station.maga" :label="station.tenga"
                    :value="station.tenga" />
                </el-select>
              </div>
            </el-col>
          </div>

          <div style="
              width: 505px;
              background-color: #fff;
              margin: 15px 15px 15px 0px;
            ">
            <el-row>
              <el-col :span="12" style="padding: 10px">
                <div style="display: flex; align-items: center">
                  <el-icon style="font-size: 25px; margin-right: 10px">
                    <Calendar />
                  </el-icon>
                  <label style="opacity: 0.5; margin-right: 10px">{{ $t('Ngày đi') }}:</label>
                  <el-radio v-model="selectedTicket" label="one-way">{{ $t('Một chiều') }}</el-radio>
                </div>
                <el-date-picker v-model="departureDate" type="date" @change="onDepartureDateChange"
                  :placeholder="$t('Chọn ngày đi')" style="width: 100%" />
              </el-col>

              <el-col :span="12" style="padding: 10px">
                <div style="display: flex; align-items: center">
                  <el-icon style="font-size: 25px; margin-right: 10px">
                    <Calendar />
                  </el-icon>
                  <label style="opacity: 0.5; margin-right: 10px">{{ $t('Ngày về') }}:</label>
                  <el-radio v-model="selectedTicket" label="round-trip">{{ $t('Khứ hồi') }}</el-radio>
                </div>
                <el-date-picker v-model="returnDate" type="date" :placeholder="$t('Chọn ngày về')" style="width: 100%"
                  :disabled="selectedTicket !== 'round-trip'" @change="onReturnDateChange" />
              </el-col>
            </el-row>
          </div>
          <div style="
              background-color: white;
              width: 150px;
              border-radius: 5px;
              margin-top: 15px;
              height: 85px;
            ">
            <div class="position-relative">
              <button @click="toggleDropdown" class="d-flex align-items-center justify-content-between w-100"
                style="height: 85px; border: 0px; border-radius: 5px">
                <div class="d-flex align-items-center">
                  <i class="fa-solid fa-user me-2"></i>
                  <div class="d-flex flex-column align-items-start">
                    <span>{{ $t('Số lượng vé') }}</span>
                    <span>{{ tempTotalTickets }} {{ $t('vé') }}</span>
                  </div>
                </div>
              </button>

              <div v-if="isOpen" class="dropdown-menu show w-100 mt-2 p-0 border rounded shadow-sm">
                <div v-for="category in ticketCategories" :key="category.label"
                  class="d-flex align-items-center justify-content-between p-3 border-bottom">
                  <div>
                    <p class="mb-0 font-weight-bold">{{ $t(category.label) }}</p>
                    <p class="mb-0 text-muted small">
                      {{ $t(category.description) }}
                    </p>
                    <p v-if="category.discount" class="mb-0 text-warning small">
                      -{{ category.discount }}%
                    </p>
                  </div>
                  <div class="d-flex align-items-center">
                    <button @click="decrement(category.label)" class="btn btn-sm btn-outline-secondary"
                      :disabled="category.count <= 0">
                      -
                    </button>
                    <span class="mx-2">{{ category.count }}</span>
                    <button @click="increment(category.label)" class="btn btn-sm btn-outline-secondary">
                      +
                    </button>
                  </div>
                </div>

                <p class="text-muted small p-3">
                  {{ $t('Vé trẻ em dưới 10 tuổi được giảm 25% giá vé') }}
                </p>
              </div>
            </div>
          </div>

          <button style="
              width: 80px;
              cursor: pointer;
              background-color: #409eff;
              border: 0px;
              margin: 15px 10px 15px 0px;
            " @click="searchTickets" :disabled="loading">
            <h3 style="color: white; font-size: 20px">{{ $t('Tìm') }}</h3>
          </button>
        </el-row>
      </div>
      <div v-if="searchPerformed">
        <!-- Vé một chiều -->
        <div v-if="selectedTicket === 'one-way'">
          <SearchTicketResult :searchgadi="searchgadi" :searchgaden="searchgaden" :searchPerformed="searchPerformed"
            :displayedDays="displayedDays" :selectedDay="selectedDay" :availableDays="availableDays"
            :currentPage="currentPage" @update:selectedDay="onDaySelected" @prevday="prevday" @nextday="nextday" />
          <div v-if="trainSchedules.length > 0">
            <TrainInfoCard v-for="(train, index) in trainSchedules" :key="index" :searchPerformed="searchPerformed"
              :searchgadi="searchgadi" :searchgaden="searchgaden" :selectedDay="train.ngaydi"
              :traintau="train.tenloaitau" :trainCode="train.tentau" :availableSeats="train.sochocon"
              :departureTime="train.giodi" :duration="train.thoigiandichuyen" :arrivalTime="train.gioden"
              :arrivalDate="train.ngayden" :ticketPrice="`${train.gia.toLocaleString()} VND`"
              :totalTickets="totalTickets" :ticketDetails="ticketDetails" :malichtrinh="train.malichtrinh"
              :isReturnTrip="false" @select-train="handleTrainSelection" />
          </div>
        </div>

        <!-- Vé khứ hồi: Ngày đi -->
        <div v-if="selectedTicket === 'round-trip' && bookingStep === 'departure'">
          <div class="text-put">{{ $t('Chọn vé cho chiều đi') }}</div>
          <SearchTicketResult :searchgadi="searchgadi" :searchgaden="searchgaden" :searchPerformed="searchPerformed"
            :displayedDays="displayedDays" :selectedDay="selectedDay" :availableDays="availableDays"
            :currentPage="currentPage" @update:selectedDay="onDaySelected" @prevday="prevday" @nextday="nextday" />
          <div v-if="trainSchedules.length > 0">
            <TrainInfoCard v-for="(train, index) in trainSchedules" :key="index" :searchPerformed="searchPerformed"
              :searchgadi="searchgadi" :searchgaden="searchgaden" :selectedDay="train.ngaydi"
              :traintau="train.tenloaitau" :trainCode="train.tentau" :availableSeats="train.sochocon"
              :departureTime="train.giodi" :duration="train.thoigiandichuyen" :arrivalTime="train.gioden"
              :arrivalDate="train.ngayden" :ticketPrice="`${train.gia.toLocaleString()} VND`"
              :totalTickets="totalTickets" :ticketDetails="ticketDetails" :malichtrinh="train.malichtrinh"
              :isReturnTrip="true" @select-train="selectDepartureTrain" @proceed-to-return="proceedToReturn" />
          </div>
        </div>

        <!-- Vé khứ hồi: Ngày về -->
        <div v-if="selectedTicket === 'round-trip' && bookingStep === 'return'">
          <div class="text-put">{{ $t('Chọn vé cho chiều về') }}</div>
          <SearchTicketResult :searchgadi="searchgadi" :searchgaden="searchgaden" :searchPerformed="searchPerformed"
            :displayedDays="displayedDays" :selectedDay="selectedDay" :availableDays="availableDays"
            :currentPage="currentPage" @update:selectedDay="newDay => returnSelected(newDay, searchReturnTicketsByDate)"
            @prevday="prevday" @nextday="nextday" />
          <div v-if="trainSchedules.length > 0">
            <TrainInfoCard v-for="(train, index) in trainSchedules" :key="index" :searchPerformed="searchPerformed"
              :searchgadi="searchgadi" :searchgaden="searchgaden" :selectedDay="train.ngaydi"
              :traintau="train.tenloaitau" :trainCode="train.tentau" :availableSeats="train.sochocon"
              :departureTime="train.giodi" :duration="train.thoigiandichuyen" :arrivalTime="train.gioden"
              :arrivalDate="train.ngayden" :ticketPrice="`${train.gia.toLocaleString()} VND`"
              :totalTickets="totalTickets" :ticketDetails="ticketDetails" :malichtrinh="train.malichtrinh"
              :isReturnTrip="true" @select-train="selectReturnTrain" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, watch, ref } from "vue";
import SearchTicketResult from "./SearchTicketResult.vue";
import TrainInfoCard from "./TrainInfoCard.vue";
import {Refresh, Calendar, ArrowLeft, ArrowRight,} from "@element-plus/icons-vue";
import { useTicketManagement } from '@/composables/useTicketManagement';
import { useDateManagement } from '@/composables/useDateManagement';
import { useSearchTickets } from '@/composables/useSearchTickets';
import { useSwapStations } from "@/composables/useSwapStations";
import { useDown } from "@/composables/useDown";
import { useFetchStations } from "@/composables/useFetchStations";
import { useRouter } from "vue-router";
import { ElNotification } from "element-plus";

const { stations, fetchStations } = useFetchStations();
const router = useRouter();
const departureBookingData = ref(null); // Lưu dữ liệu đặt vé ngày đi

// Sử dụng composable để quản lý vé
const {
  loading,
  gadi,
  gaden,
  departureDate,
  returnDate,
  totalTickets,
  tempTotalTickets,
  searchgadi,
  searchgaden,
  selectedDay,
  trainSchedules,
  searchPerformed,
  selectedTicket,
  ticketCategories,
  ticketDetails,
  syncTicketCounts,
  updateTicketDataInStore,
  increment,
  decrement,
  loadTicketCategories,
} = useTicketManagement();


const { swapStations } = useSwapStations(gadi, gaden);
const { isOpen, toggleDropdown } = useDown();

// Sử dụng composable để quản lý ngày
const {
  availableDays,
  currentPage,
  formatDateToYMD,
  generateAvailableDays,
  onDepartureDateChange,
  onReturnDateChange,
  onDaySelected,
  returnSelected,
  nextday,
  prevday,
  displayedDays
} = useDateManagement(departureDate, returnDate, () => searchTickets());

// Sử dụng composable để quản lý tìm kiếm vé
const {
  searchTickets,
  bookingStep,
  departureTrainSelected,
  selectDepartureTrain: selectDepartureTrainBase,
  selectReturnTrain: selectReturnTrainBase,
  searchReturnTicketsByDate,
} = useSearchTickets(
  loading,
  gadi,
  gaden,
  departureDate,
  returnDate,
  selectedTicket,
  searchgadi,
  searchgaden,
  generateAvailableDays,
  availableDays,
  selectedDay,
  searchPerformed,
  trainSchedules,
  formatDateToYMD,
  updateTicketDataInStore,
);

const selectDepartureTrain = (trainData) => {
  departureBookingData.value = trainData; // Lưu dữ liệu đặt vé ngày đi
  selectDepartureTrainBase(trainData); // Gọi hàm từ useSearchTickets
};

const proceedToReturn = () => {
  // Đã xử lý trong selectDepartureTrain từ useSearchTickets
};

const selectReturnTrain = (trainData) => {
  const returnBookingData = trainData;
  const combinedBookingData = {
    departure: departureBookingData.value,
    return: returnBookingData,
  };
  console.log("Combined Booking Data:", combinedBookingData);
  ElNotification.success('Đặt vé thành công ! Vui lòng thanh toán để hoàn tất !');

  // Chuyển hướng sang trang Pay với dữ liệu cả hai chuyến
  router.push({
    name: "Payment",
    query: { data: JSON.stringify(combinedBookingData) },
  });
};

const initializeSearchIfNeeded = () => {
  if (gadi.value && gaden.value && formatDateToYMD(departureDate.value)) {
    searchTickets();
  }
};

const resetSearchState = () => {
  searchPerformed.value = false; // Reset để không hiển thị kết quả cũ
  trainSchedules.value = []; // Xóa danh sách tàu cũ
};

onMounted(() => {
  fetchStations();
  loadTicketCategories();
  initializeSearchIfNeeded();
});

watch(selectedTicket, resetSearchState);

</script>

<style scoped>
@import url(@/assets/css/eticket.css);
</style>