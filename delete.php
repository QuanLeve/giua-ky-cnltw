<?php
require __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); header('Allow: POST'); exit('Chỉ chấp nhận POST.'); }
verifyToken();
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(400); exit('ID không hợp lệ.'); }
$stmt = $pdo->prepare('SELECT images FROM Users WHERE admin_id=?');
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { http_response_code(404); exit('Không tìm thấy người dùng.'); }
$stmt = $pdo->prepare('DELETE FROM Users WHERE admin_id=?');
$stmt->execute([$id]);
removeImage($user['images']);
flash('Đã xóa người dùng.');
redirect('admin.php');
