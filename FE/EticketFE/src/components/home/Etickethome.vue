<template>
  <div v-if="loading" class="loading-overlay">
    <div class="spinner"></div>
  </div>
  <div style="color: #fff">
    <h1 style="font-size: 45px">MUA VÉ TÀU TRỰC TUYẾN</h1>
    <h5>Tính năng chọn chỗ thanh toán và in vé điện tử</h5>
  </div>
  <div style="
      background-color: rgba(0, 0, 0, 0.3);
      max-width: 640px;
      border-radius: 5px;
    ">
    <el-row :gutter="20">
      <div style="
          width: 380px;
          margin: 30px 30px 0px 30px;
          height: 80px;
          background-color: #fff;
          border-radius: 5px;
        ">
        <el-col :span="24" style="display: flex; align-items: center; margin-top: 10px">
          <div style="flex: 1; margin-right: 10px">
            <label style="opacity: 0.5; margin-left: 10px">Ga đi:</label>
            <el-select v-model="gadi" placeholder="Chọn ga đi" style="width: 100%">
              <el-option v-for="station in stations" :key="station.maga" :label="station.tenga"
                :value="station.tenga" />
            </el-select>
          </div>
          <el-icon style="font-size: 25px; cursor: pointer; margin: 0 10px" @click="swapStations">
            <Refresh />
          </el-icon>
          <div style="flex: 1">
            <label style="opacity: 0.5; margin-left: 10px">Ga đến:</label>
            <el-select v-model="gaden" placeholder="Chọn ga đến" style="width: 100%">
              <el-option v-for="station in stations" :key="station.maga" :label="station.tenga"
                :value="station.tenga" />
            </el-select>
          </div>
        </el-col>
      </div>
      <div style="
          background-color: white;
          width: 200px;
          border-radius: 5px;
          margin-top: 30px;
        ">
        <div class="position-relative">
          <button @click="toggleDropdown" class="d-flex align-items-center justify-content-between w-100"
            style="height: 80px; border: 0px; border-radius: 5px">
            <div class="d-flex align-items-center">
              <i class="fa-solid fa-user me-2"></i>
              <div class="d-flex flex-column align-items-start">
                <span>Số lượng vé</span>
                <span>{{ tempTotalTickets }} vé</span>
              </div>
            </div>
          </button>

          <div v-if="isOpen" class="dropdown-menu show w-100 mt-2 p-0 border rounded shadow-sm">
            <div v-for="category in ticketCategories" :key="category.id"
              class="d-flex align-items-center justify-content-between p-3 border-bottom">
              <div>
                <p class="mb-0 font-weight-bold">{{ category.label }}</p>
                <p class="mb-0 text-muted small">{{ category.description }}</p>
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
              Một người lớn được kèm 2 trẻ dưới 6 tuổi miễn vé, ngồi chung chỗ.
            </p>
          </div>
        </div>
      </div>

      <div style="
          width: 505px;
          background-color: #fff;
          margin: 30px;
          border-radius: 5px;
        ">
        <el-row>
          <el-col :span="12" style="padding: 10px">
            <div style="display: flex; align-items: center">
              <el-icon style="font-size: 25px; margin-right: 10px">
                <Calendar />
              </el-icon>
              <label style="opacity: 0.5; margin-right: 10px">Ngày đi:</label>
              <el-radio v-model="selectedTicket" label="one-way">Một chiều</el-radio>
            </div>
            <el-date-picker v-model="departureDate" type="date" @change="onDepartureDateChange"
              placeholder="Chọn ngày đi" style="width: 100%" />
          </el-col>

          <el-col :span="12" style="padding: 10px">
            <div style="display: flex; align-items: center">
              <el-icon style="font-size: 25px; margin-right: 10px">
                <Calendar />
              </el-icon>
              <label style="opacity: 0.5; margin-right: 10px">Ngày về:</label>
              <el-radio v-model="selectedTicket" label="round-trip">Khứ hồi</el-radio>
            </div>
            <el-date-picker v-model="returnDate" type="date" placeholder="Chọn ngày về" style="width: 100%"
              :disabled="selectedTicket !== 'round-trip'" @change="onReturnDateChange" />
          </el-col>
        </el-row>
      </div>

      <button style="
          width: 80px;
          height: 84px;
          cursor: pointer;
          background-color: #409eff;
          border: 0px;
          margin: 30px 10px 0px 0px;
          border-radius: 5px;
        " @click="searchTickets" :disabled="loading">
        <h3 style="color: white; font-size: 20px">Tìm</h3>
      </button>
    </el-row>
  </div>
</template>

<script setup>
import { ElMessage, ElNotification } from "element-plus";
import { ref, onMounted } from "vue";
import { Refresh, Calendar } from "@element-plus/icons-vue";
import { useRouter } from "vue-router";
import { useSearchStore } from "@/stores/searchStore.js";
import axios from "axios";
import { useSwapStations } from "@/composables/useSwapStations";
import { useDown } from "@/composables/useDown";
import { useFetchStations } from "@/composables/useFetchStations";
import { useTicketManagement } from "@/composables/useTicketManagement";

const loading = ref(false);
const router = useRouter();
const searchStore = useSearchStore();
const gadi = ref("");
const gaden = ref("");
const departureDate = ref("");  
const returnDate = ref("");
const selectedTicket = ref("one-way");
const {swapStations} = useSwapStations(gadi, gaden)
const { isOpen, toggleDropdown } = useDown();
const {fetchStations, stations} = useFetchStations();
const { ticketCategories, totalTickets,tempTotalTickets, increment, decrement, calculateTempTotal,updateTicketDataInStore } = useTicketManagement();

const onDepartureDateChange = (date) => {
  departureDate.value = date;
  if (returnDate.value && !isValidReturnDate(date, returnDate.value)) {
    ElMessage.error("Ngày đi không được lớn hơn hoặc bằng ngày về.");
    returnDate.value = null;
  }
}; 

const onReturnDateChange = (date) => {
  if (departureDate.value && !isValidReturnDate(departureDate.value, date)) {
    ElMessage.error("Ngày về phải lớn hơn ngày đi.");
    returnDate.value = null;
  } else {
    returnDate.value = date;
  }
};

const isValidReturnDate = (depDate, retDate) => {
  return new Date(retDate) > new Date(depDate);
};

const searchTickets = async () => {
  if (loading.value) return;
  try {
    loading.value = true; 
    await new Promise((resolve) => setTimeout(resolve, 500));

    if (gadi.value && gaden.value && departureDate.value) {
      totalTickets.value = tempTotalTickets.value;
      // Chuẩn bị dữ liệu tìm kiếm
      const ticketDetails = ticketCategories.value.map((category) => ({
        type: category.label,
        count: category.count,
      }));

      const searchData = {
        gadi: gadi.value,
        gaden: gaden.value,
        departureDate: departureDate.value,
        returnDate: returnDate.value || null, 
        selectedTicket: selectedTicket.value,
        totalTickets: totalTickets.value,
        ticketDetails,
      };
      // Lưu dữ liệu vào store
      searchStore.setSearchData(searchData);
      console.log("Dữ liệu tìm kiếm đã lưu:", searchData);

      router.push({ name: "Buyeticket" });
    } else {
      if (!gadi.value) ElMessage.error("Vui lòng chọn ga đi.");
      else if (!gaden.value) ElMessage.error("Vui lòng chọn ga đến.");
      else if (!departureDate.value) ElMessage.error("Vui lòng chọn ngày đi.");
    }
  } catch (error) {
    console.error("Lỗi khi tìm kiếm vé:", error);
    ElMessage.error("Đã xảy ra lỗi khi tìm kiếm vé.");
  } finally {
    loading.value = false;
  }
};

onMounted(fetchStations);

</script>

<style scoped>
@import url(@/assets/css/etickethome.css);
</style>