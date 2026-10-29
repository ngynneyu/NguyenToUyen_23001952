'use strict';

// Chạy bằng Node, không cài thêm gói. Dùng reactivity thật của bản Vue đi kèm.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.resolve(__dirname, '..');
const runtime = vm.createContext({ console });
vm.runInContext(fs.readFileSync(path.join(root, 'assets/vendor/vue.global.prod.js'), 'utf8'), runtime);
const Vue = runtime.Vue;
let assertions = 0;

function equal(actual, expected, description) {
  assert.equal(actual, expected, description);
  assertions++;
}

function loadApp(filename) {
  let options;
  const context = { Vue: { createApp(component) {
    options = component;
    return { mount() {} };
  } } };
  vm.runInNewContext(fs.readFileSync(path.join(root, 'assets', filename), 'utf8'), context);
  const model = Vue.reactive(options.data());
  for (const [key, method] of Object.entries(options.methods || {})) {
    model[key] = method.bind(model);
  }
  for (const [key, getter] of Object.entries(options.computed || {})) {
    const value = Vue.computed(() => getter.call(model));
    Object.defineProperty(model, key, { get: () => value.value });
  }
  return model;
}

equal(Vue.version, '3.5.13', 'Đúng phiên bản Vue được lưu cục bộ');

const student = loadApp('bai1.js');
for (const [score, css] of [[0, 'danger'], [4.99, 'danger'], [5, 'warning'], [6.49, 'warning'], [6.5, 'info'], [7.99, 'info'], [8, 'success'], [10, 'success']]) {
  student.student.average = score;
  equal(student.validAverage, true, `Điểm ${score} hợp lệ`);
  equal(student.gradeClass, css, `Màu xếp loại ở mốc ${score}`);
}
for (const invalid of ['', -0.1, 10.1, NaN, Infinity, '8']) {
  student.student.average = invalid;
  equal(student.validAverage, false, `Từ chối điểm ${String(invalid)}`);
  equal(student.gradeClass, 'neutral', 'Không xếp loại điểm không hợp lệ');
}

const booking = loadApp('bai2.js');
equal(booking.canBook, false, 'Khóa đặt vé khi chưa nhập tên');
booking.customerName = '   ';
equal(booking.canBook, false, 'Khóa đặt vé khi tên chỉ có khoảng trắng');
booking.customerName = '  Nguyễn Văn An  ';
booking.ticketType = 'vip';
booking.quantity = 2;
booking.withCombo = true;
equal(booking.total, 245000, '2 vé VIP + combo = 245.000đ');
equal(booking.canBook, true, 'Cho đặt khi thông tin hợp lệ');
booking.showtime = '19:30';
booking.bookTickets();
equal(booking.confirmation.total, 245000, 'Xác nhận đúng tổng tiền');
equal(booking.confirmation.customerName, 'Nguyễn Văn An', 'Loại khoảng trắng ở tên xác nhận');
equal(booking.confirmation.showtime, '19:30', 'Xác nhận đúng suất chiếu');
booking.quantity = 3;
equal(booking.total, 345000, 'Combo chỉ cộng một lần cho mỗi đơn');
equal(booking.confirmation.total, 245000, 'Sửa form không thay đổi xác nhận đã gửi');
booking.ticketType = 'standard';
booking.withCombo = false;
equal(booking.total, 210000, '3 vé thường không combo');
for (const invalid of ['', 0, -1, 11, 1.5, NaN, Infinity, '2']) {
  booking.quantity = invalid;
  equal(booking.quantityValid, false, `Từ chối số lượng ${String(invalid)}`);
  equal(booking.canBook, false, 'Khóa đặt vé khi số lượng sai');
  const previous = booking.confirmation;
  booking.bookTickets();
  equal(booking.confirmation, previous, 'Không tạo xác nhận khi dữ liệu sai');
}
booking.quantity = 1;
booking.decreaseQuantity();
equal(booking.quantity, 1, 'Không giảm dưới 1 vé');
booking.quantity = 10;
booking.increaseQuantity();
equal(booking.quantity, 10, 'Không tăng quá 10 vé');
booking.quantity = '';
booking.increaseQuantity();
equal(booking.quantity, 1, 'Nút tăng phục hồi số lượng không hợp lệ');
booking.quantity = 1.5;
booking.decreaseQuantity();
equal(booking.quantity, 1, 'Nút giảm phục hồi số lượng không hợp lệ');
booking.resetForm();
equal(booking.customerName, '', 'Đặt lại họ tên');
equal(booking.showtime, '09:00', 'Đặt lại suất chiếu');
equal(booking.ticketType, 'standard', 'Đặt lại loại vé');
equal(booking.quantity, 1, 'Đặt lại số lượng');
equal(booking.withCombo, false, 'Bỏ combo khi đặt lại');
equal(booking.confirmation, null, 'Xóa xác nhận khi đặt lại');
equal(booking.total, 70000, 'Tổng mặc định sau khi đặt lại');

const tasks = loadApp('bai3.js');
equal(tasks.totalTasks, 3, 'Có ba công việc mẫu');
equal(tasks.remainingTasks, 2, 'Có hai việc chưa hoàn thành');
for (const empty of ['', '   ', '\t\n']) {
  tasks.newTask = empty;
  tasks.addTask();
  equal(tasks.totalTasks, 3, 'Không thêm việc rỗng hoặc chỉ có khoảng trắng');
  equal(Boolean(tasks.taskError), true, 'Có thông báo khi tên rỗng');
}
tasks.newTask = '  Đọc tài liệu Vue  ';
tasks.addTask();
equal(tasks.tasks[3].name, 'Đọc tài liệu Vue', 'Cắt khoảng trắng tên công việc');
equal(tasks.tasks[3].completed, false, 'Việc mới chưa hoàn thành');
equal(tasks.totalTasks, 4, 'Tăng tổng số sau khi thêm');
equal(tasks.remainingTasks, 3, 'Tăng số việc chưa hoàn thành');
equal(tasks.taskError, '', 'Xóa lỗi sau khi thêm hợp lệ');
equal(tasks.newTask, '', 'Xóa ô nhập sau khi thêm');
const addedId = tasks.tasks[3].id;
tasks.tasks[3].completed = true;
equal(tasks.remainingTasks, 2, 'Checkbox cập nhật thống kê');
tasks.filter = 'completed';
equal(tasks.filteredTasks.length, 2, 'Lọc việc đã hoàn thành');
tasks.filter = 'active';
equal(tasks.filteredTasks.length, 2, 'Lọc việc chưa hoàn thành');
tasks.filter = 'all';
equal(tasks.filteredTasks.length, 4, 'Lọc tất cả');
tasks.deleteTask(addedId);
equal(tasks.totalTasks, 3, 'Xóa việc theo id');
tasks.newTask = 'Việc tiếp theo';
tasks.addTask();
equal(tasks.tasks[3].id > addedId, true, 'Không tái sử dụng id đã xóa');
tasks.clearCompleted();
equal(tasks.completedTasks, 0, 'Xóa toàn bộ việc hoàn thành');
equal(tasks.totalTasks, tasks.remainingTasks, 'Giữ lại tất cả việc chưa hoàn thành');
tasks.filter = 'completed';
equal(tasks.filteredTasks.length, 0, 'Kết quả lọc có thể rỗng');
tasks.newTask = 'Việc mới khi đang lọc';
tasks.addTask();
equal(tasks.filter, 'all', 'Hiện được việc mới thêm khi trước đó lọc đã hoàn thành');
equal(new Set(tasks.tasks.map(task => task.id)).size, tasks.totalTasks, 'Mỗi việc có id duy nhất');
for (const task of [...tasks.tasks]) tasks.deleteTask(task.id);
equal(tasks.totalTasks, 0, 'Xóa hết danh sách');
equal(tasks.remainingTasks, 0, 'Thống kê về 0');
equal(tasks.filteredTasks.length, 0, 'Danh sách rỗng không sinh lỗi');

console.log(`PASS: ${assertions} kiểm tra logic và phản ứng dữ liệu cho cả 3 bài.`);
