<?php
function icon(string $name, string $class = ''): string {
    $paths = [
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/><circle cx="9" cy="7" r="4"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="7.5"/><path d="m16 16 5 5"/>',
        'arrow' => '<path d="M5 12h14m-6-6 6 6-6 6"/>',
        'back' => '<path d="M19 12H5m6-6-6 6 6 6"/>',
        'close' => '<path d="m6 6 12 12M6 18 18 6"/>',
        'image' => '<rect x="3" y="3" width="18" height="18" rx="4"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>',
        'edit' => '<path d="m16 3 5 5M4 20l4-1L21 6a2.1 2.1 0 0 0-3-3L5 16l-1 4Z"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'shield' => '<path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6l8-3Z"/><path d="m8 12 3 3 5-6"/>',
    ];
    return '<svg class="icon ' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['users']) . '</svg>';
}
function pageStart(string $title, bool $admin = false): void { ?>
<!doctype html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#151517"><title><?= e($title) ?> · QLNhânSự</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;650;700;750&display=swap" rel="stylesheet"><link rel="stylesheet" href="assets/style.css?v=5"><script defer src="assets/app.js?v=4"></script></head>
<body><a class="skip-link" href="#main">Đến nội dung chính</a><aside class="sidebar"><a class="brand" href="index.php"><span class="brand-mark"><?= icon('users') ?></span><span>QLNhânSự</span></a><nav aria-label="Điều hướng chính"><a <?= !$admin ? 'aria-current="page"' : '' ?> href="index.php"><?= icon('users') ?>Danh sách</a><a <?= $admin ? 'aria-current="page"' : '' ?> href="admin.php"><?= icon('grid') ?>Quản trị</a></nav></aside><div class="app-shell"><main id="main">
<?php if (isset($_SESSION['flash'])): ?><div class="notice" role="status"><span class="success-check"><?= icon('check') ?></span><?= e($_SESSION['flash']) ?></div><?php unset($_SESSION['flash']); endif;
}
function pageEnd(): void { ?>
</main></div><dialog id="user-dialog" aria-labelledby="dialog-title"><div class="dialog-top"><button class="icon-button" type="button" data-close-dialog aria-label="Đóng biểu mẫu"><?= icon('close') ?></button></div><div id="dialog-content"></div></dialog></body></html>
<?php }
function avatar(array $user): void {
    if ($user['images'] !== ''): ?><img class="avatar" src="<?= e($user['images']) ?>" alt="Ảnh đại diện" loading="lazy"><?php
    else: ?><span class="avatar fallback" aria-hidden="true"><?= e(strtoupper(substr($user['admin_email'], 0, 1))) ?></span><?php endif;
}
function userTable(array $users, bool $admin = false): void { ?>
<div class="table-wrap"><table class="user-table <?= $admin ? 'admin-table' : '' ?>"><thead><tr><th>Thành viên</th><th>Mã người dùng</th><th>Ảnh đại diện</th><?php if ($admin): ?><th class="align-right">Thao tác</th><?php endif; ?></tr></thead><tbody><?php foreach ($users as $user): ?><tr><td class="member-cell"><div class="member"><?php avatar($user); ?><div><strong><?= e($user['admin_email']) ?></strong></div></div></td><td class="id-cell"><span class="user-id">#<?= str_pad((string)$user['admin_id'], 3, '0', STR_PAD_LEFT) ?></span></td><td class="photo-cell"><span class="status <?= $user['images'] ? 'has-photo' : '' ?>"><span></span><?= $user['images'] ? 'Đã cập nhật ảnh' : 'Chưa có ảnh' ?></span></td><?php if ($admin): ?><td class="action-cell"><div class="actions"><a class="button secondary small" href="form.php?id=<?= e($user['admin_id']) ?>"><?= icon('edit') ?>Sửa</a><form method="post" action="delete.php" data-delete-form onsubmit="return confirm('Bạn có chắc muốn xóa người dùng này?');"><input type="hidden" name="csrf" value="<?= e(token()) ?>"><input type="hidden" name="id" value="<?= e($user['admin_id']) ?>"><button class="danger small" type="submit">Xóa</button></form></div></td><?php endif; ?></tr><?php endforeach; ?></tbody></table></div>
<?php }
function emptyUsers(bool $search = false): void { ?>
<div class="empty"><span class="empty-icon"><?= icon($search ? 'search' : 'users') ?></span><h3><?= $search ? 'Chưa tìm thấy thành viên' : 'Bắt đầu với thành viên đầu tiên' ?></h3><a class="button <?= $search ? 'secondary' : '' ?>" <?= !$search ? 'data-create-user' : '' ?> href="<?= $search ? 'index.php' : 'form.php' ?>"><?= icon($search ? 'back' : 'plus') ?><?= $search ? 'Xem tất cả' : 'Thêm người dùng' ?></a></div>
<?php }
