<?php
namespace App\Models;

use App\Core\Database;
use Exception; // Giữ lại Exception nếu dùng ở đâu đó

class User extends BaseModel{
    protected static string $table = 'users';

    /**
     * Tạo người dùng mới (mặc định đã kích hoạt).
     * @param string $username
     * @param string $email
     * @param string $password
     * @return int|false ID user nếu thành công, false nếu lỗi.
     */
    public static function createAndGetId(string $username, string $email, string $password): int|false
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        // is_email_verified đã bị xóa khỏi bảng
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = Database::prepare($sql,"sss",[$username, $email, $hash]);
        if ($stmt && $stmt->execute()) {
            $lastId = $stmt->insert_id;
            $stmt->close();
            return ($lastId > 0) ? $lastId : false; // Kiểm tra ID hợp lệ
        }
        if ($stmt) $stmt->close();
        error_log("Lỗi SQL khi tạo user: " . ($stmt ? $stmt->error : Database::conn()->error)); // Ghi log lỗi DB
        return false;
    }

    // --- Các hàm tìm kiếm và kiểm tra tồn tại (Giữ nguyên) ---
    public static function find(int $id): ?array {
        return parent::find($id);
    }
    public static function findByUsername(string $username): ?array {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = Database::prepare($sql,"s",[$username]);
        if ($stmt && $stmt->execute()) { $result = $stmt->get_result(); $user = $result ? $result->fetch_assoc() : null; $stmt->close(); return $user; }
        if ($stmt) $stmt->close(); return null;
    }
    public static function findByEmail(string $email): ?array {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = Database::prepare($sql,"s",[$email]);
        if ($stmt && $stmt->execute()) { $result = $stmt->get_result(); $user = $result ? $result->fetch_assoc() : null; $stmt->close(); return $user; }
        if ($stmt) $stmt->close(); return null;
    }
    public static function isEmailExist(string $email): bool {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = Database::prepare($sql,"s",[$email]);
        if ($stmt && $stmt->execute()) { $stmt->store_result(); $numRows = $stmt->num_rows; $stmt->close(); return $numRows > 0; }
        if ($stmt) $stmt->close(); return false;
    }
    public static function isUsernameExist(string $username): bool {
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = Database::prepare($sql,"s",[$username]);
        if ($stmt && $stmt->execute()) { $stmt->store_result(); $numRows = $stmt->num_rows; $stmt->close(); return $numRows > 0; }
        if ($stmt) $stmt->close(); return false;
    }

    // --- Các hàm cập nhật (Giữ nguyên hoặc cải thiện kiểm tra lỗi) ---
    /**
     * Cập nhật username và/hoặc email.
     * @param int $userId
     * @param array $updates Mảng dạng ['username' => 'new_user', 'email' => 'new@email.com']
     * @return bool
     */
    public static function updateProfile(int $userId, array $updates): bool {
        if (empty($updates)) return true; // Không có gì để cập nhật
        $setClauses = []; $params = []; $types = "";
        foreach ($updates as $column => $value) {
            // Chỉ cho phép cập nhật username và email
            if (in_array($column, ['username', 'email'])) {
                $setClauses[] = "`" . $column . "` = ?";
                $params[] = $value;
                $types .= "s";
            } else {
                // Ghi log hoặc bỏ qua nếu cố gắng cập nhật cột không hợp lệ
                error_log("Cố gắng cập nhật cột không hợp lệ trong User::updateProfile (simplified): " . $column);
                // return false; // Hoặc trả về false nếu muốn chặt chẽ hơn
            }
        }

        // Nếu không có cột hợp lệ nào được chọn để cập nhật
        if (empty($setClauses)) {
            return false; // Hoặc true nếu coi như thành công vì không có gì sai
        }

        $sql = "UPDATE users SET " . implode(', ', $setClauses) . " WHERE id = ?";
        $params[] = $userId; $types .= "i";
        $stmt = Database::prepare($sql, $types, $params);
        if ($stmt && $stmt->execute()) {
            $success = $stmt->affected_rows >= 0; // Chấp nhận 0 nếu không có thay đổi thực sự
            $stmt->close();
            return $success;
        }
        if ($stmt) { error_log("Lỗi SQL updateProfile (simplified) ID $userId: " . $stmt->error); $stmt->close(); }
        else { error_log("Lỗi prepare SQL updateProfile (simplified) ID $userId: " . Database::conn()->error); }
        return false;
    }


    public static function updatePassword(int $id, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        // Không cần xóa token reset mật khẩu nữa
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = Database::prepare($sql,"si",[$hash, $id]);
        if ($stmt && $stmt->execute()) {
            // Password có thể giống hệt password cũ, affected_rows có thể là 0
            // Nên coi là thành công nếu execute được
            $success = true; //$stmt->affected_rows >= 0; // Chấp nhận 0 hoặc 1
            $stmt->close();
            return $success;
        }
        if ($stmt) $stmt->close();
        error_log("Lỗi SQL khi cập nhật mật khẩu user ID $id: " . ($stmt ? $stmt->error : Database::conn()->error));
        return false;
    }

    public static function delete(int $id): bool {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = Database::prepare($sql,"i",[$id]);
        if ($stmt && $stmt->execute()) { $success = $stmt->affected_rows > 0; $stmt->close(); return $success; }
        if ($stmt) $stmt->close(); error_log("Lỗi SQL khi xóa user ID $id: " . ($stmt ? $stmt->error : Database::conn()->error)); return false;
    }

    public static function verifyPassword(int $id, string $password): bool {
        $user = self::find($id);
        // Đảm bảo $user là mảng và có key 'password'
        if (is_array($user) && isset($user['password'])) {
            return password_verify($password, $user['password']);
        }
        return false;
    }

    // --- CÁC HÀM LIÊN QUAN ĐẾN TOKEN EMAIL/RESET ĐÃ BỊ XÓA ---

}