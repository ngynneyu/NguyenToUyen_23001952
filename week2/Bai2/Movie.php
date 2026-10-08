<?php
class Movie {
    private $id;
    private $title;
    private $price;
    private $totalSeats;
    private $availableSeats;

    public function __construct($id, $title, $price, $totalSeats) {
        $this->id = $id;
        $this->title = $title;
        $this->price = $price;
        $this->totalSeats = $totalSeats;
        $this->availableSeats = $totalSeats;
    }

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function bookTicket($quantity) {
        if (!is_numeric($quantity) || $quantity <= 0) {
            echo "Lỗi [" . $this->title . "]: Số lượng vé muốn đặt không hợp lệ (phải > 0).\n";
            return false;
        }
        if ($quantity > $this->availableSeats) {
            echo "Lỗi [" . $this->title . "]: Không đủ ghế trống. Yêu cầu $quantity nhưng chỉ còn " . $this->availableSeats . " ghế.\n";
            return false;
        }
        
        $this->availableSeats -= $quantity;
        echo "Thành công: Đã đặt $quantity vé cho phim " . $this->title . ".\n";
        return true;
    }

    public function cancelTicket($quantity) {
        if (!is_numeric($quantity) || $quantity <= 0) {
            echo "Lỗi [" . $this->title . "]: Số lượng vé muốn hủy không hợp lệ (phải > 0).\n";
            return false;
        }
        
        $soldSeats = $this->getSoldSeats();
        if ($quantity > $soldSeats) {
            echo "Lỗi [" . $this->title . "]: Không thể hủy $quantity vé. Số vé đã bán chỉ là $soldSeats.\n";
            return false;
        }

        $this->availableSeats += $quantity;
        echo "Thành công: Đã hủy $quantity vé của phim " . $this->title . ".\n";
        return true;
    }

    public function getSoldSeats() {
        return $this->totalSeats - $this->availableSeats;
    }

    public function getRevenue() {
        return $this->getSoldSeats() * $this->price;
    }

    // Hàm hỗ trợ căn lề (padding) cho chuỗi có dấu tiếng Việt
    private function padStr($str, $len, $pad_string = ' ', $pad_type = STR_PAD_RIGHT) {
        $diff = strlen($str) - mb_strlen($str, 'UTF-8');
        return str_pad($str, $len + $diff, $pad_string, $pad_type);
    }

    public static function displayHeader() {
        echo "+----+----------------------+-------------+----------+--------+--------+-------------+\n";
        echo "| ID | Phim                 | Giá vé      | Tổng ghế | Trống  | Đã bán | Doanh thu   |\n";
        echo "+----+----------------------+-------------+----------+--------+--------+-------------+\n";
    }

    public static function displayFooter() {
        echo "+----+----------------------+-------------+----------+--------+--------+-------------+\n";
    }

    public function displayInfo() {
        $id = $this->padStr($this->id, 2);
        $title = $this->padStr($this->title, 20);
        $price = $this->padStr(number_format($this->price), 11, ' ', STR_PAD_LEFT);
        $total = $this->padStr($this->totalSeats, 8, ' ', STR_PAD_LEFT);
        $avail = $this->padStr($this->availableSeats, 6, ' ', STR_PAD_LEFT);
        $sold = $this->padStr($this->getSoldSeats(), 6, ' ', STR_PAD_LEFT);
        $rev = $this->padStr(number_format($this->getRevenue()), 11, ' ', STR_PAD_LEFT);

        echo "| $id | $title | $price | $total | $avail | $sold | $rev |\n";
    }
}
