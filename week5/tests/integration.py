"""Test HTTP và MySQL thật trên dữ liệu tạm; không dùng CSDL của XAMPP đang chạy.

Chạy: python week5/tests/integration.py
Có thể đặt biến PHP_BIN và MYSQL_BIN_DIR nếu XAMPP nằm ở thư mục khác.
"""
import http.cookiejar
import os
from pathlib import Path
import re
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request

ROOT = Path(__file__).resolve().parents[1]
PHP = os.environ.get('PHP_BIN', r'D:\xampp\php\php.exe')
MYSQL = Path(os.environ.get('MYSQL_BIN_DIR', r'D:\xampp\mysql\bin'))
checks = 0


def check(condition, description):
    global checks
    if not condition:
        raise AssertionError(description)
    checks += 1


def free_port():
    with socket.socket() as sock:
        sock.bind(('127.0.0.1', 0))
        return sock.getsockname()[1]


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


runtime_root = ROOT / 'tests' / '.runtime'
runtime_root.mkdir(exist_ok=True)
with tempfile.TemporaryDirectory(prefix='integration-', dir=runtime_root) as temp_name:
    temp = Path(temp_name).resolve()
    # Chỉ dọn thư mục tạm nằm bên trong tests/.runtime của bài này.
    if not temp.is_relative_to(runtime_root.resolve()):
        raise RuntimeError('Unsafe temporary path')
    data = temp / 'mysql'
    sessions = temp / 'sessions'
    sessions.mkdir()
    db_port, web_port = free_port(), free_port()
    no_window = subprocess.CREATE_NO_WINDOW if os.name == 'nt' else 0
    installed = subprocess.run([
        str(MYSQL / 'mysql_install_db.exe'), f'--datadir={data}',
        f'--port={db_port}', '--silent',
    ], capture_output=True, text=True, creationflags=no_window)
    if installed.returncode:
        raise RuntimeError(installed.stdout + installed.stderr)
    processes = []
    with open(temp / 'mysql.log', 'wb') as db_log, open(temp / 'php.log', 'wb') as php_log:
        try:
            processes.append(subprocess.Popen([
                str(MYSQL / 'mysqld.exe'), '--no-defaults',
                f'--basedir={MYSQL.parent}', f'--datadir={data}',
                f'--port={db_port}', '--bind-address=127.0.0.1',
                '--innodb-buffer-pool-size=32M', '--console',
            ], stdout=db_log, stderr=subprocess.STDOUT, creationflags=no_window))

            def sql(query, database='shopping_cart'):
                args = [str(MYSQL / 'mysql.exe'), '--host=127.0.0.1',
                        f'--port={db_port}', '--user=root', '--batch', '--skip-column-names',
                        '--default-character-set=utf8mb4']
                if database:
                    args.append(database)
                result = subprocess.run(args, input=query, text=True, encoding='utf-8',
                                        capture_output=True, creationflags=no_window)
                if result.returncode:
                    raise RuntimeError(result.stderr)
                return result.stdout.strip()

            for _ in range(100):
                try:
                    sql('SELECT 1;', '')
                    break
                except RuntimeError as error:
                    if processes[0].poll() is not None:
                        db_log.flush()
                        raise RuntimeError((temp / 'mysql.log').read_text(errors='replace')) from error
                    time.sleep(0.1)
            else:
                raise RuntimeError('MySQL did not start')

            schema = (ROOT / 'app' / 'database.sql').read_text(encoding='utf-8')
            sql(schema, '')
            sql(schema, '')
            check(sql('SELECT COUNT(*) FROM products;') == '5', 'SQL seeds five products without duplication')
            check(sql('SELECT COUNT(*) FROM users;') == '1', 'SQL seeds one account without duplication')
            product_columns = {row.split('\t')[0]: row.split('\t')[1:] for row in sql('DESCRIBE products;').splitlines()}
            user_columns = {row.split('\t')[0]: row.split('\t')[1:] for row in sql('DESCRIBE users;').splitlines()}
            for columns in [product_columns, user_columns]:
                check(columns['id'][0].startswith('int(') and columns['id'][2] == 'PRI'
                      and columns['id'][-1] == 'auto_increment', 'Integer primary key with auto increment')
            for columns, name, expected in [(product_columns, 'name', 'varchar(100)'),
                                            (product_columns, 'price', 'decimal(10,2)'),
                                            (user_columns, 'username', 'varchar(50)'),
                                            (user_columns, 'password', 'varchar(255)')]:
                check(columns[name][0] == expected and columns[name][1] == 'NO', 'Required column ' + name)
            check(product_columns['quantity'][0].startswith('int(') and product_columns['quantity'][1] == 'NO', 'Integer quantity column')
            check(user_columns['username'][2] == 'UNI', 'Unique username column')
            sources = list((ROOT / 'app').rglob('*.php'))
            check(not list((ROOT / 'app').rglob('*.css')) and all(
                not re.search(r'<style\b|\bstyle\s*=|rel=[\"\x27]stylesheet', p.read_text(encoding='utf-8'), re.I)
                for p in sources), 'No CSS files, inline styles or stylesheets')
            model = (ROOT / 'app' / 'model' / 'product.php').read_text(encoding='utf-8')
            for function in ['getAllProducts', 'getProductById', 'addProduct', 'updateProduct', 'deleteProduct']:
                check(bool(re.search(r'function\s+' + function + r'\s*\(', model)), 'Product model function ' + function)
            env = dict(os.environ, DB_PORT=str(db_port), DB_HOST='127.0.0.1', DB_NAME='shopping_cart', DB_USER='root', DB_PASS='')
            processes.append(subprocess.Popen([
                PHP, '-d', f'session.save_path={sessions}', '-d', 'display_errors=0',
                '-S', f'127.0.0.1:{web_port}', '-t', str(ROOT / 'app'),
            ], env=env, stdout=php_log, stderr=subprocess.STDOUT, creationflags=no_window))
            jar = http.cookiejar.CookieJar()
            opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar), NoRedirect())
            base = f'http://127.0.0.1:{web_port}/'

            def request(page, fields=None, client=None):
                payload = urllib.parse.urlencode(fields).encode() if fields is not None else None
                try:
                    response = (client or opener).open(base + page, payload, timeout=5)
                except urllib.error.HTTPError as error:
                    response = error
                with response:
                    body = response.read().decode('utf-8')
                    if response.code != 302:
                        check('<h1>Hệ thống quản lý Giỏ hàng</h1>' in body
                              and 'Bài tập thực hành PHP &amp; MySQL' in body and '</html>' in body,
                              'Common header/footer in ' + page)
                    return response.code, response.headers, body

            def form(page, fields):
                status, _, body = request(page)
                check(status == 200, f'GET {page}')
                token = re.search(r'name="csrf_token" value="([^"]+)"', body)
                check(bool(token), f'CSRF field in {page}')
                return request(page, dict(fields, csrf_token=token.group(1)))

            def full_error(response, status, message):
                code, _, body = response
                check(code == status and message in body and '</html>' in body, message)

            for _ in range(100):
                try:
                    request('index.php')
                    break
                except urllib.error.URLError:
                    time.sleep(0.1)
            else:
                raise RuntimeError('PHP did not start')

            status, headers, _ = request('index.php')
            check(status == 302 and headers['Location'] == 'login.php', 'Index redirects guest to login')

            for page in ['product_list.php', 'product_add.php', 'product_edit.php?id=1', 'product_delete.php?id=1']:
                for fields in [None, {'confirm': '1', 'name': 'unauthorized', 'price': '1', 'quantity': '0'}]:
                    status, headers, _ = request(page, fields)
                    check(status == 302 and headers['Location'] == 'login.php', 'Protect ' + page)
            check(sql('SELECT COUNT(*) FROM products;') == '5', 'Guest requests do not write')
            full_error(form('register.php', {'username': '', 'password': ''}), 200, 'không được để trống')
            full_error(form('register.php', {'username': 'tester', 'password': ''}), 200, 'không được để trống')
            full_error(form('register.php', {'username': '   ', 'password': 'abc'}), 200, 'không được để trống')
            full_error(form('register.php', {'username': 'a' * 51, 'password': 'abc'}), 200, '50 ký tự')
            full_error(form('register.php', {'username': 'tester', 'password': 'a' * 73}), 200, '72 byte')
            full_error(request('register.php', {'username': 'tester', 'password': 'abc'}), 403, 'không hợp lệ')
            full_error(form('register.php', {'username': 'admin', 'password': 'abc'}), 200, 'đã tồn tại')
            username = '<script>alert(1)</script>'
            status, headers, _ = form('register.php', {'username': username, 'password': 'Test@123'})
            check(status == 302 and headers['Location'] == 'login.php', 'Register redirects to login')
            check(sql("SELECT password FROM users WHERE id = 2;").startswith('$2y$'), 'Password is hashed')
            check('Đăng ký thành công' in request('login.php')[2], 'Registration success message')
            full_error(form('login.php', {'username': '', 'password': ''}), 200, 'không được để trống')
            full_error(form('login.php', {'username': 'unknown-user', 'password': 'abc'}), 200, 'không chính xác')
            full_error(form('login.php', {'username': username, 'password': 'wrong'}), 200, 'không chính xác')
            full_error(form('login.php', {'username': username, 'password': 'a' * 73}), 200, 'không chính xác')
            full_error(form('login.php', {'username': "' OR 1=1 --", 'password': 'abc'}), 200, 'không chính xác')
            old_session = next(cookie.value for cookie in jar if cookie.name == 'PHPSESSID')
            status, headers, _ = form('login.php', {'username': username, 'password': 'Test@123'})
            check(status == 302 and headers['Location'] == 'product_list.php', 'Login redirects')
            check(next(cookie.value for cookie in jar if cookie.name == 'PHPSESSID') != old_session, 'Rotate session after login')
            status, _, body = request('product_list.php')
            check(status == 200 and 'Tài khoản:' in body and 'Đăng xuất' in body, 'Display account and logout link')
            check(username not in body and '&lt;script&gt;' in body, 'Escape username')
            for label in ['ID', 'Tên sản phẩm', 'Giá', 'Số lượng', 'Chức năng']:
                check(f'<th>{label}</th>' in body, 'List table column ' + label)
            for product in range(1, 6):
                check(f'product_edit.php?id={product}' in body and f'product_delete.php?id={product}' in body,
                      'Edit/delete links for sample product ' + str(product))
            check('Áo phông nam' in body and '150,000.00 VNĐ' in body, 'Vietnamese product and formatted price')
            status, headers, _ = request('index.php')
            check(status == 302 and headers['Location'] == 'product_list.php', 'Index redirects member to list')
            for page in ['login.php', 'register.php']:
                status, headers, _ = request(page)
                check(status == 302 and headers['Location'] == 'product_list.php', 'Member redirected from ' + page)

            for page in ['product_add.php', 'product_edit.php?id=1']:
                for fields, message in [
                    ({'name': '', 'price': '1', 'quantity': '0'}, 'Tên sản phẩm không được rỗng'),
                    ({'name': 'a' * 101, 'price': '1', 'quantity': '0'}, '100 ký tự'),
                    ({'name': 'Test', 'price': '100000000', 'quantity': '0'}, 'Giá phải'),
                    ({'name': 'Test', 'price': '99999999.999', 'quantity': '0'}, 'Giá phải'),
                    ({'name': 'Test', 'price': '1', 'quantity': '1.5'}, 'Số lượng phải'),
                    ({'name': 'Test', 'price': '1', 'quantity': '2147483648'}, 'Số lượng phải'),
                    ({'name': 'Test', 'price': '0', 'quantity': '0'}, 'Giá phải'),
                    ({'name': 'Test', 'price': '-1', 'quantity': '0'}, 'Giá phải'),
                    ({'name': 'Test', 'price': 'abc', 'quantity': '0'}, 'Giá phải'),
                    ({'name': 'Test', 'price': '1', 'quantity': '-1'}, 'Số lượng phải'),
                ]:
                    full_error(form(page, fields), 200, message)
                full_error(request(page, {'name': 'Test', 'price': '1', 'quantity': '0'}), 403, 'không hợp lệ')
            body = form('product_edit.php?id=1', {'name': 'Keep entered name', 'price': '1', 'quantity': '1.5'})[2]
            check('value="Keep entered name"' in body and 'value="1.5"' in body, 'Edit keeps entered values on validation error')
            check(sql('SELECT name FROM products WHERE id=1;') == 'Áo phông nam', 'Invalid edit does not change database')

            payload = '<img src=x onerror=alert(1)>'
            fields = {'name': payload, 'price': '99999999.99', 'quantity': '2147483647'}
            check('Thêm sản phẩm thành công' in form('product_add.php', fields)[2], 'Add maximum values')
            product_id = sql('SELECT MAX(id) FROM products;')
            check(sql(f'SELECT price, quantity FROM products WHERE id={product_id};') == '99999999.99\t2147483647', 'MySQL preserves numeric bounds')
            body = form('product_add.php', fields)[2]
            check('đã tồn tại' in body and payload not in body and '&lt;img' in body, 'Escape duplicate product error')
            body = request('product_list.php')[2]
            check(payload not in body and '&lt;img' in body, 'Escape product list')
            edit = f'product_edit.php?id={product_id}'
            body = request(edit)[2]
            check('99999999.99' in body and '2147483647' in body, 'Edit displays current data')
            check('Cập nhật sản phẩm thành công' in form(edit, {'name': 'Updated', 'price': '0.01', 'quantity': '0'})[2], 'Update product')
            check(sql(f'SELECT name, price, quantity FROM products WHERE id={product_id};') == 'Updated\t0.01\t0', 'MySQL stores edited values')
            for page in ['product_edit.php', 'product_delete.php']:
                for suffix in ['', '?id=abc', '?id=0', '?id=-1', '?id=1.5', '?id[]=1', '?id=2147483648']:
                    full_error(request(page + suffix), 400, 'ID sản phẩm phải')
                full_error(request(page + '?id=2147483647'), 404, 'không tồn tại')
            delete = f'product_delete.php?id={product_id}'
            check('Xác nhận xóa' in request(delete)[2], 'Delete requires confirmation')
            check(sql(f'SELECT COUNT(*) FROM products WHERE id={product_id};') == '1', 'Opening confirmation does not delete')
            full_error(request(delete, {'confirm': '1'}), 403, 'không hợp lệ')
            check(sql(f'SELECT COUNT(*) FROM products WHERE id={product_id};') == '1', 'Invalid CSRF does not delete')
            full_error(form(delete, {}), 200, 'xác nhận trước khi xóa')
            check('Đã xóa sản phẩm thành công' in form(delete, {'confirm': '1'})[2], 'Delete confirmed')
            check(sql(f'SELECT COUNT(*) FROM products WHERE id={product_id};') == '0', 'Deleted in MySQL')

            sql('RENAME TABLE products TO products_saved;')
            full_error(request('product_list.php'), 503, 'Không thể tải danh sách')
            full_error(request('product_edit.php?id=1'), 503, 'Không thể tải sản phẩm')
            full_error(request('product_delete.php?id=1'), 503, 'Không thể xử lý sản phẩm')
            full_error(form('product_add.php', {'name': 'Test', 'price': '1', 'quantity': '0'}), 200, 'Vui lòng thử lại')
            sql('RENAME TABLE products_saved TO products;')
            full_error(request('logout.php', {}), 403, 'không hợp lệ')
            active_session = next(cookie.value for cookie in jar if cookie.name == 'PHPSESSID')
            status, headers, _ = form('logout.php', {})
            check(status == 302 and headers['Location'] == 'login.php', 'Logout redirects')
            check(request('product_list.php')[0] == 302, 'Logout clears access')
            replay = urllib.request.build_opener(NoRedirect())
            replay.addheaders = [('Cookie', 'PHPSESSID=' + active_session)]
            for page in ['product_list.php', 'product_add.php', 'product_edit.php?id=1', 'product_delete.php?id=1']:
                for fields in [None, {'confirm': '1', 'name': 'Unauthorized', 'price': '1', 'quantity': '0'}]:
                    status, headers, _ = request(page, fields)
                    check(status == 302 and headers['Location'] == 'login.php', 'Deny after logout ' + page)
                status, headers, _ = request(page, client=replay)
                check(status == 302 and headers['Location'] == 'login.php', 'Destroyed session cannot be reused ' + page)
            status, _, _ = form('login.php', {'username': 'admin', 'password': 'Admin@123'})
            check(status == 302, 'Sample admin password verifies')
            sql('DELETE FROM products;')
            check('Chưa có sản phẩm nào' in request('product_list.php')[2], 'Empty list')
            processes[0].terminate()
            processes[0].wait(timeout=10)
            full_error(request('product_list.php'), 503, 'Không thể kết nối cơ sở dữ liệu')
        finally:
            for process in reversed(processes):
                if process.poll() is None:
                    process.terminate()
                    process.wait(timeout=10)
    log = (temp / 'php.log').read_text(encoding='utf-8', errors='replace')
    check('PHP Fatal error' not in log and 'PHP Warning' not in log, 'No PHP warnings or fatal errors')
print(f'PASS: {checks} HTTP/MySQL integration checks')
