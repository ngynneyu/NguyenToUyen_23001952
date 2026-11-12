const diaChiApi = 'api/products.php';
const tienViet = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' });

function bieuMauTrong() {
  return { name: '', price: '', stock: '' };
}

async function goiApi(method = 'GET', id = null, product = null) {
  const url = id === null ? diaChiApi : diaChiApi + '?id=' + encodeURIComponent(id);
  const options = { method };

  if (product !== null) {
    options.headers = { 'Content-Type': 'application/json' };
    options.body = JSON.stringify(product);
  }

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

Vue.createApp({
  data() {
    return {
      sanPham: [],
      bieuMau: bieuMauTrong(),
      idDangSua: null,
      dangTai: false,
      dangLuu: false,
      loiDanhSach: false,
      loi: '',
      loiBieuMau: '',
      thongBao: ''
    };
  },

  mounted() {
    this.taiDanhSach();
  },

  methods: {
    dinhDangTien(gia) {
      return tienViet.format(gia);
    },

    async taiDanhSach() {
      this.dangTai = true;
      this.loiDanhSach = false;
      this.loi = '';
      try {
        const ketQua = await goiApi();
        this.sanPham = ketQua.data;
      } catch (error) {
        this.loiDanhSach = true;
        this.loi = error.message;
      } finally {
        this.dangTai = false;
      }
    },

    suaSanPham(sp) {
      this.idDangSua = sp.id;
      this.bieuMau = { name: sp.name, price: sp.price, stock: sp.stock };
      this.loi = '';
      this.loiBieuMau = '';
      this.thongBao = '';
      this.$refs.tenSanPham.focus();
    },

    huySua() {
      this.idDangSua = null;
      this.bieuMau = bieuMauTrong();
      this.loiBieuMau = '';
    },

    async luuSanPham() {
      if (this.dangLuu || this.dangTai) return;

      const dangSua = this.idDangSua !== null;
      this.dangLuu = true;
      this.loi = '';
      this.loiBieuMau = '';
      this.thongBao = '';
      try {
        await goiApi(dangSua ? 'PUT' : 'POST', this.idDangSua, this.bieuMau);
        this.huySua();
        this.thongBao = dangSua ? 'Đã cập nhật sản phẩm.' : 'Đã thêm sản phẩm.';
        await this.taiDanhSach();
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
        await goiApi('DELETE', sp.id);
        if (this.idDangSua === sp.id) this.huySua();
        this.thongBao = 'Đã xóa sản phẩm.';
        await this.taiDanhSach();
      } catch (error) {
        this.loi = error.message;
      } finally {
        this.dangLuu = false;
      }
    }
  }
}).mount('#app');
