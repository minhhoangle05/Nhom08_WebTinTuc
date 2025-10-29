# 🎉 Hướng dẫn sử dụng chức năng đăng bài đơn giản

## ✅ Đã hoàn thành

### 1. Database đã được đơn giản hóa
- **Script:** `scripts/simplify_articles.sql`
- **Loại bỏ:** status, draft system, meta fields phức tạp
- **Giữ lại:** Các trường cần thiết cho bài viết

### 2. Code đã được tối ưu
- **ArticleController:** Đơn giản hóa logic, loại bỏ draft system
- **Article Model:** Loại bỏ status constants và methods không cần thiết
- **Form:** Giao diện đơn giản, dễ sử dụng

### 3. Files đã tạo/sửa
- ✅ `app/views/articles/create_simple.php` - Form đơn giản
- ✅ `scripts/simplify_articles.sql` - Script cập nhật database
- ✅ `test_simple_article.html` - Trang test
- ✅ `test_classes.php` - Script kiểm tra classes

## 🚀 Cách sử dụng

### Bước 1: Cập nhật Database
```bash
mysql -u root -p article_portal < scripts/simplify_articles.sql
```

### Bước 2: Truy cập trang tạo bài viết
- **URL chính:** `http://localhost/Web-Project/public/articles/create`
- **Trang test:** `test_simple_article.html`

### Bước 3: Tạo bài viết
1. Điền **tiêu đề** (bắt buộc)
2. **Slug** sẽ tự động tạo từ tiêu đề
3. Điền **nội dung** (bắt buộc)
4. Chọn **chuyên mục** và **tags** (tùy chọn)
5. Nhấn **"Tạo bài viết"**

## 📋 Cấu trúc Database mới

### Bảng `articles`:
```sql
- id (INT, PRIMARY KEY)
- title (VARCHAR(200))
- slug (VARCHAR(200), UNIQUE)
- content (MEDIUMTEXT)
- summary (TEXT)
- featured_image (VARCHAR(255))
- user_id (INT, FOREIGN KEY)
- category_id (INT, FOREIGN KEY)
- views (INT, DEFAULT 0)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### Bảng `article_tags`:
```sql
- article_id (INT)
- tag_id (INT)
- PRIMARY KEY (article_id, tag_id)
```

## 🎯 Tính năng đã loại bỏ

- ❌ Draft system
- ❌ Publish/Unpublish
- ❌ Status field
- ❌ Meta description/keywords
- ❌ Reading time
- ❌ Allow comments
- ❌ Featured flag

## ✅ Tính năng còn lại

- ✅ Tạo bài viết đơn giản
- ✅ Quản lý categories và tags
- ✅ Upload ảnh đại diện
- ✅ Tự động tạo slug
- ✅ Validation cơ bản
- ✅ Error handling
- ✅ Giữ dữ liệu khi có lỗi

## 🔧 Troubleshooting

### Lỗi "Class Session not found"
- ✅ **Đã sửa:** Thêm `use App\Core\Session;` vào ArticleController

### Lỗi database
- Chạy script: `scripts/simplify_articles.sql`
- Kiểm tra kết nối database trong `config/config.php`

### Lỗi routing
- Kiểm tra file `public/index.php`
- Đảm bảo route `/articles/create` đã được định nghĩa

## 📞 Test nhanh

Chạy script test:
```bash
php test_classes.php
```

Nếu tất cả classes load thành công, bạn có thể bắt đầu sử dụng chức năng đăng bài!

---

**🎉 Chức năng đăng bài đã được đơn giản hóa hoàn toàn và sẵn sàng sử dụng!**
