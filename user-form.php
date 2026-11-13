<?php
function userForm(array $user, int $id = 0, string $error = '', bool $fragment = false): void { ?>
<div class="form-heading"><h<?= $fragment ? '2 id="dialog-title"' : '1' ?>><?= $id ? 'Chỉnh sửa người dùng' : 'Thêm người dùng' ?></h<?= $fragment ? '2' : '1' ?>></div>
<form class="user-form" method="post" action="form.php<?= $id ? '?id=' . e($id) : '' ?>" enctype="multipart/form-data" data-user-form>
<input type="hidden" name="csrf" value="<?= e(token()) ?>">
<div class="form-error <?= $error ? '' : 'hidden' ?>" role="alert" tabindex="-1" data-form-error><?= e($error) ?></div>
<div class="field"><label for="<?= $fragment ? 'quick-' : '' ?>email">Địa chỉ email <span>*</span></label><input id="<?= $fragment ? 'quick-' : '' ?>email" type="email" name="email" maxlength="255" required autocomplete="email" placeholder="thanhvien@example.com" value="<?= e($user['admin_email']) ?>"></div>
<div class="field"><label for="<?= $fragment ? 'quick-' : '' ?>password">Mật khẩu <?= !$id ? '<span>*</span>' : '' ?></label><input id="<?= $fragment ? 'quick-' : '' ?>password" type="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" <?= !$id ? 'required' : '' ?> placeholder="<?= $id ? 'Để trống nếu không thay đổi' : 'Ít nhất 8 ký tự' ?>"><?php if (!$id): ?><small>8–72 byte</small><?php endif; ?></div>
<div class="field"><label for="<?= $fragment ? 'quick-' : '' ?>image">Ảnh đại diện <span class="optional">Không bắt buộc</span></label><div class="upload"><span class="upload-preview" data-image-preview><?php if ($user['images']): ?><img src="<?= e($user['images']) ?>" alt="Ảnh đại diện hiện tại"><?php else: ?><?= icon('image') ?><?php endif; ?></span><div><input id="<?= $fragment ? 'quick-' : '' ?>image" type="file" name="image" accept="image/jpeg,image/png,image/webp" data-image-input><small>JPG, PNG hoặc WebP · tối đa 2 MB</small></div></div></div>
<div class="form-actions"><?php if ($fragment): ?><button class="button secondary" type="button" data-close-dialog>Hủy</button><?php else: ?><a class="button secondary" href="admin.php">Hủy</a><?php endif; ?><button class="button submit-button" type="submit"><span class="button-icon"><?= icon($id ? 'check' : 'plus') ?></span><span data-submit-label><?= $id ? 'Lưu thay đổi' : 'Thêm người dùng' ?></span><span class="spinner" aria-hidden="true"></span></button></div>
<span class="sr-only" role="status" aria-live="polite" data-form-status></span>
</form>
<?php }
