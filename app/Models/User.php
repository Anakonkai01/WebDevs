<?php
namespace App\Models;
use App\Core\Database;
use DateTime;
class User extends BaseModel
{
    // Table name
    protected static string $table = 'users';

    /**
     * Create a new user
     */
    public static function createAndGetId(
        string $username,
        string $email,
        string $password
    ): int|false
    {
        // Hash the password for security
        $hash = password_hash($password, PASSWORD_DEFAULT);// Hash password
        $sql = "INSERT INTO users (username, email, password, is_email_verified, email_verification_code, email_verification_expires_at, password_reset_token, password_reset_expires_at) VALUES (?, ?, ?, 0, NULL, NULL, NULL, NULL)";//sql insert
        $stmt = Database::prepare($sql, "sss", [$username, $email, $hash]);
        if ($stmt && $stmt->execute()) {
            $lastId = $stmt->insert_id;// Get id new user
            $stmt->close();
            return ($lastId > 0) ? $lastId : false; // Kiểm tra ID hợp lệ
        }
        if ($stmt) $stmt->close();
        error_log("Lỗi SQL khi tạo user: " . ($stmt ? $stmt->error : Database::conn()->error)); // Ghi log lỗi DB
        return false;
    }    
    // Find user by id
    public static function find(int $id): ?array
    {
        return parent::find($id);
    }

    // Find user by username
    public static function findByUsername(string $username): ?array
    {
        $sql = "SELECT * FROM users WHERE username = ?"; //sql
        $stmt = Database::prepare($sql, "s", [$username]);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();//get result
            $user = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            return $user;
        }
        if ($stmt) $stmt->close();
        return null;// not found
    }
    // Find user by email
    public static function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = Database::prepare($sql, "s", [$email]);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();//get result
            $user = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            return $user;
        }
        if ($stmt) $stmt->close();
        return null;
    }
    // check email
    public static function isEmailExist(string $email): bool
    {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = Database::prepare($sql, "s", [$email]);
        if ($stmt && $stmt->execute()) {
            $stmt->store_result();// store result
            $numRows = $stmt->num_rows;
            $stmt->close();
            return $numRows > 0;
        }
        if ($stmt) $stmt->close();
        return false;
    }
    // check username
    public static function isUsernameExist(string $username): bool
    {
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = Database::prepare($sql, "s", [$username]);
        if ($stmt && $stmt->execute()) {
            $stmt->store_result();//store result
            $numRows = $stmt->num_rows;
            $stmt->close();
            return $numRows > 0;
        }
        if ($stmt) $stmt->close();
        return false;
    }
    // update profile user
    public static function updateProfile(int $userId, array $updates): bool
    {
        if (empty($updates)) return true; // No updates to process

        $setClauses = [];//set clause
        $params = [];
        $types = "";

        // Thêm 'is_email_verified' vào danh sách cột được phép cập nhật
        $allowedColumns = ['username', 'email', 'is_email_verified'];
        
        foreach ($updates as $column => $value) {
            if (in_array($column, $allowedColumns)) {
                $setClauses[] = "`" . $column . "` = ?";
                $params[] = $value;
                // Determine the data type (string or integer)
                $types .= ($column === 'is_email_verified') ? "i" : "s";
            } else {
                error_log("Cố gắng cập nhật cột không hợp lệ trong User::updateProfile: " . $column);
            }
        }

        if (empty($setClauses)) return false; // No valid columns to update

        $sql = "UPDATE users SET " . implode(', ', $setClauses) . " WHERE id = ?";//sql
        $params[] = $userId;
        $types .= "i"; // Add integer type for user ID

        $stmt = Database::prepare($sql, $types, $params);//prepare sql

        if ($stmt && $stmt->execute()) {
            $success = $stmt->affected_rows >= 0;
            $stmt->close();
            return $success;
        }

        if ($stmt) {
            error_log("Lỗi SQL updateProfile ID $userId: " . $stmt->error);
            $stmt->close();
        } else {
            error_log("Lỗi prepare SQL updateProfile ID $userId: " . Database::conn()->error);
        }
        return false;
    }
    // update user password
    public static function updatePassword(int $id, string $newPassword): bool
    {
        // Hash the new password
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        // Cập nhật mật khẩu VÀ xóa token reset mật khẩu và thời gian hết hạn
        // update password and clear token
        $sql = "UPDATE users SET password = ?, password_reset_token = NULL, password_reset_expires_at = NULL WHERE id = ?";
        $stmt = Database::prepare($sql,"si",[$hash, $id]);
        if ($stmt && $stmt->execute()) {
            $success = true; // Coi là thành công nếu execute được
            $stmt->close();
            return $success;
        }
        if ($stmt) $stmt->close();
        error_log("Lỗi SQL khi cập nhật mật khẩu user ID $id: " . ($stmt ? $stmt->error : Database::conn()->error));
        return false;
    }
    // delete user
    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM users WHERE id = ?";// sql delete
        $stmt = Database::prepare($sql, "i", [$id]);
        if ($stmt && $stmt->execute()) {
            $success = $stmt->affected_rows > 0;
            $stmt->close();
            return $success;
        }
        if ($stmt) $stmt->close();
        error_log("Lỗi SQL khi xóa user ID $id: " . ($stmt ? $stmt->error : Database::conn()->error));
        return false;
    }

    // check password
    public static function verifyPassword(int $id, string $password): bool
    {
        //check password correct
        $user = self::find($id);
        if (is_array($user) && array_key_exists('password', $user)) {
            return password_verify($password, $user['password']);
        }
        return false;
    }

    //Set email verification code
    public static function setEmailVerificationCode(int $userId, ?string $code, ?DateTime $expiry): bool
    {
        //format time
        $expiryTimestamp = $expiry ? $expiry->format('Y-m-d H:i:s') : null;
        // sql update
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

    // Get email verification code
    public static function getEmailVerificationInfo(int $userId): ?array
    {
        $sql = "SELECT email_verification_code, email_verification_expires_at FROM users WHERE id = ?";
        $stmt = Database::prepare($sql, "i", [$userId]);
        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();//get result
            $data = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            if ($data) {
                return [
                    'code' => $data['email_verification_code'],
                    'expires_at' => $data['email_verification_expires_at'] ? new DateTime($data['email_verification_expires_at']) : null,
                ];
            }
        }
        if ($stmt) $stmt->close();
        return null;
    }
    // email verified
    public static function verifyEmail(int $userId): bool
    {
        //sql update email verified
        $sql = "UPDATE users SET is_email_verified = 1, email_verification_code = NULL, email_verification_expires_at = NULL WHERE id = ?";
        $stmt = Database::prepare($sql, "i", [$userId]);
        if ($stmt && $stmt->execute()) {
            $success = $stmt->affected_rows === 1; // Chỉ true nếu thực sự cập nhật được 1 dòng
            $stmt->close();
            return $success;
        }
        if ($stmt) $stmt->close();
        error_log("Lỗi SQL verifyEmail ID $userId: " . ($stmt ? $stmt->error : Database::conn()->error));
        return false;
    }
    //Set password reset token
    public static function setPasswordResetToken(int $userId, ?string $token, ?DateTime $expiry): bool
    {
        //set expiry time
        //format time
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

    //Find User By Reset Token
    public static function findUserByResetToken(string $token): ?array
    {
        $sql = "SELECT * FROM users WHERE password_reset_token = ? AND password_reset_expires_at > NOW()";//sql
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