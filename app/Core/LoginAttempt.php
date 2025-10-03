<?php
namespace App\Core;

class LoginAttempt
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 900; // 15 phút

    public static function record(string $email): void
    {
        $db = Database::getConnection();
        
        // Ghi nhận lần thử đăng nhập
        $stmt = $db->prepare('
            INSERT INTO login_attempts (email, ip_address, attempted_at)
            VALUES (?, ?, NOW())
        ');
        $stmt->execute([$email, $_SERVER['REMOTE_ADDR']]);
    }

    public static function isLocked(string $email): ?string
    {
        $db = Database::getConnection();
        
        // Đếm số lần thử đăng nhập thất bại trong 15 phút qua
        $stmt = $db->prepare('
            SELECT COUNT(*) as attempts, 
                   MAX(attempted_at) as last_attempt
            FROM login_attempts 
            WHERE email = ? 
              AND ip_address = ?
              AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ');
        $stmt->execute([$email, $_SERVER['REMOTE_ADDR'], self::LOCKOUT_TIME]);
        $result = $stmt->fetch();
        
        if ($result && $result['attempts'] >= self::MAX_ATTEMPTS) {
            $timeLeft = self::LOCKOUT_TIME - (time() - strtotime($result['last_attempt']));
            return self::formatTimeLeft($timeLeft);
        }
        
        return null;
    }

    public static function clear(string $email): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM login_attempts WHERE email = ? AND ip_address = ?');
        $stmt->execute([$email, $_SERVER['REMOTE_ADDR']]);
    }

    private static function formatTimeLeft(int $seconds): string
    {
        $minutes = ceil($seconds / 60);
        return "{$minutes} phút";
    }
}