document.addEventListener('DOMContentLoaded', () => {
  const triggers = [...document.querySelectorAll('[data-news-image]')];
  if (!triggers.length) return;

  const dialog = document.createElement('dialog');
  dialog.className = 'news-image-dialog';
  dialog.innerHTML = '<div class="news-image-dialog-inner"><header><h2></h2><button type="button" data-close>閉じる ×</button></header><figure><img alt=""></figure></div>';
  document.body.append(dialog);

  const image = dialog.querySelector('img');
  const title = dialog.querySelector('h2');
  triggers.forEach(trigger => {
    trigger.addEventListener('click', () => {
      image.src = trigger.dataset.newsImage || '';
      title.textContent = trigger.dataset.newsTitle || '';
      dialog.showModal();
    });
  });
  dialog.querySelector('[data-close]').addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', event => {
    if (event.target === dialog || event.target === dialog.querySelector('figure')) dialog.close();
  });
});
