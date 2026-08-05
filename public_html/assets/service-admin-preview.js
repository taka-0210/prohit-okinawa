(() => {
  const objectUrls = new Set();

  document.querySelectorAll('[data-service-image-field]').forEach((field) => {
    const input = field.querySelector('[data-service-image-input]');
    if (!input) return;

    let preview = field.querySelector('[data-service-image-preview]');
    let currentObjectUrl = '';

    input.addEventListener('change', () => {
      const file = input.files?.[0];
      if (!file || !file.type.startsWith('image/')) return;

      if (currentObjectUrl) {
        URL.revokeObjectURL(currentObjectUrl);
        objectUrls.delete(currentObjectUrl);
      }

      currentObjectUrl = URL.createObjectURL(file);
      objectUrls.add(currentObjectUrl);

      if (preview?.tagName !== 'IMG') {
        const image = document.createElement('img');
        image.alt = '選択した画像のプレビュー';
        image.dataset.serviceImagePreview = '';
        preview?.replaceWith(image);
        preview = image;
      }

      preview.src = currentObjectUrl;
      preview.alt = `選択した画像のプレビュー：${file.name}`;
    });
  });

  const opacityInput = document.querySelector('[data-card-image-opacity]');
  const opacityOutput = document.querySelector('[data-card-image-opacity-output]');
  if (opacityInput && opacityOutput) {
    const updateOpacity = () => { opacityOutput.textContent = `${opacityInput.value}%`; };
    opacityInput.addEventListener('input', updateOpacity);
    updateOpacity();
  }

  window.addEventListener('pagehide', () => {
    objectUrls.forEach((url) => URL.revokeObjectURL(url));
    objectUrls.clear();
  });
})();
