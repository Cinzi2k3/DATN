<template>
  <div>
    <el-container v-if="!isAdminRoute">
      <el-header id="home"><Header /></el-header>
      <el-main><RouterView /></el-main>
      <el-footer id="footer"><Footer /></el-footer>
    </el-container>
    <div v-else>
      <Dashboard />
    </div>
  </div>
</template>

<script setup>
import Header from "@/components/Header.vue";
import Footer from "@/components/Footer.vue";
import { computed } from "vue";

import { useRoute } from "vue-router";
import Dashboard from "@/views/Admin.vue";
const route = useRoute();

const isAdminRoute = computed(() => route.path.startsWith("/admin"));
</script>

<style scoped>
.el-container {
  max-width: 1600px;
  margin: 0 auto;
}
.el-header {
  background: #1b5850;
  height: 85px;
  border-top: 4px solid #1ba0e2;
}
.el-main {
  padding: 0px !important;
}
.el-footer {
  background-color: #333;
  color: #888;
  height: auto;
}
</style>