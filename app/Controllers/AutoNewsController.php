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
     * Tải ảnh từ URL về server và lưu vào thư mục uploads
     */
    private function downloadImage($imageUrl, $articleTitle)
    {
        try {
            // Kiểm tra URL hợp lệ
            if (empty($imageUrl) || !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                error_log("Invalid image URL: {$imageUrl}");
                return null;
            }

            // Tạo thư mục uploads nếu chưa có
            $uploadDir = BASE_PATH . '/public/uploads/articles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Tạo context với user agent để tránh bị chặn
            $context = stream_context_create([
                'http' => [
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'timeout' => 10,
                    'follow_location' => true
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);

            // Tải ảnh
            $imageData = @file_get_contents($imageUrl, false, $context);
            
            if ($imageData === false) {
                error_log("Cannot download image: {$imageUrl}");
                return null;
            }

            // Kiểm tra kích thước ảnh (giới hạn 5MB)
            if (strlen($imageData) > 5 * 1024 * 1024) {
                error_log("Image too large: {$imageUrl}");
                return null;
            }

            // Lấy extension từ URL hoặc phát hiện từ content
            $extension = $this->getImageExtension($imageUrl, $imageData);
            
            if (!$extension) {
                error_log("Cannot determine image extension: {$imageUrl}");
                return null;
            }

            // Tạo tên file unique
            $safeTitle = preg_replace('/[^a-z0-9\-]/i', '-', $articleTitle);
            $safeTitle = substr($safeTitle, 0, 50); // Giới hạn độ dài
            $fileName = uniqid('rss_', true) . '-' . $safeTitle . '.' . $extension;
            $filePath = $uploadDir . $fileName;

            // Lưu file
            if (file_put_contents($filePath, $imageData) === false) {
                error_log("Cannot save image: {$filePath}");
                return null;
            }

            // Kiểm tra file có phải ảnh hợp lệ không
            $imageInfo = @getimagesize($filePath);
            if ($imageInfo === false) {
                @unlink($filePath);
                error_log("Invalid image file: {$filePath}");
                return null;
            }

            echo "  ✓ Đã tải ảnh: {$fileName}\n";
            return $fileName; // Chỉ trả về tên file, không có path

        } catch (Exception $e) {
            error_log("Error downloading image: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Xác định extension của ảnh
     */
    private function getImageExtension($url, $imageData)
    {
        // Thử lấy từ URL trước
        $urlParts = parse_url($url);
        if (isset($urlParts['path'])) {
            $pathInfo = pathinfo($urlParts['path']);
            if (isset($pathInfo['extension'])) {
                $ext = strtolower($pathInfo['extension']);
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    return $ext;
                }
            }
        }

        // Phát hiện từ content nếu không lấy được từ URL
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($imageData);

        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];

        return $mimeToExt[$mimeType] ?? null;
    }

    /**
     * Trích xuất URL ảnh từ RSS item
     */
    private function extractImageUrl($item, $description)
    {
        // 1. Kiểm tra media:content (RSS 2.0 Media)
        if (isset($item->children('media', true)->content)) {
            $mediaContent = $item->children('media', true)->content;
            if (isset($mediaContent['url'])) {
                return (string)$mediaContent['url'];
            }
        }

        // 2. Kiểm tra media:thumbnail
        if (isset($item->children('media', true)->thumbnail)) {
            $mediaThumbnail = $item->children('media', true)->thumbnail;
            if (isset($mediaThumbnail['url'])) {
                return (string)$mediaThumbnail['url'];
            }
        }

        // 3. Kiểm tra enclosure
        if (isset($item->enclosure)) {
            $enclosure = $item->enclosure;
            if (isset($enclosure['type']) && strpos((string)$enclosure['type'], 'image/') === 0) {
                return (string)$enclosure['url'];
            }
        }

        // 4. Tìm ảnh trong description HTML
        if (!empty($description)) {
            $dom = new DOMDocument();
            @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $description);
            $images = $dom->getElementsByTagName('img');
            
            if ($images->length > 0) {
                $src = $images->item(0)->getAttribute('src');
                if (!empty($src)) {
                    return $src;
                }
            }
        }

        return null;
    }

    /**
     * Crawl nội dung đầy đủ từ URL bài báo
     */
    private function crawlArticleContent($url)
    {
        try {
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

            $html = @file_get_contents($url, false, $context);
            if ($html === false) {
                error_log("Cannot fetch URL: {$url}");
                return null;
            }

            $dom = new DOMDocument();
            @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
            $xpath = new DOMXPath($dom);

            $content = '';

            // Các selector phổ biến cho nội dung bài báo
            $selectors = [
                "//div[contains(@class, 'detail-content')]//p",
                "//div[@id='main-detail-body']//p",
                "//article[@class='fck_detail']//p",
                "//div[@class='Normal']//p",
                "//div[@class='details__content']//p",
                "//div[@id='abody']//p",
                "//div[@class='singular-content']//p",
                "//div[@class='dt-news__content']//p",
                "//div[@class='the-article-body']//p",
                "//article[@class='article-main']//p",
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
                        
                        if (strlen($text) > 50 && 
                            !preg_match('/^(Ảnh|Hình|Nguồn|Video|Xem thêm|Theo|Photo)/i', $text)) {
                            $tempContent[] = $text;
                        }
                    }
                    
                    if (count($tempContent) >= 3) {
                        $content = implode("\n\n", $tempContent);
                        break;
                    }
                }
            }

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

        $text = preg_replace('/\s+/', ' ', $text);
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

        $paragraphs = explode("\n\n", $content);
        $firstParagraph = $paragraphs[0] ?? '';

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

                    // Trích xuất và tải ảnh
                    $featuredImage = null;
                    echo "  → Đang tìm ảnh...\n";
                    $imageUrl = $this->extractImageUrl($item, $description);
                    
                    if ($imageUrl) {
                        echo "  → Tìm thấy ảnh: {$imageUrl}\n";
                        echo "  → Đang tải ảnh...\n";
                        $featuredImage = $this->downloadImage($imageUrl, $title);
                        
                        if ($featuredImage) {
                            echo "  ✓ Đã lưu ảnh: {$featuredImage}\n";
                        } else {
                            echo "  ✗ Không thể tải ảnh\n";
                        }
                    } else {
                        echo "  ✗ Không tìm thấy ảnh trong RSS\n";
                    }

                    // Crawl nội dung đầy đủ từ URL
                    echo "  → Đang crawl nội dung...\n";
                    $fullContent = $this->crawlArticleContent($link);
                    
                    if ($fullContent && strlen($fullContent) > 200) {
                        $content = $this->cleanContent($fullContent);
                        $summary = $this->generateSummary($content);
                        
                        echo "  ✓ Đã crawl được " . strlen($content) . " ký tự\n";
                    } else {
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
                        'auto_generated' => 1,
                        'featured_image' => $featuredImage // Thêm ảnh vào đây
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