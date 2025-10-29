<?php
namespace App\Core;

class Auth
{
    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function login(array $user, bool $remember = false): void
    {
        Session::set('user', $user);
        Session::set('last_activity', time());

        if ($remember) {
            $token = Token::generate();
            $hashedToken = Token::hash($token);
            
            // Lưu token vào database
            $db = Database::getConnection();
            $stmt = $db->prepare('
                INSERT INTO remember_tokens (user_id, token, expires_at) 
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))
            ');
            $stmt->execute([$user['id'], $hashedToken]);
            
            // Set cookie với thời hạn 30 ngày
            setcookie(
                'remember_token',
                $token,
                [
                    'expires' => time() + (30 * 24 * 60 * 60),
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );
        }
    }

    public static function logout(): void
    {
        if (isset($_COOKIE['remember_token'])) {
            // Xóa token khỏi database
            $db = Database::getConnection();
            $stmt = $db->prepare('DELETE FROM remember_tokens WHERE token = ?');
            $stmt->execute([Token::hash($_COOKIE['remember_token'])]);
            
            // Xóa cookie
            setcookie('remember_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }
        
        Session::remove('user');
        Session::remove('last_activity');
    }

    public static function checkRememberToken(): ?array
    {
        if (!isset($_COOKIE['remember_token'])) {
            return null;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('
            SELECT u.* FROM users u
            JOIN remember_tokens rt ON u.id = rt.user_id
            WHERE rt.token = ? AND rt.expires_at > NOW()
            LIMIT 1
        ');
        $stmt->execute([Token::hash($_COOKIE['remember_token'])]);
        
        if ($user = $stmt->fetch()) {
            return [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role_id' => (int)$user['role_id'],
            ];
        }

        return null;
    }

    public static function refreshSession(): void
    {
        if (self::check()) {
            Session::set('last_activity', time());
        }
    }

    public static function createPasswordResetToken(string $email): ?string
    {
        // Kiểm tra email tồn tại
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if (!$stmt->fetch()) {
            return null;
        }

        // Tạo token ngẫu nhiên
        $token = bin2hex(random_bytes(32));

        // Xóa token cũ nếu có
        $stmt = $db->prepare('DELETE FROM password_resets WHERE email = ? AND used = 0');
        $stmt->execute([$email]);

        // Thêm token mới với thời hạn 24 giờ
        $stmt = $db->prepare('
            INSERT INTO password_resets (
                email, 
                token, 
                created_at, 
                expires_at, 
                used
            ) VALUES (
                ?, 
                ?, 
                NOW(), 
                DATE_ADD(NOW(), INTERVAL 24 HOUR),
                0
            )
        ');
        $stmt->execute([$email, $token]);

        return $token;
    }

    public static function verifyPasswordResetToken(string $token): ?string
    {
        try {
            $db = Database::getConnection();
            
            // Thêm log để debug
            error_log("Verifying password reset token: " . $token);
            
            // Kiểm tra token trong database
            $stmt = $db->prepare('SELECT * FROM password_resets WHERE token = ?');
            $stmt->execute([$token]);
            $result = $stmt->fetch();
            
            if (!$result) {
                error_log("Token not found in database");
                return null;
            }
            
            error_log("Found token record: " . print_r($result, true));
            
            // Kiểm tra token đã sử dụng chưa
            if ($result['used']) {
                error_log("Token has already been used");
                return null;
            }
            
            // Kiểm tra thời hạn
            $now = new \DateTime();
            $expiresAt = new \DateTime($result['expires_at']);
            
            error_log("Current time: " . $now->format('Y-m-d H:i:s'));
            error_log("Token expires at: " . $expiresAt->format('Y-m-d H:i:s'));
            
            if ($now > $expiresAt) {
                error_log("Token has expired");
                return null;
            }

            error_log("Token is valid and not expired");
            return $result['email'];        } catch (\Exception $e) {
            error_log("Error verifying password reset token: " . $e->getMessage());
            return null;
        }
    }

    public static function resetPassword(string $token, string $newPassword): bool
    {
        error_log("Attempting to reset password with token: " . $token);
        
        $email = self::verifyPasswordResetToken($token);
        if (!$email) {
            error_log("Token verification failed during password reset");
            return false;
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Cập nhật mật khẩu mới
            $stmt = $db->prepare('UPDATE users SET password_hash = ? WHERE email = ?');
            $updated = $stmt->execute([$hashedPassword, $email]);

            if ($updated) {
                // Đánh dấu token đã sử dụng
                $stmt = $db->prepare('UPDATE password_resets SET used = 1 WHERE token = ?');
                $stmt->execute([$token]);
                
                $db->commit();
                error_log("Password reset successful for email: " . $email);
                return true;
            }

            $db->rollBack();
            error_log("Failed to update password for email: " . $email);
            return false;
            
        } catch (\Exception $e) {
            error_log("Error during password reset: " . $e->getMessage());
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return false;
        }
    }
    public static function isAdmin(): bool
    {
        if (!self::check()) {
            return false;
        }
        
        $user = self::user();
        // Giả sử role_id = 1 là admin (điều chỉnh theo logic của bạn)
        return isset($user['role_id']) && $user['role_id'] == 3;
    }
}


