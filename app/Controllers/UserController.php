<?php
// Web/app/Controllers/UserController.php

namespace App\Controllers; // Đảm bảo namespace đúng

use App\Models\User; // Sử dụng User model

class UserController extends BaseController {

    // --- HIỂN THỊ FORM ---
    public function showLoginForm() {
        // Đảm bảo session được khởi tạo (thường BaseController đã làm)
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        // Lấy lỗi flash (nếu có) từ lần submit trước
        $errorMessage = $_SESSION['flash_error'] ?? null;
        if ($errorMessage) { unset($_SESSION['flash_error']); } // Xóa lỗi sau khi đọc
        // Render view, truyền lỗi (nếu có)
        $this->render('login', ['errorMessage' => $errorMessage]);
    }

    public function showRegisterForm() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        // Lấy lỗi validation và dữ liệu cũ (nếu có) từ lần submit trước
        $formErrors = $_SESSION['form_errors'] ?? [];
        $formData = $_SESSION['form_data'] ?? [];
        // Xóa khỏi session sau khi đọc
        unset($_SESSION['form_errors'], $_SESSION['form_data']);
        // Render view, truyền lỗi và dữ liệu cũ
        $this->render('register', ['errors' => $formErrors, 'old' => $formData]);
    }

    public function showProfile() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '?page=profile';
            $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Vui lòng đăng nhập để xem hồ sơ.'];
            $this->redirect('?page=login'); return;
        }
        // Lấy thông tin user
        $userId = $_SESSION['user_id'];
        $user = User::find($userId);
        // Kiểm tra user có tồn tại không
        if (!$user) {
            // Nếu user không tồn tại trong DB (có thể bị xóa?), hủy session và báo lỗi
            unset($_SESSION['user_id'], $_SESSION['username']); session_regenerate_id(true);
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Lỗi: Không tìm thấy thông tin tài khoản. Vui lòng đăng nhập lại.'];
            $this->redirect('?page=login'); return;
        }
        // Render view profile
        $this->render('profile', ['user' => $user, 'pageTitle' => 'Hồ sơ của bạn' ]);
    }

    public function showEditProfileForm() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '?page=edit_profile';
            $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Vui lòng đăng nhập để chỉnh sửa hồ sơ.'];
            $this->redirect('?page=login'); return;
        }
        // Lấy thông tin user
        $userId = $_SESSION['user_id'];
        $user = User::find($userId);
        if (!$user) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Lỗi không tìm thấy thông tin tài khoản.'];
            $this->redirect('?page=profile'); return; // Quay về trang profile
        }
        // Lấy lỗi và dữ liệu cũ từ session (nếu có sau lần submit lỗi)
        $errors = $_SESSION['form_errors'] ?? [];
        $oldData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_errors'], $_SESSION['form_data']);
        // Render view edit_profile
        $this->render('edit_profile', [ 'user' => $user, 'errors' => $errors, 'old' => $oldData, 'pageTitle' => 'Chỉnh sửa Hồ sơ' ]);
    }

    public function showChangePasswordForm() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) { $this->redirect('?page=login'); return; }
        // Lấy lỗi từ session (nếu có sau lần submit lỗi)
        $errors = $_SESSION['form_errors'] ?? [];
        if (!empty($errors)) unset($_SESSION['form_errors']); // Xóa lỗi sau khi đọc
        // Render view change_password
        $this->render('change_password', ['errors' => $errors, 'pageTitle' => 'Đổi mật khẩu' ]);
    }

    // --- XỬ LÝ ĐĂNG KÝ ---
    public function handleRegister() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        // Lấy dữ liệu từ POST và trim khoảng trắng
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        // Validate dữ liệu nhập vào
        $errors = $this->validateRegistrationInput($_POST);

        // Nếu không có lỗi validation cơ bản, kiểm tra username/email tồn tại
        if (empty($errors)) {
            if (User::isUsernameExist($username)) { $errors['username'] = 'Tên đăng nhập này đã được sử dụng.'; }
            if (User::isEmailExist($email)) { $errors['email'] = 'Địa chỉ email này đã được sử dụng.'; }
        }

        // Nếu có bất kỳ lỗi nào
        if (!empty($errors)) {
            // Lưu lỗi và dữ liệu cũ vào session để hiển thị lại trên form
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST; // Lưu toàn bộ dữ liệu đã nhập
            $this->redirect('?page=register'); // Quay lại trang đăng ký
            exit;
        } else {
            // Nếu không có lỗi, tạo người dùng mới
            $userId = User::createAndGetId($username, $email, $password);
            // Kiểm tra tạo user thành công
            if ($userId) {
                // Tạo thành công, đặt thông báo thành công và chuyển hướng đến login
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.'];
                $this->redirect('?page=login');
                exit;
            } else {
                // Lỗi khi tạo user (ví dụ lỗi DB)
                error_log("Lỗi tạo user mới với username: $username, email: $email"); // Ghi log lỗi
                // Đặt thông báo lỗi và quay lại trang đăng ký với dữ liệu cũ
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Đã có lỗi xảy ra trong quá trình đăng ký. Vui lòng thử lại.'];
                $_SESSION['form_data'] = $_POST;
                $this->redirect('?page=register');
                exit;
            }
        }
    }

    // --- XỬ LÝ ĐĂNG NHẬP ---
    public function handleLogin() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        // Lấy username/email và password từ POST
        $loginInput = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? null;

        // Kiểm tra nhập liệu cơ bản
        if (empty($loginInput) || empty($password)) {
            $_SESSION['flash_error'] = 'Vui lòng nhập Tên đăng nhập/Email và Mật khẩu.';
            $this->redirect('?page=login'); return;
        }

        // Tìm người dùng trong DB bằng username hoặc email
        $user = User::findByUsername($loginInput) ?? User::findByEmail($loginInput);

        // Nếu tìm thấy user VÀ mật khẩu khớp
        if ($user && password_verify($password, $user['password'])) {
            // Tạo session ID mới để bảo mật
            session_regenerate_id(true);
            // Lưu thông tin user vào session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            // Lấy URL để chuyển hướng sau khi đăng nhập (nếu có) hoặc về trang chủ
            $redirectUrl = $_SESSION['redirect_after_login'] ?? '?page=home';
            unset($_SESSION['redirect_after_login']); // Xóa URL redirect khỏi session
            // Thực hiện chuyển hướng
            $this->redirect($redirectUrl);
            exit;
        } else {
            // Nếu không tìm thấy user hoặc mật khẩu sai
            $_SESSION['flash_error'] = 'Tên đăng nhập/Email hoặc Mật khẩu không chính xác.';
            $this->redirect('?page=login'); // Quay lại trang login
            exit;
        }
    }

    // --- XỬ LÝ ĐĂNG XUẤT ---
    public function logout() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        // Xóa thông tin user khỏi session
        unset($_SESSION['user_id'], $_SESSION['username']);
        // Hủy session hiện tại
        session_destroy();
        // Bắt đầu session mới để lưu flash message
        session_start();
        session_regenerate_id(true); // Tạo ID mới
        // Đặt thông báo đăng xuất thành công
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Bạn đã đăng xuất thành công.'];
        // Chuyển hướng về trang chủ
        $this->redirect('?page=home');
        exit;
    }

    // --- XỬ LÝ CẬP NHẬT HỒ SƠ ---
    public function handleUpdateProfile() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        // Kiểm tra đăng nhập và phương thức POST
        if (!isset($_SESSION['user_id'])) { $this->redirect('?page=login'); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('?page=edit_profile'); return; }

        // Lấy dữ liệu
        $userId = $_SESSION['user_id'];
        $newUsername = trim($_POST['username'] ?? '');
        $newEmail = trim($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $currentUser = User::find($userId);

        // Kiểm tra user tồn tại
        if (!$currentUser) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Lỗi không tìm thấy tài khoản.'];
            $this->redirect('?page=profile'); return;
        }

        // Xác thực mật khẩu hiện tại
        if (!User::verifyPassword($userId, $currentPassword)) {
            // Sử dụng helper để quay lại với lỗi và dữ liệu cũ
            $this->redirectBackWithErrors(['current_password' => 'Mật khẩu hiện tại không chính xác.'], $_POST, '?page=edit_profile');
            return;
        }

        // Validate username và email mới
        $errors = []; $updates = []; // Mảng chứa các thay đổi hợp lệ
        if ($newUsername !== $currentUser['username']) { // Chỉ validate nếu có thay đổi
            if (empty($newUsername)) { $errors['username'] = 'Tên đăng nhập không được để trống.'; }
            elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $newUsername)) { $errors['username'] = "Tên đăng nhập không hợp lệ (chỉ chữ, số, _, 3-20 ký tự)."; }
            elseif (User::isUsernameExist($newUsername)) { $errors['username'] = 'Tên đăng nhập này đã được sử dụng.'; }
            else { $updates['username'] = $newUsername; } // Thêm vào mảng cập nhật
        }
        if ($newEmail !== $currentUser['email']) { // Chỉ validate nếu có thay đổi
            if (empty($newEmail)) { $errors['email'] = 'Email không được để trống.'; }
            elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Định dạng email không hợp lệ.'; }
            elseif (User::isEmailExist($newEmail)) { $errors['email'] = 'Địa chỉ email này đã được sử dụng.'; }
            else { $updates['email'] = $newEmail; } // Thêm vào mảng cập nhật
        }

        // Nếu có lỗi validation
        if (!empty($errors)) {
            $this->redirectBackWithErrors($errors, $_POST, '?page=edit_profile');
            return;
        }
        // Nếu không có thông tin nào thay đổi
        if (empty($updates)) {
            $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Không có thông tin nào được thay đổi.'];
            $this->redirect('?page=profile'); return;
        }

        // Thực hiện cập nhật vào DB
        $updateSuccess = User::updateProfile($userId, $updates);

        if ($updateSuccess) {
            // Cập nhật session nếu username thay đổi
            if (isset($updates['username'])) { $_SESSION['username'] = $updates['username']; }
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Hồ sơ đã được cập nhật thành công.'];
            $this->redirect('?page=profile');
        } else {
            // Lỗi DB, quay lại form với thông báo lỗi
            $this->redirectBackWithErrors(['database' => 'Lỗi cập nhật hồ sơ trong cơ sở dữ liệu.'], $_POST, '?page=edit_profile');
        }
    }

    // --- XỬ LÝ ĐỔI MẬT KHẨU ---
    public function handleChangePassword() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        // Kiểm tra đăng nhập và phương thức POST
        if (!isset($_SESSION['user_id'])) { $this->redirect('?page=login'); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('?page=change_password'); return; }

        // Lấy dữ liệu
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmNewPassword = $_POST['confirm_new_password'] ?? '';
        $userId = $_SESSION['user_id'];
        $errors = [];

        // --- Validation ---
        if (empty($currentPassword)) { $errors['current_password'] = "Vui lòng nhập mật khẩu hiện tại."; }
        if (empty($newPassword)) { $errors['new_password'] = "Vui lòng nhập mật khẩu mới."; }
        elseif (strlen($newPassword) < 6) { $errors['new_password'] = "Mật khẩu mới phải có ít nhất 6 ký tự."; }
        if ($newPassword !== $confirmNewPassword) { $errors['confirm_new_password'] = "Xác nhận mật khẩu mới không khớp."; }

        // Nếu validation cơ bản OK, kiểm tra mật khẩu hiện tại và trùng lặp
        if (empty($errors)) {
            $currentUser = User::find($userId); // Lấy user để kiểm tra mật khẩu
            if (!$currentUser || !User::verifyPassword($userId, $currentPassword)) { // Kiểm tra mật khẩu hiện tại
                $errors['current_password'] = "Mật khẩu hiện tại không chính xác.";
            } elseif (password_verify($newPassword, $currentUser['password'])) { // Kiểm tra trùng mật khẩu cũ
                $errors['new_password'] = "Mật khẩu mới không được trùng với mật khẩu hiện tại.";
            }
        }
        // --- Kết thúc Validation ---

        // Nếu có lỗi
        if (!empty($errors)) {
            // Quay lại form với lỗi (không cần dữ liệu cũ cho form này)
            $this->redirectBackWithErrors($errors, [], '?page=change_password');
            return;
        }

        // Nếu không có lỗi, cập nhật mật khẩu mới
        $success = User::updatePassword($userId, $newPassword);

        if ($success) {
            // Thành công, đặt thông báo và về trang profile
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đổi mật khẩu thành công!'];
            $this->redirect('?page=profile');
        } else {
            // Lỗi DB, quay lại form với thông báo lỗi
            $this->redirectBackWithErrors(['database' => 'Đã có lỗi xảy ra khi cập nhật mật khẩu.'], [], '?page=change_password');
        }
    }

    // --- CÁC PHƯƠNG THỨC QUÊN/RESET MẬT KHẨU ĐÃ BỊ XÓA ---

    // --- HÀM HELPER ---
    // Helper validate dữ liệu đăng ký
    private function validateRegistrationInput(array $postData): array {
        $errors = [];
        if (empty($postData['username'])) { $errors['username'] = "Tên đăng nhập không được để trống."; }
        elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $postData['username'])) { $errors['username'] = "Tên đăng nhập chỉ chứa chữ cái, số, dấu gạch dưới và dài 3-20 ký tự."; }
        if (empty($postData['email'])) { $errors['email'] = "Email không được để trống."; }
        elseif (!filter_var($postData['email'], FILTER_VALIDATE_EMAIL)) { $errors['email'] = "Định dạng email không hợp lệ."; }
        if (empty($postData['password'])) { $errors['password'] = "Mật khẩu không được để trống."; }
        elseif (strlen($postData['password']) < 6) { $errors['password'] = "Mật khẩu phải có ít nhất 6 ký tự."; }
        if (empty($postData['password_confirm'])) { $errors['password_confirm'] = "Vui lòng xác nhận mật khẩu."; }
        elseif ($postData['password'] !== $postData['password_confirm']) { $errors['password_confirm'] = "Xác nhận mật khẩu không khớp."; }
        return $errors;
    }

    // Helper để redirect quay lại trang trước đó với lỗi và dữ liệu form cũ
    private function redirectBackWithErrors(array $errors, array $postData, string $targetPage): void {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $_SESSION['form_errors'] = $errors; // Lưu mảng lỗi
        $_SESSION['form_data'] = $postData; // Lưu dữ liệu người dùng đã nhập
        $this->redirect($targetPage); // Chuyển hướng về trang mục tiêu
        exit; // Dừng script
    }

} // End Class UserController