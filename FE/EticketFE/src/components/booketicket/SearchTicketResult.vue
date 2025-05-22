<template>
  <div
    v-if="searchPerformed"
    style="margin-top: 10px; background-color: white; padding: 10px 10px 0 10px; box-shadow: 0 0 5px #0003"
  >
    <div style="display: flex; justify-content: space-between">
      <h3 style="margin: 0; font-size: 20px; color: #333399; padding: 5px 0 0 5px">
        {{ $t('Tuyến đường') }}
      </h3>
      <h3 style="margin: 0; font-size: 20px; padding: 5px 5px 0 0px">
        {{ searchgadi }} → {{ searchgaden }}
      </h3>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center">
      <div style="display: flex; align-items: center; width: 100%">
        <el-button
          type="text"
          @click="prevday"
          :disabled="currentPage === 0"
          style="margin-right: auto"
        >
          <el-icon><ArrowLeft /></el-icon>
        </el-button>
        <el-row style="overflow-x: auto; white-space: nowrap; flex-grow: 1">
          <el-col
            v-for="(day, index) in displayedDays"
            :key="index"
            :span="3"
            style="display: inline-block; text-align: center; cursor: pointer"
            @click="day && selectDay(day.date)"
            :class="['day-card', { selected: day && selectedDay === day.date }]"
          >
            <p style="margin-top: 10px; margin-bottom: 5px">
              {{ day ? day.date : 'N/A' }}
            </p>
            <p>{{ day ? day.weekday : 'N/A' }}</p>
          </el-col>
        </el-row>
        <el-button
          type="text"
          @click="nextday"
          :disabled="currentPage + 6 >= availableDays.length"
          style="margin-left: auto"
        >
          <el-icon><ArrowRight /></el-icon>
        </el-button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ArrowLeft, ArrowRight } from "@element-plus/icons-vue";

// Props
const props = defineProps({
  searchgadi: String,
  searchgaden: String,
  searchPerformed: Boolean,
  displayedDays: {
    type: Array,
    default: () => [], // Đảm bảo mặc định là mảng rỗng
  },
  selectedDay: String,
  availableDays: {
    type: Array,
    default: () => [],
  },
  currentPage: Number,
});

// Emits
const emit = defineEmits(["update:selectedDay", "prevday", "nextday"]);

// Methods
const selectDay = (day) => {
  emit("update:selectedDay", day);
};
const prevday = () => {
  emit("prevday");
};
const nextday = () => {
  emit("nextday");
};
</script>

<style scoped>
@import url(@/assets/css/time.css);
</style>