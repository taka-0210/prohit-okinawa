document.addEventListener('DOMContentLoaded', () => {
  const editor = document.querySelector('[data-news-block-editor]');
  if (!editor) return;
  const list = editor.querySelector('[data-news-block-list]');
  const createKey = () => `block-${Date.now()}-${Math.random().toString(16).slice(2, 8)}`;

  const blockMarkup = (type, key) => {
    const section = document.createElement('section');
    section.className = 'news-edit-block';
    section.dataset.newsBlock = '';
    section.innerHTML = `<input type="hidden" name="block_key[]" value="${key}">
      <input type="hidden" name="block_type[${key}]" value="${type}">
      <header><strong>${type === 'image' ? '写真' : 'テキスト'}</strong><div>
      <button type="button" data-move-up>↑</button><button type="button" data-move-down>↓</button><button type="button" data-remove-block>削除</button>
      </div></header>`;
    const label = document.createElement('label');
    if (type === 'image') {
      label.innerHTML = `写真<input type="file" name="block_image_${key}" accept="image/jpeg,image/png,image/webp" required><small>※長辺1920pxを超える画像は、比率を保って自動縮小します。</small>`;
    } else {
      label.innerHTML = `小見出し（任意）<input name="block_subtitle[${key}]">`;
      const textLabel = document.createElement('label');
      textLabel.innerHTML = `本文<textarea name="block_text[${key}]" rows="7"></textarea>`;
      section.append(label, textLabel);
      return section;
    }
    section.append(label);
    return section;
  };

  editor.querySelector('[data-add-text]').addEventListener('click', () => {
    const block = blockMarkup('text', createKey());
    list.append(block);
    block.querySelector('textarea').focus();
  });
  editor.querySelector('[data-add-image]').addEventListener('click', () => {
    const block = blockMarkup('image', createKey());
    list.append(block);
    block.querySelector('input[type=file]').click();
  });
  list.addEventListener('click', event => {
    const block = event.target.closest('[data-news-block]');
    if (!block) return;
    if (event.target.closest('[data-remove-block]')) block.remove();
    if (event.target.closest('[data-move-up]') && block.previousElementSibling) list.insertBefore(block, block.previousElementSibling);
    if (event.target.closest('[data-move-down]') && block.nextElementSibling) list.insertBefore(block.nextElementSibling, block);
  });
  list.addEventListener('change', event => {
    const input = event.target.closest('input[type=file]');
    if (!input || !input.files || !input.files[0]) return;
    const block = input.closest('[data-news-block]');
    let preview = block.querySelector('.news-image-preview');
    if (!preview) {
      preview = document.createElement('img');
      preview.className = 'news-image-preview';
      preview.alt = '選択した写真のプレビュー';
      block.insertBefore(preview, input.closest('label'));
    }
    if (preview.dataset.previewUrl) URL.revokeObjectURL(preview.dataset.previewUrl);
    const previewUrl = URL.createObjectURL(input.files[0]);
    preview.dataset.previewUrl = previewUrl;
    preview.src = previewUrl;
  });
});
