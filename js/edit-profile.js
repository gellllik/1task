(function () {
  'use strict';

  const $ = id => document.getElementById(id);
  const photoInput = $('photoInput');
  const removeBtn = $('removePhotoBtn');
  const preview = $('photoPreview');
  const previewText = $('photoPreviewText');
  const hidden = $('photoData');
  const removeFlag = $('photoRemoveFlag');
  const fullname = $('fullname');
  const miniAvatar = $('miniAvatar');
  const miniName = $('miniName');

  let current = hidden ? (hidden.value || '') : '';

  function initials(name) {
    const parts = (name || '').trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return '??';
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }

  function render() {
    const old = preview.querySelector('img');
    if (old) old.remove();
    if (current) {
      if (previewText) previewText.style.display = 'none';
      const img = document.createElement('img');
      img.src = current;
      preview.insertBefore(img, preview.firstChild);
    } else {
      if (previewText) { previewText.style.display = ''; previewText.textContent = initials(fullname.value); }
    }
    if (miniAvatar) {
      miniAvatar.innerHTML = '';
      if (current) {
        const im = document.createElement('img');
        im.src = current;
        im.style.cssText = 'width:100%;height:100%;object-fit:cover;';
        miniAvatar.appendChild(im);
      } else {
        miniAvatar.textContent = initials(fullname.value);
      }
    }
    if (miniName) miniName.textContent = fullname.value;
  }

  photoInput?.addEventListener('change', e => {
    const file = e.target.files && e.target.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) { alert('Только изображения.'); photoInput.value = ''; return; }
    if (file.size > 2 * 1024 * 1024) { alert('Файл больше 2 МБ.'); photoInput.value = ''; return; }
    const r = new FileReader();
    r.onload = ev => {
      current = ev.target.result;
      if (hidden) hidden.value = current;
      if (removeFlag) removeFlag.value = '';
      render();
    };
    r.readAsDataURL(file);
  });

  removeBtn?.addEventListener('click', () => {
    if (!current) return;
    if (!confirm('Удалить фотографию профиля?')) return;
    current = '';
    photoInput.value = '';
    if (hidden) hidden.value = '';
    if (removeFlag) removeFlag.value = '1';
    render();
  });

  fullname?.addEventListener('input', () => {
    if (!current) render();
    if (miniName) miniName.textContent = fullname.value;
  });

  render();
})();