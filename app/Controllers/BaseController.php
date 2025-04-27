<?php

namespace App\Controllers;
use App\Models\Wishlist;

class BaseController
{
    protected function getGlobalViewData(): array
    {
        // Start session if not started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $data = [];
        $userId = $_SESSION['user_id'] ?? null;
        // Check if user is logged in
        $data['isLoggedIn'] = ($userId !== null);

        // Get cart item count
        $data['cartItemCount'] = count($_SESSION['cart'] ?? []);

        $data['wishlistedIds'] = [];
        $data['wishlistItemCount'] = 0;
        if ($data['isLoggedIn']) {
            // Get wishlisted product IDs for logged-in user
            $data['wishlistedIds'] = Wishlist::getWishlistedProductIds($userId);

            $data['wishlistItemCount'] = count($data['wishlistedIds']);
        }

        return $data;
    }

    /**
     * Render a view
     */
    protected function render($view, $data = [])
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Merge global view data with view-specific data
        $finalData = array_merge($this->getGlobalViewData(), $data);

        // Handle flash messages
        $flashMessage = $_SESSION['flash_message'] ?? null;
        if ($flashMessage) {
            $finalData['flashMessage'] = $flashMessage;
            unset($_SESSION['flash_message']);
        }

        // Construct the view path
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';

        if (file_exists($viewPath)) {
            extract($finalData);
            require $viewPath;
        } else {
            error_log("View file not found: " . $viewPath);
            http_response_code(500);
            $this->render('errors/500', ['message' => 'Server Error: View not found ' . $view]);
            exit;
        }
    }
    //redirect to new url
    public function redirect($url)
    {
        header("Location: $url");
        exit; 
    }

    public function showNotFoundPage(string $message = 'Xin lỗi, trang bạn tìm kiếm không tồn tại.')
    {
        //Set code error 404
        http_response_code(404);
        $this->render('errors/404', [
            'pageTitle' => '404 - Không tìm thấy trang',
            'message' => $message
        ]);
        exit;
    }


    public function showErrorPage(string $message = 'Đã có lỗi xảy ra. Vui lòng thử lại sau.')
    {
        //Set code error 500
        http_response_code(500);
        $this->render('errors/500', [
            'pageTitle' => 'Lỗi Server',
            'message' => $message
        ]);
        exit;
    }
}