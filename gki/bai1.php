<?php
$name = "Nguyễn Tố Uyên";
$score = 9.5;

if ($score < 0 || $score > 10) {
    echo "Điểm không hợp lệ. Vui lòng nhập điểm từ 0 đến 10.";
    return;
}elseif ($score >= 8) {
    $rank = "Giỏi";
} elseif ($score >= 6.5) {
    $rank = "Khá";
} elseif ($score >= 5) {
    $rank = "Trung bình";
} else {
    $rank = "Yếu";
}

echo "Họ tên: $name<br>";
echo "Điểm: $score<br>";
echo "Xếp loại: $rank<br>";
?>
