import { defineStore } from 'pinia';

export const useAuthStore = defineStore('authStore', {
  state: () => ({
    isLoggedIn: false,
    userName: '',
  }),
  actions: {
    // Kiểm tra trạng thái đăng nhập khi ứng dụng khởi động
    checkAuth() {
      const userData = localStorage.getItem('authData');
      if (userData) {
        const { isLoggedIn, userName } = JSON.parse(userData);
        this.isLoggedIn = isLoggedIn;
        this.userName = userName;
      }
    },
    // Đăng nhập và lưu thông tin vào Local Storage
    login(userName) {
      this.isLoggedIn = true;
      this.userName = userName;
      localStorage.setItem('authData', JSON.stringify({ isLoggedIn: true, userName }));
    },
    // Đăng xuất và xóa thông tin khỏi Local Storage
    logout() {
      this.isLoggedIn = false;
      this.userName = '';
      localStorage.removeItem('authData');
    },
  },
});
