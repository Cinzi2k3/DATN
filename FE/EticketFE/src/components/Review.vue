<template>
    <el-container class="reviews-container">
      <div class="header">
        <h2>{{ $t('Đánh giá của khách hàng sử dụng dịch vụ đặt vé tàu Sao Mai Family')}}</h2>
        <p class="rating">
          <span class="rating-score">9.4/10</span>
          <span class="rating-com"><strong>{{ $t('Tuyệt vời')}}</strong> | 14 {{ $t('đánh giá')}}</span>
        </p>
      </div>
  
      <div>
        <el-row :gutter="20" justify="center">
          <el-col :span="8" style="margin-bottom: 20px;" v-for="(review, index) in reviews.slice(0, 3)" :key="index">
            <div class="review-card">
              <div class="review-avatar">{{ review.initials }}</div>
              <div class="review-content">
                <h4 class="review-name">{{ review.name }}</h4>
                <p class="review-date">{{ review.date }}</p>
                <p class="review-text">{{ review.comment }}</p>
              </div>
            </div>
          </el-col>
        </el-row>
      </div>
  
      <el-footer class="footer">
        <el-button type="primary" size="large" @click="showDialog = true">{{ $t('Hiển thị thêm đánh giá')}}</el-button>
      </el-footer>
  
      <el-dialog
        v-model="showDialog"
        width="50%"
        @close="showDialog = false"
      >
        <div class="header">
         <h2>{{ $t('Đánh giá của khách hàng sử dụng dịch vụ đặt vé tàu Sao Mai Family')}}</h2>
          <p class="rating">
            <span class="rating-score">9.4/10</span>
            <span class="rating-com"><strong>{{ $t('Tuyệt vời')}}</strong> | 14 {{ $t('đánh giá')}}</span>
          </p>
        </div>
        <div>
          <el-row :gutter="20">
            <el-col :span="24" style="margin-bottom: 20px;" v-for="(review, index) in reviews" :key="index">
              <div class="review-card">
                <div class="review-avatar">{{ review.initials }}</div>
                <div class="review-content">
                  <h4 class="review-name">{{ review.name }}</h4>
                  <p class="review-date">{{ review.date }}</p>
                  <p class="review-text">{{ review.comment }}</p>
                </div>
              </div>
            </el-col>
          </el-row>
        </div>
      </el-dialog>
    </el-container>
  </template>
  
  <script setup>
  import { ref } from "vue";
  
  const showDialog = ref(false); 
  
  const reviews = [
    {
      initials: "HH",
      name: "Huỳnh Đình Quang Huy",
      date: "20-02-2025",
      comment: "Rất tốt",
    },
    {
      initials: "TL",
      name: "Lương Thị Bích Tuyền",
      date: "19-02-2025",
      comment:
        "Tàu cũng phức tạp, nên tự cất giữ đồ đạc, người lên xuống liên tục, các khoan qua lại, nên chạy hơi chậm, khá ổn...",
    },
    {
      initials: "MQ",
      name: "Quach Phuong Mandy",
      date: "16-02-2025",
      comment: "Dịch vụ rất tốt, sẽ ủng hộ thêm nhiều lần.",
    },
    {
      initials: "KH",
      name: "Huỳnh Phương Kayla",
      date: "16-02-2025",
      comment: "Tôi cảm thấy rất hài lòng với chuyến đi.",
    },
    {
      initials: "TH",
      name: "Hồ Anh Thơ",
      date: "29-12-2024",
      comment: "Dịch vụ chuyên nghiệp và nhanh chóng.",
    },
    {
      initials: "QL",
      name: "Lương Chấn Quyền",
      date: "24-12-2024",
      comment:
        "Bạn Duy bên CSKH tư vấn và hỗ trợ nhiệt tình, đã làm việc với bạn nhiều lần.",
    },
  ];
  </script>
  
  <style scoped>
  @import url(@/assets/css/review.css);
  </style>
  