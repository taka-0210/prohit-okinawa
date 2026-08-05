(() => {
  const container = document.querySelector('.service-blocks');
  if (!container) return;

  const blocks = () => [...container.querySelectorAll('[data-service-block]')];
  const refresh = () => {
    const items = blocks();
    items.forEach((block, index) => {
      const number = block.querySelector('[data-block-number]');
      const up = block.querySelector('[data-move-up]');
      const down = block.querySelector('[data-move-down]');
      if (number) number.textContent = String(index + 1).padStart(2, '0');
      if (up) up.disabled = index === 0;
      if (down) down.disabled = index === items.length - 1;
    });
  };

  container.addEventListener('click', (event) => {
    const button = event.target.closest('button');
    const block = button?.closest('[data-service-block]');
    if (!button || !block) return;
    if (button.matches('[data-move-up]')) {
      const previous = block.previousElementSibling;
      if (previous) container.insertBefore(block, previous);
    } else if (button.matches('[data-move-down]')) {
      const next = block.nextElementSibling;
      if (next) container.insertBefore(next, block);
    } else if (button.matches('[data-remove-block]')) {
      if (!window.confirm('このPHOTO & TEXTを削除しますか？\n保存すると公開ページからも削除されます。')) return;
      block.remove();
    } else {
      return;
    }
    refresh();
  });

  refresh();
})();
