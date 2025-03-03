import { defineStore } from 'pinia';

export const useSearchStore = defineStore('search', {
  state: () => ({
    gadi: '',
    gave: '',
    departureDate: '',
    returnDate: '',
    selectedTicket: 'one-way',
    totalTickets: 1,
    searchData: {},
  }),
  actions: {
    setSearchData(data) {
      this.searchData = data;
      this.gadi = data.gadi;
      this.gave = data.gave;
      this.departureDate = data.departureDate;
      this.returnDate = data.returnDate;
      this.selectedTicket = data.selectedTicket;
      this.totalTickets = data.totalTickets;
      this.ticketDetails = data.ticketDetails;
    },

    setTicketData(ticketType, count) {
      if (this.ticketData[ticketType] !== undefined) {
        this.ticketData[ticketType] = count;
      }
    },
    clearSearchData() {
      this.searchData = {}; // Xóa dữ liệu
    },
  },
});