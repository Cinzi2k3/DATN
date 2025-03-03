import { createRouter, createWebHistory } from 'vue-router';
import Home from '@/views/Home.vue';
import Buyeticket from '@/views/Buyeticket.vue';
import dn from '@/views/dn.vue';
import dk from '@/views/dk.vue';

// Định nghĩa các route
const routes = [
  {
    path : '/',
    name : 'Home',
    component : Home
  },
  {
    path : '/Buyeticket',
    name : 'Buyeticket',
    component : Buyeticket
  },
  {
    path: '/dn',
    name: 'dn',
    component: dn
  },
  {
    path: '/dk',
    name: 'dk',
    component: dk
  }
];

// Tạo instance router
const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;

