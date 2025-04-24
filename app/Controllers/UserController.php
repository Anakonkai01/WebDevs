<?php
// Web/app/Controllers/UserController.php

require_once BASE_PATH . '/app/Controllers/BaseController.php';
require_once BASE_PATH . '/app/Models/User.php'; // Cần User model

class UserController extends BaseController {

    public function __construct() {
        // Đảm bảo session đã được khởi động
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Hiển thị form đăng nhập
     */
    public function showLoginForm() {
        // Lấy thông báo lỗi (nếu có) từ session và xóa nó đi
        $errorMessage = $_SESSION['flash_error'] ?? null;
        if ($errorMessage) {
            unset($_SESSION['flash_error']);
        }
        $this->render('login', ['errorMessage' => $errorMessage]);
    }

    /**
     * Xử lý dữ liệu từ form đăng nhập
     */
    public function handleLogin() {
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;

        // Validation đơn giản
        if (empty($username) || empty($password)) {
            $_SESSION['flash_error'] = 'Vui lòng nhập cả tên đăng nhập và mật khẩu.';
            $this->redirect('?page=login');
            return;
        }

        // Kiểm tra đăng nhập bằng User model
        if (User::login($username, $password)) {
            // Đăng nhập thành công
            $user = User::findByUsername($username); // Lấy thông tin user để lưu vào session

            // Lưu thông tin cần thiết vào session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            // Có thể lưu thêm email, role,... nếu cần

            // Quan trọng: Tạo lại session ID để tránh tấn công session fixation
            session_regenerate_id(true);

            // Chuyển hướng đến trang chủ hoặc trang tài khoản
            $this->redirect('?page=home');
        } else {
            // Đăng nhập thất bại
            $_SESSION['flash_error'] = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
            $this->redirect('?page=login');
        }
    }

    /**
     * Hiển thị form đăng ký
     */
    public function showRegisterForm() {
        // Lấy thông báo lỗi/thành công (nếu có) từ session và xóa nó đi
        $flashMessage = $_SESSION['flash_message'] ?? null;
        $formErrors = $_SESSION['form_errors'] ?? [];
        $formData = $_SESSION['form_data'] ?? []; // Giữ lại dữ liệu form cũ nếu lỗi

        unset($_SESSION['flash_message']);
        unset($_SESSION['form_errors']);
        unset($_SESSION['form_data']);

        $this->render('register', [
            'flashMessage' => $flashMessage,
            'errors' => $formErrors,
            'old' => $formData // Dữ liệu cũ để điền lại form
        ]);
    }

    /**
     * Xử lý dữ liệu từ form đăng ký
     */
    public function handleRegister() {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $errors = []; // Mảng lưu lỗi validation

        // --- Validation ---
        if (empty($username)) {
            $errors['username'] = 'Tên đăng nhập không được để trống.';
        } elseif (User::isUsernameExist($username)) {
            $errors['username'] = 'Tên đăng nhập đã tồn tại.';
        }

        if (empty($email)) {
            $errors['email'] = 'Email không được để trống.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Định dạng email không hợp lệ.';
        } elseif (User::isEmailExist($email)) {
            $errors['email'] = 'Email đã được sử dụng.';
        }

        if (empty($password)) {
            $errors['password'] = 'Mật khẩu không được để trống.';
        } elseif (strlen($password) < 6) { // Ví dụ: kiểm tra độ dài tối thiểu
            $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
        }

        if ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Xác nhận mật khẩu không khớp.';
        }
        // --- Kết thúc Validation ---

        if (!empty($errors)) {
            // Có lỗi validation
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = ['username' => $username, 'email' => $email]; // Lưu lại dữ liệu đã nhập (trừ password)
            $this->redirect('?page=register');
        } else {
            // Dữ liệu hợp lệ -> Tiến hành đăng ký
            if (User::create($username, $email, $password)) {
                // Đăng ký thành công
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.'];
                $this->redirect('?page=login'); // Chuyển đến trang đăng nhập
            } else {
                // Lỗi khi tạo user (ví dụ: lỗi DB)
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Đã có lỗi xảy ra trong quá trình đăng ký. Vui lòng thử lại.'];
                $_SESSION['form_data'] = ['username' => $username, 'email' => $email];
                $this->redirect('?page=register');
            }
        }
    }

    /**
     * Xử lý đăng xuất
     */
    public function logout() {
        // Xóa thông tin user khỏi session
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        // unset các session khác liên quan đến user nếu có

        // Không nên dùng session_destroy() nếu bạn muốn giữ lại giỏ hàng
        // session_destroy();

        // Có thể tạo lại session ID để tăng bảo mật
        session_regenerate_id(true);

        // Chuyển hướng về trang chủ
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Bạn đã đăng xuất thành công.']; // Thông báo tùy chọn
        $this->redirect('?page=home');
    }



    /**
     * Hiển thị trang thông tin cơ bản của người dùng (Profile)
     */
    public function showProfile() {
        // 1. Kiểm tra xem người dùng đã đăng nhập chưa
        if (!isset($_SESSION['user_id'])) {
            // Lưu trang định đến sau khi đăng nhập
            $_SESSION['redirect_after_login'] = '?page=profile';
            $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Vui lòng đăng nhập để xem hồ sơ.'];
            $this->redirect('?page=login'); // Chuyển hướng đến trang đăng nhập nếu chưa
            return;
        }
        $userId = $_SESSION['user_id']; // Lấy user ID

        // 2. Lấy thông tin chi tiết của người dùng từ DB *** THAY ĐỔI Ở ĐÂY ***
        $user = User::find($userId); // Gọi User Model để lấy thông tin đầy đủ

        // Kiểm tra xem có lấy được thông tin user không
        if (!$user) {
            // Xử lý trường hợp không tìm thấy user (dù đã đăng nhập - hiếm khi xảy ra)
            // Đăng xuất và báo lỗi
            unset($_SESSION['user_id']);
            unset($_SESSION['username']);
            session_regenerate_id(true);
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Lỗi: Không tìm thấy thông tin tài khoản. Vui lòng đăng nhập lại.'];
            $this->redirect('?page=login');
            return;
        }

        // 3. Lấy thông báo flash (nếu có, ví dụ sau khi đổi MK thành công)
        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($flashMessage) {
            unset($_SESSION['flash_message']); // Xóa sau khi lấy
        }

        // 4. Render view profile.php và truyền cả $user và $flashMessage *** THAY ĐỔI Ở ĐÂY ***
        $this->render('profile', [
            'user' => $user, // Truyền thông tin user đầy đủ sang view
            'flashMessage' => $flashMessage,
            'pageTitle' => 'Hồ sơ của bạn' // Đặt tiêu đề trang
        ]);
    }

    /**
     * Hiển thị form để thay đổi mật khẩu
     */
    public function showChangePasswordForm() {
        // 1. Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('?page=login');
            return;
        }

        // 2. Lấy thông báo và lỗi validation từ session (nếu có)
        $flashMessage = $_SESSION['flash_message'] ?? null;
        $errors = $_SESSION['form_errors'] ?? [];
        // Xóa khỏi session sau khi lấy
        if ($flashMessage) unset($_SESSION['flash_message']);
        if (!empty($errors)) unset($_SESSION['form_errors']);

        // 3. Render view change_password.php
        $this->render('change_password', [
            'flashMessage' => $flashMessage,
            'errors' => $errors
        ]);
    }

    /**
     * Xử lý việc thay đổi mật khẩu từ form POST
     */
    public function handleChangePassword() {
        // 1. Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('?page=login');
            return;
        }
        // Đảm bảo đây là request POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?page=change_password');
            return;
        }

        // 2. Lấy dữ liệu từ POST
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmNewPassword = $_POST['confirm_new_password'] ?? '';
        $userId = $_SESSION['user_id']; // Lấy ID user đang đăng nhập

        // 3. Thực hiện Validation
        $errors = [];
        if (empty($currentPassword)) {
            $errors['current_password'] = "Vui lòng nhập mật khẩu hiện tại.";
        }
        if (empty($newPassword)) {
            $errors['new_password'] = "Vui lòng nhập mật khẩu mới.";
        } elseif (strlen($newPassword) < 6) { // Kiểm tra độ dài tối thiểu
            $errors['new_password'] = "Mật khẩu mới phải có ít nhất 6 ký tự.";
        }
        if ($newPassword !== $confirmNewPassword) {
            $errors['confirm_new_password'] = "Xác nhận mật khẩu mới không khớp.";
        }

        // Nếu không có lỗi nhập liệu cơ bản, kiểm tra mật khẩu hiện tại
        if (empty($errors)) {
            // Gọi hàm verifyPassword vừa thêm trong User Model
            if (!User::verifyPassword($userId, $currentPassword)) {
                $errors['current_password'] = "Mật khẩu hiện tại không chính xác.";
            }
        }

        // 4. Xử lý kết quả validation
        if (!empty($errors)) {
            // Nếu có lỗi -> Lưu lỗi vào session và quay lại form đổi mật khẩu
            $_SESSION['form_errors'] = $errors;
            $this->redirect('?page=change_password');
            return;
        }

        // 5. Nếu mọi thứ hợp lệ -> Cập nhật mật khẩu mới vào DB
        $success = User::updatePassword($userId, $newPassword); // Hàm này đã tự hash mật khẩu mới

        if ($success) {
            // Đổi mật khẩu thành công
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đổi mật khẩu thành công!'];
            $this->redirect('?page=profile'); // Chuyển về trang profile
        } else {
            // Có lỗi xảy ra khi cập nhật DB
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Đã có lỗi xảy ra khi cập nhật mật khẩu. Vui lòng thử lại.'];
            $this->redirect('?page=change_password'); // Quay lại form đổi mật khẩu
        }
    }
}