<?php
$students = [
    ["name" => "Nguyen Van An", "age" => 20, "score" => 8.5],
    ["name" => "Tran Thi Binh", "age" => 21, "score" => 6.5],
    ["name" => "Le Van Cuong", "age" => 19, "score" => 4.5],
    ["name" => "Pham Thi Dung", "age" => 20, "score" => 7.5]
];

echo "<h3>Danh sách sinh viên:</h3>";

$totalScore = 0;
$studentCount = count($students);

foreach ($students as $student) {
    echo "Họ tên: " . $student["name"] . " | Tuổi: " . $student["age"] . " | Điểm: " . $student["score"] . "<br>";
    $totalScore += $student["score"]; // Cộng dồn điểm
}

if ($studentCount > 0) {
    $averageScore = $totalScore / $studentCount;
    echo "<h3>Điểm trung bình của tất cả sinh viên: " . $averageScore . "</h3>";
}
?>