import { computed, ref } from "vue";
import { ElNotification } from "element-plus";

// Composable để quản lý logic chọn ghế/giường và đặt vé, tái sử dụng cho Seat.vue và Bed.vue
export function useTicketSelection(props, emit, options = {}) {
  // Destructuring options với giá trị mặc định
  // - itemType: Tên loại item (Ghế/Giường) để tùy chỉnh thông báo
  // - updateEvent: Sự kiện emit khi cập nhật danh sách ghế/giường đã chọn
  const { itemType = "item", updateEvent = "update:selections" } = options;

  // Tạo danh sách vé dựa trên ticketDetails và selectedSeatsByCar
  // Mỗi vé chứa thông tin: type (Người lớn/Trẻ em), ticketNumber, sohieu (ghế/giường), carType (toa)
  const ticketList = computed(() => {
    const list = [];
    // Duyệt qua ticketDetails để tạo vé cho từng loại (Người lớn, Trẻ em)
    props.ticketDetails.forEach((detail) => {
      for (let i = 0; i < detail.count; i++) {
        // Khởi tạo vé với các giá trị mặc định
        const ticket = {
          type: detail.type,
          ticketNumber: i + 1,
          sohieu: null,
          carType: null,
        };
        // Kiểm tra selectedSeatsByCar để gán ghế/giường đã chọn cho vé
        Object.entries(props.selectedSeatsByCar).forEach(([carType, items]) => {
          const item = items.find(
            (s) => s.ticketType === detail.type && s.ticketNumber === i + 1
          );
          if (item) {
            ticket.sohieu = item.sohieu;
            ticket.carType = carType.split(":")[0]; // Lấy tên toa (bỏ phần loại toa)
          }
        });
        list.push(ticket);
      }
    });
    return list;
  });

  // Quản lý chỉ số vé hiện tại đang được chọn
  // Dùng ref để theo dõi trạng thái thay đổi
  const currentTicketIndex = ref(0);

  // Hàm chọn vé để gán ghế/giường
  // Cập nhật currentTicketIndex khi người dùng nhấn vào nút vé
  const selectTicket = (index) => {
    currentTicketIndex.value = index;
  };

  // Hàm chọn hoặc bỏ chọn ghế/giường
  // - itemNumber: Mã ghế/giường (sohieu)
  // - items: Danh sách ghế/giường từ props (seats hoặc beds)
  // - selectedItems: Danh sách ghế/giường đã chọn trong toa hiện tại
  const toggleSelection = (itemNumber, items, selectedItems) => {
    // Tìm ghế/giường theo sohieu
    const item = items?.find((s) => s.sohieu === itemNumber);
    // Thoát nếu không tìm thấy hoặc ghế/giường đã được đặt
    if (!item || item.trangthai === "dadat" || item.trangthai === "Đã đặt") return;

    // Sao chép danh sách đã chọn để tránh thay đổi trực tiếp
    const newSelectedItems = [...selectedItems];
    // Lấy thông tin vé hiện tại
    const currentTicket = ticketList.value[currentTicketIndex.value];

    // Kiểm tra xem ghế/giường đã được chọn bởi vé khác trong cùng toa chưa
    const currentCarSelections = props.selectedSeatsByCar[props.car.type] || [];
    const isItemTaken = currentCarSelections.some(
      (s) =>
        s.sohieu === itemNumber &&
        (s.ticketType !== currentTicket.type ||
         s.ticketNumber !== currentTicket.ticketNumber)
    );
    if (isItemTaken) {
      ElNotification.error(`${itemType} này đã được chọn cho vé khác!`);
      return;
    }

    // Kiểm tra ràng buộc vé trẻ em: phải có vé người lớn trong cùng toa
    if (currentTicket.type === "Trẻ em") {
      const hasAdult = currentCarSelections.some((s) => s.ticketType === "Người lớn");
      if (!hasAdult) {
        ElNotification.error(`Vé trẻ em phải được chọn trong toa có vé người lớn!`);
        return;
      }
    }

    // Điều chỉnh giá cho vé trẻ em (75% giá gốc)
    // Lưu ý: Có thể cấu hình tỷ lệ giá trong tương lai nếu cần
    const adjustedPrice = currentTicket.type === "Trẻ em" ? item.gia * 0.75 : item.gia;

    // Kiểm tra xem vé hiện tại đã được gán ghế/giường chưa
    const existingItemIndex = newSelectedItems.findIndex(
      (s) =>
        s.ticketType === currentTicket.type &&
        s.ticketNumber === currentTicket.ticketNumber
    );

    if (existingItemIndex !== -1) {
      // Nếu ghế/giường đang chọn trùng với ghế/giường đã chọn, bỏ chọn nó
      if (newSelectedItems[existingItemIndex].sohieu === itemNumber) {
        newSelectedItems.splice(existingItemIndex, 1);
      } else {
        // Thay thế ghế/giường cũ bằng ghế/giường mới
        newSelectedItems[existingItemIndex] = {
          sohieu: itemNumber,
          ticketType: currentTicket.type,
          ticketNumber: currentTicket.ticketNumber,
          gia: adjustedPrice,
        };
      }
    } else {
      // Nếu vé chưa có ghế/giường và còn slot, thêm mới
      if (props.totalSelected < props.totalTickets) {
        newSelectedItems.push({
          sohieu: itemNumber,
          ticketType: currentTicket.type,
          ticketNumber: currentTicket.ticketNumber,
          gia: adjustedPrice,
        });
      } else {
        // Thông báo lỗi nếu vượt quá số lượng vé cho phép
        ElNotification.error(
          `Bạn chỉ có thể chọn tối đa ${props.totalTickets} ${itemType.toLowerCase()} trên toàn tàu!`
        );
        return;
      }
    }

    // Emit danh sách ghế/giường đã cập nhật
    emit(updateEvent, newSelectedItems);
  };

  // Hàm đặt vé, gửi payload lên component cha (Booking.vue)
  // - selectedItems: Danh sách ghế/giường đã chọn
  // - totalPrice: Tổng giá vé (từ useTotalPrice)
  const bookTickets = (selectedItems, totalPrice) => {
    if (selectedItems.length > 0) {
      const payload = {
        carType: props.car.type, // Loại toa
        items: selectedItems,    // Danh sách ghế/giường
        totalPrice: totalPrice.value // Tổng giá
      };
      emit("book", payload);
    }
  };

  // Trả về các giá trị và hàm để component sử dụng
  return {
    ticketList,
    currentTicketIndex,
    selectTicket,
    toggleSelection,
    bookTickets,
  };
}