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

  const speed = document.querySelector('[data-strength-speed]');
  const speedOutput = document.querySelector('[data-strength-speed-output]');
  if (speed && speedOutput) {
    const updateSpeed = () => speedOutput.textContent = `${speed.value}秒`;
    speed.addEventListener('input', updateSpeed);
    updateSpeed();
  }
})();
