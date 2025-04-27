<?php
// Web/app/Controllers/UserController.php

namespace App\Controllers; // Đảm bảo namespace đúng

use App\Models\User; // Sử dụng User model
use PHPMailer\PHPMailer\PHPMailer; // Thêm PHPMailer
use PHPMailer\PHPMailer\Exception as PHPMailerException; // Thêm Exception
use DateTime; // Thêm DateTime

class UserController extends BaseController {

    /**
     * Gửi email sử dụng cấu hình trong config.php
     * @param string $toEmail Địa chỉ người nhận
     * @param string $toName Tên người nhận
     * @param string $subject Chủ đề email
     * @param string $htmlBody Nội dung email (HTML)
     * @return string|bool True nếu gửi thành công, chuỗi lỗi (string) nếu thất bại
     */
    protected function sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody): string|bool {
        $mail = new PHPMailer(true); // Enable exceptions

        try {
            // Server settings from config.php
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = MAIL_ENCRYPTION; 
            $mail->Port       = MAIL_PORT;
            $mail->CharSet    = PHPMailer::CHARSET_UTF8;

            // Recipients
            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addAddress($toEmail, $toName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody); // Tạo nội dung text đơn giản tự động

            $mail->send();
            return true; // Trả về true nếu thành công
        } catch (PHPMailerException $e) {
            error_log("PHPMailer Error sending email to {$toEmail}: {$mail->ErrorInfo}");
            return $mail->ErrorInfo; // *** Trả về chuỗi lỗi ErrorInfo ***
        } catch (\Exception $e) {
            error_log("General Error sending email to {$toEmail}: {$e->getMessage()}");
            return "Lỗi hệ thống: " . $e->getMessage(); // *** Trả về chuỗi lỗi chung ***
        }
    }

    // --- HIỂN THỊ FORM ---
    public function showLoginForm() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $errorMessage = $_SESSION['flash_error'] ?? null;
        // Lấy thêm flash message nếu có (ví dụ sau khi xác thực thành công)
        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($errorMessage) { unset($_SESSION['flash_error']); }
        if ($flashMessage) { unset($_SESSION['flash_message']); }
        $this->render('login', ['errorMessage' => $errorMessage, 'flashMessage' => $flashMessage]); // Truyền cả flashMessage
    }

    public function showRegisterForm() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $formErrors = $_SESSION['form_errors'] ?? [];
        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_errors'], $_SESSION['form_data']);
        $this->render('register', ['errors' => $formErrors, 'old' => $formData]);
    }

    public function showProfile() {
         if (session_status() == PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '?page=profile';
            $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Vui lòng đăng nhập để xem hồ sơ.'];
            $this->redirect('?page=login'); return;
        }
        $userId = $_SESSION['user_id'];
        $user = User::find($userId);
        if (!$user) {
            unset($_SESSION['user_id'], $_SESSION['username']); session_regenerate_id(true);
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Lỗi: Không tìm thấy thông tin tài khoản. Vui lòng đăng nhập lại.'];
            $this->redirect('?page=login'); return;
        }
        $this->render('profile', ['user' => $user, 'pageTitle' => 'Hồ sơ của bạn' ]);
    }

    public function showEditProfileForm() {
         if (session_status() == PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '?page=edit_profile';
            $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Vui lòng đăng nhập để chỉnh sửa hồ sơ.'];
            $this->redirect('?page=login'); return;
        }
        $userId = $_SESSION['user_id'];
        $user = User::find($userId);
        if (!$user) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Lỗi không tìm thấy thông tin tài khoản.'];
            $this->redirect('?page=profile'); return;
        }
        $errors = $_SESSION['form_errors'] ?? [];
        $oldData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_errors'], $_SESSION['form_data']);
        $this->render('edit_profile', [ 'user' => $user, 'errors' => $errors, 'old' => $oldData, 'pageTitle' => 'Chỉnh sửa Hồ sơ' ]);
    }

    public function showChangePasswordForm() {
         if (session_status() == PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user_id'])) { $this->redirect('?page=login'); return; }
        $errors = $_SESSION['form_errors'] ?? [];
        if (!empty($errors)) unset($_SESSION['form_errors']);
        $this->render('change_password', ['errors' => $errors, 'pageTitle' => 'Đổi mật khẩu' ]);
    }

    // --- XỬ LÝ ĐĂNG KÝ (CÓ XÁC THỰC EMAIL) ---
    public function handleRegister() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = $this->validateRegistrationInput($_POST);

        if (empty($errors)) {
            if (User::isUsernameExist($username)) { $errors['username'] = 'Tên đăng nhập này đã được sử dụng.'; }
            if (User::isEmailExist($email)) { $errors['email'] = 'Địa chỉ email này đã được sử dụng.'; }
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            $this->redirect('?page=register');
            exit;
        } else {
            $userId = User::createAndGetId($username, $email, $password);

            if ($userId) {
                // --- GỬI EMAIL XÁC THỰC ---
                try {
                    $verificationCode = random_int(100000, 999999); // Tạo mã 6 chữ số
                    $expiryTime = (new DateTime())->modify('+15 minutes'); // Hết hạn sau 15 phút
                    User::setEmailVerificationCode($userId, (string)$verificationCode, $expiryTime); // Lưu mã vào DB

                    $subject = "MyShop - Xác thực địa chỉ Email của bạn";
                    $body = "Chào {$username},<br><br>" .
                            "Mã xác thực tài khoản MyShop của bạn là: <strong>{$verificationCode}</strong><br>" .
                            "Mã này sẽ hết hạn sau 15 phút.<br><br>" .
                            "Nếu bạn không đăng ký tài khoản này, vui lòng bỏ qua email này.<br><br>" .
                            "Trân trọng,<br>Đội ngũ MyShop";

                    $sendResult = $this->sendEmail($email, $username, $subject, $body);

                    if ($sendResult === true) { // Kiểm tra thành công
                        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đăng ký thành công! Vui lòng kiểm tra email (' . $email . ') để nhập mã xác thực.'];
                        // Chuyển hướng đến trang nhập mã xác thực
                        $this->redirect('?page=verify_email&email=' . urlencode($email)); // Đã sửa chuyển hướng
                        exit;
                    } else {
                        // Gửi email thất bại, $sendResult chứa chuỗi lỗi
                        error_log("Lỗi gửi email xác thực cho user ID: $userId, email: $email - Chi tiết: " . $sendResult);
                        // Hiển thị lỗi chi tiết để debug
                        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Lỗi gửi email xác thực: ' . htmlspecialchars($sendResult)];
                        User::delete($userId);
                        $_SESSION['flash_message']['message'] = 'Đã xảy ra lỗi khi gửi email xác thực. Vui lòng thử đăng ký lại.';
                        $this->redirect('?page=register'); // Redirect về trang đăng ký với lỗi
                        exit;
                    }
                } catch (\Exception $e) {
                     error_log("Lỗi tạo mã xác thực hoặc DateTime cho user ID: $userId, email: $email - " . $e->getMessage());
                     $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Đăng ký thành công nhưng có lỗi khi tạo mã xác thực. Vui lòng liên hệ hỗ trợ.'];
                     $this->redirect('?page=login');
                     exit;
                }
                // --- KẾT THÚC GỬI EMAIL XÁC THỰC ---

            } else {
                error_log("Lỗi tạo user mới với username: $username, email: $email");
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Đã có lỗi xảy ra trong quá trình đăng ký. Vui lòng thử lại.'];
                $_SESSION['form_data'] = $_POST;
                $this->redirect('?page=register');
                exit;
            }
        }
    }

    // --- XỬ LÝ ĐĂNG NHẬP (KIỂM TRA XÁC THỰC EMAIL) ---
    public function handleLogin() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $loginInput = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? null;

        if (empty($loginInput) || empty($password)) {
            $_SESSION['flash_error'] = 'Vui lòng nhập Tên đăng nhập/Email và Mật khẩu.';
            $this->redirect('?page=login'); return;
        }

        $user = User::findByUsername($loginInput) ?? User::findByEmail($loginInput);

        if ($user && password_verify($password, $user['password'])) {
            // --- KIỂM TRA XÁC THỰC EMAIL ---
            if ($user['is_email_verified'] != 1) {
                 $_SESSION['flash_error'] = 'Tài khoản của bạn chưa được xác thực. Vui lòng kiểm tra email hoặc nhập mã xác thực.';
                 // Chuyển hướng đến trang yêu cầu nhập mã, gửi kèm email
                 $this->redirect('?page=verify_email&email=' . urlencode($user['email']));
                 exit;
            }
            // --- KẾT THÚC KIỂM TRA ---

            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $redirectUrl = $_SESSION['redirect_after_login'] ?? '?page=home';
            unset($_SESSION['redirect_after_login']);
            $this->redirect($redirectUrl);
            exit;
        } else {
            $_SESSION['flash_error'] = 'Tên đăng nhập/Email hoặc Mật khẩu không chính xác.';
            $this->redirect('?page=login');
            exit;
        }
    }

    // --- ĐĂNG XUẤT ---
    public function logout() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        unset($_SESSION['user_id'], $_SESSION['username']);
        session_destroy();
        session_start();
        session_regenerate_id(true);
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Bạn đã đăng xuất thành công.'];
        $this->redirect('?page=home');
        exit;
    }

// --- CẬP NHẬT HỒ SƠ ---
public function handleUpdateProfile() {
    if (session_status() == PHP_SESSION_NONE) { session_start(); }
    if (!isset($_SESSION['user_id'])) { $this->redirect('?page=login'); return; }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('?page=edit_profile'); return; }

    $userId = $_SESSION['user_id'];
    $newUsername = trim($_POST['username'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $currentUser = User::find($userId);

    if (!$currentUser) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Lỗi không tìm thấy tài khoản.'];
        $this->redirect('?page=profile'); return;
    }

    // Kiểm tra mật khẩu hiện tại
    if (!User::verifyPassword($userId, $currentPassword)) {
        $this->redirectBackWithErrors(['current_password' => 'Mật khẩu hiện tại không chính xác.'], $_POST, '?page=edit_profile');
        return;
    }

    $errors = [];
    $updates = [];
    $emailChanged = false; // Cờ kiểm tra email có thay đổi không

    // Xử lý username (giữ nguyên)
    if ($newUsername !== $currentUser['username']) {
        if (empty($newUsername)) { $errors['username'] = 'Tên đăng nhập không được để trống.'; }
        elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $newUsername)) { $errors['username'] = "Tên đăng nhập không hợp lệ (chữ, số, _, 3-20 ký tự)."; }
        elseif (User::isUsernameExist($newUsername)) { $errors['username'] = 'Tên đăng nhập này đã được sử dụng.'; }
        else { $updates['username'] = $newUsername; }
    }

    // Xử lý email
    if ($newEmail !== $currentUser['email']) {
        if (empty($newEmail)) {
            $errors['email'] = 'Email không được để trống.';
        } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Định dạng email không hợp lệ.';
        } elseif (User::isEmailExist($newEmail)) {
            $errors['email'] = 'Địa chỉ email này đã được sử dụng.';
        } else {
            $updates['email'] = $newEmail;
            $updates['is_email_verified'] = 0; // Quan trọng: Đặt lại trạng thái xác thực
            $emailChanged = true;
        }
    }

    // Nếu có lỗi validation
    if (!empty($errors)) {
        $this->redirectBackWithErrors($errors, $_POST, '?page=edit_profile');
        return;
    }

    // Nếu không có gì để cập nhật
    if (empty($updates)) {
        $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Không có thông tin nào được thay đổi.'];
        $this->redirect('?page=profile');
        return;
    }

    // Thực hiện cập nhật vào DB trước
    $updateSuccess = User::updateProfile($userId, $updates);

    if ($updateSuccess) {
        // Cập nhật session username nếu nó thay đổi
        if (isset($updates['username'])) {
            $_SESSION['username'] = $updates['username'];
        }

        // Nếu email đã thay đổi -> gửi mail xác thực và chuyển hướng
        if ($emailChanged) {
            try {
                $verificationCode = random_int(100000, 999999);
                $expiryTime = (new DateTime())->modify('+15 minutes');

                // Lưu mã code VÀO DB SAU KHI ĐÃ CẬP NHẬT EMAIL MỚI THÀNH CÔNG
                if (!User::setEmailVerificationCode($userId, (string)$verificationCode, $expiryTime)) {
                    // Lỗi khi lưu mã code -> Báo lỗi và chuyển về profile
                    error_log("Lỗi lưu mã xác thực đổi email cho user ID: $userId");
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Hồ sơ đã cập nhật nhưng lỗi khi tạo mã xác thực email mới. Vui lòng liên hệ hỗ trợ.'];
                    $this->redirect('?page=profile');
                    exit; // DỪNG LẠI
                }

                // Chuẩn bị gửi email
                $subject = "MyShop - Xác thực địa chỉ Email mới của bạn";
                // Lấy username mới nhất (có thể vừa được cập nhật)
                $emailRecipientName = $updates['username'] ?? $currentUser['username'];
                $body = "Chào {$emailRecipientName},<br><br>" .
                        "Bạn đã yêu cầu thay đổi email tài khoản MyShop của mình.<br>" .
                        "Mã xác thực cho địa chỉ email mới ({$newEmail}) của bạn là: <strong>{$verificationCode}</strong><br>" .
                        "Mã này sẽ hết hạn sau 15 phút.<br><br>" .
                        "Vui lòng nhập mã này trên trang web để hoàn tất việc thay đổi email.<br><br>" .
                        "Trân trọng,<br>Đội ngũ MyShop";

                // Gửi email ĐẾN ĐỊA CHỈ EMAIL MỚI
                $sendResult = $this->sendEmail($newEmail, $emailRecipientName, $subject, $body);

                // Xử lý kết quả gửi mail
                if ($sendResult === true) {
                    // Gửi thành công -> Thông báo và chuyển hướng đến trang verify_email
                    $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Hồ sơ đã được cập nhật. Vui lòng kiểm tra email mới (' . $newEmail . ') và nhập mã xác thực để hoàn tất.'];
                    $this->redirect('?page=verify_email&email=' . urlencode($newEmail));
                    exit; // DỪNG LẠI
                } else {
                    // Gửi thất bại -> Thông báo lỗi và chuyển hướng về profile
                    error_log("Lỗi gửi email xác thực đổi email cho user ID: $userId, email mới: $newEmail - Chi tiết: " . $sendResult);
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Hồ sơ đã được cập nhật, nhưng không thể gửi email xác thực đến địa chỉ mới. Lỗi: ' . htmlspecialchars($sendResult) . '. Vui lòng liên hệ hỗ trợ.'];
                    $this->redirect('?page=profile');
                    exit; // DỪNG LẠI
                }
            } catch (\Exception $e) {
                // Lỗi Exception (ví dụ: random_int, DateTime) -> Báo lỗi và chuyển về profile
                error_log("Lỗi Exception khi đổi email user ID: $userId - " . $e->getMessage());
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Hồ sơ đã cập nhật, nhưng có lỗi hệ thống khi xử lý email mới. Vui lòng liên hệ hỗ trợ.'];
                $this->redirect('?page=profile');
                exit; // DỪNG LẠI
            }
        } else {
            // Chỉ cập nhật username -> Thông báo thành công và chuyển hướng về profile
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Hồ sơ đã được cập nhật thành công.'];
            $this->redirect('?page=profile');
            exit; // DỪNG LẠI
        }
    } else {
        // Lỗi cập nhật DB -> Quay lại form edit với lỗi
        $this->redirectBackWithErrors(['database' => 'Lỗi cập nhật hồ sơ trong cơ sở dữ liệu.'], $_POST, '?page=edit_profile');
        // Không cần exit ở đây vì redirectBackWithErrors đã có exit
    }
}

    // --- ĐỔI MẬT KHẨU ---
    public function handleChangePassword() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user_id'])) { $this->redirect('?page=login'); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('?page=change_password'); return; }
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmNewPassword = $_POST['confirm_new_password'] ?? '';
        $userId = $_SESSION['user_id'];
        $errors = [];
        if (empty($currentPassword)) { $errors['current_password'] = "Vui lòng nhập mật khẩu hiện tại."; }
        if (empty($newPassword)) { $errors['new_password'] = "Vui lòng nhập mật khẩu mới."; }
        elseif (strlen($newPassword) < 6) { $errors['new_password'] = "Mật khẩu mới phải có ít nhất 6 ký tự."; }
        if ($newPassword !== $confirmNewPassword) { $errors['confirm_new_password'] = "Xác nhận mật khẩu mới không khớp."; }
        if (empty($errors)) {
            $currentUser = User::find($userId);
            if (!$currentUser || !User::verifyPassword($userId, $currentPassword)) {
                $errors['current_password'] = "Mật khẩu hiện tại không chính xác.";
            } elseif (password_verify($newPassword, $currentUser['password'])) {
                $errors['new_password'] = "Mật khẩu mới không được trùng với mật khẩu hiện tại.";
            }
        }
        if (!empty($errors)) {
            $this->redirectBackWithErrors($errors, [], '?page=change_password');
            return;
        }
        $success = User::updatePassword($userId, $newPassword);
        if ($success) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đổi mật khẩu thành công!'];
            $this->redirect('?page=profile');
        } else {
            $this->redirectBackWithErrors(['database' => 'Đã có lỗi xảy ra khi cập nhật mật khẩu.'], [], '?page=change_password');
        }
    }

    // ========== CÁC PHƯƠNG THỨC MỚI CHO XÁC THỰC EMAIL VÀ QUÊN MẬT KHẨU ==========

    /**
     * Hiển thị form nhập mã xác thực email.
     */
    public function showVerifyEmailForm() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $email = $_GET['email'] ?? null; // Lấy email từ GET để hiển thị lại nếu cần
        $flashMessage = $_SESSION['flash_message'] ?? null; // Lấy flash message nếu có
        $errors = $_SESSION['form_errors'] ?? []; // Lấy lỗi nếu redirect về
        if ($flashMessage) { unset($_SESSION['flash_message']); }
        if (!empty($errors)) { unset($_SESSION['form_errors']); }
        $this->render('verify_email', ['email' => $email, 'errors' => $errors, 'flashMessage' => $flashMessage, 'pageTitle' => 'Xác thực Email']);
    }

    /**
     * Xử lý mã xác thực email được submit từ form.
     */
    public function handleVerifyEmail() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $code = trim($_POST['verification_code'] ?? '');
        $email = trim($_POST['email'] ?? ''); // Lấy email từ form (có thể là hidden input)
        $errors = [];

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Địa chỉ email không hợp lệ.'; } // Thêm validate email
        if (empty($code)) { $errors['verification_code'] = 'Mã xác thực không được để trống.'; }
        elseif (!ctype_digit($code) || strlen($code) !== 6) { $errors['verification_code'] = 'Mã xác thực phải là 6 chữ số.'; }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $this->redirect('?page=verify_email&email=' . urlencode($email));
            return;
        }

        $user = User::findByEmail($email);
        if (!$user) {
            // Không nên báo lỗi 'Email không tồn tại' trực tiếp cho form này
            // Thay vào đó, báo mã không chính xác để tránh lộ thông tin
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Mã xác thực hoặc email không chính xác.'];
            $this->redirect('?page=verify_email'); // Không truyền email lại
            return;
        }

        if ($user['is_email_verified']) {
            $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Email này đã được xác thực trước đó.'];
            $this->redirect('?page=login');
            return;
        }

        $verificationInfo = User::getEmailVerificationInfo($user['id']);

        // *** ĐOẠN CODE ĐÃ SỬA CHO LỖI DATETIME ***
        $expiryDateTime = $verificationInfo['expires_at'] ?? null; // Lấy trực tiếp DateTime object hoặc null

        if (!$verificationInfo || $verificationInfo['code'] !== $code) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Mã xác thực không chính xác.'];
            $this->redirect('?page=verify_email&email=' . urlencode($email));
            return;
        }

        // Kiểm tra thời gian hết hạn
        if ($expiryDateTime && $expiryDateTime < new DateTime()) { // So sánh trực tiếp
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Mã xác thực đã hết hạn.'];
             // Tùy chọn: Thêm logic gửi lại mã ở đây hoặc hướng dẫn người dùng
             $this->redirect('?page=verify_email&email=' . urlencode($email));
             return;
        }
        // *** KẾT THÚC PHẦN SỬA ***

        // Xác thực thành công
        if (User::verifyEmail($user['id'])) {
             $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Xác thực email thành công! Bạn có thể đăng nhập ngay bây giờ.'];
            $this->redirect('?page=login');
        } else {
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Lỗi khi cập nhật trạng thái xác thực. Vui lòng thử lại.'];
             $this->redirect('?page=verify_email&email=' . urlencode($email));
        }
    }


        /**
     * Hiển thị form yêu cầu reset mật khẩu (nhập email).
     */
    public function showForgotPasswordForm() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($flashMessage) { unset($_SESSION['flash_message']); }
        // Sử dụng lại view cũ forgot_password_request.php nếu giao diện phù hợp
        $this->render('forgot_password_request', ['flashMessage' => $flashMessage, 'pageTitle' => 'Quên Mật Khẩu']);
    }

    /**
     * Xử lý yêu cầu quên mật khẩu: tạo mã code, gửi email, chuyển hướng nhập code.
     */
    public function handleForgotPasswordRequest() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Vui lòng nhập địa chỉ email hợp lệ.'];
            $this->redirect('?page=forgot_password');
            return;
        }

        $user = User::findByEmail($email);

        // Luôn hiển thị thông báo thành công chung để tránh lộ thông tin email có tồn tại hay không
        $successMessage = 'Nếu email của bạn tồn tại trong hệ thống và đã được xác thực, một mã đặt lại mật khẩu đã được gửi. Vui lòng kiểm tra hộp thư của bạn (kể cả Spam). Mã có hiệu lực trong 15 phút.';

        if ($user && $user['is_email_verified']) { // Chỉ gửi nếu user tồn tại và đã xác thực email
            try {
                $resetCode = random_int(100000, 999999); // Tạo mã 6 chữ số
                $expiryTime = (new DateTime())->modify('+15 minutes'); // Hết hạn sau 15 phút

                // Lưu mã code vào cột password_reset_token, thời gian hết hạn vào cột tương ứng
                if (User::setPasswordResetToken($user['id'], (string)$resetCode, $expiryTime)) {
                    $subject = "MyShop - Mã đặt lại mật khẩu của bạn";
                    $body = "Chào {$user['username']},<br><br>" .
                            "Mã đặt lại mật khẩu MyShop của bạn là: <strong>{$resetCode}</strong><br>" .
                            "Mã này sẽ hết hạn sau 15 phút.<br><br>" .
                            "Nếu bạn không yêu cầu điều này, vui lòng bỏ qua email này.<br><br>" .
                            "Trân trọng,<br>Đội ngũ MyShop";

                    $sendResult = $this->sendEmail($user['email'], $user['username'], $subject, $body);
                    if ($sendResult !== true) {
                        // Ghi log lỗi gửi mail nhưng không báo lỗi chi tiết cho người dùng
                        error_log("Lỗi gửi email reset pass cho {$user['email']}: " . $sendResult);
                        // Có thể đặt thông báo lỗi chung chung hơn ở đây nếu muốn
                        // $successMessage = 'Có lỗi xảy ra khi gửi email. Vui lòng thử lại sau.';
                    } else {
                         // Gửi mail thành công, chuyển hướng đến trang nhập code
                         $_SESSION['flash_message'] = ['type' => 'success', 'message' => $successMessage];
                         $this->redirect('?page=enter_reset_code&email=' . urlencode($email));
                         return; // Dừng thực thi sau khi chuyển hướng
                    }
                } else {
                    // Ghi log lỗi lưu DB nhưng không báo lỗi chi tiết
                    error_log("Lỗi lưu mã reset pass cho user ID: {$user['id']}");
                }
            } catch (\Exception $e) {
                // Ghi log lỗi hệ thống nhưng không báo lỗi chi tiết
                error_log("Lỗi tạo mã hoặc DateTime cho reset pass user ID: {$user['id']} - " . $e->getMessage());
            }
        } else {
            // Email không tồn tại hoặc chưa xác thực, ghi log nhưng không báo lỗi khác biệt
            error_log("Yêu cầu reset pass cho email không tồn tại hoặc chưa xác thực: {$email}");
        }

        // Luôn hiển thị thông báo thành công chung và quay lại trang forgot_password
        $_SESSION['flash_message'] = ['type' => 'info', 'message' => $successMessage]; // Dùng info thay vì success để không gây hiểu nhầm nếu mail lỗi
        $this->redirect('?page=forgot_password');
    }

    /**
     * Hiển thị form nhập mã xác thực đặt lại mật khẩu.
     */
    public function showEnterResetCodeForm() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $email = $_GET['email'] ?? null; // Lấy email từ GET để điền sẵn (tùy chọn)
        $flashMessage = $_SESSION['flash_message'] ?? null;
        $errors = $_SESSION['form_errors'] ?? [];
        if ($flashMessage) unset($_SESSION['flash_message']);
        if (!empty($errors)) unset($_SESSION['form_errors']);

        $this->render('enter_reset_code', [ // Render view mới
            'email' => $email,
            'errors' => $errors,
            'flashMessage' => $flashMessage,
            'pageTitle' => 'Nhập Mã Đặt Lại Mật Khẩu'
        ]);
    }

    /**
     * Xử lý mã xác thực đặt lại mật khẩu.
     */
    public function handleEnterResetCode() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $code = trim($_POST['reset_code'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $errors = [];

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Địa chỉ email không hợp lệ.'; }
        if (empty($code)) { $errors['reset_code'] = 'Mã đặt lại không được để trống.'; }
        elseif (!ctype_digit($code) || strlen($code) !== 6) { $errors['reset_code'] = 'Mã đặt lại phải là 6 chữ số.'; }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $this->redirect('?page=enter_reset_code&email=' . urlencode($email));
            return;
        }

        $user = User::findByEmail($email);

                // --- THAY ĐỔI LOGIC KIỂM TRA ---
                if (!$user) {
                    // Email không tồn tại -> Báo lỗi chung
                    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Email hoặc Mã đặt lại mật khẩu không chính xác hoặc đã hết hạn.'];
                    $this->redirect('?page=enter_reset_code&email=' . urlencode($email));
                    return;
                }
        
                // Lấy thông tin mã reset và thời gian hết hạn TỪ USER ĐÃ TÌM THEO EMAIL
                $resetCodeInDb = $user['password_reset_token'] ?? null;
                $expiryTimeString = $user['password_reset_expires_at'] ?? null;
                $expiryDateTime = null;
                if ($expiryTimeString) {
                    try {
                        $expiryDateTime = new DateTime($expiryTimeString);
                    } catch (\Exception $e) {
                        // Ghi log lỗi nếu định dạng thời gian sai, nhưng không báo người dùng
                        error_log("Lỗi chuyển đổi DateTime cho password_reset_expires_at user ID {$user['id']}: " . $e->getMessage());
                    }
                }
        
                // Kiểm tra mã code có khớp không VÀ thời gian có hợp lệ không
                if ($resetCodeInDb === null || $resetCodeInDb !== $code || $expiryDateTime === null || $expiryDateTime < new DateTime()) {
                    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Mã đặt lại mật khẩu không chính xác hoặc đã hết hạn.'];
                    $this->redirect('?page=enter_reset_code&email=' . urlencode($email));
                    return;
                }
        
        
                // Mã hợp lệ, đặt cờ session và chuyển hướng đến trang nhập mật khẩu mới
                $_SESSION['reset_user_id'] = $user['id']; // Lưu ID user cần reset
                $_SESSION['reset_code_verified'] = true; // Đặt cờ đã xác thực
        
                // Xóa mã code khỏi DB sau khi xác thực thành công
                User::setPasswordResetToken($user['id'], null, null);
        
                $this->redirect('?page=reset_password_from_code'); // Chuyển hướng đến trang mới
    }


    /**
     * Hiển thị form đặt lại mật khẩu mới (sau khi đã xác thực mã code).
     */
    public function showResetPasswordFormFromCode() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }

        // Kiểm tra xem người dùng đã xác thực mã code chưa
        if (!isset($_SESSION['reset_code_verified']) || $_SESSION['reset_code_verified'] !== true || !isset($_SESSION['reset_user_id'])) {
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Bạn cần nhập mã xác thực trước khi đặt lại mật khẩu.'];
             $this->redirect('?page=forgot_password'); // Hoặc chuyển về trang nhập code
             return;
        }

        $errors = $_SESSION['form_errors'] ?? [];
        if (!empty($errors)) unset($_SESSION['form_errors']);

        $this->render('reset_password_from_code', [ // Render view mới
            'errors' => $errors,
            'pageTitle' => 'Đặt Lại Mật Khẩu Mới'
        ]);
    }

    /**
     * Xử lý việc đặt lại mật khẩu mới (sau khi đã xác thực mã code).
     */
    public function handleResetPasswordFromCode() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }

        // Kiểm tra lại cờ session
        if (!isset($_SESSION['reset_code_verified']) || $_SESSION['reset_code_verified'] !== true || !isset($_SESSION['reset_user_id'])) {
            $this->redirect('?page=forgot_password');
            return;
        }

        $userId = $_SESSION['reset_user_id'];
        $newPassword = $_POST['new_password'] ?? '';
        $confirmNewPassword = $_POST['confirm_new_password'] ?? '';
        $errors = [];

        // Validate mật khẩu mới
        if (empty($newPassword)) { $errors['new_password'] = "Vui lòng nhập mật khẩu mới."; }
        elseif (strlen($newPassword) < 6) { $errors['new_password'] = "Mật khẩu mới phải có ít nhất 6 ký tự."; }
        if ($newPassword !== $confirmNewPassword) { $errors['confirm_new_password'] = "Xác nhận mật khẩu mới không khớp."; }

        // Kiểm tra xem mật khẩu mới có trùng mật khẩu cũ không
        if (empty($errors)) {
             $currentUser = User::find($userId); // Lấy thông tin user để kiểm tra pass cũ
             if ($currentUser && password_verify($newPassword, $currentUser['password'])) {
                  $errors['new_password'] = "Mật khẩu mới không được trùng với mật khẩu cũ.";
             }
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $this->redirect('?page=reset_password_from_code');
            return;
        }

        // Cập nhật mật khẩu (Hàm updatePassword của User model đã tự xóa token/code)
        if (User::updatePassword($userId, $newPassword)) {
            // Xóa các cờ session sau khi thành công
            unset($_SESSION['reset_user_id'], $_SESSION['reset_code_verified']);

            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập với mật khẩu mới.'];
            $this->redirect('?page=login');
        } else {
            // Lỗi cập nhật DB
            $_SESSION['form_errors'] = ['database' => 'Lỗi khi cập nhật mật khẩu. Vui lòng thử lại.'];
            $this->redirect('?page=reset_password_from_code');
        }
    }

    // --- HÀM HELPER ---
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

    private function redirectBackWithErrors(array $errors, array $postData, string $targetPage): void {
         if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $postData;
        $this->redirect($targetPage);
        exit;
    }

} 