<?php
// app/Controllers/ApiController.php - LOCAL SUMMARIZER ONLY

namespace App\Controllers;

use App\Core\Controller;

class ApiController extends Controller
{
    /**
     * Tóm tắt nội dung bài viết - CHỈ DÙNG THUẬT TOÁN LOCAL
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
            
            // Sử dụng thuật toán tóm tắt thông minh
            $summary = $this->intelligentSummarize($content);
            
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            $this->jsonResponse([
                'success' => true,
                'summary' => $summary,
                'message' => 'Tóm tắt thành công',
                'processing_time_ms' => $processingTime,
                'method' => 'extractive-smart'
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
     * THUẬT TOÁN TÓM TẮT THÔNG MINH - OPTIMIZED
     * Kết hợp nhiều kỹ thuật NLP để tạo summary chất lượng cao
     */
    private function intelligentSummarize($text)
    {
        // Bước 1: Làm sạch và chuẩn hóa
        $text = $this->cleanText($text);
        
        if (mb_strlen($text) < 100) {
            return $text; // Văn bản quá ngắn, trả về nguyên văn
        }
        
        // Bước 2: Tách câu thông minh
        $sentences = $this->smartSplitSentences($text);
        
        if (count($sentences) <= 3) {
            return $this->formatSummary($sentences);
        }

        // Bước 3: Phân tích và đánh giá câu
        $scoredSentences = $this->scoreSentences($sentences, $text);
        
        // Bước 4: Chọn câu tốt nhất
        $selectedSentences = $this->selectBestSentences($scoredSentences, $sentences);
        
        // Bước 5: Format kết quả
        return $this->formatSummary($selectedSentences);
    }

    /**
     * Làm sạch văn bản
     */
    private function cleanText($text)
    {
        // Loại bỏ HTML
        $text = strip_tags($text);
        
        // Chuẩn hóa whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Loại bỏ ký tự đặc biệt không cần thiết (giữ dấu câu)
        $text = preg_replace('/[^\p{L}\p{N}\s.,!?;:\-()""\'%]/u', '', $text);
        
        return trim($text);
    }

    /**
     * Tách câu thông minh - Hỗ trợ tiếng Việt
     */
    private function smartSplitSentences($text)
    {
        // Pattern tách câu cho tiếng Việt và tiếng Anh
        $pattern = '/(?<=[.!?。！？])\s+(?=[A-ZÀÁẢÃẠĂẰẮẲẴẶÂẦẤẨẪẬÈÉẺẼẸÊỀẾỂỄỆÌÍỈĨỊÒÓỎÕỌÔỒỐỔỖỘƠỜỚỞỠỢÙÚỦŨỤƯỪỨỬỮỰỲÝỶỸỴĐ])/u';
        
        $rawSentences = preg_split($pattern, $text, -1, PREG_SPLIT_NO_EMPTY);
        
        $sentences = [];
        foreach ($rawSentences as $sentence) {
            $sentence = trim($sentence);
            $wordCount = $this->countWords($sentence);
            
            // Lọc câu có độ dài hợp lý (5-100 từ)
            if ($wordCount >= 5 && $wordCount <= 100) {
                $sentences[] = $sentence;
            }
        }
        
        return $sentences;
    }

    /**
     * Đánh giá và cho điểm mỗi câu
     */
    private function scoreSentences($sentences, $fullText)
    {
        $scores = [];
        $totalSentences = count($sentences);
        
        // Tính TF-IDF để tìm từ khóa quan trọng
        $tfidf = $this->calculateTFIDF($sentences);
        
        // Trích xuất cụm từ khóa
        $keyPhrases = $this->extractKeyPhrases($fullText);
        
        // Từ chỉ tầm quan trọng
        $importanceIndicators = [
            'high' => [
                'quan trọng', 'chính', 'cần', 'phải', 'đặc biệt', 'chủ yếu', 
                'trọng tâm', 'cốt lõi', 'then chốt', 'thiết yếu', 'cấp thiết',
                'important', 'main', 'key', 'essential', 'critical', 'vital'
            ],
            'medium' => [
                'thêm', 'cũng', 'ngoài ra', 'bên cạnh', 'đồng thời', 'kèm theo',
                'also', 'additionally', 'furthermore', 'moreover'
            ],
            'low' => [
                'có thể', 'đôi khi', 'thỉnh thoảng', 'đại khái', 'khoảng',
                'maybe', 'perhaps', 'possibly', 'sometimes'
            ]
        ];
        
        foreach ($sentences as $index => $sentence) {
            $score = 0;
            $sentenceLower = mb_strtolower($sentence);
            
            // 1. ĐIỂM VỊ TRÍ (Position Score)
            if ($index === 0) {
                $score += 10; // Câu đầu tiên cực kỳ quan trọng
            } elseif ($index < 3) {
                $score += 6;  // Đoạn mở đầu
            } elseif ($index >= $totalSentences - 2) {
                $score += 5;  // Đoạn kết luận
            } else {
                $score += 2;  // Phần thân bài
            }
            
            // 2. ĐIỂM ĐỘ DÀI (Length Score)
            $wordCount = $this->countWords($sentence);
            if ($wordCount >= 12 && $wordCount <= 30) {
                $score += 8; // Độ dài lý tưởng
            } elseif ($wordCount >= 8 && $wordCount <= 40) {
                $score += 5;
            } elseif ($wordCount < 8) {
                $score -= 3; // Câu quá ngắn
            }
            
            // 3. ĐIỂM TỪ KHÓA (TF-IDF Score)
            $tfidfScore = 0;
            foreach ($tfidf as $word => $value) {
                if (mb_stripos($sentenceLower, $word) !== false) {
                    $tfidfScore += $value;
                }
            }
            $score += min($tfidfScore * 3, 12); // Tối đa 12 điểm
            
            // 4. ĐIỂM CỤM TỪ KHÓA (Key Phrases Score)
            foreach ($keyPhrases as $phrase) {
                if (mb_stripos($sentenceLower, $phrase) !== false) {
                    $score += 4;
                }
            }
            
            // 5. ĐIỂM DỮ LIỆU CỤ THỂ (Numerical Data Score)
            // Số lượng
            $numberCount = preg_match_all('/\b\d+\b/', $sentence);
            $score += min($numberCount * 2, 6);
            
            // Phần trăm
            if (preg_match('/\d+\s*%/', $sentence)) {
                $score += 4;
            }
            
            // Năm (1900-2099)
            if (preg_match('/\b(19|20)\d{2}\b/', $sentence)) {
                $score += 3;
            }
            
            // 6. ĐIỂM TÊN RIÊNG (Named Entities Score)
            $capitalCount = preg_match_all('/\b[A-ZÀÁẢÃẠĂẰẮẲẴẶÂẦẤẨẪẬÈÉẺẼẸÊỀẾỂỄỆ][a-zàáảãạăằắẳẵặâầấẩẫậèéẻẽẹêềếểễệìíỉĩịòóỏõọôồốổỗộơờớởỡợùúủũụưừứửữựỳýỷỹỵđ]+/u', $sentence);
            $score += min($capitalCount * 2, 8);
            
            // 7. ĐIỂM TỪ CHỈ TẦM QUAN TRỌNG (Importance Indicators)
            foreach ($importanceIndicators['high'] as $indicator) {
                if (mb_stripos($sentenceLower, $indicator) !== false) {
                    $score += 5;
                    break;
                }
            }
            foreach ($importanceIndicators['medium'] as $indicator) {
                if (mb_stripos($sentenceLower, $indicator) !== false) {
                    $score += 2;
                    break;
                }
            }
            foreach ($importanceIndicators['low'] as $indicator) {
                if (mb_stripos($sentenceLower, $indicator) !== false) {
                    $score -= 2;
                }
            }
            
            // 8. ĐIỂM CẤU TRÚC CÂU (Sentence Structure Score)
            $actionVerbs = [
                'là', 'có', 'được', 'cho', 'giúp', 'tạo', 'làm', 'gây', 'đem', 'mang',
                'is', 'are', 'was', 'were', 'has', 'have', 'makes', 'creates'
            ];
            foreach ($actionVerbs as $verb) {
                if (preg_match('/\b' . preg_quote($verb, '/') . '\b/iu', $sentence)) {
                    $score += 2;
                    break;
                }
            }
            
            // 9. ĐIỂM TRÙ (Penalty Scores)
            // Câu bắt đầu bằng liên từ yếu
            $weakStarts = ['tuy nhiên', 'bởi vì', 'nhưng', 'và', 'however', 'but', 'and'];
            foreach ($weakStarts as $start) {
                if (mb_stripos($sentenceLower, $start) === 0) {
                    $score -= 3;
                    break;
                }
            }
            
            // Câu có quá nhiều dấu phấy (khó đọc)
            $commaCount = substr_count($sentence, ',');
            if ($commaCount > 4) {
                $score -= 2;
            }
            
            // 10. ĐIỂM THƯỞNG (Bonus Scores)
            // Câu hỏi (thường là điểm nhấn)
            if (mb_strpos($sentence, '?') !== false) {
                $score += 3;
            }
            
            // Trích dẫn
            if (preg_match('/[""]/', $sentence)) {
                $score += 2;
            }
            
            // Lưu điểm (không cho âm)
            $scores[$index] = max($score, 0);
        }
        
        return $scores;
    }

    /**
     * Tính TF-IDF (Term Frequency-Inverse Document Frequency)
     */
    private function calculateTFIDF($sentences)
    {
        $wordDocCount = [];
        $totalDocs = count($sentences);
        
        // Đếm số document chứa mỗi từ
        foreach ($sentences as $sentence) {
            $words = $this->extractWords($sentence);
            $uniqueWords = array_unique($words);
            
            foreach ($uniqueWords as $word) {
                if (!isset($wordDocCount[$word])) {
                    $wordDocCount[$word] = 0;
                }
                $wordDocCount[$word]++;
            }
        }
        
        // Tính IDF
        $tfidf = [];
        foreach ($wordDocCount as $word => $docCount) {
            $idf = log($totalDocs / $docCount);
            
            // Chỉ giữ từ có IDF cao (từ hiếm = quan trọng)
            if ($idf > 0.5) {
                $tfidf[$word] = $idf;
            }
        }
        
        // Sắp xếp và lấy top từ khóa
        arsort($tfidf);
        return array_slice($tfidf, 0, 20, true);
    }

    /**
     * Trích xuất cụm từ khóa (Key Phrases)
     */
    private function extractKeyPhrases($text)
    {
        $text = mb_strtolower($text);
        
        // Tìm bigrams và trigrams
        preg_match_all('/\b(\w+\s+\w+\s+\w+|\w+\s+\w+)\b/u', $text, $matches);
        
        if (empty($matches[0])) {
            return [];
        }
        
        // Đếm tần suất
        $phrases = array_count_values($matches[0]);
        arsort($phrases);
        
        // Lọc phrases xuất hiện >= 2 lần và đủ dài
        $keyPhrases = [];
        foreach ($phrases as $phrase => $count) {
            if ($count >= 2 && mb_strlen($phrase) > 8) {
                $keyPhrases[] = $phrase;
            }
        }
        
        return array_slice($keyPhrases, 0, 12);
    }

    /**
     * Chọn các câu tốt nhất với đa dạng nội dung
     */
    private function selectBestSentences($scores, $sentences)
    {
        // Sắp xếp theo điểm giảm dần
        arsort($scores);
        
        $selected = [];
        $selectedIndices = [];
        $usedWords = [];
        
        // Tính số câu cần chọn (15-22% tổng số câu)
        $totalSentences = count($sentences);
        $targetCount = min(6, max(3, (int)($totalSentences * 0.18)));
        
        foreach ($scores as $index => $score) {
            if (count($selected) >= $targetCount) {
                break;
            }
            
            $sentence = $sentences[$index];
            $words = $this->extractWords($sentence);
            
            // Tính độ trùng lặp với các câu đã chọn
            $overlap = 0;
            foreach ($words as $word) {
                if (in_array($word, $usedWords)) {
                    $overlap++;
                }
            }
            
            // Chỉ chọn nếu độ trùng lặp < 35%
            $overlapRatio = count($words) > 0 ? $overlap / count($words) : 0;
            
            if ($overlapRatio < 0.35) {
                $selected[$index] = $sentence;
                $selectedIndices[] = $index;
                $usedWords = array_merge($usedWords, $words);
            }
        }
        
        // Sắp xếp lại theo thứ tự xuất hiện trong bài gốc
        ksort($selected);
        
        return array_values($selected);
    }

    /**
     * Format summary để dễ đọc
     */
    private function formatSummary($sentences)
    {
        if (empty($sentences)) {
            return 'Không thể tạo tóm tắt cho nội dung này.';
        }
        
        $formatted = [];
        
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            
            // Thêm dấu chấm nếu thiếu
            if (!preg_match('/[.!?]$/', $sentence)) {
                $sentence .= '.';
            }
            
            $formatted[] = $sentence;
        }
        
        // Nối các câu
        $summary = implode(' ', $formatted);
        
        // Viết hoa chữ cái đầu
        $summary = mb_strtoupper(mb_substr($summary, 0, 1)) . mb_substr($summary, 1);
        
        // Làm sạch khoảng trắng thừa
        $summary = preg_replace('/\s+/', ' ', $summary);
        $summary = preg_replace('/\s+([.,!?])/', '$1', $summary);
        
        return trim($summary);
    }

    /**
     * Trích xuất từ (bỏ stopwords)
     */
    private function extractWords($text)
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\s]/u', ' ', $text);
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        // Stopwords tiếng Việt và tiếng Anh
        $stopwords = [
            // Tiếng Anh
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 
            'of', 'with', 'by', 'from', 'is', 'was', 'are', 'were', 'been', 'be',
            'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could',
            'this', 'that', 'these', 'those', 'it', 'its',
            // Tiếng Việt
            'của', 'và', 'có', 'là', 'được', 'này', 'một', 'các', 'trong', 'cho',
            'vì', 'với', 'từ', 'đã', 'sẽ', 'đang', 'để', 'những', 'bị', 'hay',
            'khi', 'nếu', 'thì', 'như', 'đó', 'ở', 'về', 'ra', 'vào', 'lên',
            'xuống', 'bởi', 'do', 'nên', 'rằng', 'mà', 'thế', 'cũng'
        ];
        
        $filtered = [];
        foreach ($words as $word) {
            // Giữ từ dài hơn 2 ký tự và không phải stopword
            if (mb_strlen($word) > 2 && !in_array($word, $stopwords)) {
                $filtered[] = $word;
            }
        }
        
        return $filtered;
    }

    /**
     * Đếm số từ (hỗ trợ Unicode/tiếng Việt)
     */
    private function countWords($text)
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        
        if (empty($text)) {
            return 0;
        }
        
        return count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY));
    }

    /**
     * API endpoint tương thích với frontend
     */
    public function summarizeLocal()
    {
        // Gọi lại hàm summarize() chính
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
        header('X-Content-Type-Options: nosniff');
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}