<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Session;
use App\Models\User;

class ProfileController extends Controller
{
    public function show(): void
    {
        if (!Auth::check()) {
            Session::set('return_url', BASE_URL . '/account/profile');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        $currentUser = Auth::user();
        $userModel = new User();
        $user = $userModel->findById((int)$currentUser['id']);

        $this->view('account/profile', [
            'title' => 'Thông tin tài khoản',
            'user' => $user,
            'error' => Session::flash('error'),
            'success' => Session::flash('success'),
            'csrf' => CSRF::token(),
        ]);
    }

    public function update(): void
    {
        if (!Auth::check()) {
            Session::set('return_url', BASE_URL . '/account/profile');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            Session::flash('error', 'Phiên làm việc đã hết hạn, vui lòng thử lại');
            header('Location: ' . BASE_URL . '/account/profile');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim(strtolower($_POST['email'] ?? ''));

        if ($name === '' || $email === '') {
            Session::flash('error', 'Vui lòng nhập đầy đủ họ tên và email');
            header('Location: ' . BASE_URL . '/account/profile');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            Session::flash('error', 'Email không hợp lệ');
            header('Location: ' . BASE_URL . '/account/profile');
            exit;
        }

        if (strlen($name) < 3 || strlen($name) > 100) {
            Session::flash('error', 'Tên phải có từ 3 đến 100 ký tự');
            header('Location: ' . BASE_URL . '/account/profile');
            exit;
        }

        $currentUser = Auth::user();
        $userModel = new User();

        $existing = $userModel->findByEmail($email);
        if ($existing && (int)$existing['id'] !== (int)$currentUser['id']) {
            Session::flash('error', 'Email đã được sử dụng bởi tài khoản khác');
            header('Location: ' . BASE_URL . '/account/profile');
            exit;
        }

        $updated = $userModel->updateProfile((int)$currentUser['id'], $name, $email);
        if ($updated) {
            Session::set('user', [
                'id' => (int)$currentUser['id'],
                'name' => $name,
                'email' => $email,
                'role_id' => (int)$currentUser['role_id'],
            ]);
            Session::flash('success', 'Cập nhật thông tin thành công');
        } else {
            Session::flash('error', 'Không thể cập nhật thông tin. Vui lòng thử lại sau');
        }

        header('Location: ' . BASE_URL . '/account/profile');
        exit;
    }
}


