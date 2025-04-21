<?php

require_once 'BaseModel.php';


class User extends BaseModel{
    protected static string $table = 'users';

    // dang ky nguoi dung moi
    public static function create(string $username, string $email, string $password): bool
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password) VALUES (?,?,?)";
        $stmt = Database::prepare($sql,"sss",[$username, $email, $hash]);
        return $stmt->execute();
    }


    // find by id
    public static function find(int $id): ?array {
        return parent::find($id);
    }

    // find by username
    public static function findByUsername(string $username): ?array
    {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = Database::prepare($sql,"s",[$username]);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()? :null;
    }


    // check login
    public static function login(string $username, string $password): bool {
        $user = self::findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            return true;
        }
        return false;
    }


    // kiem tra email da ton tai
    public static function isEmailExist(string $email): bool {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = Database::prepare($sql,"s",[$email]);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows >0;
    }

    // kiem tra username da ton tai
    public static function isUsernameExist(string $username): bool {
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = Database::prepare($sql,"s",[$username]);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows >0;
    }

    // update email
    public static function updateEmail(int $id, string $newEmail): bool {
        $sql = "UPDATE users SET email = ? WHERE id = ?";
        $stmt = Database::prepare($sql,"si",[$newEmail, $id]);
        return $stmt->execute();
    }

    // update username
    public static function updateUsername(int $id, string $newUsername): bool {
        $sql = "UPDATE users SET username = ? WHERE id = ?";
        $stmt = Database::prepare($sql,"si",[$newUsername, $id]);
        return $stmt->execute();
    }

    // doi mat khau
    public static function updatePassword(int $id, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = Database::prepare($sql,"si",[$hash, $id]);
        return $stmt->execute();
    }


    // delete account
    public static function delete(int $id): bool {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = Database::prepare($sql,"i",[$id]);
        return $stmt->execute();
    }
}
