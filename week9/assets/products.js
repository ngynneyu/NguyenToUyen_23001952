const diaChiApi = 'api/products.php';
const tienViet = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' });
const dungLuongAnhToiDa = 2 * 1024 * 1024;

function bieuMauTrong() {
  return { name: '', price: '', stock: '' };
}

async function goiApi(url, options) {
  let response;
  let result;
  try {
    response = await fetch(url, options);
    result = await response.json();
  } catch {
    throw new Error('Không thể đọc phản hồi từ API. Hãy kiểm tra máy chủ Apache.');
  }

  if (!response.ok || result?.success !== true) {
    throw new Error(result?.message || 'Yêu cầu không thành công.');
  }
  return result;
}

function luuThongTin(id, sanPham) {
  return goiApi(id === null ? diaChiApi : diaChiApi + '?id=' + encodeURIComponent(id), {
    method: id === null ? 'POST' : 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(sanPham)
  });
}

Vue.createApp({
  data() {
    return {
      sanPham: [],
      phanTrang: { page: 1, limit: 5, total: 0, total_pages: 1 },
      trangYeuCau: 1,
      bieuMau: bieuMauTrong(),
      idDangSua: null,
      anhHienTai: '',
      anhDaChon: null,
      anhXemTruoc: '',
      canThuLai: false,
      dangTai: false,
      dangLuu: false,
      loiDanhSach: false,
      loi: '',
      loiBieuMau: '',
      loiAnh: '',
      thongBao: ''
    };
  },

  mounted() {
    this.taiDanhSach();
  },

  beforeUnmount() {
    this.datLaiAnhDaChon();
  },

  methods: {
    dinhDangTien(gia) {
      return tienViet.format(gia);
    },

    async taiDanhSach(trang = this.trangYeuCau) {
      this.trangYeuCau = trang;
      this.dangTai = true;
      this.loiDanhSach = false;
      this.loi = '';
      try {
        const ketQua = await goiApi(diaChiApi + '?page=' + trang + '&limit=5');
        this.sanPham = ketQua.data;
        this.phanTrang = ketQua.pagination;
        this.trangYeuCau = ketQua.pagination.page;
      } catch (error) {
        this.loiDanhSach = true;
        this.loi = error.message;
      } finally {
        this.dangTai = false;
      }
    },

    suaSanPham(sp) {
      this.huySua();
      this.idDangSua = sp.id;
      this.bieuMau = { name: sp.name, price: sp.price, stock: sp.stock };
      this.anhHienTai = sp.image || '';
      this.loi = '';
      this.loiBieuMau = '';
      this.thongBao = '';
      this.$refs.tenSanPham.focus();
    },

    huySua() {
      this.datLaiAnhDaChon();
      this.idDangSua = null;
      this.bieuMau = bieuMauTrong();
      this.anhHienTai = '';
      this.canThuLai = false;
      this.loiBieuMau = '';
      this.loiAnh = '';
    },

    datLaiAnhDaChon() {
      if (this.anhXemTruoc) URL.revokeObjectURL(this.anhXemTruoc);
      this.anhDaChon = null;
      this.anhXemTruoc = '';
      if (this.$refs.tepAnh) this.$refs.tepAnh.value = '';
    },

    chonAnh(event) {
      const tep = event.target.files[0];
      if (this.anhXemTruoc) URL.revokeObjectURL(this.anhXemTruoc);
      this.anhDaChon = null;
      this.anhXemTruoc = '';
      this.loiAnh = '';
      if (!tep) return;

      const mime = /\.png$/i.test(tep.name) ? 'image/png' : 'image/jpeg';
      if (!/\.(jpe?g|png)$/i.test(tep.name) || (tep.type && tep.type !== mime)) {
        this.loiAnh = 'Chỉ chấp nhận ảnh JPG, JPEG hoặc PNG.';
      } else if (tep.size > dungLuongAnhToiDa) {
        this.loiAnh = 'Ảnh không được lớn hơn 2 MB.';
      } else {
        this.anhDaChon = tep;
        this.anhXemTruoc = URL.createObjectURL(tep);
        return;
      }
      event.target.value = '';
    },

    async taiAnh(id) {
      const formData = new FormData();
      formData.append('id', id);
      formData.append('image', this.anhDaChon);
      try {
        await goiApi('api/upload.php', { method: 'POST', body: formData });
        return true;
      } catch (error) {
        this.canThuLai = true;
        this.loiAnh = error.message;
        return false;
      }
    },

    async thuLaiAnh() {
      if (this.dangLuu || this.dangTai || !this.canThuLai || !this.anhDaChon) return;

      this.dangLuu = true;
      this.loiAnh = '';
      this.thongBao = '';
      try {
        if (await this.taiAnh(this.idDangSua)) {
          this.huySua();
          this.thongBao = 'Đã tải ảnh lên.';
          await this.taiDanhSach(this.phanTrang.page);
        }
      } finally {
        this.dangLuu = false;
      }
    },

    async luuSanPham() {
      if (this.dangLuu || this.dangTai) return;

      const id = this.idDangSua;
      this.dangLuu = true;
      this.loi = '';
      this.loiBieuMau = '';
      this.loiAnh = '';
      this.thongBao = '';
      try {
        const ketQua = await luuThongTin(id, this.bieuMau);
        this.idDangSua = id ?? ketQua.id;
        this.thongBao = id === null ? 'Đã thêm sản phẩm.' : 'Đã cập nhật sản phẩm.';
        if (!this.anhDaChon || await this.taiAnh(this.idDangSua)) {
          this.huySua();
        }
        await this.taiDanhSach(id === null ? 1 : this.phanTrang.page);
      } catch (error) {
        this.loiBieuMau = error.message;
      } finally {
        this.dangLuu = false;
      }
    },

    async xoaSanPham(sp) {
      if (this.dangLuu || !window.confirm('Bạn có muốn xóa sản phẩm “' + sp.name + '” không?')) return;

      this.dangLuu = true;
      this.loi = '';
      this.thongBao = '';
      try {
        await goiApi(diaChiApi + '?id=' + encodeURIComponent(sp.id), { method: 'DELETE' });
        if (this.idDangSua === sp.id) this.huySua();
        this.thongBao = 'Đã xóa sản phẩm.';
        await this.taiDanhSach(this.phanTrang.page);
      } catch (error) {
        this.loi = error.message;
      } finally {
        this.dangLuu = false;
      }
    }
  }
}).mount('#app');
