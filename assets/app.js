(() => {
  'use strict';
  const dialog = document.getElementById('user-dialog');
  const content = document.getElementById('dialog-content');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let opener = null;
  let loading = false;
  let objectUrl = null;

  function closeDialog() {
    if (dialog.querySelector('[aria-busy="true"]')) return;
    dialog.classList.add('closing');
    setTimeout(() => {
      dialog.close();
      dialog.classList.remove('closing');
      document.body.classList.remove('dialog-open');
      if (objectUrl) URL.revokeObjectURL(objectUrl);
      opener?.focus();
    }, reduceMotion.matches ? 0 : 180);
  }

  document.addEventListener('click', async (event) => {
    const create = event.target.closest('[data-create-user]');
    if (create && dialog && typeof dialog.showModal === 'function') {
      event.preventDefault();
      if (loading) return;
      opener = create;
      const rect = create.getBoundingClientRect();
      dialog.classList.remove('entered');
      dialog.style.setProperty('--desktop-top', `${Math.max(24, Math.min(rect.bottom + 12, window.innerHeight - 650))}px`);
      dialog.style.setProperty('--desktop-right', `${Math.max(24, window.innerWidth - rect.right)}px`);
      content.innerHTML = '<h2 id="dialog-title">Thêm người dùng</h2><div class="dialog-loading" role="status"><span class="spinner"></span>Đang tải biểu mẫu…</div>';
      dialog.showModal();
      document.body.classList.add('dialog-open');
      loading = true;
      try {
        const response = await fetch('form.php?fragment=1', { headers: { 'X-Requested-With': 'fetch' } });
        if (!response.ok) throw new Error('Không tải được biểu mẫu.');
        content.innerHTML = await response.text();
        content.querySelector('input[type="email"]')?.focus({ preventScroll: true });
      } catch {
        content.innerHTML = '<h2 id="dialog-title">Chưa tải được biểu mẫu</h2><p class="dialog-error" role="alert">Vui lòng kiểm tra kết nối rồi thử lại.</p><a class="button" href="form.php">Mở trang thêm người dùng</a>';
      } finally { loading = false; }
    }
    if (event.target.closest('[data-close-dialog]')) closeDialog();
  });
  dialog?.addEventListener('cancel', (event) => { event.preventDefault(); closeDialog(); });
  dialog?.addEventListener('animationend', (event) => {
    if (event.target === dialog && ['dialog-in', 'sheet-in'].includes(event.animationName)) dialog.classList.add('entered');
  });
  dialog?.addEventListener('click', (event) => {
    if (event.target !== dialog) return;
    const r = dialog.getBoundingClientRect();
    if (event.clientX < r.left || event.clientX > r.right || event.clientY < r.top || event.clientY > r.bottom) closeDialog();
  });

  document.addEventListener('change', (event) => {
    if (!event.target.matches('[data-image-input]')) return;
    const input = event.target;
    const file = input.files[0];
    input.setCustomValidity('');
    if (!file) return;
    if (file.size > 2 * 1024 * 1024 || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
      input.setCustomValidity('Chọn ảnh JPG, PNG hoặc WebP không quá 2 MB.');
      input.reportValidity();
      return;
    }
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = URL.createObjectURL(file);
    const image = document.createElement('img');
    image.src = objectUrl;
    image.alt = 'Ảnh đại diện đã chọn';
    input.closest('.upload').querySelector('[data-image-preview]').replaceChildren(image);
  });

  document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (form.matches('[data-delete-form]')) {
      if (!event.defaultPrevented) { form.querySelector('button').disabled = true; form.querySelector('button').textContent = 'Đang xóa…'; }
      return;
    }
    if (form.matches('[data-search-form]')) {
      const button = form.querySelector('button');
      button.textContent = 'Đang tìm…';
      button.disabled = true;
      return;
    }
    if (!form.matches('[data-user-form]')) return;
    event.preventDefault();
    if (form.getAttribute('aria-busy') === 'true') return;
    const submit = form.querySelector('[type="submit"]');
    const label = form.querySelector('[data-submit-label]');
    const originalLabel = label.textContent;
    const error = form.querySelector('[data-form-error]');
    const status = form.querySelector('[data-form-status]');
    const controls = [...form.querySelectorAll('button, .form-actions a')];
    form.setAttribute('aria-busy', 'true');
    controls.forEach((control) => { if (control.tagName === 'BUTTON') control.disabled = true; else control.setAttribute('aria-disabled', 'true'); });
    error.classList.add('hidden');
    label.textContent = 'Đang lưu…';
    status.textContent = 'Đang lưu thông tin người dùng.';
    try {
      const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } });
      const type = response.headers.get('Content-Type') || '';
      if (!type.includes('application/json')) throw new Error(response.status === 403 ? 'Phiên biểu mẫu đã hết hạn. Hãy tải lại trang.' : 'Không kết nối được máy chủ. Vui lòng thử lại.');
      let result;
      try { result = await response.json(); }
      catch { throw new Error('Máy chủ chưa trả về kết quả hợp lệ. Hãy kiểm tra danh sách trước khi thử lại.'); }
      if (!response.ok || !result.ok) throw new Error(result.message || 'Chưa lưu được thông tin.');
      form.classList.add('is-saved');
      label.textContent = result.message;
      form.querySelector('.button-icon').innerHTML = '<svg class="save-check" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m7 12 3 3 7-7"/></svg>';
      status.textContent = result.message + '. Đang mở danh sách.';
      setTimeout(() => window.location.assign(result.redirect), reduceMotion.matches ? 0 : 480);
    } catch (err) {
      error.textContent = err.message;
      error.classList.remove('hidden');
      error.focus();
      status.textContent = 'Chưa lưu được thông tin.';
      form.removeAttribute('aria-busy');
      controls.forEach((control) => { if (control.tagName === 'BUTTON') control.disabled = false; else control.removeAttribute('aria-disabled'); });
      label.textContent = originalLabel;
    }
  });
  window.addEventListener('pageshow', (event) => { if (event.persisted) window.location.reload(); });
})();
