<?php
// app/Controllers/ApiController.php - AI SUMMARIZER

namespace App\Controllers;

use App\Core\Controller;

class ApiController extends Controller
{
    // API Keys - LƯU VÀO .env HOẶC config.php
    private const GEMINI_API_KEY = 'YOUR_GEMINI_API_KEY_HERE'; // Lấy tại: https://makersuite.google.com/app/apikey
    
    /**
     * Tóm tắt nội dung bài viết - SỬ DỤNG AI
     */
    public function summarize()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $content = $input['content'] ?? '';

        if (empty($content)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Nội dung không được để trống'
            ]);
            return;
        }

        // Giới hạn độ dài hợp lý
        $maxLength = 5000;
        if (mb_strlen($content) > $maxLength) {
            $content = mb_substr($content, 0, $maxLength);
        }

        try {
            $startTime = microtime(true);
            
            // Ưu tiên: Thử Google Gemini (FREE) → OpenAI (Paid) → Local fallback
            $summary = null;
            $method = '';
            
            // 1. Thử Google Gemini trước (MIỄN PHÍ)
            if (defined('self::GEMINI_API_KEY') && self::GEMINI_API_KEY !== 'AIzaSyDsNrPnjFRCnc08s2gwSpxigIpKUQAFgqg') {
                error_log("Trying Google Gemini API...");
                $summary = $this->summarizeWithGemini($content);
                if ($summary) {
                    $method = 'google-gemini-ai';
                    error_log("✓ Gemini API succeeded");
                }
            }
            
            // 3. Fallback sang local nếu cả 2 thất bại
            if (!$summary) {
                error_log("All AI APIs failed, using local fallback...");
                $summary = $this->intelligentSummarize($content);
                $method = 'extractive-local';
            }
            
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            $this->jsonResponse([
                'success' => true,
                'summary' => $summary,
                'message' => 'Tóm tắt thành công',
                'processing_time_ms' => $processingTime,
                'method' => $method
            ]);

        } catch (\Exception $e) {
            error_log("Summarization error: " . $e->getMessage());
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Lỗi khi tóm tắt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * TÓM TẮT BẰNG GOOGLE GEMINI (MIỄN PHÍ - 60 requests/phút)
     */
    private function summarizeWithGemini($content)
    {
        try {
            $apiKey = self::GEMINI_API_KEY;
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$apiKey}";
            
            // Tạo prompt chi tiết bằng tiếng Việt
            $prompt = $this->createVietnamesePrompt($content);
            
            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 500,
                ]
            ];
            
            $response = $this->sendHttpRequest($url, $payload, 'POST', [
                'Content-Type: application/json'
            ]);
            
            if (!$response) {
                error_log("Gemini: Empty response");
                return null;
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $summary = trim($data['candidates'][0]['content']['parts'][0]['text']);
                
                // Loại bỏ markdown formatting nếu có
                $summary = preg_replace('/^#+\s+/m', '', $summary);
                $summary = preg_replace('/\*\*(.*?)\*\*/', '$1', $summary);
                
                return $summary;
            }
            
            error_log("Gemini: Invalid response format - " . print_r($data, true));
            return null;
            
        } catch (\Exception $e) {
            error_log("Gemini API error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tạo prompt tiếng Việt chi tiết
     */
    private function createVietnamesePrompt($content)
    {
        // Cắt ngắn nội dung nếu quá dài
        if (mb_strlen($content) > 4000) {
            $content = mb_substr($content, 0, 4000) . '...';
        }
        
        return <<<PROMPT
Hãy đọc kỹ bài viết sau và tạo một bản tóm tắt ngắn gọn, KHÔNG QUÁ 200 từ, bằng tiếng Việt.

YÊU CÀU:
- Tóm tắt phải BẮT ĐẦU NGAY bằng nội dung chính, KHÔNG cần mở đầu như "Bài viết này nói về...", "Nội dung chính là..."
- Sử dụng ngôn ngữ tự nhiên, mạch lạc, dễ hiểu
- Tập trung vào các ý chính và thông tin quan trọng nhất
- Giữ nguyên các con số, tên riêng, phần trăm nếu có
- Viết thành 1 đoạn văn liền mạch, KHÔNG chia thành nhiều điểm
- Kết thúc bằng dấu chấm

NỘI DUNG BÀI VIẾT:
{$content}

TÓM TẮT (bắt đầu ngay, không dùng mở đầu):
PROMPT;
    }

    /**
     * Gửi HTTP request với cURL
     */
    private function sendHttpRequest($url, $data, $method = 'POST', $headers = [])
    {
        $ch = curl_init($url);
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            error_log("cURL error: {$error}");
            return null;
        }
        
        if ($httpCode < 200 || $httpCode >= 300) {
            error_log("HTTP error {$httpCode}: {$response}");
            return null;
        }
        
        return $response;
    }

    /**
     * THUẬT TOÁN LOCAL FALLBACK (giữ nguyên từ code cũ)
     */
    private function intelligentSummarize($text)
    {
        $text = $this->cleanText($text);
        
        if (mb_strlen($text) < 100) {
            return $text;
        }
        
        $sentences = $this->smartSplitSentences($text);
        
        if (count($sentences) <= 3) {
            return $this->formatSummary($sentences);
        }

        $scoredSentences = $this->scoreSentences($sentences, $text);
        $selectedSentences = $this->selectBestSentences($scoredSentences, $sentences);
        
        return $this->formatSummary($selectedSentences);
    }

    // ... (Giữ nguyên tất cả các method helper từ code cũ: cleanText, smartSplitSentences, scoreSentences, v.v.)
    
    private function cleanText($text)
    {
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/[^\p{L}\p{N}\s.,!?;:\-()""\'%]/u', '', $text);
        return trim($text);
    }

    private function smartSplitSentences($text)
    {
        $pattern = '/(?<=[.!?。！？])\s+(?=[A-ZÀÁẢÃẠĂẰẮẲẴẶÂẦẤẨẪẬÈÉẺẼẸÊỀẾỂỄỆÌÍỈĨỊÒÓỎÕỌÔỒỐỔỖỘƠỜỚỞỠỢÙÚỦŨỤƯỪỨỬỮỰỲÝỶỸỴĐ])/u';
        $rawSentences = preg_split($pattern, $text, -1, PREG_SPLIT_NO_EMPTY);
        
        $sentences = [];
        foreach ($rawSentences as $sentence) {
            $sentence = trim($sentence);
            $wordCount = $this->countWords($sentence);
            if ($wordCount >= 5 && $wordCount <= 100) {
                $sentences[] = $sentence;
            }
        }
        return $sentences;
    }

    private function scoreSentences($sentences, $fullText)
    {
        $scores = [];
        $totalSentences = count($sentences);
        
        foreach ($sentences as $index => $sentence) {
            $score = 0;
            
            // Position score
            if ($index === 0) {
                $score += 10;
            } elseif ($index < 3) {
                $score += 6;
            } elseif ($index >= $totalSentences - 2) {
                $score += 5;
            }
            
            // Length score
            $wordCount = $this->countWords($sentence);
            if ($wordCount >= 12 && $wordCount <= 30) {
                $score += 8;
            }
            
            $scores[$index] = $score;
        }
        
        return $scores;
    }

    private function selectBestSentences($scores, $sentences)
    {
        arsort($scores);
        $targetCount = min(4, max(2, (int)(count($sentences) * 0.2)));
        
        $selected = [];
        foreach (array_keys($scores) as $index) {
            if (count($selected) >= $targetCount) break;
            $selected[$index] = $sentences[$index];
        }
        
        ksort($selected);
        return array_values($selected);
    }

    private function formatSummary($sentences)
    {
        if (empty($sentences)) {
            return 'Không thể tạo tóm tắt cho nội dung này.';
        }
        
        $summary = implode(' ', array_map('trim', $sentences));
        $summary = mb_strtoupper(mb_substr($summary, 0, 1)) . mb_substr($summary, 1);
        
        return trim($summary);
    }

    private function countWords($text)
    {
        return count(preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY));
    }

    /**
     * Endpoint tương thích
     */
    public function summarizeLocal()
    {
        return $this->summarize();
    }

    /**
     * Trả về JSON response
     */
    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}