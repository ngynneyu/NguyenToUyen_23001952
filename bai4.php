<?php
class Student {
    public $name;
    public $age;
    public $score;

    public function __construct($name, $age, $score) {
        $this->name = $name;
        $this->age = $age;
        $this->score = $score;
    }

    public function getRank() {
        if ($this->score >= 8) return "Giỏi";
        if ($this->score >= 6.5) return "Khá";
        if ($this->score >= 5) return "Trung bình";
        return "Yếu";
    }

    public function isPassed() {
        return $this->score >= 5;
    }

    public function display() {
        $rank = $this->getRank();
        $status = $this->isPassed() ? "Đạt" : "Không đạt";
        echo "Họ tên: {$this->name} | Tuổi: {$this->age} | Điểm: {$this->score} | Xếp loại: {$rank} | Trạng thái: {$status}<br>";
    }
}

function getBestStudentOOP($studentsList) {
    $best = $studentsList[0];
    foreach ($studentsList as $student) {
        if ($student->score > $best->score) {
            $best = $student;
        }
    }
    return $best;
}

function countPassedOOP($studentsList) {
    $count = 0;
    foreach ($studentsList as $student) {
        if ($student->isPassed()) {
            $count++;
        }
    }
    return $count;
}

function getAverageScoreOOP($studentsList) {
    $total = 0;
    foreach ($studentsList as $student) {
        $total += $student->score;
    }
    return count($studentsList) > 0 ? ($total / count($studentsList)) : 0;
}


// Tạo các object
$student1 = new Student("Nguyen Van An", 20, 8.5);
$student2 = new Student("Tran Thi Binh", 21, 6.5);
$student3 = new Student("Le Van Cuong", 19, 4.5);
$student4 = new Student("Pham Thi Dung", 20, 7.5);

// Tạo danh sách (mảng các object)
$studentsList = [$student1, $student2, $student3, $student4];

echo "<h3>Danh sách sinh viên (OOP):</h3>";
foreach ($studentsList as $student) {
    $student->display();
}

echo "<h3>Thống kê lớp học:</h3>";
$bestStudent = getBestStudentOOP($studentsList);
echo "Sinh viên điểm cao nhất: <b>{$bestStudent->name}</b> ({$bestStudent->score} điểm)<br>";

$passedCount = countPassedOOP($studentsList);
echo "Số lượng sinh viên đạt: <b>{$passedCount}</b><br>";

$avgScore = getAverageScoreOOP($studentsList);
echo "Điểm trung bình của lớp: <b>{$avgScore}</b><br>";
?>