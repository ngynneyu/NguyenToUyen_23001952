'use strict';

const bai3App = Vue.createApp({
  data() {
    return {
      gioHang: [
        { id: 1, ten: 'Bút bi xanh', donGia: 5000, soLuong: 2 },
        { id: 2, ten: 'Vở kẻ ngang', donGia: 12000, soLuong: 3 }
      ],
      loi: '',
      thongBao: ''
    };
  },
  computed: {
    tongSoLuong() { return this.gioHang.reduce((tong, sp) => tong + sp.soLuong, 0); },
    tongTien() { return this.gioHang.reduce((tong, sp) => tong + sp.donGia * sp.soLuong, 0); }
  },
  methods: {
    dinhDangTien(soTien) { return new Intl.NumberFormat('vi-VN').format(soTien) + ' đ'; },
    capNhatSoLuong(id, soLuongMoi) {
      const sp = this.gioHang.find(sp => sp.id === id);
      if (!sp) { this.loi = 'Sản phẩm không tồn tại trong giỏ.'; return false; }
      if (typeof soLuongMoi !== 'number' || !Number.isInteger(soLuongMoi) || soLuongMoi < 1 || soLuongMoi > 99) {
        this.loi = 'Số lượng phải là số nguyên từ 1 đến 99.';
        return false;
      }
      sp.soLuong = soLuongMoi;
      this.loi = '';
      this.thongBao = `Đã cập nhật số lượng “${sp.ten}”.`;
      return true;
    },
    xoaSanPham(id) {
      const sp = this.gioHang.find(sp => sp.id === id);
      if (!sp) { this.loi = 'Sản phẩm không tồn tại trong giỏ.'; return false; }
      if (!window.confirm(`Bạn có chắc muốn xóa “${sp.ten}” khỏi giỏ hàng?`)) return false;
      this.gioHang = this.gioHang.filter(sp => sp.id !== id);
      this.loi = '';
      this.thongBao = `Đã xóa “${sp.ten}” khỏi giỏ.`;
      return true;
    },
    khoiPhucMau() {
      this.gioHang = [
        { id: 1, ten: 'Bút bi xanh', donGia: 5000, soLuong: 2 },
        { id: 2, ten: 'Vở kẻ ngang', donGia: 12000, soLuong: 3 }
      ];
      this.loi = '';
      this.thongBao = 'Đã khôi phục giỏ hàng mẫu.';
    }
  }
});
bai3App.component('dong-gio-hang', DongGioHang);
bai3App.mount('#app');
