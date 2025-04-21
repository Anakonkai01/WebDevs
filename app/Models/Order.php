<?php
require_once 'BaseModel.php';

class Order extends BaseModel
{
    protected static string $table = 'orders';

    // tao don hang moi
    public static function create(int $user_id, float $total): bool
    {
        $sql = "INSERT INTO orders(user_id, total) VALUES (?, ?)";
        $stmt = Database::prepare($sql, "id", [$user_id, $total]);
        return $stmt->execute();
    }

    // tim don hang theo id
    public static function find(int $id): ?array
    {
        return parent::find($id); // dùng lại từ BaseModel
    }

    // lay tat ca don hang cua 1 user
    public static function getByUser(int $user_id): array
    {
        $sql = "SELECT * FROM orders WHERE user_id = ?";
        $stmt = Database::prepare($sql, "i", [$user_id]);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // xoa don hang theo id
    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM orders WHERE id = ?";
        $stmt = Database::prepare($sql, "i", [$id]);
        return $stmt->execute();
    }
}
