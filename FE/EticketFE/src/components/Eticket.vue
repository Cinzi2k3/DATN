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
                <div v-for="category in ticketCategories" :key="category.id"
                  class="d-flex align-items-center justify-content-between p-3 border-bottom">
                  <div>
                    <p class="mb-0 font-weight-bold">{{ category.label }}</p>
                    <p class="mb-0 text-muted small">
                      {{ category.description }}
                    </p>
                    <p v-if="category.discount" class="mb-0 text-warning small">
                      -{{ category.discount }}%
                    </p>
                  </div>
                  <div class="d-flex align-items-center">
                    <button @click="decrement(category.id)" class="btn btn-sm btn-outline-secondary"
                      :disabled="category.count <= 0">
                      -
                    </button>
                    <span class="mx-2">{{ category.count }}</span>
                    <button @click="increment(category.id)" class="btn btn-sm btn-outline-secondary">
                      +
                    </button>
                  </div>
                </div>

                <p class="text-muted small p-3">
                  {{ $t('Một người lớn được kèm 2 trẻ dưới 6 tuổi miễn vé, ngồi chung chỗ.') }}
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
      <SearchTicketResult :searchgadi="searchgadi" :searchgaden="searchgaden" :searchPerformed="searchPerformed"
        :displayedDays="displayedDays" :selectedDay="selectedDay" :availableDays="availableDays"
        :currentPage="currentPage" @update:selectedDay="onDaySelected" @prevday="prevday" @nextday="nextday" />

      <div v-if="trainSchedules.length > 0">
        <TrainInfoCard v-for="(train, index) in trainSchedules" :key="index" :searchPerformed="searchPerformed"
          :searchgadi="searchgadi" :searchgaden="searchgaden" :selectedDay="train.ngaydi" :traintau="train.tenloaitau"
          :trainCode="train.tentau" :availableSeats="train.sochocon" :departureTime="train.giodi"
          :duration="train.thoigiandichuyen" :arrivalTime="train.gioden" :arrivalDate="train.ngayden"
          :ticketPrice="`${train.gia.toLocaleString()} VND`" 
          :totalTickets="totalTickets"
          :ticketDetails="ticketDetails"
          :malichtrinh="train.malichtrinh" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, watch } from "vue";
import SearchTicketResult from "@/components/SearchTicketResult.vue";
import TrainInfoCard from "@/components/TrainInfoCard.vue";
import {
  Refresh,
  Calendar,
  ArrowLeft,
  ArrowRight,
} from "@element-plus/icons-vue";

import { useTicketManagement } from '@/composables/useTicketManagement';
import { useDateManagement } from '@/composables/useDateManagement';
import { useSearchTickets } from '@/composables/useSearchTickets';
import { useSwapStations } from "@/composables/useSwapStations";
import { useDown } from "@/composables/useDown";
import { useFetchStations } from "@/composables/useFetchStations";
const {stations,fetchStations} = useFetchStations();

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
} = useTicketManagement();


const {swapStations} = useSwapStations(gadi, gaden);
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
  nextday,
  prevday,
  displayedDays
} = useDateManagement(departureDate, returnDate, () => searchTickets());

// Sử dụng composable để quản lý tìm kiếm vé
const { searchTickets } = useSearchTickets(
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
  updateTicketDataInStore
);

onMounted(() => {
  fetchStations();
  if (gadi.value && gaden.value && formatDateToYMD(departureDate.value)) {
    searchTickets();
    syncTicketCounts();
  }
});

watch(totalTickets, ( newValue) => {
  console.log("Total Tickets:",  newValue);
});
</script>



<style scoped>
@import url(@/assets/css/eticket.css);
</style>