(() => {
  const list = document.querySelector('[data-work-image-list]');
  if (!list) return;

  const updateLabels = () => {
    const cards = [...list.querySelectorAll('[data-work-image-card]:not([hidden])')];
    cards.forEach((card, index) => {
      const label = card.querySelector('small');
      if (label) label.textContent = index === 0 ? 'メイン画像' : `画像 ${index + 1}`;
    });

    const count = document.querySelector('.gallery-heading span');
    if (count) count.textContent = `${cards.length} / 10枚`;
  };

  let draggedCard = null;
  list.querySelectorAll('[data-work-image-card]').forEach(card => {
    card.addEventListener('dragstart', event => {
      draggedCard = card;
      card.classList.add('is-dragging');
      event.dataTransfer.effectAllowed = 'move';
    });

    card.addEventListener('dragend', () => {
      card.classList.remove('is-dragging');
      list.querySelectorAll('.is-drag-over').forEach(item => item.classList.remove('is-drag-over'));
      draggedCard = null;
      updateLabels();
    });

    card.addEventListener('dragover', event => {
      if (!draggedCard || draggedCard === card || card.hidden) return;
      event.preventDefault();
      event.dataTransfer.dropEffect = 'move';
      card.classList.add('is-drag-over');
    });

    card.addEventListener('dragleave', () => card.classList.remove('is-drag-over'));

    card.addEventListener('drop', event => {
      if (!draggedCard || draggedCard === card || card.hidden) return;
      event.preventDefault();
      card.classList.remove('is-drag-over');

      const box = card.getBoundingClientRect();
      const insertAfter = event.clientX > box.left + box.width / 2;
      list.insertBefore(draggedCard, insertAfter ? card.nextSibling : card);
      updateLabels();
    });
  });

  list.querySelectorAll('[data-work-image-delete]').forEach(button => {
    button.addEventListener('click', () => {
      if (!window.confirm('この写真を施工事例から削除しますか？\n「保存する」を押すと削除が確定します。')) return;

      const card = button.closest('[data-work-image-card]');
      if (!card) return;

      const keep = card.querySelector('input[name="keep_images[]"]');
      if (keep) keep.checked = false;
      card.hidden = true;
      updateLabels();
    });
  });
})();
