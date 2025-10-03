<?php
namespace App\Core;

class ActivityLogger
{
    public static function log(string $activityType, ?int $referenceId = null): void
    {
        if (!Auth::check()) {
            return;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('
            INSERT INTO user_activities (user_id, activity_type, ip_address, user_agent, reference_id)
            VALUES (?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            Auth::user()['id'],
            $activityType,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $referenceId
        ]);
    }

    public static function logView(int $articleId): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('
            INSERT INTO article_views (article_id, user_id, ip_address, user_agent)
            VALUES (?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $articleId,
            Auth::check() ? Auth::user()['id'] : null,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
}