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
}


