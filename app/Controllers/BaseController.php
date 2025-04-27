<?php
namespace App\Controllers;

use App\Models\Wishlist;
use App\Models\Cart; // Giả sử bạn có model Cart hoặc xử lý cart trong BaseController

class BaseController{

    /**
     * Lấy dữ liệu chung cần thiết cho mọi view.
     * @return array
     */
    protected function getGlobalViewData(): array {
        // *** Đảm bảo session luôn được khởi động ***
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $data = [];
        $userId = $_SESSION['user_id'] ?? null;
        $data['isLoggedIn'] = ($userId !== null);

        // Lấy số lượng giỏ hàng
        // Cách 1: Đếm trực tiếp từ session (nếu dùng session cart)
        $data['cartItemCount'] = count($_SESSION['cart'] ?? []);

        // Cách 2: Nếu dùng DB cart, cần gọi Model (ví dụ)
        // $data['cartItemCount'] = $data['isLoggedIn'] ? Cart::countItemsByUser($userId) : 0;

        // Lấy số lượng wishlist
        $data['wishlistedIds'] = []; // Mặc định
        $data['wishlistItemCount'] = 0; // Mặc định
        if ($data['isLoggedIn']) {
            $data['wishlistedIds'] = Wishlist::getWishlistedProductIds($userId);
            if (!is_array($data['wishlistedIds'])) {
                error_log("Warning: Wishlist::getWishlistedProductIds did not return an array for user ID: " . $userId);
                $data['wishlistedIds'] = [];
            }
            $data['wishlistItemCount'] = count($data['wishlistedIds']);
        }

        return $data;
    }


    /**
     * Render view và tự động truyền dữ liệu chung + flash message.
     * @param string $view Tên file view (không có .php)
     * @param array $data Dữ liệu cụ thể cho view này
     * @return void
     */
    protected function render($view, $data = [])
    {
        // Đảm bảo session đã chạy (quan trọng nếu getGlobalViewData không được gọi trước)
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Trộn dữ liệu chung với dữ liệu riêng của view
        $finalData = array_merge($this->getGlobalViewData(), $data);

        // --- QUAN TRỌNG: Xử lý Flash Message ở đây ---
        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($flashMessage) {
            $finalData['flashMessage'] = $flashMessage; // Truyền flash message vào data cho view
            unset($_SESSION['flash_message']); // Xóa ngay sau khi đọc
        }
        // --- Kết thúc xử lý Flash Message ---

        // Đường dẫn đến view
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';

        if (file_exists($viewPath)) {
            extract($finalData); // Làm cho các biến trong $finalData có sẵn trong view
            require $viewPath; // Nạp view
        } else {
            // Xử lý lỗi không tìm thấy view
            error_log("View file not found: " . $viewPath);
            http_response_code(500);
            // echo "Lỗi: Không tìm thấy tệp giao diện."; // Hoặc render trang lỗi 500
            $this->render('errors/500', ['message' => 'Lỗi server: Không tìm thấy view ' . $view]);
            exit;
        }
    }

    /**
     * Hàm chuyển hướng tiện ích.
     * @param string $url URL cần chuyển đến
     * @return void
     */
    public function redirect($url)
    {
        header("Location: $url");
        exit; // Luôn exit sau khi gọi header Location
    }


    /**
     * Hiển thị trang lỗi 404.
     * @param string $message Thông báo lỗi tùy chỉnh
     * @return void
     */
    public function showNotFoundPage(string $message = 'Xin lỗi, trang bạn tìm kiếm không tồn tại.') {
        http_response_code(404);
        $this->render('errors/404', [
            'pageTitle' => '404 - Không tìm thấy trang',
            'message' => $message
        ]);
        exit; // Dừng script sau khi hiển thị trang lỗi
    }

    /**
     * Hiển thị trang lỗi 500.
     * @param string $message Thông báo lỗi tùy chỉnh
     * @return void
     */
    public function showErrorPage(string $message = 'Đã có lỗi xảy ra. Vui lòng thử lại sau.') {
        http_response_code(500);
        // Giả sử bạn có view errors/500.php
        $this->render('errors/500', [
            'pageTitle' => 'Lỗi Server',
            'message' => $message
        ]);
        exit; // Dừng script sau khi hiển thị trang lỗi
    }

} // End Class BaseController