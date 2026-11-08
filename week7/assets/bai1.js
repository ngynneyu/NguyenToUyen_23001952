'use strict';

const bai1App = Vue.createApp({
  data() {
    return {
      trangThai: 'Đang khởi tạo',
      tuKhoa: '',
      danhMuc: '',
      sapXep: '',
      thongBaoTimKiem: '',
      thongBao: '',
      loi: {},
      idTiepTheo: 7,
      cacDanhMuc: ['Bút & dụng cụ', 'Sổ & giấy', 'Phụ kiện'],
      sanPham: [
        { id: 1, ten: 'Bút bi xanh', gia: 5000, danhMuc: 'Bút & dụng cụ', tonKho: 32 },
        { id: 2, ten: 'Vở kẻ ngang', gia: 12000, danhMuc: 'Sổ & giấy', tonKho: 24 },
        { id: 3, ten: 'Bút chì gỗ', gia: 8000, danhMuc: 'Bút & dụng cụ', tonKho: 0 },
        { id: 4, ten: 'Sổ tay chấm bi', gia: 45000, danhMuc: 'Sổ & giấy', tonKho: 15 },
        { id: 5, ten: 'Túi vải học tập', gia: 89000, danhMuc: 'Phụ kiện', tonKho: 0 },
        { id: 6, ten: 'Đèn bàn mini', gia: 185000, danhMuc: 'Phụ kiện', tonKho: 8 }
      ],
      sanPhamMoi: { ten: '', gia: '', danhMuc: 'Bút & dụng cụ', tonKho: '' }
    };
  },
  computed: {
    sanPhamHienThi() {
      const tuKhoa = this.chuanHoaTimKiem(this.tuKhoa);
      // filter tạo mảng mới; sort không thay đổi mảng sanPham gốc.
      const ketQua = this.sanPham.filter(sp =>
        this.chuanHoaTimKiem(sp.ten).includes(tuKhoa) &&
        (!this.danhMuc || sp.danhMuc === this.danhMuc)
      );
      if (this.sapXep === 'tang') ketQua.sort((a, b) => a.gia - b.gia);
      if (this.sapXep === 'giam') ketQua.sort((a, b) => b.gia - a.gia);
      return ketQua;
    },
    soSanPhamHienThi() { return this.sanPhamHienThi.length; },
    tongSanPham() { return this.sanPham.length; },
    tongGiaTriTonKho() {
      return this.sanPhamHienThi.reduce((tong, sp) => tong + sp.gia * sp.tonKho, 0);
    }
  },
  watch: {
    tuKhoa(giaTri) {
      this.thongBaoTimKiem = Array.from(giaTri).length === 1
        ? 'Từ khóa có 1 ký tự. Nhập thêm để tìm chính xác hơn.' : '';
    }
  },
  mounted() { this.trangThai = 'Sẵn sàng'; },
  methods: {
    dinhDangTien(soTien) { return new Intl.NumberFormat('vi-VN').format(soTien) + ' đ'; },
    xoaBoLoc() { this.tuKhoa = ''; this.danhMuc = ''; this.sapXep = ''; },
    chuanHoaTen(ten) {
      return ten.normalize('NFC').trim().replace(/\s+/g, ' ');
    },
    chuanHoaTimKiem(chuoi) {
      // Bỏ dấu tiếng Việt và chuyển cả đ/Đ thành d để tìm bằng từ khóa không dấu.
      return this.chuanHoaTen(chuoi).normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[đĐ]/g, 'd')
        .toLocaleLowerCase('vi-VN');
    },
    themSanPham() {
      const { ten, gia, danhMuc, tonKho } = this.sanPhamMoi;
      const tenHopLe = typeof ten === 'string' ? this.chuanHoaTen(ten) : '';
      const loi = {};
      if (!tenHopLe) loi.ten = 'Vui lòng nhập tên sản phẩm.';
      // Kiểm tra toàn bộ danh sách, kể cả sản phẩm đang bị ẩn bởi bộ lọc.
      else if (this.sanPham.some(sp => this.chuanHoaTen(sp.ten).toLocaleLowerCase('vi-VN') === tenHopLe.toLocaleLowerCase('vi-VN'))) {
        loi.ten = 'Tên sản phẩm đã tồn tại. Vui lòng nhập tên khác.';
      }
      if (typeof gia !== 'number' || !Number.isFinite(gia) || gia <= 0) loi.gia = 'Giá phải là số lớn hơn 0.';
      if (typeof tonKho !== 'number' || !Number.isSafeInteger(tonKho) || tonKho < 0) loi.tonKho = 'Tồn kho phải là số nguyên không âm.';
      if (!this.cacDanhMuc.includes(danhMuc)) loi.danhMuc = 'Vui lòng chọn danh mục hợp lệ.';
      this.loi = loi;
      this.thongBao = '';
      if (Object.keys(loi).length) return false;
      this.sanPham.push({ id: this.idTiepTheo++, ten: tenHopLe, gia, danhMuc, tonKho });
      this.thongBao = `Đã thêm “${tenHopLe}”. Sản phẩm sẽ hiện nếu phù hợp bộ lọc.`;
      this.sanPhamMoi = { ten: '', gia: '', danhMuc: 'Bút & dụng cụ', tonKho: '' };
      return true;
    },
    xoaSanPham(id) {
      const sp = this.sanPham.find(sp => sp.id === id);
      if (!sp || !window.confirm(`Bạn có chắc muốn xóa “${sp.ten}”?`)) return false;
      this.sanPham = this.sanPham.filter(sp => sp.id !== id);
      this.thongBao = `Đã xóa “${sp.ten}”.`;
      return true;
    }
  }
});
bai1App.mount('#app');
