<?php
$students = [
    ["name" => "Nguyen Van An", "age" => 20, "score" => 8.5],
    ["name" => "Tran Thi Binh", "age" => 21, "score" => 6.5],
    ["name" => "Le Van Cuong", "age" => 19, "score" => 4.5],
    ["name" => "Pham Thi Dung", "age" => 20, "score" => 7.5]
];

// Tìm sinh viên điểm cao nhất
function findBestStudent($students) {
    $best = $students[0];
    foreach ($students as $student) {
        if ($student["score"] > $best["score"]) {
            $best = $student;
        }
    }
    return $best;
}

// Tìm sinh viên điểm thấp nhất
function findWorstStudent($students) {
    $worst = $students[0];
    foreach ($students as $student) {
        if ($student["score"] < $worst["score"]) {
            $worst = $student;
        }
    }
    return $worst;
}

// Đếm số sinh viên đạt (điểm >= 5)
function countPassedStudents($students) {
    $count = 0;
    foreach ($students as $student) {
        if ($student["score"] >= 5) {
            $count++;
        }
    }
    return $count;
}

// Tìm sinh viên theo tên
function findStudentByName($students, $name) {
    foreach ($students as $student) {
        // Dùng strtolower để tìm kiếm không phân biệt chữ hoa/thường
        if (strtolower($student["name"]) == strtolower($name)) {
            return $student;
        }
    }
    return null; 
}

$best = findBestStudent($students);
echo "Sinh viên điểm cao nhất: {$best['name']} ({$best['score']} điểm)<br>";

$worst = findWorstStudent($students);
echo "Sinh viên điểm thấp nhất: {$worst['name']} ({$worst['score']} điểm)<br>";

$passed = countPassedStudents($students);
echo "Số sinh viên đạt (>= 5 điểm): {$passed}<br>";

$searchName = "Tran Thi Binh";
$found = findStudentByName($students, $searchName);
if ($found) {
    echo "Tìm thấy sinh viên: {$found['name']} - Tuổi: {$found['age']} - Điểm: {$found['score']}";
} else {
    echo "Không tìm thấy sinh viên tên: {$searchName}";
}
?>