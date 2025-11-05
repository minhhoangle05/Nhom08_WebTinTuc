<?php
namespace App\Controllers;

use App\Models\AutoNewsSource;
use App\Models\AutoNewsLog;
use App\Models\Article;
use Exception;
use DOMDocument;
use DOMXPath;

class AutoNewsController
{
    /**
     * Crawl nội dung đầy đủ từ URL bài báo
     */
    private function crawlArticleContent($url)
    {
        try {
            // Tạo context với user agent
            $context = stream_context_create([
                'http' => [
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'timeout' => 15,
                    'follow_location' => true
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);

            // Lấy HTML
            $html = @file_get_contents($url, false, $context);
            if ($html === false) {
                error_log("Cannot fetch URL: {$url}");
                return null;
            }

            // Parse HTML
            $dom = new DOMDocument();
            @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
            $xpath = new DOMXPath($dom);

            $content = '';

            // Thử các selector phổ biến cho nội dung bài báo
            $selectors = [
                // Tuổi Trẻ
                "//div[contains(@class, 'detail-content')]//p",
                "//div[@id='main-detail-body']//p",
                
                // VnExpress
                "//article[@class='fck_detail']//p",
                "//div[@class='Normal']//p",
                
                // Thanh Niên
                "//div[@class='details__content']//p",
                "//div[@id='abody']//p",
                
                // Dân Trí
                "//div[@class='singular-content']//p",
                "//div[@class='dt-news__content']//p",
                
                // Zing News
                "//div[@class='the-article-body']//p",
                "//article[@class='article-main']//p",
                
                // Generic selectors (dự phòng)
                "//article//p",
                "//div[contains(@class, 'content')]//p",
                "//div[contains(@class, 'article')]//p",
                "//div[contains(@class, 'post-content')]//p"
            ];

            foreach ($selectors as $selector) {
                $paragraphs = $xpath->query($selector);
                
                if ($paragraphs && $paragraphs->length > 0) {
                    $tempContent = [];
                    
                    foreach ($paragraphs as $p) {
                        $text = trim($p->textContent);
                        
                        // Loại bỏ các đoạn quá ngắn hoặc là caption ảnh
                        if (strlen($text) > 50 && 
                            !preg_match('/^(Ảnh|Hình|Nguồn|Video|Xem thêm|Theo|Photo)/i', $text)) {
                            $tempContent[] = $text;
                        }
                    }
                    
                    // Nếu tìm được ít nhất 3 đoạn văn, coi như thành công
                    if (count($tempContent) >= 3) {
                        $content = implode("\n\n", $tempContent);
                        break;
                    }
                }
            }

            // Nếu vẫn không tìm được nội dung, thử lấy toàn bộ text
            if (empty($content)) {
                error_log("Cannot extract content from: {$url}");
                return null;
            }

            return $content;

        } catch (Exception $e) {
            error_log("Error crawling content: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Làm sạch và format nội dung
     */
    private function cleanContent($text)
    {
        if (empty($text)) return '';

        // Loại bỏ whitespace thừa
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Loại bỏ các dòng quảng cáo phổ biến
        $text = preg_replace('/Theo .+?\n/i', '', $text);
        $text = preg_replace('/Xem thêm:.+?\n/i', '', $text);
        $text = preg_replace('/\[.*?\]/i', '', $text);
        
        return trim($text);
    }

    /**
     * Tạo tóm tắt từ nội dung đầy đủ
     */
    private function generateSummary($content, $maxLength = 300)
    {
        if (empty($content)) return '';

        // Lấy đoạn đầu tiên
        $paragraphs = explode("\n\n", $content);
        $firstParagraph = $paragraphs[0] ?? '';

        // Cắt ngắn nếu quá dài
        if (mb_strlen($firstParagraph) > $maxLength) {
            $firstParagraph = mb_substr($firstParagraph, 0, $maxLength);
            $lastSpace = mb_strrpos($firstParagraph, ' ');
            if ($lastSpace !== false) {
                $firstParagraph = mb_substr($firstParagraph, 0, $lastSpace);
            }
            $firstParagraph .= '...';
        }

        return $firstParagraph;
    }

    /**
     * Tạo slug từ tiêu đề
     */
    private function createSlug($title)
    {
        $slug = mb_strtolower($title, 'UTF-8');
        
        $vietnamese = [
            'à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
            'đ' => 'd',
            'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
        ];
        
        $slug = strtr($slug, $vietnamese);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        $slug = preg_replace('/-+/', '-', $slug);
        
        return $slug;
    }

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
                    
                    echo "\n[{$articlesFetched}] Đang xử lý: {$title}\n";
                    echo "URL: {$link}\n";
                    
                    // Tạo slug
                    $baseSlug = $this->createSlug($title);
                    $slug = $baseSlug;
                    
                    // Đảm bảo slug unique
                    $pdo = $sourceModel->getConnection();
                    $counter = 1;
                    while (true) {
                        $check = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE slug = ?");
                        $check->execute([$slug]);
                        
                        if ($check->fetchColumn() == 0) {
                            break;
                        }
                        
                        $slug = $baseSlug . '-' . $counter;
                        $counter++;
                    }

                    // Crawl nội dung đầy đủ từ URL
                    echo "  → Đang crawl nội dung...\n";
                    $fullContent = $this->crawlArticleContent($link);
                    
                    if ($fullContent && strlen($fullContent) > 200) {
                        // Sử dụng nội dung đã crawl
                        $content = $this->cleanContent($fullContent);
                        $summary = $this->generateSummary($content);
                        
                        echo "  ✓ Đã crawl được " . strlen($content) . " ký tự\n";
                    } else {
                        // Fallback sang description nếu không crawl được
                        echo "  ✗ Không crawl được, dùng description\n";
                        $content = strip_tags($description);
                        $summary = mb_substr($content, 0, 300);
                    }

                    // Lưu bài viết
                    $articleModel->insertArticle([
                        'user_id'        => 10,
                        'title'          => $title,
                        'summary'        => $summary,
                        'slug'           => $slug,
                        'content'        => $content,
                        'views'          => 0,
                        'created_at'     => date('Y-m-d H:i:s'),
                        'updated_at'     => date('Y-m-d H:i:s'),
                        'source_url'     => $link,
                        'source_name'    => $source['name'],
                        'auto_generated' => 1
                    ]);

                    $articlesSaved++;
                    $totalSaved++;
                    
                    echo "  ✓ Đã lưu bài viết\n";
                    
                    // Delay để tránh bị chặn
                    usleep(500000); // 0.5 giây
                }
                
            } catch (Exception $e) {
                $status = 'failed';
                $errorMessage = $e->getMessage();
                echo "✗ Lỗi: {$errorMessage}\n";
            }

            $endTime  = microtime(true);
            $duration = round($endTime - $startTime, 2);

            // Ghi log
            $logModel->insertLog(
                $source['name'],
                $articlesFetched,
                $articlesSaved,
                $status,
                $errorMessage,
                $duration
            );
        }

        echo "\n";
        echo json_encode([
            'success' => true,
            'message' => "Cập nhật hoàn tất! Tổng số bài mới: {$totalSaved}"
        ], JSON_UNESCAPED_UNICODE);
    }
}