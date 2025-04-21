<?php


class BaseController{

    // ham hien thi view
    protected function render($view, $data = [])
    {
        extract($data);
        require_once BASE_PATH . '/app/Views/' . $view . '.php';
    }

    // ham chuyen huong redirect
    protected function redirect($url)
    {
        header("Location: $url");
        exit;
    }

}
