<?php
require_once 'MovieFunctions.php';

echo "<pre>\n";
echo "=== THỰC HIỆN CÁC BƯỚC YÊU CẦU CỦA ĐỀ BÀI ===\n\n";

echo "Bước 1. Tạo danh sách các object Movie.\n";
$movies = [
    new Movie(1, "Avengers", 100000, 100),
    new Movie(2, "Avatar", 120000, 80),
    new Movie(3, "Batman", 90000, 120),
    new Movie(4, "Spider-Man", 110000, 150),
    new Movie(5, "Inception", 130000, 90)
];
echo "-> Đã tạo 5 phim: Avengers, Avatar, Batman, Spider-Man, Inception.\n";

echo "\nBước 2. Đặt vé cho phim Avengers.\n";
$movies[0]->bookTicket(5); // Đặt 5 vé Avengers

echo "\nBước 3. Đặt vé cho phim Avatar.\n";
$movies[1]->bookTicket(10); // Đặt 10 vé Avatar

echo "\nBước 4. Hủy một số vé đã đặt của phim Avengers.\n";
$movies[0]->cancelTicket(2); // Mua 5 hủy 2, còn 3

echo "\nBước 5. Hiển thị thông tin của tất cả các phim.\n";
Movie::displayHeader();
foreach ($movies as $movie) {
    $movie->displayInfo();
}
Movie::displayFooter();

echo "\nBước 6. Tính tổng doanh thu của tất cả các phim.\n";
echo "-> Tổng doanh thu rạp: " . number_format(getTotalRevenue($movies)) . " VNĐ\n";

echo "\nBước 7. Tìm và hiển thị phim có số vé bán ra nhiều nhất.\n";
$bestSelling = getBestSellingMovie($movies);
if ($bestSelling) {
    echo "-> Phim bán chạy nhất: " . $bestSelling->getTitle() . " với " . $bestSelling->getSoldSeats() . " vé.\n";
}


echo "\n\n=== TEST CÁC TRƯỜNG HỢP BẮT BUỘC XỬ LÝ LỖI ===\n\n";

echo "- Thử đặt số vé nhỏ hơn hoặc bằng 0 (<= 0):\n";
$movies[0]->bookTicket(0);
$movies[0]->bookTicket(-5);

echo "\n- Thử đặt số vé lớn hơn số ghế còn lại:\n";
$movies[0]->bookTicket(150); // Avengers chỉ có 100 ghế

echo "\n- Thử hủy số vé nhỏ hơn hoặc bằng 0 (<= 0):\n";
$movies[0]->cancelTicket(0);
$movies[0]->cancelTicket(-2);

echo "\n- Thử hủy số vé lớn hơn số vé đã bán:\n";
// Hiện Avengers đã bán 3 vé, ta thử hủy 10 vé
$movies[0]->cancelTicket(10);

echo "\n- Thử tìm phim không tồn tại (ID = 99):\n";
$notFound = findMovieById($movies, 99);
if (!$notFound) {
    echo "-> Kết quả: Không tìm thấy phim có ID = 99.\n";
}

echo "\n- Test danh sách phim rỗng khi gọi các function xử lý danh sách:\n";
$emptyMovies = [];
echo " + Hàm findMovieById(): ";
if (findMovieById($emptyMovies, 1) === null) echo "Xử lý an toàn, trả về null.\n";

echo " + Hàm getTotalRevenue(): Xử lý an toàn, doanh thu là " . getTotalRevenue($emptyMovies) . "\n";

echo " + Hàm getBestSellingMovie(): ";
if (getBestSellingMovie($emptyMovies) === null) echo "Xử lý an toàn, trả về null.\n";

echo "</pre>\n";
?>
