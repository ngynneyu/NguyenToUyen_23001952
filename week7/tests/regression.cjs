'use strict';

// Kiểm tra các yêu cầu nghiệp vụ bằng reactivity thật của bản Vue cục bộ.
// Không cần npm, DOM giả hoặc cài thêm thư viện.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.resolve(__dirname, '..');
const runtime = vm.createContext({ console });
vm.runInContext(fs.readFileSync(path.join(root, 'assets/vendor/vue.global.prod.js'), 'utf8'), runtime);
const Vue = runtime.Vue;
let checks = 0;
let confirmResult = false;
let confirmCalls = 0;
const equal = (actual, expected, description) => { assert.equal(actual, expected, description); checks++; };
const ok = (value, description) => { assert.ok(value, description); checks++; };
const loadDefinition = (file, name) => vm.runInNewContext(fs.readFileSync(path.join(root, file), 'utf8') + '\n' + name, { console });

function loadApp(file, definitions = {}) {
  let options;
  let mounted = false;
  const registered = {};
  const context = {
    ...definitions,
    window: { confirm() { confirmCalls++; return confirmResult; } },
    Vue: { createApp(value) {
      options = value;
      return {
        component(name, definition) { assert.equal(mounted, false, 'Đăng ký trước mount'); registered[name] = definition; return this; },
        mount() { mounted = true; }
      };
    } }
  };
  vm.runInNewContext(fs.readFileSync(path.join(root, file), 'utf8'), context);
  const model = Vue.reactive(options.data());
  for (const [name, method] of Object.entries(options.methods || {})) model[name] = method.bind(model);
  for (const [name, getter] of Object.entries(options.computed || {})) {
    const value = Vue.computed(() => getter.call(model));
    Object.defineProperty(model, name, { get: () => value.value });
  }
  for (const [name, handler] of Object.entries(options.watch || {})) Vue.watch(() => model[name], value => handler.call(model, value));
  if (options.mounted) options.mounted.call(model);
  return { model, options, registered };
}

(async () => {
  equal(Vue.version, '3.5.13', 'Vue 3 chạy cục bộ');
  const { model: inventory } = loadApp('assets/bai1.js');
  equal(inventory.trangThai, 'Sẵn sàng', 'mounted đặt trạng thái');
  ok(inventory.sanPham.length >= 6, 'Có ít nhất 6 sản phẩm');
  equal(new Set(inventory.sanPham.map(sp => sp.danhMuc)).size, 3, 'Đủ 3 danh mục');
  equal(inventory.tongGiaTriTonKho, 2603000, 'Tổng tồn kho ban đầu');
  const original = inventory.sanPham.map(sp => sp.id).join(',');
  inventory.tuKhoa = 'BÚT';
  inventory.danhMuc = 'Bút & dụng cụ';
  inventory.sapXep = 'giam';
  equal(inventory.soSanPhamHienThi, 2, 'Kết hợp tìm không phân biệt hoa thường và lọc');
  equal(inventory.sanPhamHienThi[0].gia, 8000, 'Giá giảm dần');
  equal(inventory.tongGiaTriTonKho, 160000, 'Thống kê chỉ tính dữ liệu sau lọc');
  inventory.sapXep = 'tang';
  equal(inventory.sanPhamHienThi[0].gia, 5000, 'Giá tăng dần');
  equal(inventory.sanPham.map(sp => sp.id).join(','), original, 'Sắp xếp không làm đổi thứ tự dữ liệu gốc');
  inventory.tuKhoa = 'không có sản phẩm này';
  equal(inventory.soSanPhamHienThi, 0, 'Tìm không thấy: số hiển thị bằng 0');
  equal(inventory.tongGiaTriTonKho, 0, 'Tìm không thấy: giá trị tồn bằng 0');
  equal(inventory.tongSanPham, 6, 'Tổng sản phẩm không thay đổi khi lọc');
  inventory.tuKhoa = 'b'; await Vue.nextTick();
  ok(inventory.thongBaoTimKiem.length > 0, 'watch thông báo khi đúng 1 ký tự');
  inventory.tuKhoa = 'bú'; await Vue.nextTick();
  equal(inventory.thongBaoTimKiem, '', 'watch xóa thông báo khi 2 ký tự');
  inventory.tuKhoa = 'x'; await Vue.nextTick();
  inventory.tuKhoa = ''; await Vue.nextTick();
  equal(inventory.thongBaoTimKiem, '', 'watch xóa thông báo khi rỗng');
  inventory.xoaBoLoc();
  equal(inventory.soSanPhamHienThi, 6, 'Đặt lại bộ lọc');
  const valid = { ten: '  Bút dạ quang  ', gia: 15000, danhMuc: 'Bút & dụng cụ', tonKho: 0 };
  for (const [key, values] of Object.entries({ ten: ['', '   '], gia: ['', 0, -1, NaN, Infinity, '5000'], tonKho: ['', -1, 1.5, NaN, Infinity, '2'], danhMuc: ['Sai danh mục'] })) {
    for (const value of values) {
      inventory.sanPhamMoi = { ...valid, [key]: value };
      equal(inventory.themSanPham(), false, `Từ chối ${key} = ${String(value)}`);
      equal(inventory.tongSanPham, 6, 'Không thêm dữ liệu sai');
    }
  }
  inventory.sanPhamMoi = { ...valid };
  equal(inventory.themSanPham(), true, 'Cho phép tồn kho bằng 0');
  equal(inventory.sanPham.at(-1).ten, 'Bút dạ quang', 'Cắt khoảng trắng tên mới');
  const addedId = inventory.sanPham.at(-1).id;
  confirmResult = false;
  equal(inventory.xoaSanPham(addedId), false, 'Hủy xác nhận thì không xóa');
  equal(inventory.tongSanPham, 7, 'Giữ sản phẩm khi hủy');
  confirmResult = true;
  equal(inventory.xoaSanPham(addedId), true, 'Đồng ý xác nhận thì xóa');
  equal(confirmCalls, 2, 'Hỏi xác nhận trước khi xóa');
  equal(inventory.tongSanPham, 6, 'Cập nhật tổng sau xóa');
  inventory.sanPhamMoi = { ...valid, tonKho: 2 };
  inventory.themSanPham();
  ok(inventory.sanPham.at(-1).id > addedId, 'ID không bị trùng sau thêm/xóa');
  for (const sp of [...inventory.sanPham]) inventory.xoaSanPham(sp.id);
  equal(inventory.tongSanPham, 0, 'Xóa hết: tổng sản phẩm 0');
  equal(inventory.soSanPhamHienThi, 0, 'Xóa hết: số hiển thị 0');
  equal(inventory.tongGiaTriTonKho, 0, 'Xóa hết: tồn kho 0');

  const { model: uniqueInventory } = loadApp('assets/bai1.js');
  const originalProducts = JSON.stringify(uniqueInventory.sanPham);
  uniqueInventory.tuKhoa = 'Không có kết quả';
  uniqueInventory.danhMuc = 'Sổ & giấy';
  for (const ten of ['Bút bi xanh', '  BÚT BI XANH  ', 'Bút   bi xanh', 'Bút\tbi xanh', 'Bút bi xanh'.normalize('NFD')]) {
    uniqueInventory.sanPhamMoi = { ten, gia: 9000, danhMuc: 'Phụ kiện', tonKho: 5 };
    equal(uniqueInventory.themSanPham(), false, 'Chặn tên trùng dù khác hoa/thường, khoảng trắng, Unicode, giá hoặc danh mục');
    equal(uniqueInventory.loi.ten, 'Tên sản phẩm đã tồn tại. Vui lòng nhập tên khác.', 'Báo lỗi tại trường tên');
    equal(JSON.stringify(uniqueInventory.sanPham), originalProducts, 'Không thêm, gộp hoặc sửa tồn kho khi trùng tên');
    equal(uniqueInventory.idTiepTheo, 7, 'Tên trùng không tiêu thụ ID mới');
    equal(uniqueInventory.sanPhamMoi.ten, ten, 'Giữ biểu mẫu để người dùng sửa tên');
  }
  uniqueInventory.sanPhamMoi = { ten: '  Hộp   bút  ', gia: 80000, danhMuc: 'Bút & dụng cụ', tonKho: 0 };
  equal(uniqueInventory.themSanPham(), true, 'Thêm được tên mới khi bộ lọc không hiển thị sản phẩm');
  equal(uniqueInventory.sanPham.at(-1).ten, 'Hộp bút', 'Chuẩn hóa khoảng trắng tên mới');
  uniqueInventory.sanPhamMoi = { ten: 'HỘP BÚT', gia: 80000, danhMuc: 'Bút & dụng cụ', tonKho: 13 };
  equal(uniqueInventory.themSanPham(), false, 'Chặn thêm Hộp bút lần hai như trường hợp người dùng báo');
  equal(uniqueInventory.tongSanPham, 7, 'Tên trùng không tạo dòng thứ tám');
  equal(uniqueInventory.sanPham.at(-1).tonKho, 0, 'Không tự cộng tồn kho khi tên trùng');
  uniqueInventory.sanPhamMoi.ten = 'Hộp bút lớn';
  equal(uniqueInventory.themSanPham(), true, 'Sửa thành tên khác thì thêm được');
  equal(Object.keys(uniqueInventory.loi).length, 0, 'Xóa lỗi sau khi thêm tên hợp lệ');
  equal(uniqueInventory.sanPham.at(-1).id, 8, 'ID liên tục sau khi từ chối tên trùng');

  const TheSanPham = loadDefinition('components/TheSanPham.js', 'TheSanPham');
  const catalog = loadApp('assets/bai2.js', { TheSanPham });
  equal(catalog.model.sanPham.length, 6, 'Danh mục có 6 sản phẩm sau khi bổ sung');
  equal(catalog.registered['the-san-pham'], TheSanPham, 'Đúng tên đăng ký component');
  const firstCard = TheSanPham.data();
  const secondCard = TheSanPham.data();
  firstCard.hienMoTa = true;
  equal(secondCard.hienMoTa, false, 'Mỗi component có data độc lập');
  for (const name of ['ten', 'gia', 'anh', 'moTa', 'tonKho']) ok(TheSanPham.props[name].type, `Có kiểu dữ liệu prop ${name}`);
  ok(TheSanPham.props.ten.required, 'Prop tên bắt buộc');
  ok(Object.hasOwn(TheSanPham.props.moTa, 'default'), 'Prop mô tả có giá trị mặc định');
  ok(TheSanPham.template.includes(':disabled="tonKho === 0"'), 'Khóa nút mua khi hết hàng');
  equal(catalog.model.chonMua(1), true, 'Cho mua sản phẩm còn hàng');
  equal(catalog.model.sanPham[0].tonKho, 31, 'Mua một bút giảm tồn kho từ 32 xuống 31');
  equal(catalog.model.sanPham[1].tonKho, 24, 'Mua bút không đổi tồn kho của vở');
  equal(catalog.model.sanPham.length, 6, 'Mua không đổi số loại sản phẩm trong danh mục');
  ok(catalog.model.thongBao.includes('Bút bi xanh') && catalog.model.thongBao.includes('31'), 'Thông báo đúng sản phẩm và số lượng còn lại');
  const stockAfterBuy = JSON.stringify(catalog.model.sanPham);
  equal(catalog.model.chonMua(12345), false, 'Từ chối mua ID không tồn tại');
  equal(JSON.stringify(catalog.model.sanPham), stockAfterBuy, 'ID sai không thay đổi tồn kho');
  equal(catalog.model.chonMua(3), false, 'Từ chối mua túi vải đã hết hàng');
  equal(catalog.model.sanPham[2].tonKho, 0, 'Sản phẩm hết hàng không bị trừ âm');
  catalog.model.sanPham[4].tonKho = 1;
  equal(catalog.model.chonMua(5), true, 'Cho mua sản phẩm cuối cùng');
  equal(catalog.model.sanPham[4].tonKho, 0, 'Mua sản phẩm cuối đưa tồn kho về 0');
  equal(catalog.model.chonMua(5), false, 'Không mua vượt tồn kho khi bấm tiếp');
  equal(catalog.model.sanPham[4].tonKho, 0, 'Bấm tiếp không tạo tồn kho âm');
  equal(catalog.model.thongBao, '', 'Không giữ thông báo mua thành công sau khi yêu cầu bị từ chối');
  ok(catalog.model.loi.includes('hết hàng'), 'Báo đúng lý do không mua được');

  const DongGioHang = loadDefinition('components/DongGioHang.js', 'DongGioHang');
  const { model: cart, registered } = loadApp('assets/bai3.js', { DongGioHang });
  equal(registered['dong-gio-hang'], DongGioHang, 'Đăng ký dòng giỏ hàng');
  equal(cart.tongSoLuong, 5, 'Giỏ mẫu có 5 sản phẩm');
  equal(cart.tongTien, 46000, '2 bút + 3 vở đúng 46.000 đồng');
  for (const value of [0, -1, 100, 1.5, NaN, Infinity, '', '3', null, undefined]) {
    equal(cart.capNhatSoLuong(1, value), false, `Cha từ chối số lượng ${String(value)}`);
    equal(cart.tongTien, 46000, 'Dữ liệu sai không đổi tổng tiền');
  }
  equal(cart.capNhatSoLuong(12345, 2), false, 'Cha từ chối ID không tồn tại');
  equal(cart.capNhatSoLuong(1, 1), true, 'Cho phép cận dưới 1');
  equal(cart.tongSoLuong, 4, 'Tổng lượng cập nhật');
  equal(cart.tongTien, 41000, 'Tổng tiền cập nhật');
  equal(cart.capNhatSoLuong(1, 99), true, 'Cho phép cận trên 99');
  equal(cart.tongTien, 531000, 'Tổng tiền đúng ở số lượng 99');
  const events = [];
  // Object.freeze khiến bài test thất bại ngay nếu component ghi vào props.
  const child = Object.freeze({ id: 1, ten: 'Bút bi xanh', donGia: 5000, soLuong: 2, $emit: (...args) => events.push(args), doiSoLuong: DongGioHang.methods.doiSoLuong });
  equal(DongGioHang.computed.thanhTien.call(child), 10000, 'Thành tiền dòng từ props');
  DongGioHang.methods.doiSoLuong.call(child, 3);
  equal(JSON.stringify(events.pop()), JSON.stringify(['doi-so-luong', 1, 3]), 'Sự kiện gửi ID và số lượng mới');
  const invalidInput = { target: { value: '1.5' } };
  DongGioHang.methods.nhapSoLuong.call(child, invalidInput);
  equal(invalidInput.target.value, 2, 'Khôi phục ô nhập sai, không sửa props');
  equal(child.soLuong, 2, 'Component không gán lại props');
  ok(DongGioHang.emits.includes('doi-so-luong'), 'Khai báo sự kiện đổi số lượng');
  ok(DongGioHang.emits.includes('yeu-cau-xoa'), 'Khai báo sự kiện xóa');
  ok(DongGioHang.template.includes("$emit('yeu-cau-xoa', id)"), 'Nút xóa gửi mã sản phẩm');
  const confirmCallsBeforeDelete = confirmCalls;
  equal(cart.xoaSanPham(12345), false, 'Từ chối xóa ID không tồn tại');
  equal(confirmCalls, confirmCallsBeforeDelete, 'ID không tồn tại không mở xác nhận');
  const cartBeforeDelete = JSON.stringify(cart.gioHang);
  confirmResult = false;
  equal(cart.xoaSanPham(1), false, 'Hủy xác nhận thì không xóa khỏi giỏ');
  equal(confirmCalls, confirmCallsBeforeDelete + 1, 'Hỏi xác nhận khi xóa sản phẩm trong giỏ');
  equal(JSON.stringify(cart.gioHang), cartBeforeDelete, 'Hủy xác nhận giữ nguyên toàn bộ giỏ hàng');
  equal(cart.tongSoLuong, 102, 'Hủy xác nhận không đổi tổng số lượng');
  equal(cart.tongTien, 531000, 'Hủy xác nhận không đổi tổng tiền');
  confirmResult = true;
  equal(cart.xoaSanPham(1), true, 'Đồng ý xác nhận mới xóa khỏi giỏ');
  equal(cart.gioHang.length, 1, 'Sau xác nhận còn một dòng sản phẩm');
  equal(cart.tongTien, 36000, 'Sau xác nhận cập nhật đúng tổng tiền');
  equal(cart.xoaSanPham(2), true, 'Xác nhận xóa sản phẩm cuối cùng');
  equal(cart.gioHang.length, 0, 'Xóa hết giỏ hàng');
  equal(cart.tongSoLuong, 0, 'Giỏ trống: tổng lượng 0');
  equal(cart.tongTien, 0, 'Giỏ trống: tổng tiền 0');
  cart.khoiPhucMau();
  equal(cart.tongTien, 46000, 'Khôi phục giỏ hàng mẫu');
  equal(cart.tongSoLuong, 5, 'Khôi phục tổng lượng mẫu');
  for (const file of ['index.html', 'bai1.html', 'bai2.html', 'bai3.html']) {
    const html = fs.readFileSync(path.join(root, file), 'utf8');
    for (const [, reference] of html.matchAll(/(?:src|href)="([^"#]+)"/g)) {
      if (!/^[a-z]+:/.test(reference)) ok(fs.existsSync(path.join(root, reference.split(/[?#]/)[0])), `${file}: đường dẫn ${reference} tồn tại`);
    }
  }
  console.log(`PASS: ${checks} kiểm tra. Bộ lọc, thống kê, validation, watch, component, props và sự kiện đều đúng.`);
})().catch(error => { console.error(error); process.exitCode = 1; });
