<template>
  <div class="action-buttons">
    <el-button @click="gotoback">
      <el-icon><ArrowLeft /></el-icon> Quay lại
    </el-button>
    <el-button type="primary" >
      Thanh toán
      <el-icon class="el-icon--right"><ArrowRight /></el-icon>
    </el-button>
  </div>
</template>

<script setup>
import {  ArrowLeft, ArrowRight } from "@element-plus/icons-vue";

const gotoback = () => {
  window.history.back();
};

</script>

<style scoped>

.action-buttons {
  display: flex;
  margin-top: 24px;
  gap: 12px;
}
.el-button{
    font-size: 17px;
    height: 45px;
}
</style>