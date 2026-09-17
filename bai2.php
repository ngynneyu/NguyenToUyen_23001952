<?php
$students = [
    ["name" => "Nguyen Van An", "age" => 20, "score" => 8.5],
    ["name" => "Tran Thi Binh", "age" => 21, "score" => 6.5],
    ["name" => "Le Van Cuong", "age" => 19, "score" => 4.5],
    ["name" => "Pham Thi Dung", "age" => 20, "score" => 7.5]
];

// 1. Hàm tính điểm trung bình
function calculateAverageScore($students) {
    if (count($students) == 0) return 0;
    $total = 0;
    foreach ($students as $student) {
        $total += $student["score"];
    }
    return $total / count($students);
}

// 2. Hàm lấy xếp loại
function getRank($score) {
    if ($score >= 8) return "Giỏi";
    if ($score >= 6.5) return "Khá";
    if ($score >= 5) return "Trung bình";
    return "Yếu";
}

// 3. Hàm hiển thị thông tin 1 sinh viên
function displayStudent($student) {
    $rank = getRank($student["score"]);
    echo "Họ tên: {$student['name']} | Tuổi: {$student['age']} | Điểm: {$student['score']} | Xếp loại: {$rank}<br>";
}

// --- THỰC THI CHƯƠNG TRÌNH ---
echo "<h3>Thông tin sinh viên và xếp loại:</h3>";
foreach ($students as $student) {
    displayStudent($student);
}

$avg = calculateAverageScore($students);
echo "<h3>Điểm trung bình của lớp: {$avg}</h3>";
?>