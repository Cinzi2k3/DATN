import { createApp } from 'vue';
import App from './App.vue';
import router from './router'; 
import { createI18n } from 'vue-i18n';
import en from '../locales/en.json';
import vi from '../locales/vi.json';
import { createPinia } from 'pinia';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import { useAuthStore } from '@/stores/authStore.js';
import axios from '@/url/api.js';

const i18n = createI18n({
    locale: 'vi', // Ngôn ngữ mặc định
    fallbackLocale: 'en',
    messages: { en, vi }
  });

const app = createApp(App);
app.config.globalProperties.$axios = axios;
const pinia = createPinia();
app.use(pinia);
app.use(i18n);
app.use(ElementPlus);
const authStore = useAuthStore();
authStore.checkAuth(); // Kiểm tra trạng thái đăng nhập
app.use(router);
app.mount('#app');
