'use strict';

Vue.createApp({
  data() {
    return {
      movieTitle: 'Doraemon: Nobita và bản giao hưởng Địa Cầu',
      showtimes: ['09:00', '14:00', '19:30'],
      customerName: '',
      showtime: '09:00',
      ticketType: 'standard',
      quantity: 1,
      withCombo: false,
      comboPrice: 45000,
      confirmation: null
    };
  },
  computed: {
    ticketPrice() {
      return this.ticketType === 'vip' ? 100000 : 70000;
    },
    quantityValid() {
      // .number vẫn trả về chuỗi rỗng khi xóa input, nên kiểm tra cả kiểu và số nguyên.
      return Number.isInteger(this.quantity) && this.quantity >= 1 && this.quantity <= 10;
    },
    ticketSubtotal() {
      return this.quantityValid ? this.ticketPrice * this.quantity : 0;
    },
    total() {
      return this.ticketSubtotal + (this.withCombo ? this.comboPrice : 0);
    },
    canBook() {
      return this.customerName.trim().length > 0 && this.quantityValid
        && this.showtimes.includes(this.showtime)
        && ['standard', 'vip'].includes(this.ticketType);
    }
  },
  methods: {
    formatMoney(amount) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    },
    increaseQuantity() {
      this.quantity = this.quantityValid ? Math.min(10, this.quantity + 1) : 1;
    },
    decreaseQuantity() {
      this.quantity = this.quantityValid ? Math.max(1, this.quantity - 1) : 1;
    },
    bookTickets() {
      if (!this.canBook) return;
      // Sao chép giá trị lúc gửi để xác nhận không đổi khi tiếp tục sửa form.
      this.confirmation = {
        customerName: this.customerName.trim(),
        movieTitle: this.movieTitle,
        showtime: this.showtime,
        ticketLabel: this.ticketType === 'vip' ? 'Vé VIP' : 'Vé thường',
        quantity: this.quantity,
        withCombo: this.withCombo,
        total: this.total
      };
    },
    resetForm() {
      this.customerName = '';
      this.showtime = this.showtimes[0];
      this.ticketType = 'standard';
      this.quantity = 1;
      this.withCombo = false;
      this.confirmation = null;
    }
  }
}).mount('#booking-app');
