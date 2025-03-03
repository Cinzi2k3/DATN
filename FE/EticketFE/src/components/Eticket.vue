<template>
  <div style="justify-items: center">
    <div style="width: 1170px">
      <div style="background-color: #e5eaf3">
        <el-row :gutter="20">
          <div
            style="
              width: 380px;
              margin: 15px 15px 15px 25px;
              background-color: #fff;
            "
          >
            <el-col
              :span="24"
              style="display: flex; align-items: center; margin-top: 10px"
            >
              <div style="flex: 1; margin-right: 10px">
                <label style="opacity: 0.5; margin-left: 10px">Ga đi:</label>
                <el-select
                  v-model="gadi"
                  placeholder="Chọn ga đi"
                  style="width: 100%"
                >
                  <el-option
                    v-for="station in stations"
                    :key="station"
                    :label="station"
                    :value="station"
                  />
                </el-select>
              </div>
              <el-icon
                style="font-size: 25px; cursor: pointer; margin: 0 10px"
                @click="swapStations"
              >
                <Refresh />
              </el-icon>
              <div style="flex: 1">
                <label style="opacity: 0.5; margin-left: 10px">Ga đến:</label>
                <el-select
                  v-model="gave"
                  placeholder="Chọn ga đến"
                  style="width: 100%"
                >
                  <el-option
                    v-for="station in stations"
                    :key="station"
                    :label="station"
                    :value="station"
                  />
                </el-select>
              </div>
            </el-col>
          </div>

          <div
            style="
              width: 505px;
              background-color: #fff;
              margin: 15px 15px 15px 0px;
            "
          >
            <el-row>
              <el-col :span="12" style="padding: 10px">
                <div style="display: flex; align-items: center">
                  <el-icon style="font-size: 25px; margin-right: 10px">
                    <Calendar />
                  </el-icon>
                  <label style="opacity: 0.5; margin-right: 10px"
                    >Ngày đi:</label
                  >
                  <el-radio v-model="selectedTicket" label="one-way"
                    >Một chiều</el-radio
                  >
                </div>
                <el-date-picker
                  v-model="departureDate"
                  type="date"
                  @change="onDepartureDateChange"
                  placeholder="Chọn ngày đi"
                  style="width: 100%"
                />
              </el-col>

              <el-col :span="12" style="padding: 10px">
                <div style="display: flex; align-items: center">
                  <el-icon style="font-size: 25px; margin-right: 10px">
                    <Calendar />
                  </el-icon>
                  <label style="opacity: 0.5; margin-right: 10px"
                    >Ngày về:</label
                  >
                  <el-radio v-model="selectedTicket" label="round-trip"
                    >Khứ hồi</el-radio
                  >
                </div>
                <el-date-picker
                  v-model="returnDate"
                  type="date"
                  placeholder="Chọn ngày về"
                  style="width: 100%"
                  :disabled="selectedTicket !== 'round-trip'"
                  @change="onReturnDateChange"
                />
              </el-col>
            </el-row>
          </div>
          <div
            style="
              background-color: white;
              width: 150px;
              border-radius: 5px;
              margin-top: 15px;
              height: 85px;
            "
          >
            <div class="position-relative">
              <button
                @click="toggleDropdown"
                class="d-flex align-items-center justify-content-between w-100"
                style="height: 85px; border: 0px; border-radius: 5px"
              >
                <div class="d-flex align-items-center">
                  <i class="fa-solid fa-user me-2"></i>
                  <div class="d-flex flex-column align-items-start">
                    <span>Số lượng vé</span>
                    <span>{{ totalTickets }} vé</span>
                  </div>
                </div>
              </button>

              <div
                v-if="isOpen"
                class="dropdown-menu show w-100 mt-2 p-0 border rounded shadow-sm"
              >
                <div
                  v-for="category in ticketCategories"
                  :key="category.id"
                  class="d-flex align-items-center justify-content-between p-3 border-bottom"
                >
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
                    <button
                      @click="decrement(category.id)"
                      class="btn btn-sm btn-outline-secondary"
                      :disabled="category.count <= 0"
                    >
                      -
                    </button>
                    <span class="mx-2">{{ category.count }}</span>
                    <button
                      @click="increment(category.id)"
                      class="btn btn-sm btn-outline-secondary"
                    >
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

          <button
            style="
              width: 80px;
              cursor: pointer;
              background-color: #409eff;
              border: 0px;
              margin: 15px 10px 15px 0px;
            "
            @click="searchTickets"
          >
            <h3 style="color: white; font-size: 20px">Tìm</h3>
          </button>
        </el-row>
      </div>
      <SearchTicketResult
        :searchgadi="searchgadi"
        :searchgave="searchgave"
        :searchPerformed="searchPerformed"
        :displayedDays="displayedDays"
        :selectedDay="selectedDay"
        :availableDays="availableDays"
        :currentPage="currentPage"
        @update:selectedDay="selectedDay = $event"
        @prevday="prevday"
        @nextday="nextday"
      />

      <TrainInfoCard
        :searchPerformed="searchPerformed"
        :searchgadi="searchgadi"
        :searchgave="searchgave"
        :selectedDay="selectedDay"
        :trainCode="'NA1'"
        :availableSeats="88"
        :departureTime="'6:30'"
        :duration="'6h0p'"
        :arrivalTime="'12:30'"
        :arrivalDate="'03 tháng 11'"
        :ticketPrice="'500.000đ'"
      />
    </div>
  </div>
</template>

<script setup>
import { ElMessage,ElNotification } from "element-plus";
import { ref, computed, onMounted } from "vue";
import SearchTicketResult from "@/components/Time.vue";
import TrainInfoCard from "@/components/TrainInfoCard.vue";
import {Refresh,Calendar,ArrowLeft,ArrowRight,} from "@element-plus/icons-vue";
import { useSearchStore } from "../stores/searchStore";
const searchStore = useSearchStore();
const isOpen = ref(false);
const gadi = ref(searchStore.gadi);
const gave = ref(searchStore.gave);
const departureDate = ref(searchStore.departureDate);
const returnDate = ref(searchStore.returnDate);
const totalTickets = ref(searchStore.totalTickets);
const searchgadi = ref(null);
const searchgave = ref(null);
const selectedDay = ref(null);
const searchPerformed = ref(false);
const selectedTicket = ref("one-way");
const availableDays = ref([]);
const currentPage = ref(0);
const stations = ref([
  "Hà Nội",
  "Vinh",
  "Huế",
  "Sài Gòn",
  "Nha Trang",
  "Đà Nẵng",
]);

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
  console.log("Updated ticketDetails in Pinia:", searchStore.searchData.ticketDetails);
};

onMounted(() => {
  if (gadi.value && gave.value && departureDate.value) {
    searchTickets();
    syncTicketCounts();
  }
});

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const increment = (id) => {
  const category = ticketCategories.value.find((item) => item.id === id);
  // Nếu là "child" và không có vé "adult", ngăn không cho tăng
  if (id === "child") {
    const adultCategory = ticketCategories.value.find((item) => item.id === "adult");
    if (adultCategory.count === 0) {
      ElNotification .error("Trẻ em phải đi cùng với người lớn");
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
    const childCategory = ticketCategories.value.find((item) => item.id === "child");
    if (category.count === 1 && childCategory.count > 0) {
      ElNotification .error("Trẻ em phải đi cùng với người lớn");
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

const generateAvailableDays = (startDate) => {
  const days = [];
  const start = new Date(startDate);
  const endDate = new Date(start);
  const weekdays = [
    "Chủ nhật",
    "Thứ hai",
    "Thứ ba",
    "Thứ tư",
    "Thứ năm",
    "Thứ sáu",
    "Thứ bảy",
  ];
  endDate.setMonth(start.getMonth() + 1);

  while (start < endDate) {
    const formattedDate = start.toLocaleDateString("vi-VN").replace(/\//g, "-");
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

  // Cập nhật danh sách ngày có sẵn
  availableDays.value = generateAvailableDays(departureDate.value);
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

const searchTickets = () => {
  if (gadi.value && gave.value && departureDate.value) {
    searchgadi.value = gadi.value;
    searchgave.value = gave.value;
    searchPerformed.value = true;

    if (selectedTicket.value === "one-way" && departureDate.value) {
      availableDays.value = generateAvailableDays(departureDate.value);
    } else if (
      selectedTicket.value === "round-trip" &&
      departureDate.value &&
      returnDate.value
    ) {
      availableDays.value = generateAvailableDays(departureDate.value);
    } else if (!returnDate.value) {
      searchPerformed.value = false;
      ElMessage.error("Vui lòng chọn ngày về.");
    }

    if (availableDays.value.length > 0) {
      selectedDay.value = availableDays.value[0].date; // Chọn ngày đầu tiên trong danh sách
    }
    console.log(selectedDay.value);
  } else {
    if (!gadi.value) {
      ElMessage.error("Vui lòng chọn ga đi.");
    } else if (!gave.value) {
      ElMessage.error("Vui lòng chọn ga đến.");
    } else if (!departureDate.value) {
      ElMessage.error("Vui lòng chọn ngày đi.");
    }
  }
};

const swapStations = () => {
  const temp = gadi.value;
  gadi.value = gave.value;
  gave.value = temp;
};
</script>

<style scoped>
.day-card {
  text-align: center;
  cursor: pointer;
}

.day-card.selected {
  color: #409eff;
  border-bottom: 2px solid #409eff;
}
.text-center {
  text-align: center;
}
.text-center2 {
  margin-left: 100px;
}
.text-center1 {
  padding-top: 10px;
}
</style>
