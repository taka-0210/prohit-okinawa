document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-mini-project-map]').forEach(map => {
    const canvas = map.querySelector('.mini-map-canvas');
    const image = canvas?.querySelector('img');
    const pin = canvas?.querySelector('span');
    if (!canvas || !image || !pin) return;

    let zoom = 1;
    let offsetX = 0;
    let offsetY = 0;
    let pinchDistance = 0;
    let pinchZoom = 1;
    let panStart = null;
    const midpoint = touches => ({
      x: (touches[0].clientX + touches[1].clientX) / 2,
      y: (touches[0].clientY + touches[1].clientY) / 2
    });
    const distance = touches => Math.hypot(
      touches[0].clientX - touches[1].clientX,
      touches[0].clientY - touches[1].clientY
    );
    const render = () => {
      const maxX = map.clientWidth * (zoom - 1);
      const maxY = map.clientHeight * (zoom - 1);
      offsetX = Math.min(0, Math.max(-maxX, offsetX));
      offsetY = Math.min(0, Math.max(-maxY, offsetY));
      canvas.style.setProperty('--mini-map-zoom-inverse', String(1 / zoom));
      canvas.style.transform = `translate(${offsetX}px, ${offsetY}px) scale(${zoom})`;
      map.classList.toggle('is-zoomed', zoom > 1.01);
    };

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
      render();
    };

    map.addEventListener('touchstart', event => {
      if (event.touches.length === 2) {
        event.preventDefault();
        pinchDistance = distance(event.touches);
        pinchZoom = zoom;
      } else if (event.touches.length === 1 && zoom > 1.01) {
        panStart = {
          x: event.touches[0].clientX,
          y: event.touches[0].clientY,
          offsetX,
          offsetY
        };
      }
    }, {passive: false});
    map.addEventListener('touchmove', event => {
      if (event.touches.length === 2 && pinchDistance > 0) {
        event.preventDefault();
        const center = midpoint(event.touches);
        const rect = map.getBoundingClientRect();
        const nextZoom = Math.max(1, Math.min(3, pinchZoom * distance(event.touches) / pinchDistance));
        const ratio = nextZoom / zoom;
        const centerX = center.x - rect.left;
        const centerY = center.y - rect.top;
        offsetX = centerX - (centerX - offsetX) * ratio;
        offsetY = centerY - (centerY - offsetY) * ratio;
        zoom = nextZoom;
        render();
      } else if (event.touches.length === 1 && zoom > 1.01 && panStart) {
        event.preventDefault();
        offsetX = panStart.offsetX + event.touches[0].clientX - panStart.x;
        offsetY = panStart.offsetY + event.touches[0].clientY - panStart.y;
        render();
      }
    }, {passive: false});
    map.addEventListener('touchend', () => {
      pinchDistance = 0;
      panStart = null;
    });
    ['gesturestart', 'gesturechange', 'gestureend'].forEach(type => {
      map.addEventListener(type, event => event.preventDefault(), {passive: false});
    });

    image.addEventListener('load', position);
    if (image.complete) position();
    window.addEventListener('resize', position);
  });
});
