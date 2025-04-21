<?php
require_once  BASE_PATH . '/app/Core/Database.php';


abstract class BaseModel{
    // moi class con se khai bao bang tuong ung
    protected static string $table;

    // lay toan bo du lieu
    public static function all(): array{
        $sql = "select * from " . static::$table;
        $result = Database::query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }


    // find by id
    public static function find(int $id):  ?array{
        $sql = "select * from " . static::$table . " where id = ?";
        $stmt = Database::prepare($sql, "i",[$id]);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null; // lay 1 dong duy nhat con ?: toan tu null coalescing
    }
}