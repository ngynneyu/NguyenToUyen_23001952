'use strict';

const DongGioHang = {
  props: {
    id: { type: Number, required: true },
    ten: { type: String, required: true },
    donGia: { type: Number, required: true },
    soLuong: { type: Number, required: true }
  },
  emits: ['doi-so-luong', 'yeu-cau-xoa'],
  computed: {
    thanhTien() { return this.donGia * this.soLuong; }
  },
  methods: {
    dinhDangTien(soTien) { return new Intl.NumberFormat('vi-VN').format(soTien) + ' đ'; },
    doiSoLuong(soLuongMoi) {
      // Chỉ gửi yêu cầu; cha là nơi duy nhất thay đổi dữ liệu giỏ hàng.
      this.$emit('doi-so-luong', this.id, soLuongMoi);
    },
    nhapSoLuong(event) {
      const soLuongMoi = Number(event.target.value);
      this.doiSoLuong(soLuongMoi);
      // Khôi phục ô nhập khi dữ liệu không hợp lệ, không gán lại props.
      if (!Number.isInteger(soLuongMoi) || soLuongMoi < 1 || soLuongMoi > 99) {
        event.target.value = this.soLuong;
      }
    }
  },
  template: `
    <article class="cart-row">
      <div class="cart-product">
        <div><h2>{{ ten }}</h2><p class="muted">Đơn giá: {{ dinhDangTien(donGia) }}</p></div>
      </div>
      <div class="quantity-control">
        <button type="button" @click="doiSoLuong(soLuong - 1)" :disabled="soLuong <= 1" :aria-label="'Giảm số lượng ' + ten">−</button>
        <input type="number" min="1" max="99" step="1" :value="soLuong" @input="nhapSoLuong" :aria-label="'Số lượng ' + ten">
        <button type="button" @click="doiSoLuong(soLuong + 1)" :disabled="soLuong >= 99" :aria-label="'Tăng số lượng ' + ten">+</button>
      </div>
      <strong class="line-total">{{ dinhDangTien(thanhTien) }}</strong>
      <button type="button" class="delete-button" @click="$emit('yeu-cau-xoa', id)" :aria-label="'Xóa ' + ten">Xóa</button>
    </article>`
};
