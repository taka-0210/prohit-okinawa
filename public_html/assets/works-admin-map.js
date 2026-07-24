document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-map-fields]');
  if (!root) return;

  const select = root.querySelector('[data-map-select]');
  const map = root.querySelector('[data-position-map]');
  const image = map.querySelector('img');
  image.style.height = 'auto';
  image.style.maxHeight = 'none';
  image.style.objectFit = 'fill';
  const pin = map.querySelector('span');
  const x = root.querySelector('[data-position-x]');
  const y = root.querySelector('[data-position-y]');
  const axis = root.querySelector('.map-axis-fields');

  const clamp = value => Math.max(0, Math.min(100, value));
  const place = () => {
    const scale = Number(map.dataset.scale) || 1;
    const storedX = clamp(Number(x.value) || 0);
    const storedY = clamp(Number(y.value) || 0);
    pin.style.left = `${50 + (storedX - 50) * scale}%`;
    pin.style.top = `${50 + (storedY - 50) * scale}%`;
  };
  const updateFromPointer = event => {
    const rect = map.getBoundingClientRect();
    const scale = Number(map.dataset.scale) || 1;
    const displayX = (event.clientX - rect.left) / rect.width * 100;
    const displayY = (event.clientY - rect.top) / rect.height * 100;
    x.value = clamp(50 + (displayX - 50) / scale).toFixed(1);
    y.value = clamp(50 + (displayY - 50) / scale).toFixed(1);
    place();
  };
  const render = () => {
    const option = select.selectedOptions[0];
    const source = option?.dataset.image || '';
    const outside = select.value === 'outside' || !source;
    map.hidden = outside;
    axis.hidden = outside;
    x.disabled = outside;
    y.disabled = outside;
    if (!outside) {
      const scale = Number(option.dataset.scale) || 1;
      map.dataset.scale = scale;
      map.style.setProperty('--map-scale', scale);
      image.style.transform = `scale(${scale})`;
      image.style.transformOrigin = 'center';
      image.src = source;
      place();
    }
  };

  select.addEventListener('change', render);
  x.addEventListener('input', place);
  y.addEventListener('input', place);
  map.addEventListener('click', updateFromPointer);

  pin.addEventListener('pointerdown', event => {
    event.preventDefault();
    event.stopPropagation();
    pin.classList.add('dragging');
    pin.setPointerCapture(event.pointerId);
    updateFromPointer(event);
  });
  pin.addEventListener('pointermove', event => {
    if (!pin.hasPointerCapture(event.pointerId)) return;
    updateFromPointer(event);
  });
  const stopDragging = event => {
    if (pin.hasPointerCapture(event.pointerId)) pin.releasePointerCapture(event.pointerId);
    pin.classList.remove('dragging');
  };
  pin.addEventListener('pointerup', stopDragging);
  pin.addEventListener('pointercancel', stopDragging);

  render();
});
