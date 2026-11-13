<?php
declare(strict_types=1);
$sessionDirectory = __DIR__ . '/var/sessions';
if (!is_dir($sessionDirectory) && !mkdir($sessionDirectory, 0700, true)) {
    http_response_code(500);
    exit('Không tạo được thư mục session. Hãy cấp quyền ghi cho thư mục dự án.');
}
session_save_path($sessionDirectory);
session_start();

function abortPage(int $status, string $title, string $message): void {
    http_response_code($status);
    if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
    } else {
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        echo '<!doctype html><html lang="vi"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . $title . '</title><link rel="stylesheet" href="assets/style.css?v=4"><body><main class="service-error"><span class="eyebrow">KHÔNG GIAN NHÂN SỰ</span><h1>' . $title . '</h1><p>' . $message . '</p><a class="button" href="index.php">Trở về danh sách</a></main></body></html>';
    }
    exit;
}

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=QLNhanSu;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    abortPage(503, 'Chưa tải được dữ liệu', 'Không kết nối được MySQL. Hãy bật MySQL và kiểm tra kết nối rồi tải lại trang.');
}

function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verifyToken(): void {
    if (!hash_equals(token(), (string)($_POST['csrf'] ?? ''))) {
        abortPage(403, 'Phiên biểu mẫu đã hết hạn', 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng tải lại trang rồi thử lại.');
    }
}
function flash(string $message): void { $_SESSION['flash'] = $message; }
function redirect(string $url): void { header('Location: ' . $url); exit; }
function removeImage(string $path): void {
    if (preg_match('~^uploads/[a-f0-9]{32}\.(jpg|png|webp)$~', $path) && is_file(__DIR__ . '/' . $path)) {
        unlink(__DIR__ . '/' . $path);
    }
}
function uploadImage(): ?string {
    $file = $_FILES['image'] ?? null;
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Tải ảnh thất bại. Vui lòng thử lại.');
    if ($file['size'] > 2 * 1024 * 1024) throw new RuntimeException('Ảnh tối đa 2 MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime]) || !getimagesize($file['tmp_name'])) throw new RuntimeException('Chỉ chấp nhận ảnh JPG, PNG hoặc WebP.');
    $directory = __DIR__ . '/uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) throw new RuntimeException('Không tạo được thư mục ảnh.');
    $path = 'uploads/' . bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $path)) throw new RuntimeException('Không lưu được ảnh.');
    return $path;
}
