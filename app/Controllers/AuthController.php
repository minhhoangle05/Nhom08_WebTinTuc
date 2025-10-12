<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\ActivityLogger;
use App\Core\LoginAttempt;
use App\Core\Mailer;
use App\Models\User;

class AuthController extends Controller
{

    public function login(): void
    {
        // Kiểm tra xem có token "remember me" không
        if (!Auth::check() && ($user = Auth::checkRememberToken())) {
            Auth::login($user);
            ActivityLogger::log('login', null, 'Tự động đăng nhập bằng token ghi nhớ');
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        $this->view('auth/login', [
            'title' => 'Đăng nhập',
            'error' => Session::flash('error'),
            'success' => Session::flash('success'),
            'csrf' => CSRF::token()
        ]);
    }

    public function doLogin(): void
    {
        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            Session::flash('error', 'Phiên làm việc đã hết hạn, vui lòng thử lại');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        $email = trim(strtolower($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Kiểm tra thông tin đầu vào
        if ($email === '' || $password === '') {
            Session::flash('error', 'Vui lòng nhập email và mật khẩu');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        // Kiểm tra giới hạn đăng nhập
        if ($lockedUntil = LoginAttempt::isLocked($email)) {
            Session::flash('error', "Tài khoản tạm thời bị khóa. Vui lòng thử lại sau {$lockedUntil}");
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        // Kiểm tra thông tin đăng nhập
        if (!$user || !password_verify($password, $user['password_hash'])) {
            LoginAttempt::record($email);
            Session::flash('error', 'Email hoặc mật khẩu không đúng');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        // Kiểm tra tài khoản bị khóa
        if (isset($user['is_locked']) && $user['is_locked']) {
            Session::flash('error', 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        // Đăng nhập thành công
        $userData = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role_id' => (int)$user['role_id'],
        ];

        // Xóa các lần đăng nhập thất bại
        LoginAttempt::clear($email);
        
        // Đăng nhập với tùy chọn ghi nhớ
        Auth::login($userData, $remember);
        
        // Ghi nhận hoạt động
        ActivityLogger::log('login', null, $remember ? 'Đăng nhập với ghi nhớ' : 'Đăng nhập thường');
        
        // Chuyển hướng người dùng
        $returnUrl = Session::get('return_url', BASE_URL . '/');
        Session::remove('return_url');
        header('Location: ' . $returnUrl);
        exit;
    }

    public function logout(): void
    {
        if (Auth::check()) {
            ActivityLogger::log('logout');
            Auth::logout();
        }
        header('Location: ' . BASE_URL . '/');
        exit;
    }

    private function isStrongPassword(string $password): bool
    {
        // Ít nhất 8 ký tự
        if (strlen($password) < 8) return false;
        
        // Kiểm tra có chữ hoa
        if (!preg_match('/[A-Z]/', $password)) return false;
        
        // Kiểm tra có chữ thường
        if (!preg_match('/[a-z]/', $password)) return false;
        
        // Kiểm tra có số
        if (!preg_match('/[0-9]/', $password)) return false;
        
        // Kiểm tra có ký tự đặc biệt
        if (!preg_match('/[@$!%*?&]/', $password)) return false;
        
        return true;
    }

    private function sanitizeName(string $name): string
    {
        // Loại bỏ các ký tự đặc biệt, chỉ giữ lại chữ cái và khoảng trắng
        $name = preg_replace('/[^A-Za-zÀ-ỹ\s]/', '', $name);
        // Chuẩn hóa khoảng trắng
        $name = trim(preg_replace('/\s+/', ' ', $name));
        return $name;
    }

    public function register(): void
    {
        $this->view('auth/register', [
            'title' => 'Đăng ký',
            'error' => Session::flash('error'),
            'csrf' => CSRF::token(),
            'csrf' => CSRF::token(),
        ]);
    }

    /**
     * Show forgot password form
     */
    public function forgotPassword(): void
    {
        if (Auth::check()) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $this->view('auth/forgot-password', [
            'title' => 'Quên mật khẩu',
            'csrf' => CSRF::token()
        ]);
    }

    /**
     * Process forgot password request
     */
    public function processForgotPassword(): void
    {
        if (!CSRF::validate($_POST['csrf'] ?? '')) {
            Session::flash('error', 'Phiên làm việc đã hết hạn, vui lòng thử lại');
            header('Location: ' . BASE_URL . '/auth/forgot-password');
            exit;
        }

        $email = trim(strtolower($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Email không hợp lệ');
            header('Location: ' . BASE_URL . '/auth/forgot-password');
            exit;
        }

        // Tạo token đặt lại mật khẩu
        $token = Auth::createPasswordResetToken($email);
        if ($token === null) {
            // Không hiển thị lỗi cụ thể để tránh lộ thông tin
            Session::flash('success', 'Nếu email tồn tại trong hệ thống, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu.');
            header('Location: ' . BASE_URL . '/auth/forgot-password');
            exit;
        }

        // Tạo link đặt lại mật khẩu
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $resetLink = $scheme . '://' . $host . BASE_URL . '/auth/reset-password/' . urlencode($token);
        
        // Gửi email
        if (Mailer::sendPasswordReset($email, $resetLink)) {
            Session::flash('success', 'Email hướng dẫn đặt lại mật khẩu đã được gửi.');
        } else {
            Session::flash('error', 'Không thể gửi email. Vui lòng thử lại sau.');
            error_log("Failed to send password reset email to: $email");
        }

        header('Location: ' . BASE_URL . '/auth/forgot-password');
        exit;
    }

    /**
     * Show reset password form
     */
    public function resetPassword(string $token): void
    {
        error_log("Reset password request received with token: " . $token);
        
        // Decode URL-encoded token
        $token = urldecode($token);
        error_log("Decoded token: " . $token);
        
        if (!Auth::verifyPasswordResetToken($token)) {
            error_log("Token verification failed");
            Session::flash('error', 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
        
        error_log("Token verification successful");

        $this->view('auth/reset-password', [
            'title' => 'Đặt lại mật khẩu',
            'token' => $token,
            'error' => Session::flash('error'),
            'csrf' => CSRF::token()
        ]);
    }

    /**
     * Process reset password
     */
    public function processResetPassword(): void
    {
        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            Session::flash('error', 'Phiên làm việc đã hết hạn, vui lòng thử lại');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['password_confirm'] ?? '';

        // Log để debug
        error_log("Processing reset password request:");
        error_log("Password length: " . strlen($password));
        error_log("Confirm password length: " . strlen($confirmPassword));
        error_log("Raw password: " . $password);
        error_log("Raw confirm password: " . $confirmPassword);

        // Kiểm tra mật khẩu
        if (!$this->isStrongPassword($password)) {
            Session::flash('error', 'Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt');
            header('Location: ' . BASE_URL . '/auth/reset-password/' . urlencode($token));
            exit;
        }

        // Trim để loại bỏ khoảng trắng thừa
        $password = trim($password);
        $confirmPassword = trim($confirmPassword);

        if ($password !== $confirmPassword) {
            error_log("Password mismatch detected:");
            error_log("Password: " . $password);
            error_log("Confirm password: " . $confirmPassword);
            Session::flash('error', 'Mật khẩu xác nhận không khớp');
            header('Location: ' . BASE_URL . '/auth/reset-password/' . urlencode($token));
            exit;
        }

        // Đặt lại mật khẩu
        if (Auth::resetPassword($token, $password)) {
            Session::flash('success', 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập.');
            ActivityLogger::log('password_reset', null, 'Password reset successful');
        } else {
            Session::flash('error', 'Không thể đặt lại mật khẩu. Liên kết có thể đã hết hạn.');
        }

        header('Location: ' . BASE_URL . '/auth/login');
        exit;
    }

    public function doRegister(): void
    {
        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            Session::flash('error', 'CSRF không hợp lệ');
            header('Location: ' . BASE_URL . '/auth/register');
            exit;
        }

        // Kiểm tra đã đồng ý điều khoản
        if (!isset($_POST['terms'])) {
            Session::flash('error', 'Bạn phải đồng ý với điều khoản sử dụng');
            header('Location: ' . BASE_URL . '/auth/register');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim(strtolower($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        // Kiểm tra dữ liệu đầu vào
        if ($name === '' || $email === '' || $password === '' || $password2 === '') {
            Session::flash('error', 'Vui lòng điền đủ thông tin');
            header('Location: ' . BASE_URL . '/auth/register');
            exit;
        }

        // Xử lý và kiểm tra tên
        $name = $this->sanitizeName($name);
        if (strlen($name) < 3 || strlen($name) > 100) {
            Session::flash('error', 'Tên phải có từ 3 đến 100 ký tự');
            header('Location: ' . BASE_URL . '/auth/register');
            exit;
        }

        // Kiểm tra email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            Session::flash('error', 'Email không hợp lệ');
            header('Location: ' . BASE_URL . '/auth/register');
            exit;
        }

        // Kiểm tra email đã tồn tại
        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            Session::flash('error', 'Email đã được sử dụng');
            header('Location: ' . BASE_URL . '/auth/register');
            exit;
        }

        // Kiểm tra mật khẩu
        if (!$this->isStrongPassword($password)) {
            Session::flash('error', 'Mật khẩu không đủ mạnh. Yêu cầu ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt');
            header('Location: ' . BASE_URL . '/auth/register');
            exit;
        }

        if ($password !== $password2) {
            Session::flash('error', 'Mật khẩu xác nhận không khớp');
            header('Location: ' . BASE_URL . '/auth/register');
            exit;
        }
        if (strlen($password) < 6) {
            Session::flash('error', 'Mật khẩu tối thiểu 6 ký tự');
            header('Location: ' . BASE_URL . '/auth/register');
            exit;
        }
        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            Session::flash('error', 'Email đã tồn tại');
            header('Location: ' . BASE_URL . '/auth/register');
            exit;
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $userId = $userModel->create($name, $email, $hash, 1);
        Auth::login([
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'role_id' => 1,
        ]);
        header('Location: ' . BASE_URL . '/');
        exit;
    }
}


