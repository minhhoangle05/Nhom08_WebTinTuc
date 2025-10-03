<?php
namespace App\Core;

class CSRF
{
    public static function token(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set(CSRF_TOKEN_KEY, $token);
        return $token;
    }

    public static function validate(?string $token): bool
    {
        $sessionToken = Session::get(CSRF_TOKEN_KEY);
        $ok = is_string($token) && is_string($sessionToken) && hash_equals($sessionToken, $token);
        // One-time token
        Session::remove(CSRF_TOKEN_KEY);
        return $ok;
    }
}


