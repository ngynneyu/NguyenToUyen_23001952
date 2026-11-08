'use strict';

const bai2App = Vue.createApp({
  data() {
    return {
      thongBao: '',
      loi: '',
      sanPham: [
        { id: 1, ten: 'Bút bi xanh', gia: 5000, anh: 'assets/images/pen.svg', moTa: 'Mực xanh đều màu, đầu bi 0,5 mm. Thân bút nhẹ, vừa tay cho những buổi học dài.', tonKho: 32 },
        { id: 2, ten: 'Vở kẻ ngang', gia: 12000, anh: 'assets/images/notebook.svg', moTa: 'Vở 120 trang với giấy màu kem dịu mắt, dòng kẻ rõ ràng và bìa xanh tối giản.', tonKho: 24 },
        { id: 3, ten: 'Túi vải học tập', gia: 89000, anh: 'assets/images/bag.svg', moTa: 'Túi vải canvas có quai chắc chắn, đủ chỗ cho sổ, sách và những món đồ dùng mỗi ngày.', tonKho: 0 },
        { id: 4, ten: 'Đèn bàn mini', gia: 185000, anh: 'assets/images/lamp.svg', moTa: 'Đèn để bàn nhỏ gọn, chụp đèn xoay linh hoạt. Một điểm sáng ấm áp cho góc học tập.', tonKho: 8 },
        { id: 5, ten: 'Hộp bút', gia: 80000, anh: 'assets/images/pencil-case.svg', moTa: 'Hộp bút vải có khóa kéo, đựng được bút, tẩy và các dụng cụ học tập nhỏ.', tonKho: 13 },
        { id: 6, ten: 'Thước kẻ', gia: 10000, anh: 'assets/images/ruler.svg', moTa: 'Thước gỗ dài 20 cm, vạch chia rõ ràng, tiện dùng để đo và kẻ đường thẳng.', tonKho: 25 }
      ]
    };
  },
  methods: {
    chonMua(id) {
      this.thongBao = '';
      this.loi = '';
      const sp = this.sanPham.find(sp => sp.id === id);
      if (!sp) { this.loi = 'Sản phẩm không tồn tại.'; return false; }
      if (sp.tonKho <= 0) { this.loi = `“${sp.ten}” đã hết hàng.`; return false; }
      // Cha cập nhật dữ liệu; component con chỉ phát yêu cầu mua.
      sp.tonKho -= 1;
      this.thongBao = `Đã mua 1 “${sp.ten}”. Còn lại ${sp.tonKho} sản phẩm.`;
      return true;
    }
  }
});
bai2App.component('the-san-pham', TheSanPham);
bai2App.mount('#app');
