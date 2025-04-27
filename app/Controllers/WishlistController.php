<?php

namespace App\Controllers;

use App\Models\Wishlist;
use App\Models\Product;

class WishlistController extends BaseController
{


    public function __construct()
    {
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Check if the user is logged in, redirect if not

        if (!isset($_SESSION['user_id'])) {
            // Check if the request is AJAX
            $isAjax = isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1;

            // Handle AJAX request
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401); // Unauthorized
                ob_clean();
                echo json_encode([
                    'success' => false,
                    'message' => 'Please log in to use the wishlist.',
                    'login_required' => true
                ]);
                exit;
            }
            // Handle non-AJAX request
            else {
                $intendedPage = $_SERVER['REQUEST_URI'] ?? '?page=home';

                // Redirect to the previous page if the request is for adding/removing wishlist items
                if (strpos($intendedPage, 'wishlist_add') !== false || strpos($intendedPage, 'wishlist_remove') !== false) {
                    $intendedPage = $_SERVER['HTTP_REFERER'] ?? '?page=home';
                }

                // Store the intended page for redirection after login
                $_SESSION['redirect_after_login'] = $intendedPage;

                // Set a flash message to inform the user to log in
                $_SESSION['flash_message'] = [
                    'type' => 'warning',
                    'message' => 'Please log in to use the wishlist.'
                ];
                $this->redirect('?page=login');
                exit;
            }
        }
    }


    public function index()
    {
        // Get user ID from session

        $userId = $_SESSION['user_id'];
        $wishlistItems = Wishlist::getByUser($userId);
        
        // Render the 'wishlist' view and pass the wishlist items and page title
        $this->render('wishlist', [
            'wishlistItems' => $wishlistItems,
            'pageTitle' => 'Danh sách yêu thích'
        ]);
    }


    public function add()
    {
        // Get data from request
        $productIdInput = $_REQUEST['id'] ?? null;
        $productId = filter_var($productIdInput, FILTER_VALIDATE_INT);

        $userId = $_SESSION['user_id'];
        $isAjax = isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1;

        // Cấu trúc phản hồi JSON mặc định
        $response = ['success' => false, 'message' => 'ID sản phẩm không hợp lệ.', 'wishlistItemCount' => 0];
        
        // Check if the product ID is valid
        if ($productId === false || $productId <= 0) {} 
        // If the product ID is valid
        else {

            // Check if the product exists
            if (!Product::find($productId)) {

                $response['message'] = 'Product does not exist.';
            } 
            // If the product exists
            else {
                 // Add the product to the wishlist
                $success = Wishlist::add($userId, $productId);

                // If the product is added successfully
                if ($success) { 
                    $response = ['success' => true, 'message' => 'Added to wishlist!'];
                } 
                // If the product is not added successfully
                else { 
                    // Check if the product is already in the wishlist
                    if (Wishlist::isWishlisted($userId, $productId)) {
                        $response = [
                            'success' => false, 
                            'message' => 'This product is already in your wishlist.', 
                            'already_added' => true
                        ];
                    } 
                    // If the error is not because the product is already in the wishlist
                    else {
                        $response['message'] = 'Error adding product to wishlist.';
                    }
                }
            }
        }

        // Get the current number of wishlist items
        $wishlistIds = Wishlist::getWishlistedProductIds($userId);
        $response['wishlistItemCount'] = count($wishlistIds);

        // Return the result
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            // Set HTTP status code (e.g., 400 Bad Request if ID is invalid)
            if (!$response['success'] && ($productId === false || $productId <= 0)) {
                http_response_code(400);
            } else if (!$response['success'] && !isset($response['already_added'])) {
                http_response_code(500); // Server error if the product cannot be added
            }
            ob_clean();
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        } else {    // Handle non-AJAX requests
            $_SESSION['flash_message'] = ['type' => $response['success'] ? 'success' : 'error', 'message' => $response['message']];
            $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '?page=shop_grid';
            if (!isset($_REQUEST['redirect']) || $_REQUEST['redirect'] !== 'no') {
                $this->redirect($redirectUrl);
            }
            exit;
        }
    }

    public function remove()
    {
        $productIdInput = $_REQUEST['id'] ?? null;

        $productId = filter_var($productIdInput, FILTER_VALIDATE_INT);
        $userId = $_SESSION['user_id'];
        $isAjax = isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1;

        // Default JSON response structure
        $response = [
            'success' => false, 
            'message' => 'Invalid product ID.', 
            'wishlistItemCount' => 0
        ];

        // Check if the product ID is valid
        if ($productId === false || $productId <= 0) {} 
        // If the product ID is valid
        else {

            // Remove the product from the wishlist
            $success = Wishlist::remove($userId, $productId);

            // If the product is removed successfully
            if ($success) {
                $response = ['success' => true, 'message' => 'Removed from wishlist.']; 
            } 
            // If the product is not removed successfully
            else {
                $response['message'] = 'Error removing product or product not in wishlist.';
            }
        }

        $wishlistIds = Wishlist::getWishlistedProductIds($userId);
        $response['wishlistItemCount'] = count($wishlistIds);

        // Return the result
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            if (!$response['success'] && ($productId === false || $productId <= 0)) {
                http_response_code(400);
            } else if (!$response['success']) {
                http_response_code(500); // Server error if the product cannot be removed
            }
            ob_clean();
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        } 
        // Handle non-AJAX requests
        else {
            $_SESSION['flash_message'] = ['type' => $response['success'] ? 'success' : 'error', 'message' => $response['message']];
            $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '?page=wishlist'; 
            if (!isset($_REQUEST['redirect']) || $_REQUEST['redirect'] !== 'no') {
                $this->redirect($redirectUrl);
            }
            exit;
        }
    }
}