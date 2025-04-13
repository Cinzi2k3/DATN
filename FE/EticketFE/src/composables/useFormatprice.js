
export function useFormatPrice() {
  // Định dạng giá ghế: 300000 → 300k
  const formatPrice = (price) => {
    if (!price) return '';
    if (price >= 1000) {
      return `${Math.round(price / 1000)}k`;
    } else {
      return `${price}`;
    }
  };

  // Định dạng tổng số tiền: 300000 → 300.000đ
  const formatTotalPrice = (price) => {
    if (!price) return '';
    return new Intl.NumberFormat('vi-VN', {
      style: 'decimal',
      minimumFractionDigits: 0,
    })
      .format(price)
      .replace(/,/g, '.') + 'đ';
  };

  return { formatPrice, formatTotalPrice };
}