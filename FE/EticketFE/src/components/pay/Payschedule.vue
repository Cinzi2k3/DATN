<template>
  <el-card >
    <!-- Lịch trình chuyến đi -->
    <div class="mb-4">
      <h5 v-if="returnData">Chiều đi</h5>
      <el-row class="mb-3">
        <el-col :span="24" class="d-flex align-items-center">
          <span class="fw-medium fs-6">{{ departureData.searchgadi }}</span>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 100 20"
            width="120"
            height="20"
            style="margin-left: -60px"
          >
            <line
              x1="60"
              y1="10"
              x2="90"
              y2="10"
              stroke="black"
              stroke-width="2"
            />
            <polygon points="90,5 100,10 90,15" fill="black" />
          </svg>
          <span class="fw-medium fs-6">{{ departureData.searchgaden }}</span>
          <span class="ms-auto badge bg-primary fs-6 fw-bold">{{
            departureData.trainCode
          }}</span>
        </el-col>
      </el-row>
      <el-row>
        <el-col :span="8">
          <div class="fw-bold fs-5">{{ departureData.departureTime }}</div>
          <div class="text-muted">{{ departureData.selectedDay }}</div>
        </el-col>
        <el-col :span="8" class="text-center text-muted fs-4">
          <i class="fa-solid fa-train"></i>
          <div class="text-muted">{{ departureData.duration }}</div>
        </el-col>
        <el-col :span="8" class="text-end">
          <div class="fw-bold fs-5">{{ departureData.arrivalTime }}</div>
          <div class="text-muted">{{ departureData.arrivalDate }}</div>
        </el-col>
      </el-row>
    </div>

    <el-divider v-if="returnData" />

    <!-- Lịch trình chuyến về -->
    <div v-if="returnData" class="mb-4">
      <h5>Chiều về</h5>
      <el-row class="mb-3">
        <el-col :span="24" class="d-flex align-items-center">
          <span class="fw-medium fs-6">{{ returnData.searchgadi }}</span>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 100 20"
            width="120"
            height="20"
            style="margin-left: -60px"
          >
            <line
              x1="60"
              y1="10"
              x2="90"
              y2="10"
              stroke="black"
              stroke-width="2"
            />
            <polygon points="90,5 100,10 90,15" fill="black" />
          </svg>
          <span class="fw-medium fs-6">{{ returnData.searchgaden }}</span>
          <span class="ms-auto badge bg-primary fs-6 fw-bold">{{
            returnData.trainCode
          }}</span>
        </el-col>
      </el-row>
      <el-row>
        <el-col :span="8">
          <div class="fw-bold fs-5">{{ returnData.departureTime }}</div>
          <div class="text-muted">{{ returnData.selectedDay }}</div>
        </el-col>
        <el-col :span="8" class="text-center text-muted fs-4">
          <i class="fa-solid fa-train"></i>
          <div class="text-muted">{{ returnData.duration }}</div>
        </el-col>
        <el-col :span="8" class="text-end">
          <div class="fw-bold fs-5">{{ returnData.arrivalTime }}</div>
          <div class="text-muted">{{ returnData.arrivalDate }}</div>
        </el-col>
      </el-row>
    </div>

    <el-divider />

    <!-- Chi tiết giá -->
    <div class="mb-4">
      <el-row class="mb-2">
        <el-col :span="12">Tổng tiền vé</el-col>
        <el-col :span="12" class="text-end fw-bold">{{
          formatTotalPrice(totalTicketPrice)
        }}</el-col>
      </el-row>
      <el-row class="mb-2">
        <el-col :span="12">
          <div class="d-flex align-items-center gap-2">
            Phí bảo hiểm
            <el-tooltip content="Phí bảo hiểm hành khách" placement="top">
              <el-icon><InfoFilled /></el-icon>
            </el-tooltip>
          </div>
        </el-col>
        <el-col :span="12" class="text-end">Chưa có</el-col>
      </el-row>
      <el-row>
        <el-col :span="12">
          <div class="d-flex align-items-center gap-2">
            Phí dịch vụ
            <el-tooltip content="Phí xử lý giao dịch" placement="top">
              <el-icon><InfoFilled /></el-icon>
            </el-tooltip>
          </div>
        </el-col>
        <el-col :span="12" class="text-end">Chưa có</el-col>
      </el-row>
    </div>

    <el-divider />

    <!-- Tổng tiền -->
    <div class="d-flex justify-content-between align-items-center">
      <span class="fs-5 fw-medium">Tổng tiền</span>
      <span class="fs-4 fw-bold text-warning">{{
        formatTotalPrice(totalTicketPrice)
      }}</span>
    </div>
  </el-card>
</template>
  
  <script setup>
import { InfoFilled } from "@element-plus/icons-vue";
import { computed } from "vue";
import { useFormatPrice } from "@/composables/useFormatprice";

const { formatTotalPrice } = useFormatPrice();
const { departureData, returnData } = defineProps({
  departureData: { type: Object, required: true },
  returnData: { type: Object, default: null },
});
const totalTicketPrice = computed(() => {
  const departurePrice = departureData?.totalPrice || 0;
  const returnPrice = returnData?.totalPrice || 0;
  return departurePrice + returnPrice;
});
</script>
  
  <style scoped>
.text-muted {
  font-size: 14px;
}
</style>
  