<?php
require __DIR__ . '/config.php';
require __DIR__ . '/layout.php';
$users = $pdo->query('SELECT admin_id, admin_email, images FROM Users ORDER BY admin_id DESC')->fetchAll();
pageStart('Quản trị người dùng', true);
?>
<section class="heading"><h1>Quản trị người dùng</h1><a class="button primary-action" data-create-user href="form.php"><?= icon('plus') ?>Thêm người dùng</a></section><section class="panel"><div class="panel-head"><div class="section-title"><h2>Thành viên</h2><span class="count-badge"><?= count($users) ?></span></div></div><?php if ($users) userTable($users, true); else emptyUsers(); ?></section>
<?php pageEnd(); ?>
