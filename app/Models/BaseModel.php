<?php
namespace App\Models; // <--- Namespace

use App\Core\Database; // <-- Dùng Database từ Core
use mysqli_result;     // <-- Dùng class global mysqli_result

abstract class BaseModel{

    protected static string $table;

    public static function all(): array{
        $sql = "select * from " . static::$table;
        $result = Database::query($sql);
        // Thêm kiểm tra kiểu trả về của query
        return $result instanceof mysqli_result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function find(int $id): ?array{
        $sql = "select * from " . static::$table . " where id = ?";
        $stmt = Database::prepare($sql, "i", [$id]);
        if ($stmt && $stmt->execute()) { // Kiểm tra execute thành công
            $result = $stmt->get_result();
            $data = $result ? $result->fetch_assoc() : null; // fetch_assoc() trả về null nếu không có dòng nào
            $stmt->close();
            return $data;
        }
        // Đóng stmt nếu có lỗi execute hoặc prepare
        if ($stmt) $stmt->close();
        return null;
    }
}