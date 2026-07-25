(() => {
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
})();
