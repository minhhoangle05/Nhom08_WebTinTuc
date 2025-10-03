<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class AdminController extends Controller
{
    public function index(): void
    {
        if (!Auth::check() || Auth::user()['role_id'] !== 3) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }
        $db = Database::getConnection();
        
        // Tổng quan
        // Thống kê cơ bản
        $stats = [
            'articles' => (int)$db->query('SELECT COUNT(*) FROM articles')->fetchColumn(),
            'users' => (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'comments' => (int)$db->query('SELECT COUNT(*) FROM comments')->fetchColumn(),
            'total_views' => (int)$db->query('SELECT COUNT(*) FROM article_views')->fetchColumn()
        ];
        
        // Thống kê lượt xem theo thời gian
        $viewStats = $db->query('
            SELECT 
                DATE(viewed_at) as date,
                COUNT(*) as view_count,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(DISTINCT ip_address) as unique_ips
            FROM article_views
            WHERE viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(viewed_at)
            ORDER BY date DESC
        ')->fetchAll();

        // Hoạt động người dùng gần đây
        $recentActivities = $db->query('
            SELECT 
                ua.*,
                u.name as user_name,
                CASE 
                    WHEN ua.activity_type IN ("article_view", "article_create", "article_edit", "article_delete") 
                    THEN (SELECT title FROM articles WHERE id = ua.reference_id)
                    ELSE NULL
                END as article_title
            FROM user_activities ua
            JOIN users u ON ua.user_id = u.id
            ORDER BY ua.created_at DESC
            LIMIT 10
        ')->fetchAll();

        // Bài viết mới nhất
        $latestArticles = $db->query('
            SELECT a.*, u.name as author_name, c.name as category_name 
            FROM articles a 
            LEFT JOIN users u ON a.user_id = u.id 
            LEFT JOIN categories c ON a.category_id = c.id 
            ORDER BY a.created_at DESC 
            LIMIT 5
        ')->fetchAll();

        // Người dùng mới nhất
        $latestUsers = $db->query('
            SELECT u.*, r.name as role_name,
                   (SELECT COUNT(*) FROM articles WHERE user_id = u.id) as article_count
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id
            ORDER BY u.created_at DESC 
            LIMIT 5
        ')->fetchAll();

        // Thống kê theo danh mục
        $categoryStats = $db->query('
            SELECT c.name, COUNT(a.id) as article_count, SUM(a.views) as total_views
            FROM categories c
            LEFT JOIN articles a ON c.id = a.category_id
            GROUP BY c.id, c.name
            ORDER BY article_count DESC
        ')->fetchAll();

        // Bài viết xem nhiều nhất
        $popularArticles = $db->query('
            SELECT a.*, u.name as author_name, c.name as category_name
            FROM articles a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            ORDER BY a.views DESC
            LIMIT 5
        ')->fetchAll();

        $this->view('admin/dashboard', [
            'title' => 'Bảng điều khiển',
            'stats' => $stats,
            'latestArticles' => $latestArticles,
            'latestUsers' => $latestUsers,
            'categoryStats' => $categoryStats,
            'popularArticles' => $popularArticles,
            'viewStats' => $viewStats,
            'recentActivities' => $recentActivities
        ]);
    }
}


