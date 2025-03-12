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
                    <span>Số lượng vé</span>
                    <span>{{ totalTickets }} vé</span>
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
                  Một người lớn được kèm 2 trẻ dưới 6 tuổi miễn vé, ngồi chung
                  chỗ.
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
            <h3 style="color: white; font-size: 20px">Tìm</h3>
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
          :ticketPrice="`${train.gia.toLocaleString()} VND`" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ElMessage, ElNotification } from "element-plus";
import { ref, computed, onMounted } from "vue";
import SearchTicketResult from "@/components/SearchTicketResult.vue";
import TrainInfoCard from "@/components/TrainInfoCard.vue";
import {
  Refresh,
  Calendar,
  ArrowLeft,
  ArrowRight,
} from "@element-plus/icons-vue";
import { useSearchStore } from "../stores/searchStore";
import axios from "axios";
const loading = ref(false);
const searchStore = useSearchStore();
const isOpen = ref(false);
const gadi = ref(searchStore.gadi);
const gaden = ref(searchStore.gaden);
const departureDate = ref(searchStore.departureDate);
const returnDate = ref(searchStore.returnDate);
const totalTickets = ref(searchStore.totalTickets);
const searchgadi = ref(null);
const searchgaden = ref(null);
const selectedDay = ref(null);
const trainSchedules = ref([]);
const searchPerformed = ref(false);
const selectedTicket = ref(searchStore.selectedTicket);
const availableDays = ref([]);
const currentPage = ref(0);
const stations = ref([]);

const ticketCategories = ref([
  {
    id: "adult",
    label: "Người lớn",
    description: "Từ 11 - 59 tuổi",
    count: 1,
    discount: null,
  },
  {
    id: "child",
    label: "Trẻ em",
    description: "6 - 10 tuổi",
    count: 0,
    discount: 25,
  },
]);
const onDaySelected = async (newSelectedDay) => {
  // Cập nhật ngày đi dựa trên ngày được chọn
  departureDate.value = new Date(newSelectedDay);

  // Cập nhật ngày đã chọn
  selectedDay.value = newSelectedDay;

  // Gọi lại hàm searchTickets để tìm kiếm lại vé
  await searchTickets();
};
const fetchStations = async () => {
  try {
    const response = await axios.get("/ga");
    if (response.data.success) {
      stations.value = response.data.data; // Gán dữ liệu từ API vào stations
    }
  } catch (error) {
    console.error("Có lỗi xảy ra khi tải dữ liệu ga:", error);
  }
};

onMounted(fetchStations);

const ticketDetails = computed(() => searchStore.searchData.ticketDetails);

const syncTicketCounts = () => {
  ticketCategories.value.forEach((category) => {
    const matchingDetail = ticketDetails.value.find(
      (detail) => detail.type === category.label
    );
    if (matchingDetail) {
      category.count = matchingDetail.count;
    }
  });
};

const updateTicketDataInStore = () => {
  const updatedDetails = ticketCategories.value.map((category) => ({
    type: category.label,
    count: category.count,
  }));
  searchStore.setSearchData({
    ...searchStore.searchData,
    ticketDetails: updatedDetails,
  });
  console.log(
    "Updated ticketDetails in Pinia:",
    searchStore.searchData.ticketDetails
  );
};

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const increment = (id) => {
  const category = ticketCategories.value.find((item) => item.id === id);
  // Nếu là "child" và không có vé "adult", ngăn không cho tăng
  if (id === "child") {
    const adultCategory = ticketCategories.value.find(
      (item) => item.id === "adult"
    );
    if (adultCategory.count === 0) {
      ElNotification.error("Trẻ em phải đi cùng với người lớn");
      return;
    }
  }
  if (category) {
    category.count++;
    updateTicketDataInStore();
    calculateTotal();
  }
};

const decrement = (id) => {
  const category = ticketCategories.value.find((item) => item.id === id);
  // Nếu là "adult" và số lượng "child" > 0, không cho phép giảm "adult" về 0
  if (id === "adult") {
    const childCategory = ticketCategories.value.find(
      (item) => item.id === "child"
    );
    if (category.count === 1 && childCategory.count > 0) {
      ElNotification.error("Trẻ em phải đi cùng với người lớn");
      return;
    }
  }
  if (category && category.count > 0) {
    category.count--;
    updateTicketDataInStore();
    calculateTotal();
  }
};

const calculateTotal = () => {
  totalTickets.value = ticketCategories.value.reduce(
    (sum, category) => sum + category.count,
    0
  );
};

const formatDateToYMD = (date) => {
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

const generateAvailableDays = (centerDate) => {
  const days = [];
  const weekdays = [
    "Chủ nhật",
    "Thứ hai",
    "Thứ ba",
    "Thứ tư",
    "Thứ năm",
    "Thứ sáu",
    "Thứ bảy",
  ];
  const start = new Date(centerDate);
  const end = new Date(centerDate);
  // Lùi lại 15 ngày
  start.setDate(start.getDate() - 15);
  // Tiến thêm 15 ngày
  end.setDate(end.getDate() + 15);

  while (start <= end) {
    const formattedDate = formatDateToYMD(start);
    days.push({
      date: formattedDate,
      weekday: weekdays[start.getDay()],
    });
    start.setDate(start.getDate() + 1);
  }

  return days;
};

const onDepartureDateChange = (date) => {
  departureDate.value = date;
  // Kiểm tra nếu ngày về không hợp lệ
  if (returnDate.value && !isValidReturnDate(date, returnDate.value)) {
    ElMessage.error("Ngày đi không được lớn hơn hoặc bằng ngày về.");
    returnDate.value = null; // Reset ngày về
  }
  // Cập nhật danh sách ngày xung quanh ngày được chọn
  availableDays.value = generateAvailableDays(departureDate.value);
  // Đặt currentPage sao cho ngày được chọn nằm ở giữa
  const selectedIndex = availableDays.value.findIndex((day) => day.date === formatDateToYMD(date));
  currentPage.value = Math.max(0, selectedIndex - 3);
};

// Quản lý danh sách ngày hiển thị
const displayedDays = computed(() => {
  // Lấy 7 ngày tiếp theo từ currentPage
  return availableDays.value.slice(currentPage.value, currentPage.value + 8);
});

const isValidReturnDate = (depDate, retDate) => {
  return new Date(retDate) > new Date(depDate);
};

const onReturnDateChange = (date) => {
  if (departureDate.value && !isValidReturnDate(departureDate.value, date)) {
    ElMessage.error("Ngày về phải lớn hơn ngày đi.");
    returnDate.value = null; // Reset ngày về
  } else {
    returnDate.value = date; // Cập nhật ngày về nếu hợp lệ
  }
};

const nextday = () => {
  if (currentPage.value + 7 < availableDays.value.length) {
    currentPage.value++;
  }
};

const prevday = () => {
  if (currentPage.value > 0) {
    currentPage.value--;
  }
};

const searchTickets = async () => {
  try {
    loading.value = true;
    await new Promise((resolve) => setTimeout(resolve, 500)); // Giả lập thời gian tải

    if (gadi.value && gaden.value && departureDate.value  ) {
      // Cập nhật giá trị ga đi và ga đến
      searchgadi.value = gadi.value;
      searchgaden.value = gaden.value;
      searchPerformed.value = true;

      // Kiểm tra loại vé
      if (selectedTicket.value === "one-way" && departureDate.value) {
        // Sinh danh sách các ngày có sẵn cho vé một chiều
        availableDays.value = generateAvailableDays(departureDate.value);
      } else if (
        selectedTicket.value === "round-trip" &&
        departureDate.value &&
        returnDate.value
      ) {
        // Sinh danh sách các ngày có sẵn cho vé khứ hồi
        availableDays.value = generateAvailableDays(departureDate.value);
      } else if (!returnDate.value) {
        // Thông báo nếu không chọn ngày về cho vé khứ hồi
        searchPerformed.value = false;
        ElMessage.error("Vui lòng chọn ngày về.");
        loading.value = false;
        return;
      }

      if (availableDays.value.length > 0) {
        selectedDay.value = availableDays.value[15].date;
      }
      const response = await axios.get("/lichtrinh", {
        params: {
          gadi: gadi.value,
          gaden: gaden.value,
          ngaydi: formatDateToYMD(departureDate.value),
        },
      });
      if (response.data.success && response.data.data.length > 0) {
        trainSchedules.value = response.data.data; // Lưu danh sách lịch trình vào biến trainSchedules
      } else {
        ElMessage.error("Không tìm thấy chuyến tàu.");
        trainSchedules.value = [];
      }
    } else {
      // Kiểm tra xem có thiếu thông tin nào không
      if (!gadi.value) {
        ElMessage.error("Vui lòng chọn ga đi.");
      } else if (!gaden.value) {
        ElMessage.error("Vui lòng chọn ga đến.");
      } else if (!departureDate.value) {
        ElMessage.error("Vui lòng chọn ngày đi.");
      }
    }
  } catch (error) {
    console.error("Lỗi khi tìm kiếm vé:", error);
    ElMessage.error("Đã xảy ra lỗi khi tìm kiếm vé.");
  } finally {
    loading.value = false;
  }
};
onMounted(() => {
  if (gadi.value && gaden.value && formatDateToYMD(departureDate.value)) {
    searchTickets();
    syncTicketCounts();
  }
});

const swapStations = () => {
  const temp = gadi.value;
  gadi.value = gaden.value;
  gaden.value = temp;
};
</script>

<style scoped>
@import url(@/assets/css/eticket.css);
</style>
