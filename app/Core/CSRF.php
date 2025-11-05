<?php
namespace App\Core;

class CSRF
{
    /**
     * Generate CSRF token
     */
    public static function token(): string
    {
        if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    public static function validate(?string $token): bool
    {
        if (empty($token)) {
            error_log("CSRF validation failed: Token is empty");
            return false;
        }

        if (!isset($_SESSION['csrf_token'])) {
            error_log("CSRF validation failed: No token in session");
            return false;
        }

        $valid = hash_equals($_SESSION['csrf_token'], $token);
        
        if (!$valid) {
            error_log("CSRF validation failed: Token mismatch");
            error_log("Expected: " . $_SESSION['csrf_token']);
            error_log("Received: " . $token);
        }
        
        return $valid;
    }

    /**
     * Regenerate CSRF token (call after successful form submission)
     */
    public static function regenerate(): string
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    /**
     * Get token input field HTML
     */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf" value="' . htmlspecialchars(self::token()) . '">';
    }
}