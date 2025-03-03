<template>
    <div class="travel-section">
      <h2 class="title">{{ $t('Du lịch bằng tàu hỏa')}}</h2>
      <p class="subtitle">{{ $t('Cập nhật ngay tin tức và hành trình hấp dẫn')}}</p>
      <el-row :gutter="20" class="travel-cards">
        <el-col
          :xs="24"
          :sm="12"
          :md="8"
          :lg="6"
          v-for="(item, index) in articles"
          :key="index"
          cursor="pointer"
        >
          <div shadow="hover" class="card">
            <img :src="item.image" alt="Article image" class="card-image" />
            <div class="card-content">
              <h3 class="card-title">{{ item.title }}</h3>
              <p class="card-description">{{ item.description }}</p>
            </div>
          </div>
        </el-col>
      </el-row>
    </div>
  </template>
  
  <script setup>
import { ref } from "vue";

const articles = ref([
  {
    image: new URL('@/assets/img/tintuc1.jpg', import.meta.url).href,
    title: "Ra mắt đoàn tàu La Reine tuyến Đà Lạt - Trại Mát...",
    description: "Một trải nghiệm thú vị cùng phong cảnh thơ mộng...",
  },
  {
    image: new URL('@/assets/img/tintuc2.png', import.meta.url).href,
    title: "Khám phá bến trong tour tàu xuyên Việt 7 sao với...",
    description: "Trải nghiệm tàu cao cấp, tận hưởng không gian sang trọng...",
  },
  {
    image: new URL('@/assets/img/tintuc3.png', import.meta.url).href,
    title: "Tăng cường thêm 5000 chỗ cho chuyến tàu Tết Ất Tỵ...",
    description: "Đáp ứng nhu cầu di chuyển của khách hàng trong dịp lễ lớn...",
  },
  {
    image: new URL('@/assets/img/tintuc4.jpg', import.meta.url).href,
    title: "Kinh nghiệm đi tàu hỏa từ A đến Z cho người lần...",
    description: "Hướng dẫn chi tiết để có một chuyến đi thuận lợi...",
  },
]);
</script>

  
  <style scoped>
   @import url(@/assets/css/tintuc.css);
  </style>
  