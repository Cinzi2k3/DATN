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

  const currentTicket = ticketList.value[currentTicketIndex.value];
  const currentCarType = props.car.type;

  // Kiểm tra xem ghế đã được chọn cho vé khác trong CÙNG toa hay chưa
  const currentCarSelections = props.selectedSeatsByCar[currentCarType] || [];
  const isItemTakenInCurrentCar = currentCarSelections.some(
    (s) =>
      s.sohieu === itemNumber &&
      (s.ticketType !== currentTicket.type ||
       s.ticketNumber !== currentTicket.ticketNumber)
  );

  if (isItemTakenInCurrentCar) {
    ElNotification.error(`${itemType} này đã được chọn cho vé khác trong toa ${currentCarType}!`);
    return;
  }

  // Kiểm tra vé trẻ em
  if (currentTicket.type === "Trẻ em") {
    const hasAdult = Object.values(props.selectedSeatsByCar).some((carItems) =>
      carItems.some((s) => s.ticketType === "Người lớn")
    );
    if (!hasAdult) {
      ElNotification.error(`Vé trẻ em phải được chọn trong toa có vé người lớn!`);
      return;
    }
  }

  // Lấy discount từ props.ticketDetails
  const ticketDetail = props.ticketDetails.find(
    (detail) => detail.type === currentTicket.type
  );
  let discount = ticketDetail?.discount || 0;
  if (discount > 1) {
    discount = discount / 100;
  }

  // Tính giá sau giảm giá
  const adjustedPrice = item.gia * (1 - discount);
  console.log(`Giá gốc: ${item.gia}, Discount: ${discount}, Giá sau giảm: ${adjustedPrice}`);

  const newSelectedItems = [...selectedItems];
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
        carType: currentCarType,
      };
    }
  } else {
    if (props.totalSelected < props.totalTickets) {
      newSelectedItems.push({
        sohieu: itemNumber,
        ticketType: currentTicket.type,
        ticketNumber: currentTicket.ticketNumber,
        gia: adjustedPrice,
        carType: currentCarType,
      });
    } else {
      ElNotification.error(
        `Bạn chỉ có thể chọn tối đa ${props.totalTickets} ${itemType.toLowerCase()} trên toàn tàu!`
      );
      return;
    }
  }

  console.log("newSelectedItems:", newSelectedItems);
  emit(updateEvent, newSelectedItems);
};

  const bookTickets = (selectedItems, totalPrice) => {
  if (selectedItems.length === 0) {
    ElNotification.error(`Vui lòng chọn ít nhất một ${itemType.toLowerCase()}!`);
    return;
  }

  const totalSelectedAcrossAllCars = Object.values(props.selectedSeatsByCar || {})
    .flat()
    .filter((item) => item && item.sohieu).length;

  if (totalSelectedAcrossAllCars !== props.totalTickets) {
    ElNotification.error(
      `Vui lòng chọn đủ ${props.totalTickets} ${itemType.toLowerCase()}! Hiện tại đã chọn: ${totalSelectedAcrossAllCars}/${props.totalTickets}`
    );
    return;
  }

  const itemsByCar = itemType === "Giường" ? props.beds : props.seats;

  if (!itemsByCar || typeof itemsByCar !== "object") {
    ElNotification.error(`Không tìm thấy dữ liệu ${itemType.toLowerCase()} để kiểm tra trạng thái!`);
    return;
  }

  const invalidItems = [];
  Object.entries(props.selectedSeatsByCar || {}).forEach(([carType, carItems]) => {
    const items = itemsByCar[carType] || []; // Lấy danh sách ghế/giường của toa
    carItems.forEach((selectedItem) => {
      const itemData = items.find((s) => s.sohieu === selectedItem.sohieu);
      if (itemData && (itemData.trangthai === "dadat" || itemData.trangthai === "danggiu")) {
        invalidItems.push({ ...selectedItem, carType });
      }
    });
  });

  if (invalidItems.length > 0) {
    ElNotification.error(
      `Một số ${itemType.toLowerCase()} đã được đặt hoặc đang giữ: ${invalidItems
        .map((i) => `${i.sohieu} (toa ${i.carType})`)
        .join(", ")}`
    );
    return;
  }

  const totalPriceAllCars = Object.values(props.selectedSeatsByCar || {})
    .flat()
    .reduce((sum, item) => sum + (item.gia || 0), 0);

  const payload = {
    carType: props.car.type, // Có thể cần gửi danh sách tất cả toa
    items: Object.values(props.selectedSeatsByCar || {}).flat(),
    totalPrice: totalPriceAllCars,
    malichtrinh: props.malichtrinh,
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