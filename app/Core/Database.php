<?php
namespace App\Core;

use mysqli;
use mysqli_result;
use mysqli_stmt;
use mysqli_sql_exception;

// Include the configuration file
require_once BASE_PATH . '/config.php';

class Database {
    // Store the database connection
    private static ?mysqli $conn = null;

    // Get the database connection
    public static function conn(): mysqli {
        if (self::$conn === null) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                // Create new database connection
                self::$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                self::$conn->set_charset(DB_CHARSET);
            } catch (mysqli_sql_exception $e) {
                // Log error message
                error_log("Database Connection Error: " . $e->getMessage());
                // Display error and terminate
                die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
            }
        }
        return self::$conn;
    }

    // Execute a query
    public static function query(string $query): mysqli_result|bool {
        try {
            return self::conn()->query($query);
        } catch (\Exception $e) {
            error_log("Database Query Error: " . $e->getMessage() . " | SQL: " . $query);
            return false; // Trả về false khi có lỗi
        }
    }

    // Prepare a statement
    public static function prepare(string $sql, string $types = '', array $params = []): mysqli_stmt|false {
        try {
            // Prepare a statement
            $stmt = self::conn()->prepare($sql);
            // Bind params
            if ($stmt && $types && $params) {
                if (strlen($types) != count($params)) {
                    // Throw error if number of types and params mismatch
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