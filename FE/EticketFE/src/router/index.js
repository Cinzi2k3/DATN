import { createRouter, createWebHistory } from 'vue-router';
import Home from '@/views/Home.vue';
import Buyeticket from '@/views/Buyeticket.vue';
import Signin from '@/views/Signin.vue';
import Payment from '@/views/Payment.vue';
// import Boking from '@/views/Boking.vue';


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
    path: '/Signin',
    name: 'Signin',
    component: Signin
  },
  {
    path: '/Payment',
    name: 'Payment',
    component: Payment
  },
  {
    path: '/payment/result',
    name: 'PaymentResult',
    component: () => import('@/views/PaymentResult.vue'),
  },
];

// Tạo instance router
const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;

