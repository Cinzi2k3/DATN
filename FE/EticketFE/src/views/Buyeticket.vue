<template>
    <div class="hea">
      <el-icon><HomeFilled /></el-icon>
      <a href="/" class="no-underline">{{  $t('Trang chủ') }}</a>
    </div>
    <div class="eticket">
      <Eticket />
    </div>
</template>

<script setup>
import Eticket from "../components/booketicket/Eticket.vue";
import { HomeFilled } from "@element-plus/icons-vue";

</script>

<style scoped>
.hea {
  margin: 0 auto;
  padding: 20px 190px;
  background-color: #f2f7fa;
}
.no-underline {
  text-decoration: none;
}
.eticket {
  margin-bottom: 20px;
  background-color: #f2f7fa;
}
.el-icon {
  margin-right: 5px;
  top: 2px;
}
</style>