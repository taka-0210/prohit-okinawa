document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-map-fields]');
  if (!root) return;

  const select = root.querySelector('[data-map-select]');
  const map = root.querySelector('[data-position-map]');
  const image = map.querySelector('img');
  const pin = map.querySelector('span');
  const x = root.querySelector('[data-position-x]');
  const y = root.querySelector('[data-position-y]');
  const axis = root.querySelector('.map-axis-fields');

  const clamp = value => Math.max(0, Math.min(100, value));
  const place = () => {
    pin.style.left = `${clamp(Number(x.value) || 0)}%`;
    pin.style.top = `${clamp(Number(y.value) || 0)}%`;
  };
  const updateFromPointer = event => {
    const rect = map.getBoundingClientRect();
    x.value = clamp((event.clientX - rect.left) / rect.width * 100).toFixed(1);
    y.value = clamp((event.clientY - rect.top) / rect.height * 100).toFixed(1);
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
