document.addEventListener('DOMContentLoaded', () => {
  const triggers = [...document.querySelectorAll('.work-gallery-trigger')];
  if (!triggers.length) return;

  const dialog = document.createElement('dialog');
  dialog.className = 'work-lightbox';
  dialog.innerHTML = '<div class="lightbox-inner"><div class="lightbox-head"><h2></h2><button type="button" data-close>閉じる ×</button></div><div class="lightbox-image"><img alt=""></div><div class="lightbox-controls"><button type="button" data-prev>← 前へ</button><span></span><button type="button" data-next>次へ →</button></div></div>';
  document.body.append(dialog);

  let images = [];
  let index = 0;
  const image = dialog.querySelector('img');
  const count = dialog.querySelector('.lightbox-controls span');
  const render = () => {
    if (!images.length) return;
    index = (index + images.length) % images.length;
    image.src = images[index];
    count.textContent = `${index + 1} / ${images.length}`;
  };

  triggers.forEach(trigger => {
    trigger.addEventListener('click', event => {
      event.preventDefault();
      event.stopPropagation();
      try {
        images = JSON.parse(trigger.dataset.images || '[]');
      } catch {
        images = [];
      }
      if (!images.length) return;
      index = Number.parseInt(trigger.dataset.startIndex || '0', 10);
      if (!Number.isFinite(index)) index = 0;
      dialog.querySelector('h2').textContent = trigger.dataset.title || '';
      render();
      dialog.showModal();
    });
  });

  dialog.querySelector('[data-close]').addEventListener('click', () => dialog.close());
  dialog.querySelector('[data-prev]').addEventListener('click', () => {
    index--;
    render();
  });
  dialog.querySelector('[data-next]').addEventListener('click', () => {
    index++;
    render();
  });
  dialog.addEventListener('click', event => {
    if (event.target === dialog) dialog.close();
  });
  document.addEventListener('keydown', event => {
    if (!dialog.open) return;
    if (event.key === 'ArrowLeft') {
      index--;
      render();
    }
    if (event.key === 'ArrowRight') {
      index++;
      render();
    }
  });
});
