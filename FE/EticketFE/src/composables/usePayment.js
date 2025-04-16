// src/composables/usePayment.js
import axios from 'axios';

export function usePayment() {
  const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    }
  });

  const createVnPayPayment = async (bookingData) => {
    try {
      const response = await api.post('/vnpay/create', bookingData);
      return response.data;
    } catch (error) {
      console.error('Lỗi khi tạo thanh toán:', error);
      throw error;
    }
  };

  const getOrderDetails = async (transactionId) => {
    try {
      const response = await api.get(`/orders/${transactionId}`);
      return response.data;
    } catch (error) {
      console.error('Lỗi khi lấy thông tin đơn hàng:', error);
      throw error;
    }
  };

  return {
    createVnPayPayment,
    getOrderDetails
  };
}