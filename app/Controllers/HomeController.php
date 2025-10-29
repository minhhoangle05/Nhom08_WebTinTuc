<?php
namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $categories = [];
        
        try {
            $categoryModel = $this->model('Category');
            $categories = $categoryModel->getPopularCategories();
        } catch (\Exception $e) {
            // Log error if needed
            // error_log($e->getMessage());
        }
        
        $this->view('home/index', [
            'title' => 'Trang chủ',
            'user' => \App\Core\Auth::user(),
            'categories' => $categories
        ]);
    }
    
}


