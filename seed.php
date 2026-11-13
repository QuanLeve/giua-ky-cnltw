<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Chỉ chạy bằng dòng lệnh.');
}
require __DIR__ . '/config.php';
$examples = [
    'an.nguyen@example.com',
    'binh.tran@example.com',
    'chi.le@example.com',
];
$check = $pdo->prepare('SELECT COUNT(*) FROM Users WHERE admin_email = ?');
$insert = $pdo->prepare('INSERT INTO Users (admin_email, admin_password, images) VALUES (?, ?, ?)');
foreach ($examples as $email) {
    $check->execute([$email]);
    if ((int)$check->fetchColumn() === 0) {
        $insert->execute([$email, password_hash('Demo@12345', PASSWORD_BCRYPT), '']);
        echo "Đã thêm $email\n";
    }
}
