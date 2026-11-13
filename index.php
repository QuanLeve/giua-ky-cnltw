<?php
require __DIR__ . '/config.php';
require __DIR__ . '/layout.php';
$q = trim((string)($_GET['q'] ?? ''));
$stmt = $pdo->prepare('SELECT admin_id, admin_email, images FROM Users WHERE admin_email LIKE ? ORDER BY admin_id DESC');
$stmt->execute(['%' . $q . '%']);
$users = $stmt->fetchAll();
$summary = $pdo->query("SELECT COUNT(*) AS total, COALESCE(SUM(images <> ''), 0) AS photos FROM Users")->fetch();
pageStart('Danh sách người dùng');
?>
<section class="heading"><h1>Người dùng</h1><a class="button primary-action" data-create-user href="form.php"><?= icon('plus') ?>Thêm người dùng</a></section>
<section class="stats" aria-label="Thống kê người dùng"><article><span class="stat-icon"><?= icon('users') ?></span><div><span>Tổng người dùng</span><strong><?= (int)$summary['total'] ?><small>thành viên</small></strong></div></article><article><span class="stat-icon neutral"><?= icon('image') ?></span><div><span>Đã có ảnh đại diện</span><strong><?= (int)$summary['photos'] ?><small>thành viên</small></strong></div></article><article><span class="stat-icon neutral"><?= icon('edit') ?></span><div><span>Chưa có ảnh đại diện</span><strong><?= (int)$summary['total'] - (int)$summary['photos'] ?><small>thành viên</small></strong></div></article></section>
<section class="panel"><div class="panel-head"><div class="section-title"><h2>Tất cả thành viên</h2><span class="count-badge"><?= count($users) ?></span></div><form class="search" method="get" data-search-form><label class="sr-only" for="q">Tìm theo email</label><?= icon('search') ?><input id="q" name="q" type="search" placeholder="Tìm kiếm theo email…" value="<?= e($q) ?>"><button type="submit">Tìm</button></form></div><?php if ($q !== ''): ?><div class="filter-summary">Kết quả cho “<?= e($q) ?>”<a href="index.php">Xóa bộ lọc <?= icon('close') ?></a></div><?php endif; ?>
<?php if ($users) userTable($users); else emptyUsers($q !== ''); ?>
</section>
<?php pageEnd(); ?>
