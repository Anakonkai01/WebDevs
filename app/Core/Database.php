<?php
namespace App\Core; 

use mysqli;
use mysqli_result;
use mysqli_stmt;
use mysqli_sql_exception;

require_once BASE_PATH . '/config.php';

class Database {

    private static ?mysqli $conn = null;

    public static function conn(): mysqli {
        if (self::$conn === null) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                self::$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                self::$conn->set_charset(DB_CHARSET);
            } catch (mysqli_sql_exception $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
            }
        }
        return self::$conn;
    }

    public static function query(string $query): mysqli_result|bool { // Sửa kiểu trả về
        try {
            return self::conn()->query($query);
        } catch (\Exception $e) {
            error_log("Database Query Error: " . $e->getMessage() . " | SQL: " . $query);
            return false; // Trả về false khi có lỗi
        }
    }

    public static function prepare(string $sql, string $types = '', array $params = []): mysqli_stmt|false {
        try {
            $stmt = self::conn()->prepare($sql);
            // Chỉ bind khi prepare thành công và có tham số
            if ($stmt && $types && $params) {
                // Kiểm tra số lượng type và params khớp nhau
                if (strlen($types) != count($params)) {
                    throw new \InvalidArgumentException("Number of types and params mismatch in prepare statement.");
                }
                $stmt->bind_param($types, ...$params);
            }
            return $stmt; // Trả về stmt hoặc false nếu prepare lỗi
        } catch (\Exception $e) {
            error_log("Database Prepare Error: " . $e->getMessage() . " | SQL: " . $sql);
            return false;
        }
    }
}