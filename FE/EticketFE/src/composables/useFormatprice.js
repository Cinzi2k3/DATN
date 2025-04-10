export function useFormatPrice() {
    const formatPrice = (price) => {
      if (!price) return '';
      if (price >= 1000) {
        return `${Math.round(price / 1000)}k`;
      } else {
        return `${price}`;
      }
    };
    return { formatPrice };
  }