import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  resolve: {
    alias: {
      '@': '/src', // Đảm bảo alias này trỏ đúng đến thư mục src
    },
  },
  plugins: [vue()],
})
