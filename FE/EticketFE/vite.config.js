import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  resolve: {
    alias: {
      '@': '/src', // Đảm bảo alias này trỏ đúng đến thư mục src
    },
  },
  plugins: [vue()],
  server: {
    host: '0.0.0.0', // Cho phép truy cập từ mọi địa chỉ IP
    port: 5173,      // Port mặc định (có thể thay đổi)
    disableHostCheck: true
  }
})
