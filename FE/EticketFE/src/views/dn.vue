<template>
    <div>
      <h1>Đăng Nhập</h1>
      <form @submit.prevent="login">
        <input type="email" v-model="email" placeholder="Email" required />
        <input type="password" v-model="password" placeholder="Mật khẩu" required />
        <button type="submit">Đăng nhập</button>
      </form>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  export default {
    data() {
      return {                                                                                                                                                                      
        email: '',
        password: '',
      };
    },
    methods: {
      async login() {
        try {
          const response = await axios.post('http://127.0.0.1:8000/api/login', {
            email: this.email,
            password: this.password,
          });
          alert(response.data.message);
          this.$router.push('/');
        } catch (error) {
          // Kiểm tra nếu có phản hồi lỗi từ API
          if (error.response) {
            alert(error.response.data.message || 'Thông tin đăng nhập không hợp lệ');
          } else {
            alert('Đã có lỗi xảy ra, vui lòng thử lại');
          }
        }
      },
    },
  };
  </script>
  