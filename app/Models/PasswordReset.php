<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class PasswordReset extends Model
{
    /**
     * Create a new password reset token
     */
    public function create(string $email): string
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $this->db->prepare('
            INSERT INTO password_resets (email, token, created_at, expires_at)
            VALUES (?, ?, NOW(), ?)
        ');
        $stmt->execute([$email, $token, $expires]);

        return $token;
    }

    /**
     * Find valid token
     */
    public function findValidToken(string $token): ?array
    {
        $stmt = $this->db->prepare('
            SELECT * FROM password_resets 
            WHERE token = ? 
            AND used = 0 
            AND expires_at > NOW()
            ORDER BY created_at DESC 
            LIMIT 1
        ');
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        return $reset ?: null;
    }

    /**
     * Mark token as used
     */
    public function markAsUsed(string $token): bool
    {
        $stmt = $this->db->prepare('
            UPDATE password_resets 
            SET used = 1 
            WHERE token = ?
        ');
        return $stmt->execute([$token]);
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpired(): bool
    {
        $stmt = $this->db->prepare('
            DELETE FROM password_resets 
            WHERE expires_at < NOW() 
            OR used = 1
        ');
        return $stmt->execute();
    }
}