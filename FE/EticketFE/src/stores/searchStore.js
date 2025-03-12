// src/stores/searchStore.js
import { defineStore } from 'pinia';

export const useSearchStore = defineStore('search', {
  state: () => ({
    gadi: '',                // Ga đi
    gaden: '',                // Ga đến
    departureDate: '',       // Ngày khởi hành
    returnDate: '',          // Ngày trở lại (nếu có)
    selectedTicket: 'one-way', // Loại vé (một chiều hay khứ hồi)
    totalTickets: 1,         // Tổng số vé
    searchData: {            // Dữ liệu tìm kiếm
      gadi: '',
      gaden: '',
      departureDate: '',
      returnDate: '',
      selectedTicket: 'one-way',
      totalTickets: 1,
      ticketDetails: {}      // Chi tiết về vé
    },
    trainResults: []         // Lưu kết quả tìm kiếm
  }),

  actions: {
    // Lưu thông tin tìm kiếm vào state
    setSearchData(data) {
      this.searchData = data;  // Lưu tất cả dữ liệu tìm kiếm vào state
      this.gadi = data.gadi;
      this.gaden = data.gaden;
      this.departureDate = data.departureDate;
      this.returnDate = data.returnDate;
      this.selectedTicket = data.selectedTicket;
      this.totalTickets = data.totalTickets;
      this.ticketDetails = data.ticketDetails;
    },

    // Cập nhật kết quả tìm kiếm tuyến tàu
    setTrainResults(results) {
      this.trainResults = results; // Lưu kết quả tìm kiếm vào state
    },

    // Xóa dữ liệu tìm kiếm
    clearSearchData() {
      this.searchData = {};   // Xóa dữ liệu tìm kiếm
      this.trainResults = []; // Xóa kết quả tìm kiếm
      this.gadi = '';
      this.gaden = '';
      this.departureDate = '';
      this.returnDate = '';
      this.selectedTicket = 'one-way';
      this.totalTickets = 1;
    }
  }
});
