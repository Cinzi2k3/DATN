import { defineStore } from 'pinia';

export const useAuthStore = defineStore('authStore', {
  state: () => ({
    isLoggedIn: false,
    userId: null,
    userName: '',
    userEmail: '',
    phoneNumber: '',
    address: '',
    birthDate: '',
  }),
  actions: {
    // Kiểm tra trạng thái đăng nhập khi ứng dụng khởi động
    checkAuth() {
      const userData = localStorage.getItem('authData');
      if (userData) {
        const { isLoggedIn, userId, userName, userEmail, phoneNumber, address, birthDate } = JSON.parse(userData);
        this.isLoggedIn = isLoggedIn;
        this.userId = userId;
        this.userName = userName;
        this.userEmail = userEmail;
        this.phoneNumber = phoneNumber;
        this.address = address;
        this.birthDate = birthDate;
      }
    },
    // Đăng nhập và lưu thông tin vào Local Storage
    login(userId, userName, userEmail, phoneNumber = '', address = '', birthDate = '') {
      this.userId = userId;
      this.userName = userName;
      this.userEmail = userEmail;
      this.phoneNumber = phoneNumber;
      this.address = address;
      this.birthDate = birthDate;
      this.isLoggedIn = true;
      localStorage.setItem('authData', JSON.stringify({
        isLoggedIn: true,
        userId,
        userName,
        userEmail,
        phoneNumber,
        address,
        birthDate
      }));
    },
    // Đăng xuất và xóa thông tin khỏi Local Storage
    logout() {
      this.isLoggedIn = false;
      this.userId = null;
      this.userName = '';
      this.userEmail = '';
      this.phoneNumber = '';
      this.address = '';
      this.birthDate = '';
      localStorage.removeItem('authData');
    },
    updateProfile({ name, email, phoneNumber, address, birthDate }) {
      this.userName = name || this.userName;
      this.userEmail = email || this.userEmail;
      this.phoneNumber = phoneNumber || this.phoneNumber;
      this.address = address || this.address;
      this.birthDate = birthDate || this.birthDate;
      localStorage.setItem('authData', JSON.stringify({
        isLoggedIn: this.isLoggedIn,
        userId: this.userId,
        userName: this.userName,
        userEmail: this.userEmail,
        phoneNumber: this.phoneNumber,
        address: this.address,
        birthDate: this.birthDate
      }));
    },
  },
});