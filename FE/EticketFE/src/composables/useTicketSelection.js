import { computed, ref } from "vue";
import { ElNotification } from "element-plus";

export function useTicketSelection(props, emit, options = {}) {
  const { itemType = "item", updateEvent = "update:selections" } = options;

  const ticketList = computed(() => {
    const list = [];
    props.ticketDetails.forEach((detail) => {
      for (let i = 0; i < detail.count; i++) {
        const ticket = {
          type: detail.type,
          ticketNumber: i + 1,
          sohieu: null,
          carType: null,
        };
        Object.entries(props.selectedSeatsByCar).forEach(([carType, items]) => {
          const item = items.find(
            (s) => s.ticketType === detail.type && s.ticketNumber === i + 1
          );
          if (item) {
            ticket.sohieu = item.sohieu;
            ticket.carType = carType.split(":")[0];
          }
        });
        list.push(ticket);
      }
    });
    return list;
  });

  const currentTicketIndex = ref(0);

  const selectTicket = (index) => {
    currentTicketIndex.value = index;
  };

  const toggleSelection = (itemNumber, items, selectedItems) => {
    const item = items?.find((s) => s.sohieu === itemNumber);
    if (!item) {
      ElNotification.error(`Không tìm thấy ${itemType.toLowerCase()} này!`);
      return;
    }
    if (item.trangthai === "dadat") {
      ElNotification.warning(`${itemType} này đã được đặt!`);
      return;
    }
    if (item.trangthai === "danggiu") {
      ElNotification.warning(`${itemType} này đang được giữ, vui lòng chọn ${itemType.toLowerCase()} khác!`);
      return;
    }

    const newSelectedItems = [...selectedItems];
    const currentTicket = ticketList.value[currentTicketIndex.value];

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

    if (currentTicket.type === "Trẻ em") {
      const hasAdult = currentCarSelections.some((s) => s.ticketType === "Người lớn") ||
                       newSelectedItems.some((s) => s.ticketType === "Người lớn");
      if (!hasAdult) {
        ElNotification.error(`Vé trẻ em phải được chọn trong toa có vé người lớn!`);
        return;
      }
    }

    const adjustedPrice = currentTicket.type === "Trẻ em" ? item.gia * 0.75 : item.gia;

    const existingItemIndex = newSelectedItems.findIndex(
      (s) =>
        s.ticketType === currentTicket.type &&
        s.ticketNumber === currentTicket.ticketNumber
    );

    if (existingItemIndex !== -1) {
      if (newSelectedItems[existingItemIndex].sohieu === itemNumber) {
        newSelectedItems.splice(existingItemIndex, 1);
      } else {
        newSelectedItems[existingItemIndex] = {
          sohieu: itemNumber,
          ticketType: currentTicket.type,
          ticketNumber: currentTicket.ticketNumber,
          gia: adjustedPrice,
        };
      }
    } else {
      if (props.totalSelected < props.totalTickets) {
        newSelectedItems.push({
          sohieu: itemNumber,
          ticketType: currentTicket.type,
          ticketNumber: currentTicket.ticketNumber,
          gia: adjustedPrice,
        });
      } else {
        ElNotification.error(
          `Bạn chỉ có thể chọn tối đa ${props.totalTickets} ${itemType.toLowerCase()} trên toàn tàu!`
        );
        return;
      }
    }

    emit(updateEvent, newSelectedItems);
  };

  const bookTickets = (selectedItems, totalPrice) => {
    if (selectedItems.length === 0) {
      ElNotification.error(`Vui lòng chọn ít nhất một ${itemType.toLowerCase()}!`);
      return;
    }
    if (selectedItems.length !== props.totalTickets) {
      ElNotification.error(`Vui lòng chọn đủ ${props.totalTickets} ${itemType.toLowerCase()}!`);
      return;
    }

    // Kiểm tra xem sử dụng seats hay beds dựa trên itemType
    const items = itemType === "Giường" ? props.beds : props.seats;

    if (!items || !Array.isArray(items)) {
      ElNotification.error(`Không tìm thấy dữ liệu ${itemType.toLowerCase()} để kiểm tra trạng thái!`);
      return;
    }

    const invalidItems = selectedItems.filter((item) =>
      items.some((s) => s.sohieu === item.sohieu && (s.trangthai === "dadat" || s.trangthai === "danggiu"))
    );
    if (invalidItems.length > 0) {
      ElNotification.error(
        `Một số ${itemType.toLowerCase()} đã được đặt hoặc đang giữ: ${invalidItems.map((i) => i.sohieu).join(", ")}`
      );
      return;
    }

    const payload = {
      carType: props.car.type,
      items: selectedItems,
      totalPrice: totalPrice.value,
      malichtrinh: props.malichtrinh
    };
    emit("book", payload);
  };

  return {
    ticketList,
    currentTicketIndex,
    selectTicket,
    toggleSelection,
    bookTickets,
  };
}