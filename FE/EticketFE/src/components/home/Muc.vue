<template>
    <el-row
      gutter="20"
      display="flex"
      justify-content="center"
    >
      <el-col :xs="24" :sm="24" :md="12" :lg="8" class="benefit">
        <div class="v_field gap-3">
          <div class="v_field__icon-container">
            <el-image
              src="https://res.ivivu.com/train/images/icon/sparkles.png"
              fit="contain"
              style="width: 40px; height: 40px"
            />
          </div>
          <div class="v_field__content">
            <p class="v_field__text text-xl leading-6 mb-0 text-gray-800">{{ $t('Tìm chuyến tàu phù hợp')}}</p>
            <p class="v_field__description text-lg">{{ $t('Lựa chọn lịch trình linh hoạt')}}</p>
          </div>
        </div>
      </el-col>
      <el-col :xs="24" :sm="24" :md="12" :lg="8" class="benefit">
        <div class="v_field gap-3">
          <div class="v_field__icon-container">
            <el-image
              src="https://res.ivivu.com/train/images/icon/train-get-ticket.png"
              fit="contain"
              style="width: 40px; height: 40px"
            />
          </div>
          <div class="v_field__content">
            <p class="v_field__text text-xl leading-6 mb-0 text-gray-800">{{ $t('Đặt vé nhanh, dễ dàng')}}</p>
            <p class="v_field__description text-lg">{{ $t('Nhận vé ngay sau khi đặt')}}</p>
          </div>
        </div>
      </el-col>
      <el-col :xs="24" :sm="24" :md="12" :lg="8" class="benefit">
        <a href="https://id.zalo.me/account?continue=https%3A%2F%2Fchat.zalo.me%2F" target="_blank">
          <div class="v_field gap-3">
            <div class="v_field__icon-container">
              <el-image
                src="https://res.ivivu.com/train/images/icon/contact.png"
                fit="contain"
                style="width: 40px; height: 40px"
              />
            </div>
            <div class="v_field__content">
              <p class="v_field__text text-xl leading-6 mb-0 text-gray-800">{{ $t('Luôn sẵn sàng hỗ trợ')}}</p>
              <p class="v_field__description text-lg">
                {{ $t('Phản hồi trong 15 phút qua điện thoại, zalo')}}
              </p>
            </div>
          </div>
        </a>
      </el-col>
    </el-row>
  </template>
  
  <script setup>
  import { ElRow, ElCol, ElImage } from 'element-plus';
  </script>
  
  <style scoped>
@import url('@/assets/css/muc.css');
  </style>
  