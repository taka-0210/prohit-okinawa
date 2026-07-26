(() => {
  const body = document.querySelector('[data-strength-body]');
  const insertHeading = document.querySelector('[data-strength-heading-insert]');
  if (body && insertHeading) {
    insertHeading.addEventListener('click', () => {
      const heading = window.prompt('小見出しを入力してください。');
      if (!heading || !heading.trim()) return;
      const start = body.selectionStart;
      const end = body.selectionEnd;
      const before = body.value.slice(0, start).replace(/\s*$/, '');
      const after = body.value.slice(end).replace(/^\s*/, '');
      const insertion = `${before ? '\n\n' : ''}## ${heading.trim()}${after ? '\n\n' : ''}`;
      body.setRangeText(insertion, start, end, 'end');
      body.focus();
    });
  }

  document.querySelectorAll('[data-strength-image-delete]').forEach(button => {
    button.addEventListener('click', () => {
      if (!window.confirm('この写真を登録から削除しますか？\n「OUR STRENGTHを保存する」を押すと削除が確定します。')) return;
      const card = button.closest('[data-strength-image-card]');
      if (!card) return;
      const keep = card.querySelector('input[name="keep_images[]"]');
      if (keep) keep.checked = false;
      card.hidden = true;
    });
  });

  const input = document.querySelector('[data-strength-images]');
  const preview = document.querySelector('[data-strength-preview]');
  if (!input || !preview) return;

  input.addEventListener('change', () => {
    preview.replaceChildren();
    [...input.files].forEach(file => {
      if (!file.type.startsWith('image/')) return;
      const image = document.createElement('img');
      image.alt = '';
      image.src = URL.createObjectURL(file);
      image.addEventListener('load', () => URL.revokeObjectURL(image.src), {once: true});
      preview.append(image);
    });
  });

  const speed = document.querySelector('[data-strength-speed]');
  const speedOutput = document.querySelector('[data-strength-speed-output]');
  if (speed && speedOutput) {
    const updateSpeed = () => speedOutput.textContent = `${speed.value}秒`;
    speed.addEventListener('input', updateSpeed);
    updateSpeed();
  }
})();
