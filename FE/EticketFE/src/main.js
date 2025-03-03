import { createApp } from 'vue';
import App from './App.vue';
import router from './router'; 
import { createI18n } from 'vue-i18n';
import en from '../locales/en.json';
import vi from '../locales/vi.json';
import { createPinia } from 'pinia';
import axios from 'axios';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

axios.defaults.baseURL = 'http://127.0.0.1:8000';
axios.defaults.headers.common['Content-Type'] = 'application/json';

const i18n = createI18n({
    locale: 'vi', // Ngôn ngữ mặc định
    fallbackLocale: 'en',
    messages: { en, vi }
  });

const app = createApp(App);
const pinia = createPinia();

app.use(i18n);
app.use(ElementPlus);
app.use(pinia);
app.use(router);
app.mount('#app');
