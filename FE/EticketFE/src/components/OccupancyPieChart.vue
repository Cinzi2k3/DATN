<!-- src/components/OccupancyPieChart.vue -->
<template>
  <div class="chart">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Chart, registerables } from 'chart.js';

// Đăng ký các thành phần cần thiết của Chart.js
Chart.register(...registerables);

const props = defineProps({
  chartData: {
    type: Object,
    required: true,
  },
  height: {
    type: Number,
    default: 280,
  },
});

const chartCanvas = ref(null);
let chartInstance = null;

const createChart = () => {
  if (!chartCanvas.value || !props.chartData.labels?.length) return;

  // Hủy biểu đồ cũ nếu có
  if (chartInstance) {
    chartInstance.destroy();
  }

  // Tạo biểu đồ mới
  chartInstance = new Chart(chartCanvas.value, {
    type: 'pie',
    data: props.chartData,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false, // Legend hiển thị riêng trong template
        },
      },
    },
  });
};

// Tạo biểu đồ khi component được mount
onMounted(() => {
  createChart();
});

// Cập nhật biểu đồ khi dữ liệu thay đổi
watch(
  () => props.chartData,
  () => {
    createChart();
  },
  { deep: true }
);
</script>

<style scoped>
.chart {
  position: relative;
  width: 100%;
  height: v-bind('height + "px"');
}
</style>