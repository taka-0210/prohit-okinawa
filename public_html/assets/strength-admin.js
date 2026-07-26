(() => {
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
