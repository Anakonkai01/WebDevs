<?php
namespace App\Controllers;

use App\Models\Wishlist;
use App\Models\Cart;

class BaseController{

    /**
     * Lấy dữ liệu chung cần thiết cho mọi view.
     * @return array
     */
    protected function getGlobalViewData(): array {
        // Kiểm tra và khởi động session nếu chưa có
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $data = [];
        $userId = $_SESSION['user_id'] ?? null;
        $data['isLoggedIn'] = ($userId !== null);

        // Đếm số sản phẩm trong giỏ hàng
        $data['cartItemCount'] = count($_SESSION['cart'] ?? []);

        // Lấy danh sách sản phẩm yêu thích
        $data['wishlistedIds'] = []; 
        $data['wishlistItemCount'] = 0; 
        if ($data['isLoggedIn']) {
            $data['wishlistedIds'] = Wishlist::getWishlistedProductIds($userId);
            if (!is_array($data['wishlistedIds'])) {
                error_log("Lỗi: Không lấy được danh sách sản phẩm yêu thích cho user ID: " . $userId);
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
        // Kiểm tra session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Gộp dữ liệu chung và dữ liệu riêng của trang
        $finalData = array_merge($this->getGlobalViewData(), $data);

        // Xử lý thông báo flash
        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($flashMessage) {
            $finalData['flashMessage'] = $flashMessage;
            unset($_SESSION['flash_message']);
        }

        // Tìm và hiển thị file view
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';

        if (file_exists($viewPath)) {
            extract($finalData);
            require $viewPath;
        } else {
            error_log("Không tìm thấy file view: " . $viewPath);
            http_response_code(500);
            $this->render('errors/500', ['message' => 'Lỗi server: Không tìm thấy trang ' . $view]);
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
        exit;
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
        exit;
    }

    /**
     * Hiển thị trang lỗi 500.
     * @param string $message Thông báo lỗi tùy chỉnh
     * @return void
     */
    public function showErrorPage(string $message = 'Đã có lỗi xảy ra. Vui lòng thử lại sau.') {
        http_response_code(500);
        $this->render('errors/500', [
            'pageTitle' => 'Lỗi Server',
            'message' => $message
        ]);
        exit;
    }

} 