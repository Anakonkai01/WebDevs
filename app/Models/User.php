<?php
namespace App\Models;

use App\Core\Database;
use Exception;
use DateTime; // Thêm để sử dụng DateTime cho expiry

class User extends BaseModel{
    // Tên bảng trong database
    protected static string $table = 'users';

    /**
     * Tạo người dùng mới (MẶC ĐỊNH CHƯA XÁC THỰC EMAIL).
     * @param string $username
     * @param string $email
     * @param string $password
     * @return int|false ID user nếu thành công, false nếu lỗi.
     */
    public static function createAndGetId(string $username, string $email, string $password): int|false
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password, is_email_verified, email_verification_code, email_verification_expires_at, password_reset_token, password_reset_expires_at) VALUES (?, ?, ?, 0, NULL, NULL, NULL, NULL)";
        $stmt = Database::prepare($sql, "sss", [$username, $email, $hash]);
        if ($stmt && $stmt->execute()) {
            $lastId = $stmt->insert_id;
            $stmt->close();
            return ($lastId > 0) ? $lastId : false;
        }
        if ($stmt) $stmt->close();
        error_log("Lỗi SQL khi tạo user: " . ($stmt ? $stmt->error : Database::conn()->error));
        return false;
    }

    // Tìm user theo ID
    public static function find(int $id): ?array {
        return parent::find($id);
    }

    // Tìm user theo tên đăng nhập
    public static function findByUsername(string $username): ?array {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = Database::prepare($sql,"s",[$username]);
        if ($stmt && $stmt->execute()) { $result = $stmt->get_result(); $user = $result ? $result->fetch_assoc() : null; $stmt->close(); return $user; }
        if ($stmt) $stmt->close(); return null;
    }

    // Tìm user theo email
    public static function findByEmail(string $email): ?array {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = Database::prepare($sql,"s",[$email]);
        if ($stmt && $stmt->execute()) { $result = $stmt->get_result(); $user = $result ? $result->fetch_assoc() : null; $stmt->close(); return $user; }
        if ($stmt) $stmt->close(); return null;
    }

    // Kiểm tra email đã tồn tại chưa
    public static function isEmailExist(string $email): bool {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = Database::prepare($sql,"s",[$email]);
        if ($stmt && $stmt->execute()) { $stmt->store_result(); $numRows = $stmt->num_rows; $stmt->close(); return $numRows > 0; }
        if ($stmt) $stmt->close(); return false;
    }

    // Kiểm tra tên đăng nhập đã tồn tại chưa
    public static function isUsernameExist(string $username): bool {
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = Database::prepare($sql,"s",[$username]);
        if ($stmt && $stmt->execute()) { $stmt->store_result(); $numRows = $stmt->num_rows; $stmt->close(); return $numRows > 0; }
        if ($stmt) $stmt->close(); return false;
    }

    // Cập nhật thông tin user
    public static function updateProfile(int $userId, array $updates): bool {
        if (empty($updates)) return true;

        $setClauses = [];
        $params = [];
        $types = "";

        $allowedColumns = ['username', 'email', 'is_email_verified'];

        foreach ($updates as $column => $value) {
            if (in_array($column, $allowedColumns)) {
                $setClauses[] = "`" . $column . "` = ?";
                $params[] = $value;
                $types .= ($column === 'is_email_verified') ? "i" : "s";
            } else {
                error_log("Cố gắng cập nhật cột không hợp lệ trong User::updateProfile: " . $column);
            }
        }

        if (empty($setClauses)) {
            return false;
        }

        $sql = "UPDATE users SET " . implode(', ', $setClauses) . " WHERE id = ?";
        $params[] = $userId;
        $types .= "i";

        $stmt = Database::prepare($sql, $types, $params);

        if ($stmt && $stmt->execute()) {
            $success = $stmt->affected_rows >= 0;
            $stmt->close();
            return $success;
        }

        if ($stmt) { error_log("Lỗi SQL updateProfile ID $userId: " . $stmt->error); $stmt->close(); }
        else { error_log("Lỗi prepare SQL updateProfile ID $userId: " . Database::conn()->error); }
        return false;
    }

    /**
     * Cập nhật mật khẩu
     * @param int $id User ID
     * @param string $newPassword Mật khẩu mới (chưa hash)
     * @return bool True nếu thành công
     */
    public static function updatePassword(int $id, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ?, password_reset_token = NULL, password_reset_expires_at = NULL WHERE id = ?";
        $stmt = Database::prepare($sql,"si",[$hash, $id]);
        if ($stmt && $stmt->execute()) {
            $success = true;
            $stmt->close();
            return $success;
        }
        if ($stmt) $stmt->close();
        return false;
    }

    // Xóa user
    public static function delete(int $id): bool {
        return parent::delete($id);
    }

    // Kiểm tra mật khẩu
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    // Tạo mã xác thực email
    public static function generateEmailVerificationCode(): string {
        return bin2hex(random_bytes(16));
    }

    // Tạo token reset mật khẩu
    public static function generatePasswordResetToken(): string {
        return bin2hex(random_bytes(32));
    }

    // Cập nhật mã xác thực email
    public static function updateEmailVerificationCode(int $userId, string $code): bool {
        $expiresAt = (new DateTime('+24 hours'))->format('Y-m-d H:i:s');
        $sql = "UPDATE users SET email_verification_code = ?, email_verification_expires_at = ? WHERE id = ?";
        $stmt = Database::prepare($sql, "ssi", [$code, $expiresAt, $userId]);
        if ($stmt && $stmt->execute()) {
            $success = $stmt->affected_rows === 1;
            $stmt->close();
            return $success;
        }
        if ($stmt) $stmt->close();
        return false;
    }

    // Cập nhật token reset mật khẩu
    public static function updatePasswordResetToken(int $userId, string $token): bool {
        $expiresAt = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
        $sql = "UPDATE users SET password_reset_token = ?, password_reset_expires_at = ? WHERE id = ?";
        $stmt = Database::prepare($sql, "ssi", [$token, $expiresAt, $userId]);
        if ($stmt && $stmt->execute()) {
            $success = $stmt->affected_rows === 1;
            $stmt->close();
            return $success;
        }
        if ($stmt) $stmt->close();
        return false;
    }

    // Xác thực email
    public static function verifyEmail(int $userId, string $code): bool {
        $sql = "SELECT email_verification_code, email_verification_expires_at FROM users WHERE id = ?";
        $stmt = Database::prepare($sql, "i", [$userId]);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $user = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if (!$user || !$user['email_verification_code']) {
                return false;
            }

            if ($user['email_verification_code'] !== $code) {
                return false;
            }

            $expiresAt = new DateTime($user['email_verification_expires_at']);
            if ($expiresAt < new DateTime()) {
                return false;
            }

            $updateSql = "UPDATE users SET is_email_verified = 1, email_verification_code = NULL, email_verification_expires_at = NULL WHERE id = ?";
            $updateStmt = Database::prepare($updateSql, "i", [$userId]);
            if ($updateStmt && $updateStmt->execute()) {
                $success = $updateStmt->affected_rows === 1;
                $updateStmt->close();
                return $success;
            }
            if ($updateStmt) $updateStmt->close();
        }
        if ($stmt) $stmt->close();
        return false;
    }

    // Kiểm tra token reset mật khẩu
    public static function verifyPasswordResetToken(int $userId, string $token): bool {
        $sql = "SELECT password_reset_token, password_reset_expires_at FROM users WHERE id = ?";
        $stmt = Database::prepare($sql, "i", [$userId]);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $user = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if (!$user || !$user['password_reset_token']) {
                return false;
            }

            if ($user['password_reset_token'] !== $token) {
                return false;
            }

            $expiresAt = new DateTime($user['password_reset_expires_at']);
            return $expiresAt >= new DateTime();
        }
        if ($stmt) $stmt->close();
        return false;
    }

    // --- HÀM MỚI CHO XÁC THỰC EMAIL ---

    /**
     * Lưu mã xác thực email và thời gian hết hạn cho user.
     * @param int $userId User ID
     * @param string|null $code Mã xác thực (null để xóa)
     * @param DateTime|null $expiry Thời gian hết hạn (null để xóa)
     * @return bool True nếu thành công
     */
    public static function setEmailVerificationCode(int $userId, ?string $code, ?DateTime $expiry): bool {
        $expiryTimestamp = $expiry ? $expiry->format('Y-m-d H:i:s') : null;
        $sql = "UPDATE users SET email_verification_code = ?, email_verification_expires_at = ? WHERE id = ?";
        $stmt = Database::prepare($sql, "ssi", [$code, $expiryTimestamp, $userId]);
        if ($stmt && $stmt->execute()) {
            $success = $stmt->affected_rows >= 0;
            $stmt->close();
            return $success;
        }
        if ($stmt) $stmt->close();
        error_log("Lỗi SQL setEmailVerificationCode ID $userId: " . ($stmt ? $stmt->error : Database::conn()->error));
        return false;
    }

    /**
     * Lấy mã xác thực và thời gian hết hạn.
     * @param int $userId User ID
     * @return array|null Mảng chứa ['code' => ..., 'expires_at' => ...] hoặc null nếu không tìm thấy
     */
    public static function getEmailVerificationInfo(int $userId): ?array {
        $sql = "SELECT email_verification_code, email_verification_expires_at FROM users WHERE id = ?";
        $stmt = Database::prepare($sql, "i", [$userId]);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            if ($data) {
                return [
                    'code' => $data['email_verification_code'],
                    'expires_at' => $data['email_verification_expires_at'] ? new DateTime($data['email_verification_expires_at']) : null
                ];
            }
        }
        if ($stmt) $stmt->close();
        return null;
    }

    /**
     * Đánh dấu email của user đã được xác thực.
     * @param int $userId User ID
     * @return bool True nếu thành công
     */
    public static function markEmailAsVerified(int $userId): bool {
        $sql = "UPDATE users SET is_email_verified = 1, email_verification_code = NULL, email_verification_expires_at = NULL WHERE id = ?";
        $stmt = Database::prepare($sql, "i", [$userId]);
        if ($stmt && $stmt->execute()) {
            $success = $stmt->affected_rows === 1;
            $stmt->close();
            return $success;
        }
        if ($stmt) $stmt->close();
        error_log("Lỗi SQL markEmailAsVerified ID $userId: " . ($stmt ? $stmt->error : Database::conn()->error));
        return false;
    }

    // --- HÀM MỚI CHO QUÊN MẬT KHẨU ---

    /**
     * Lưu token reset mật khẩu và thời gian hết hạn.
     * @param int $userId User ID
     * @param string|null $token Token (null để xóa)
     * @param DateTime|null $expiry Thời gian hết hạn (null để xóa)
     * @return bool True nếu thành công
     */
    public static function setPasswordResetToken(int $userId, ?string $token, ?DateTime $expiry): bool {
        $expiryTimestamp = $expiry ? $expiry->format('Y-m-d H:i:s') : null;
        $sql = "UPDATE users SET password_reset_token = ?, password_reset_expires_at = ? WHERE id = ?";
        $stmt = Database::prepare($sql, "ssi", [$token, $expiryTimestamp, $userId]);
        if ($stmt && $stmt->execute()) {
            $success = $stmt->affected_rows >= 0;
            $stmt->close();
            return $success;
        }
        if ($stmt) $stmt->close();
        error_log("Lỗi SQL setPasswordResetToken ID $userId: " . ($stmt ? $stmt->error : Database::conn()->error));
        return false;
    }

    /**
     * Tìm user bằng token reset mật khẩu và kiểm tra hạn sử dụng.
     * @param string $token Token cần tìm
     * @return array|null Thông tin user nếu token hợp lệ và chưa hết hạn, ngược lại null.
     */
    public static function findUserByResetToken(string $token): ?array {
        $sql = "SELECT * FROM users WHERE password_reset_token = ? AND password_reset_expires_at > NOW()";
        $stmt = Database::prepare($sql, "s", [$token]);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();
            $user = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            return $user;
        }
        if ($stmt) $stmt->close();
        return null;
    }

}