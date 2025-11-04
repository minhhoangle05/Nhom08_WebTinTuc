<?php
namespace App\Controllers;

use App\Models\AutoNewsSource;
use App\Models\AutoNewsLog;
use App\Models\Article;
use Exception;

class AutoNewsController
{
    public function fetchNews()
    {
        $sourceModel  = new AutoNewsSource();
        $logModel     = new AutoNewsLog();
        $articleModel = new Article();

        $sources    = $sourceModel->getActiveSources();
        $totalSaved = 0;

        foreach ($sources as $source) {
            $startTime       = microtime(true);
            $articlesFetched = 0;
            $articlesSaved   = 0;
            $status          = 'success';
            $errorMessage    = '';

            try {
                $feed = @simplexml_load_file($source['url']);
                if (!$feed) throw new Exception("Không thể đọc RSS từ {$source['url']}");

                echo "<pre>";
                echo "Đang lấy nguồn: {$source['url']}\n";
                echo "Số item: " . count($feed->channel->item) . "\n";
                echo "</pre>";

                foreach ($feed->channel->item as $item) {
                    $articlesFetched++;
                    $title       = trim((string)$item->title);
                    $link        = trim((string)$item->link);
                    $description = trim((string)$item->description);
                    $slug        = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));

                    // Kiểm tra trùng bài viết
                    $pdo = $sourceModel->getConnection();
                    $check = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE slug = ?");
                    $check->execute([$slug]);

                    if ($check->fetchColumn() == 0) {
                        // ✅ Thêm bài viết mới
                        $articleModel->insertArticle([
                            'user_id'        => 10,
                            'title'          => $title,
                            'summary'        => strip_tags($description),
                            'slug'           => $slug,
                            'content'        => strip_tags($description),
                            'views'          => 0,
                            'created_at'     => date('Y-m-d H:i:s'),
                            'updated_at'     => date('Y-m-d H:i:s'),
                            'source_url'     => $link,
                            'source_name'    => $source['name'],
                            'auto_generated' => 1
                        ]);

                        $articlesSaved++;
                        $totalSaved++;
                    }
                }
            } catch (Exception $e) {
                $status = 'failed';
                $errorMessage = $e->getMessage();
            }

            $endTime  = microtime(true);
            $duration = round($endTime - $startTime, 2);

            // Ghi log lại
            $logModel->insertLog(
                $source['name'],
                $articlesFetched,
                $articlesSaved,
                $status,
                $errorMessage,
                $duration
            );
        }

        echo json_encode([
            'success' => true,
            'message' => "Cập nhật hoàn tất! Tổng số bài mới: {$totalSaved}"
        ], JSON_UNESCAPED_UNICODE);
    }
}
