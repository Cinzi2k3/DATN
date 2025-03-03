<template>
  <el-container>
    <el-header id="home"><Header/></el-header>
    <el-main><RouterView/></el-main>
    <el-footer id="footer"><Footer/></el-footer>
  </el-container>
</template>

<script setup>
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue';

import { RouterView, useRoute } from 'vue-router';
const route = useRoute();


</script>

<style scoped>
.el-container{
max-width: 1600px;
margin:0 auto;
}
.el-header{
background: #1B5850;
height: 85px;
border-top: 4px solid #1ba0e2;
}
.el-main{
padding: 0px !important;
}
.el-footer{
background-color: #333;
color: #888;
height:  auto;
}
</style>