'use strict';

// Được nạp bởi thẻ script và đăng ký trước khi ứng dụng cha mount.
const TheSanPham = {
  props: {
    ten: { type: String, required: true },
    gia: { type: Number, required: true, validator: value => Number.isFinite(value) && value > 0 },
    anh: { type: String, default: 'assets/images/notebook.svg' },
    moTa: { type: String, default: 'Chưa có mô tả cho sản phẩm này.' },
    tonKho: { type: Number, default: 0, validator: value => Number.isSafeInteger(value) && value >= 0 }
  },
  emits: ['mua'],
  data() {
    // Mỗi lần tạo component đều có trạng thái riêng.
    return { hienMoTa: false };
  },
  methods: {
    dinhDangTien(soTien) { return new Intl.NumberFormat('vi-VN').format(soTien) + ' đ'; }
  },
  template: `
    <article class="product-card">
      <div class="product-image">
        <img :src="anh" :alt="ten" width="320" height="240">
      </div>
      <div class="product-content">
        <h2>{{ ten }}</h2>
        <p class="product-status"><span class="badge" :class="tonKho > 0 ? 'available' : 'sold-out'">
          {{ tonKho > 0 ? 'Còn hàng' : 'Hết hàng' }}
        </span></p>
        <div class="product-price-row"><strong class="price">{{ dinhDangTien(gia) }}</strong><span class="muted">Tồn: {{ tonKho }}</span></div>
        <button type="button" class="description-toggle" @click="hienMoTa = !hienMoTa" :aria-expanded="hienMoTa" :aria-label="(hienMoTa ? 'Ẩn mô tả ' : 'Xem mô tả ') + ten">
          {{ hienMoTa ? 'Ẩn mô tả' : 'Xem mô tả' }}
        </button>
        <p v-show="hienMoTa" class="product-description">{{ moTa }}</p>
        <button type="button" class="button primary buy-button" :disabled="tonKho === 0" @click="$emit('mua')" :aria-label="'Mua ' + ten">Mua</button>
      </div>
    </article>`
};
