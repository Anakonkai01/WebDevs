<?php

namespace App\Models;

use App\Core\Database;

class Order extends BaseModel
{
    // Table name
    protected static string $table = 'orders';

    // Create a new order and get its ID
    public static function createAndGetId(
        int $user_id,
        float $total,
        string $customer_name,
        string $customer_address,
        string $customer_phone,
        ?string $customer_email = null,
        ?string $notes = null,
        string $status = 'Pending'
    ) {
        // SQL to insert new order
        $sql = "INSERT INTO orders(user_id, total, customer_name, customer_address, customer_phone, customer_email, notes, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        // Prepare statement
        $stmt = Database::prepare($sql, "idssssss", [
            $user_id, $total, $customer_name, $customer_address, $customer_phone, $customer_email, $notes, $status
        ]);

        // Execute the prepared statement.
        if ($stmt && $stmt->execute()) {
            // Get the ID of the last inserted record.
            $lastId = $stmt->insert_id; //get last id

            $stmt->close(); //close statement

            return $lastId; // Return id

        }
        if ($stmt) $stmt->close();
        return false;
    }

    public static function find(int $id): ?array
    {
        return parent::find($id); // dùng lại từ BaseModel
    }

    // Get orders by user with filtering and pagination
    public static function getByUser(int $user_id, string $statusFilter = 'all', ?int $limit = null, int $offset = 0): array
    {
        // Params
        $params = [$user_id];
        $types = "i";

        // SQL to select orders
        $sql = "SELECT * FROM orders WHERE user_id = ?";

        // Filter by status
        if ($statusFilter !== 'all' && !empty($statusFilter)) {
            $sql .= " AND status = ?";
            $params[] = $statusFilter;
            $types .= "s";
        }

        // Order by created_at
        $sql .= " ORDER BY created_at DESC";

        // Add limit and offset
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ii";
        }
        // Prepare and execute
        $stmt = Database::prepare($sql, $types, $params);
        if ($stmt && $stmt->execute()) {
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        if ($stmt) $stmt->close();
        return [];
    }

    /**
     * count by user
     */
    public static function countByUser(int $user_id, string $statusFilter = 'all'): int
    {
        // Params and types
        $params = [$user_id];
        $types = "i";

        // SQL query
        $sql = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";

        // Status filter
        if ($statusFilter !== 'all' && !empty($statusFilter)) {
            $sql .= " AND status = ?";
            $params[] = $statusFilter;
            $types .= "s";
        }

        // Prepare and execute
        $stmt = Database::prepare($sql, $types, $params);
        if ($stmt && $stmt->execute()) {
            $row = $stmt->get_result()->fetch_assoc();
            return $row['total'] ?? 0;
        }
        if ($stmt) $stmt->close();
        return 0;
    }

    public static function updateStatus(int $orderId, string $newStatus): bool
    {
        // update status
        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = Database::prepare($sql, "si", [$newStatus, $orderId]);
        if ($stmt && $stmt->execute()) {
            return $stmt->affected_rows === 1;
        }
        if ($stmt) $stmt->close();
        return false;
    }
     /**
     * delete an order by id
     */
    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM orders WHERE id = ?";
        $stmt = Database::prepare($sql, "i", [$id]);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}