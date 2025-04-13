<template>
  <div class="ticket-selection-container">
    <h5 class="text-center mb-3">Chọn ghế cho</h5>
    <div class="ticket-buttons">
      <button 
        v-for="(ticket, index) in ticketDetails" 
        :key="index"
        class="ticket-btn"
        :class="{
          'active': activeTicketIndex === index,
          'completed': ticketSeatsSelected[index] === ticket.quantity,
          'child-ticket': isChildTicket(ticket),
          'adult-ticket': !isChildTicket(ticket)
        }"
        @click="selectTicket(index)"
        :disabled="ticketSeatsSelected[index] === ticket.quantity"
      >
        <div class="ticket-icon">
          <i :class="isChildTicket(ticket) ? 'bi bi-person-child' : 'bi bi-person-fill'"></i>
        </div>
        <div class="ticket-info">
          <div class="ticket-type">{{ getTicketTypeDisplay(ticket) }}</div>
          <div class="ticket-progress">{{ ticketSeatsSelected[index] }}/{{ ticket.quantity }}</div>
        </div>
        <div class="ticket-status">
          <i v-if="ticketSeatsSelected[index] === ticket.quantity" class="bi bi-check-circle-fill"></i>
          <i v-else class="bi bi-circle"></i>
        </div>
      </button>
    </div>
    
    <div class="current-selection mt-3" v-if="activeTicketIndex !== null">
      <div class="alert" :class="isCurrentTicketChild ? 'alert-info' : 'alert-primary'">
        <i :class="isCurrentTicketChild ? 'bi bi-person-child me-2' : 'bi bi-person-fill me-2'"></i>
        Đang chọn ghế cho: <strong>{{ getTicketTypeDisplay(ticketDetails[activeTicketIndex]) }}</strong>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  ticketDetails: {
    type: Array,
    required: true
  },
  totalTickets: {
    type: Number,
    required: true
  },
  selectedSeats: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:active-ticket']);

// Trạng thái
const activeTicketIndex = ref(0);
const ticketSeatsSelected = ref([]);

// Khởi tạo mảng theo dõi số ghế đã chọn cho mỗi loại vé
const initTicketSeatsTracking = () => {
  ticketSeatsSelected.value = props.ticketDetails.map(() => 0);
};

// Cập nhật khi ticketDetails thay đổi
watch(() => props.ticketDetails, () => {
  initTicketSeatsTracking();
}, { immediate: true });

// Cập nhật khi selectedSeats thay đổi
watch(() => props.selectedSeats, (newSelectedSeats) => {
  // Reset tracking
  initTicketSeatsTracking();
  
  // Cần triển khai logic để phân bổ ghế đã chọn vào các loại vé
  // Đây là ví dụ đơn giản, bạn có thể cần điều chỉnh dựa trên cách lưu trữ dữ liệu
  if (props.selectedSeats.length > 0) {
    // Giả sử mỗi ghế đã được gán cho một loại vé cụ thể
    // Trong thực tế, bạn có thể cần lưu trữ thông tin này khi người dùng chọn ghế
    
    // Ví dụ giả định: Phân bổ ghế theo thứ tự loại vé
    let seatIndex = 0;
    for (let i = 0; i < props.ticketDetails.length; i++) {
      const ticket = props.ticketDetails[i];
      for (let j = 0; j < ticket.quantity; j++) {
        if (seatIndex < newSelectedSeats.length) {
          ticketSeatsSelected.value[i]++;
          seatIndex++;
        }
      }
    }
    
    // Tự động chọn loại vé tiếp theo chưa hoàn thành
    updateActiveTicket();
  }
});

// Tự động chọn loại vé chưa hoàn thành
const updateActiveTicket = () => {
  for (let i = 0; i < props.ticketDetails.length; i++) {
    if (ticketSeatsSelected.value[i] < props.ticketDetails[i].quantity) {
      activeTicketIndex.value = i;
      emit('update:active-ticket', {
        index: i,
        type: getTicketType(props.ticketDetails[i])
      });
      return;
    }
  }
};

// Kiểm tra xem có phải là vé trẻ em không
const isChildTicket = (ticket) => {
  const type = ticket.type?.toLowerCase() || '';
  return type === 'trẻ em' || type === 'treem';
};

// Lấy tên hiển thị cho loại vé
const getTicketTypeDisplay = (ticket) => {
  return isChildTicket(ticket) ? 'Trẻ em' : 'Người lớn';
};

// Lấy loại vé
const getTicketType = (ticket) => {
  return isChildTicket(ticket) ? 'child' : 'adult';
};

// Computed để kiểm tra loại vé hiện tại
const isCurrentTicketChild = computed(() => {
  if (activeTicketIndex.value === null) return false;
  return isChildTicket(props.ticketDetails[activeTicketIndex.value]);
});

// Chọn loại vé
const selectTicket = (index) => {
  if (ticketSeatsSelected.value[index] < props.ticketDetails[index].quantity) {
    activeTicketIndex.value = index;
    emit('update:active-ticket', {
      index: index,
      type: getTicketType(props.ticketDetails[index])
    });
  }
};
</script>

<style scoped>
.ticket-selection-container {
  margin-bottom: 20px;
  padding: 15px;
  border-radius: 8px;
  background-color: #f8f9fa;
}

.ticket-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: center;
}

.ticket-btn {
  display: flex;
  align-items: center;
  padding: 10px 15px;
  border-radius: 8px;
  border: 1px solid #dee2e6;
  background-color: white;
  min-width: 150px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.ticket-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.ticket-btn.active {
  border: 2px solid #007bff;
  background-color: #e7f1ff;
}

.ticket-btn.completed {
  background-color: #d4edda;
  border-color: #c3e6cb;
}

.ticket-btn:disabled {
  cursor: not-allowed;
  opacity: 0.7;
}

.ticket-icon {
  margin-right: 10px;
  font-size: 1.5rem;
}

.adult-ticket .ticket-icon {
  color: #007bff;
}

.child-ticket .ticket-icon {
  color: #17a2b8;
}

.ticket-info {
  flex-grow: 1;
  text-align: left;
}

.ticket-type {
  font-weight: bold;
  margin-bottom: 3px;
}

.ticket-progress {
  font-size: 0.85rem;
  color: #6c757d;
}

.ticket-status {
  margin-left: 10px;
}

.ticket-status .bi-check-circle-fill {
  color: #28a745;
}

.ticket-status .bi-circle {
  color: #6c757d;
}

.current-selection {
  text-align: center;
}

/* Thêm hỗ trợ responsive */
@media (max-width: 576px) {
  .ticket-btn {
    width: 100%;
  }
}
</style>