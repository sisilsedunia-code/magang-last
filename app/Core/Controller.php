<?php
namespace App\Core;

class Controller {
    public function view($view, $data = []) {
        extract($data);
        $viewFile = dirname(__DIR__, 2) . '/app/Views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("View $view not found at $viewFile");
        }
    }

    public function redirect($url) {
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '/magang-last') . $url);
        exit;
    }
}


