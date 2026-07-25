document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-mini-project-map]').forEach(map => {
    const image = map.querySelector('img');
    const pin = map.querySelector('span');
    if (!image || !pin) return;

    const position = () => {
      if (!image.naturalWidth || !image.naturalHeight) return;
      const boxWidth = map.clientWidth;
      const boxHeight = map.clientHeight;
      const imageRatio = image.naturalWidth / image.naturalHeight;
      const boxRatio = boxWidth / boxHeight;
      let displayWidth = boxWidth;
      let displayHeight = boxWidth / imageRatio;
      if (imageRatio < boxRatio) {
        displayHeight = boxHeight;
        displayWidth = boxHeight * imageRatio;
      }
      const scale = Math.max(.4, Math.min(1.5, Number.parseFloat(map.dataset.scale || '1')));
      displayWidth *= scale;
      displayHeight *= scale;
      const left = (boxWidth - displayWidth) / 2;
      const top = (boxHeight - displayHeight) / 2;
      image.style.width = `${displayWidth}px`;
      image.style.height = `${displayHeight}px`;
      image.style.left = `${left}px`;
      image.style.top = `${top}px`;
      const x = Math.max(0, Math.min(100, Number.parseFloat(map.dataset.x || '50')));
      const y = Math.max(0, Math.min(100, Number.parseFloat(map.dataset.y || '50')));
      pin.style.left = `${left + displayWidth * x / 100}px`;
      pin.style.top = `${top + displayHeight * y / 100}px`;
    };

    image.addEventListener('load', position);
    if (image.complete) position();
    window.addEventListener('resize', position);
  });
});
