-- Script tạo dữ liệu bài viết mẫu với đầy đủ thông tin
-- Chạy script này sau khi đã tạo database và tables

USE `article_portal`;

-- Thêm dữ liệu mẫu cho categories
INSERT IGNORE INTO categories (name, slug) VALUES
('Công nghệ thông tin', 'cong-nghe-thong-tin'),
('Kinh doanh & Khởi nghiệp', 'kinh-doanh-khoi-nghiep'),
('Lối sống & Sức khỏe', 'loi-song-suc-khoe'),
('Giáo dục & Học tập', 'giao-duc-hoc-tap'),
('Du lịch & Khám phá', 'du-lich-kham-pha'),
('Nghệ thuật & Văn hóa', 'nghe-thuat-van-hoa'),
('Thể thao & Giải trí', 'the-thao-giai-tri'),
('Khoa học & Nghiên cứu', 'khoa-hoc-nghien-cuu');

-- Thêm dữ liệu mẫu cho tags
INSERT IGNORE INTO tags (name, slug) VALUES
('PHP', 'php'),
('JavaScript', 'javascript'),
('Python', 'python'),
('React', 'react'),
('Vue.js', 'vue-js'),
('Node.js', 'node-js'),
('MySQL', 'mysql'),
('MongoDB', 'mongodb'),
('API', 'api'),
('Web Development', 'web-development'),
('Mobile App', 'mobile-app'),
('AI', 'ai'),
('Machine Learning', 'machine-learning'),
('Data Science', 'data-science'),
('Blockchain', 'blockchain'),
('Cloud Computing', 'cloud-computing'),
('DevOps', 'devops'),
('Security', 'security'),
('UX/UI Design', 'ux-ui-design'),
('Productivity', 'productivity'),
('Startup', 'startup'),
('Marketing', 'marketing'),
('SEO', 'seo'),
('Social Media', 'social-media'),
('E-commerce', 'e-commerce'),
('Finance', 'finance'),
('Investment', 'investment'),
('Health', 'health'),
('Fitness', 'fitness'),
('Nutrition', 'nutrition'),
('Mental Health', 'mental-health'),
('Travel', 'travel'),
('Photography', 'photography'),
('Music', 'music'),
('Art', 'art'),
('Literature', 'literature'),
('History', 'history'),
('Science', 'science'),
('Environment', 'environment'),
('Education', 'education'),
('Career', 'career'),
('Leadership', 'leadership'),
('Innovation', 'innovation'),
('Sustainability', 'sustainability'),
('Digital Transformation', 'digital-transformation');

-- Thêm users mẫu (nếu chưa có)
INSERT IGNORE INTO users (name, email, password_hash, role_id) VALUES
('Nguyễn Văn A', 'nguyenvana@example.com', '$2y$10$2gH0mB9VwGQz0QKk7s3QWuQy6VhKMpa8t0XKy1kI8s2lYo0a2bK9K', 1),
('Trần Thị B', 'tranthib@example.com', '$2y$10$2gH0mB9VwGQz0QKk7s3QWuQy6VhKMpa8t0XKy1kI8s2lYo0a2bK9K', 1),
('Lê Văn C', 'levanc@example.com', '$2y$10$2gH0mB9VwGQz0QKk7s3QWuQy6VhKMpa8t0XKy1kI8s2lYo0a2bK9K', 2),
('Phạm Thị D', 'phamthid@example.com', '$2y$10$2gH0mB9VwGQz0QKk7s3QWuQy6VhKMpa8t0XKy1kI8s2lYo0a2bK9K', 2);

-- Lấy ID của categories và users
SET @cat_tech = (SELECT id FROM categories WHERE slug = 'cong-nghe-thong-tin' LIMIT 1);
SET @cat_business = (SELECT id FROM categories WHERE slug = 'kinh-doanh-khoi-nghiep' LIMIT 1);
SET @cat_lifestyle = (SELECT id FROM categories WHERE slug = 'loi-song-suc-khoe' LIMIT 1);
SET @cat_education = (SELECT id FROM categories WHERE slug = 'giao-duc-hoc-tap' LIMIT 1);

SET @user1 = (SELECT id FROM users WHERE email = 'nguyenvana@example.com' LIMIT 1);
SET @user2 = (SELECT id FROM users WHERE email = 'tranthib@example.com' LIMIT 1);
SET @user3 = (SELECT id FROM users WHERE email = 'levanc@example.com' LIMIT 1);
SET @user4 = (SELECT id FROM users WHERE email = 'phamthid@example.com' LIMIT 1);

-- Tạo bài viết mẫu với đầy đủ thông tin
INSERT IGNORE INTO articles (
    user_id, 
    category_id, 
    title, 
    slug, 
    content, 
    summary,
    featured_image,
    meta_description,
    meta_keywords,
    status,
    views, 
    is_featured, 
    created_at
) VALUES
(
    @user1, 
    @cat_tech, 
    'Hướng dẫn lập trình PHP từ cơ bản đến nâng cao', 
    'huong-dan-lap-trinh-php-tu-co-ban-den-nang-cao',
    '# Giới thiệu về PHP

PHP (PHP: Hypertext Preprocessor) là một ngôn ngữ lập trình phổ biến được sử dụng rộng rãi trong phát triển web. Với cú pháp đơn giản và khả năng tích hợp mạnh mẽ với HTML, PHP đã trở thành lựa chọn hàng đầu cho nhiều nhà phát triển web.

## Tại sao nên học PHP?

1. **Dễ học**: Cú pháp PHP tương tự như C và Java, dễ hiểu cho người mới bắt đầu
2. **Phổ biến**: Được sử dụng bởi hơn 80% các website trên thế giới
3. **Cộng đồng lớn**: Có nhiều tài liệu, tutorial và hỗ trợ từ cộng đồng
4. **Framework mạnh mẽ**: Laravel, Symfony, CodeIgniter...

## Cài đặt môi trường

### XAMPP
XAMPP là gói phần mềm bao gồm Apache, MySQL, PHP và phpMyAdmin, rất phù hợp cho người mới bắt đầu.

### Composer
Composer là công cụ quản lý dependencies cho PHP, tương tự như npm trong Node.js.

## Cú pháp cơ bản

```php
<?php
echo "Hello, World!";
?>
```

## Kết luận

PHP là một ngôn ngữ lập trình tuyệt vời để bắt đầu với web development. Với sự đơn giản và mạnh mẽ, PHP sẽ giúp bạn xây dựng những ứng dụng web chất lượng.',
    'Hướng dẫn chi tiết về lập trình PHP từ những khái niệm cơ bản nhất đến các kỹ thuật nâng cao, phù hợp cho cả người mới bắt đầu và developer có kinh nghiệm.',
    'img-share.jpg',
    'Học lập trình PHP từ cơ bản đến nâng cao - Hướng dẫn chi tiết với ví dụ thực tế và best practices',
    'php, lập trình, web development, hướng dẫn, tutorial',
    'published',
    150,
    1,
    NOW()
),
(
    @user2, 
    @cat_business, 
    'Bí quyết khởi nghiệp thành công trong thời đại số', 
    'bi-quyet-khoi-nghiep-thanh-cong-trong-thoi-dai-so',
    '# Khởi nghiệp trong thời đại số

Thời đại số đã mở ra vô số cơ hội cho các startup, nhưng cũng tạo ra những thách thức mới. Để thành công, các nhà khởi nghiệp cần nắm vững những nguyên tắc cơ bản và áp dụng công nghệ một cách hiệu quả.

## Xác định vấn đề và giải pháp

### 1. Tìm hiểu thị trường
- Nghiên cứu đối thủ cạnh tranh
- Phân tích nhu cầu khách hàng
- Xác định khoảng trống thị trường

### 2. Xây dựng MVP (Minimum Viable Product)
- Tập trung vào tính năng cốt lõi
- Phát triển nhanh và test thị trường
- Thu thập feedback từ người dùng

## Tận dụng công nghệ

### Digital Marketing
- SEO/SEM
- Social Media Marketing
- Content Marketing
- Email Marketing

### Công nghệ hỗ trợ
- Cloud Computing
- AI và Machine Learning
- Mobile-first approach
- API và Integration

## Quản lý tài chính

1. **Lập kế hoạch tài chính chi tiết**
2. **Tìm nguồn vốn phù hợp**
3. **Quản lý cash flow hiệu quả**
4. **Đo lường và tối ưu ROI**

## Xây dựng đội ngũ

- Tuyển dụng nhân tài phù hợp
- Tạo văn hóa công ty tích cực
- Đầu tư vào đào tạo và phát triển
- Xây dựng hệ thống quản lý hiệu quả

## Kết luận

Khởi nghiệp trong thời đại số đòi hỏi sự kết hợp giữa tầm nhìn chiến lược, kỹ năng công nghệ và khả năng thích ứng nhanh với thay đổi. Hãy bắt đầu với những bước nhỏ, học hỏi liên tục và không ngừng đổi mới.',
    'Khám phá những bí quyết vàng để khởi nghiệp thành công trong thời đại số, từ việc xác định ý tưởng đến xây dựng doanh nghiệp bền vững.',
    'img-share.jpg',
    'Bí quyết khởi nghiệp thành công trong thời đại số - Hướng dẫn từ A-Z cho startup',
    'khởi nghiệp, startup, kinh doanh, digital, công nghệ',
    'published',
    89,
    1,
    NOW()
),
(
    @user3, 
    @cat_lifestyle, 
    '10 thói quen lành mạnh giúp nâng cao chất lượng cuộc sống', 
    '10-thoi-quen-lanh-manh-giup-nang-cao-chat-luong-cuoc-song',
    '# Thói quen lành mạnh cho cuộc sống tốt đẹp hơn

Cuộc sống hiện đại với nhịp độ nhanh và áp lực cao đòi hỏi chúng ta phải có những thói quen lành mạnh để duy trì sức khỏe thể chất và tinh thần.

## 1. Ngủ đủ giấc (7-8 tiếng/đêm)

Giấc ngủ chất lượng là nền tảng của sức khỏe:
- Tăng cường hệ miễn dịch
- Cải thiện trí nhớ và khả năng tập trung
- Giảm stress và lo âu
- Tăng cường sức khỏe tim mạch

## 2. Uống đủ nước (2-3 lít/ngày)

Nước chiếm 60-70% trọng lượng cơ thể:
- Hỗ trợ tiêu hóa
- Làm đẹp da
- Tăng cường trao đổi chất
- Giảm mệt mỏi

## 3. Tập thể dục thường xuyên

Ít nhất 30 phút/ngày, 5 ngày/tuần:
- Tăng cường sức mạnh cơ bắp
- Cải thiện sức khỏe tim mạch
- Giảm nguy cơ bệnh tật
- Tăng cường tinh thần

## 4. Ăn uống cân bằng

- Nhiều rau xanh và trái cây
- Protein từ thịt nạc, cá, đậu
- Carbohydrate phức hợp
- Hạn chế đường và chất béo bão hòa

## 5. Thiền định và mindfulness

- Giảm stress và lo âu
- Tăng cường khả năng tập trung
- Cải thiện giấc ngủ
- Tăng cường sự tự nhận thức

## 6. Đọc sách thường xuyên

- Mở rộng kiến thức
- Cải thiện vốn từ vựng
- Giảm stress
- Tăng cường khả năng tư duy

## 7. Dành thời gian cho gia đình và bạn bè

- Tăng cường mối quan hệ
- Giảm cảm giác cô đơn
- Tăng cường hạnh phúc
- Hỗ trợ tinh thần

## 8. Học hỏi liên tục

- Phát triển kỹ năng mới
- Tăng cường sự tự tin
- Mở rộng cơ hội nghề nghiệp
- Giữ cho não bộ hoạt động

## 9. Quản lý thời gian hiệu quả

- Lập kế hoạch hàng ngày
- Ưu tiên công việc quan trọng
- Tránh trì hoãn
- Cân bằng công việc và cuộc sống

## 10. Thực hành lòng biết ơn

- Viết nhật ký biết ơn
- Tập trung vào điều tích cực
- Tăng cường hạnh phúc
- Cải thiện mối quan hệ

## Kết luận

Những thói quen lành mạnh này không chỉ cải thiện sức khỏe thể chất mà còn nâng cao chất lượng cuộc sống tổng thể. Hãy bắt đầu từ những thay đổi nhỏ và kiên trì thực hiện để tạo ra sự khác biệt tích cực trong cuộc sống của bạn.',
    'Khám phá 10 thói quen lành mạnh đã được khoa học chứng minh giúp nâng cao chất lượng cuộc sống, từ sức khỏe thể chất đến tinh thần.',
    'img-share.jpg',
    '10 thói quen lành mạnh giúp nâng cao chất lượng cuộc sống - Hướng dẫn chi tiết',
    'sức khỏe, lối sống, thói quen, wellness, healthy living',
    'published',
    67,
    0,
    NOW()
),
(
    @user4, 
    @cat_education, 
    'Phương pháp học tập hiệu quả cho thời đại số', 
    'phuong-phap-hoc-tap-hieu-qua-cho-thoi-dai-so',
    '# Học tập trong thời đại số

Thời đại số đã thay đổi cách chúng ta học tập và tiếp thu kiến thức. Với sự phát triển của công nghệ, việc học tập trở nên linh hoạt và hiệu quả hơn bao giờ hết.

## Xu hướng học tập hiện đại

### 1. E-learning và Online Learning
- Học mọi lúc, mọi nơi
- Tiết kiệm chi phí và thời gian
- Nội dung đa dạng và phong phú
- Tương tác trực tuyến

### 2. Microlearning
- Học từng phần nhỏ
- Dễ tiếp thu và ghi nhớ
- Phù hợp với lịch trình bận rộn
- Tăng cường retention

### 3. Gamification
- Biến việc học thành trò chơi
- Tăng động lực học tập
- Cạnh tranh lành mạnh
- Phản hồi tức thì

## Công cụ học tập hiệu quả

### 1. Spaced Repetition
- Ôn tập theo khoảng cách thời gian
- Tăng cường trí nhớ dài hạn
- Sử dụng flashcards
- Ứng dụng như Anki, Quizlet

### 2. Active Recall
- Tự kiểm tra kiến thức
- Không chỉ đọc mà còn suy nghĩ
- Tạo câu hỏi cho chính mình
- Giải thích cho người khác

### 3. Interleaving
- Học xen kẽ các chủ đề
- Tránh học thuộc lòng
- Tăng khả năng áp dụng
- Phát triển tư duy linh hoạt

## Kỹ năng cần thiết

### 1. Digital Literacy
- Sử dụng công nghệ hiệu quả
- Tìm kiếm thông tin chính xác
- Đánh giá nguồn tin
- Bảo mật thông tin

### 2. Critical Thinking
- Phân tích và đánh giá
- Tư duy phản biện
- Giải quyết vấn đề
- Ra quyết định sáng suốt

### 3. Collaboration
- Làm việc nhóm trực tuyến
- Giao tiếp hiệu quả
- Chia sẻ kiến thức
- Học hỏi từ người khác

## Chiến lược học tập

### 1. SMART Goals
- Specific (Cụ thể)
- Measurable (Đo lường được)
- Achievable (Khả thi)
- Relevant (Liên quan)
- Time-bound (Có thời hạn)

### 2. Pomodoro Technique
- Học 25 phút, nghỉ 5 phút
- Tăng cường tập trung
- Tránh burnout
- Quản lý thời gian hiệu quả

### 3. Feynman Technique
- Giải thích như cho trẻ em
- Tìm hiểu sâu hơn
- Xác định lỗ hổng kiến thức
- Đơn giản hóa phức tạp

## Kết luận

Học tập trong thời đại số đòi hỏi chúng ta phải thích ứng và áp dụng những phương pháp mới. Bằng cách kết hợp công nghệ với các kỹ thuật học tập đã được chứng minh, chúng ta có thể tối ưu hóa quá trình học tập và đạt được kết quả tốt nhất.',
    'Khám phá những phương pháp học tập hiệu quả nhất trong thời đại số, từ e-learning đến gamification và các kỹ thuật tăng cường trí nhớ.',
    'img-share.jpg',
    'Phương pháp học tập hiệu quả cho thời đại số - Hướng dẫn toàn diện',
    'học tập, education, e-learning, kỹ năng, phương pháp',
    'published',
    45,
    0,
    NOW()
);

-- Lấy ID của các bài viết vừa tạo
SET @article1 = (SELECT id FROM articles WHERE slug = 'huong-dan-lap-trinh-php-tu-co-ban-den-nang-cao' LIMIT 1);
SET @article2 = (SELECT id FROM articles WHERE slug = 'bi-quyet-khoi-nghiep-thanh-cong-trong-thoi-dai-so' LIMIT 1);
SET @article3 = (SELECT id FROM articles WHERE slug = '10-thoi-quen-lanh-manh-giup-nang-cao-chat-luong-cuoc-song' LIMIT 1);
SET @article4 = (SELECT id FROM articles WHERE slug = 'phuong-phap-hoc-tap-hieu-qua-cho-thoi-dai-so' LIMIT 1);

-- Lấy ID của các tags
SET @tag_php = (SELECT id FROM tags WHERE slug = 'php' LIMIT 1);
SET @tag_web_dev = (SELECT id FROM tags WHERE slug = 'web-development' LIMIT 1);
SET @tag_startup = (SELECT id FROM tags WHERE slug = 'startup' LIMIT 1);
SET @tag_marketing = (SELECT id FROM tags WHERE slug = 'marketing' LIMIT 1);
SET @tag_health = (SELECT id FROM tags WHERE slug = 'health' LIMIT 1);
SET @tag_productivity = (SELECT id FROM tags WHERE slug = 'productivity' LIMIT 1);
SET @tag_education = (SELECT id FROM tags WHERE slug = 'education' LIMIT 1);
SET @tag_ai = (SELECT id FROM tags WHERE slug = 'ai' LIMIT 1);

-- Gán tags cho các bài viết
INSERT IGNORE INTO article_tags (article_id, tag_id) VALUES
-- Bài viết 1: PHP
(@article1, @tag_php),
(@article1, @tag_web_dev),
-- Bài viết 2: Startup
(@article2, @tag_startup),
(@article2, @tag_marketing),
-- Bài viết 3: Sức khỏe
(@article3, @tag_health),
(@article3, @tag_productivity),
-- Bài viết 4: Giáo dục
(@article4, @tag_education),
(@article4, @tag_ai);

-- Tạo một số comments mẫu
INSERT IGNORE INTO comments (article_id, user_id, content, likes, created_at) VALUES
(@article1, @user2, 'Bài viết rất hay và chi tiết! Cảm ơn tác giả đã chia sẻ.', 5, NOW()),
(@article1, @user3, 'Tôi đã học được nhiều điều bổ ích từ bài viết này.', 3, NOW()),
(@article2, @user1, 'Những lời khuyên rất thực tế và hữu ích cho startup.', 7, NOW()),
(@article3, @user4, 'Tôi sẽ áp dụng những thói quen này vào cuộc sống hàng ngày.', 4, NOW());

SELECT 'Dữ liệu bài viết mẫu đã được tạo thành công!' AS status;
