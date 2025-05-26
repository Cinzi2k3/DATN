import { createRouter, createWebHistory } from 'vue-router';
import Home from '@/views/Home.vue';
import Buyeticket from '@/views/Buyeticket.vue';
import Signin from '@/views/Signin.vue';
import Payment from '@/views/Payment.vue';
import Account from '@/views/Account.vue';
import PaymentResult from '@/views/PaymentResult.vue';
import CheckIn from '@/views/CheckIn.vue';
import ResetPassword from '@/views/ResetPassword.vue';

// Định nghĩa các route
const userRoutes = [
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
    component: PaymentResult,
  },
  {
    path: '/check-in',
    name: 'CheckIn',
    component: CheckIn,
  },
  {
    path: '/account',
    name: 'Account',
    component: Account,
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: ResetPassword,
  }
];

const adminRoutes = [
  { path: '/admin/dashboard', component: () => import('@/components/admin/Dashboard.vue'), meta: { requiresAdmin: true } },
  { path: '/admin/orders', component: () => import('@/components/admin/Orders.vue') },
  { path: '/admin/reports', component: () => import('@/components/admin/Reports.vue') },
  { path: '/admin/users', component: () => import('@/components/admin/Users.vue') },
  { path: '/admin/status', component: () => import('@/components/admin/status.vue') },
  { path: '/admin/support', component: () => import('@/components/admin/support.vue') },
  { path: '/admin/ticket', component: () => import('@/components/admin/TicketCategory.vue') },

  
];

const routes = [
  ...userRoutes,
  ...adminRoutes,
  { path: '/admin', redirect: '/admin/dashboard' },
];


// Tạo instance router
const router = createRouter({
  history: createWebHistory(),
  routes,
});



export default router;

