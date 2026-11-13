<?php
require __DIR__ . '/config.php';
require __DIR__ . '/layout.php';
require __DIR__ . '/user-form.php';
$wantsJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
if ($id === false || $id < 0) abortPage(400, 'Đường dẫn không hợp lệ', 'Mã người dùng không hợp lệ. Vui lòng chọn lại từ danh sách.');
$user = ['admin_email' => '', 'images' => ''];
if ($id) {
    $stmt = $pdo->prepare('SELECT admin_id, admin_email, images FROM Users WHERE admin_id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) abortPage(404, 'Không tìm thấy người dùng', 'Thành viên này có thể đã được xóa. Vui lòng kiểm tra lại danh sách.');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyToken();
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $newImage = null;
    try {
        if (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Vui lòng nhập email hợp lệ, tối đa 255 ký tự.');
        if ((!$id || $password !== '') && (strlen($password) < 8 || strlen($password) > 72)) throw new RuntimeException('Mật khẩu cần từ 8 đến 72 byte (nên dùng chữ, số và ký tự đặc biệt).');
        $newImage = uploadImage();
        $image = $newImage ?? $user['images'];
        if ($id) {
            if ($password !== '') {
                $stmt = $pdo->prepare('UPDATE Users SET admin_email=?, admin_password=?, images=? WHERE admin_id=?');
                $stmt->execute([$email, password_hash($password, PASSWORD_BCRYPT), $image, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE Users SET admin_email=?, images=? WHERE admin_id=?');
                $stmt->execute([$email, $image, $id]);
            }
        } else {
            $stmt = $pdo->prepare('INSERT INTO Users (admin_email, admin_password, images) VALUES (?, ?, ?)');
            $stmt->execute([$email, password_hash($password, PASSWORD_BCRYPT), $image]);
        }
        if ($newImage) removeImage($user['images']);
        flash($id ? 'Đã cập nhật người dùng.' : 'Đã thêm người dùng thành công.');
        if ($wantsJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => true, 'message' => $id ? 'Đã lưu thay đổi' : 'Đã thêm người dùng', 'redirect' => 'admin.php'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        redirect('admin.php');
    } catch (Throwable $e) {
        if ($newImage) removeImage($newImage);
        $error = $e instanceof PDOException ? ($e->getCode() === '23000' ? 'Email đã tồn tại. Vui lòng dùng email khác.' : 'Không lưu được dữ liệu. Vui lòng thử lại.') : $e->getMessage();
    }
    $user['admin_email'] = $email;
    if ($wantsJson) {
        http_response_code(422);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => $error], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
if (isset($_GET['fragment']) && !$id) {
    userForm($user, 0, '', true);
    exit;
}
pageStart($id ? 'Sửa người dùng' : 'Thêm người dùng', true);
?>
<a class="back" href="admin.php"><?= icon('back') ?>Trở về quản trị</a><section class="form-page"><?php userForm($user, (int)$id, $error); ?></section>
<?php pageEnd(); ?>
