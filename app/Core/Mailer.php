<?php
namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private static ?PHPMailer $mailer = null;

    /**
     * Get PHPMailer instance with default configuration
     */
    public static function getMailer(): PHPMailer
    {
        if (self::$mailer === null) {
            self::$mailer = new PHPMailer(true);
            
            // Server settings
            self::$mailer->isSMTP();
            self::$mailer->Host = SMTP_HOST;
            self::$mailer->SMTPAuth = true;
            self::$mailer->Username = SMTP_USERNAME;
            self::$mailer->Password = SMTP_PASSWORD;
            self::$mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            self::$mailer->Port = SMTP_PORT;
            
            // Default settings
            self::$mailer->isHTML(true);
            self::$mailer->CharSet = 'UTF-8';
            self::$mailer->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        }

        return self::$mailer;
    }

    /**
     * Send password reset email
     */
    public static function sendPasswordReset(string $to, string $resetLink): bool
    {
        try {
            error_log("Đang gửi email đặt lại mật khẩu đến: {$to}");
            $mailer = self::getMailer();
            
            // Xóa tất cả địa chỉ cũ
            $mailer->clearAddresses();
            
            error_log("Thêm địa chỉ người nhận: {$to}");
            $mailer->addAddress($to);
            $mailer->Subject = 'Đặt lại mật khẩu - ArticleHub';
            
            error_log("Cấu hình SMTP:");
            error_log("Máy chủ: " . SMTP_HOST);
            error_log("Tài khoản: " . SMTP_USERNAME);
            error_log("Cổng: " . SMTP_PORT);
            
            // HTML email content
            $body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;">
                <h2 style="color: #333; margin-bottom: 20px;">Đặt lại mật khẩu</h2>
                <p style="color: #666; line-height: 1.6;">Xin chào,</p>
                <p style="color: #666; line-height: 1.6;">Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn tại ArticleHub. Vui lòng nhấn vào nút bên dưới để đặt lại mật khẩu:</p>
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . htmlspecialchars($resetLink) . '" style="background-color: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Đặt lại mật khẩu</a>
                </div>
                <p style="color: #666; line-height: 1.6;">Hoặc sao chép và dán liên kết sau vào trình duyệt của bạn:</p>
                <p style="background-color: #f8f8f8; padding: 10px; border-radius: 3px; word-break: break-all;">' . htmlspecialchars($resetLink) . '</p>
                <p style="color: #666; line-height: 1.6;">Liên kết này sẽ hết hạn sau 1 giờ. Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
                <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
                <p style="color: #999; font-size: 12px;">Email này được gửi tự động, vui lòng không trả lời.</p>
            </div>';
            
            $mailer->Body = $body;
            $mailer->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $body));
            
            try {
                if ($mailer->send()) {
                    error_log("Đã gửi email thành công đến: {$to}");
                    return true;
                } else {
                    error_log("Không thể gửi email đến: {$to}");
                    return false;
                }
            } catch (Exception $e) {
                error_log("Lỗi khi gửi email đến {$to}: " . $e->getMessage());
                return false;
            };
            $mailer->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $body));
            
            return $mailer->send();
            
        } catch (Exception $e) {
            error_log("Error sending password reset email: " . $e->getMessage());
            return false;
        }
    }
}