<?php
namespace App\Models;

use App\Core\Database;
use mysqli_result;

abstract class BaseModel{

    protected static string $table;

    // Get all records from the table
    public static function all(): array{
        $sql = "select * from " . static::$table;
        $result = Database::query($sql);
        // Check result type
        return $result instanceof mysqli_result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Find a record by ID
    public static function find(int $id): ?array{
        $sql = "select * from " . static::$table . " where id = ?";
        $stmt = Database::prepare($sql, "i", [$id]);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            return $data;
        }
        if ($stmt) $stmt->close();
        return null;
    }
}