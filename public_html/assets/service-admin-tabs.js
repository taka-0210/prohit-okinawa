const initializeServiceAdminTabs = async () => {
  const form = document.querySelector('.admin-main form');
  if (!form) return;
  let services = [
    {id: 'kitchen-design-opening', title: '厨房設計・開業支援', published: true},
    {id: 'equipment-sales-purchase', title: '厨房機器 販売・買取', published: true},
    {id: 'interior-exterior', title: '内装・外装工事', published: true},
    {id: 'uriten', title: 'ウリテン事業', published: true},
    {id: 'okinawa-opening', title: '沖縄での飲食店開業サポート', published: true},
    {id: 'rational', title: 'ラショナル製品の導入支援', published: true},
  ];
  try {
    const response = await fetch('service-tabs-data.php', {cache: 'no-store'});
    if (response.ok) {
      const storedServices = await response.json();
      if (Array.isArray(storedServices) && storedServices.length) {
        services = storedServices;
      }
    }
  } catch (_) {
    // 通信できない場合も既定のタブで編集を続けられるようにする。
  }

  const current = new URLSearchParams(location.search).get('id') || services[0].id;
  const wrapper = document.createElement('div');
  wrapper.className = 'service-admin-tab-area';
  wrapper.innerHTML = '<p>サービスを横にドラッグすると、公開サイトの表示順も入れ替わります。</p>';
  const tabs = document.createElement('nav');
  tabs.className = 'service-admin-tabs';
  tabs.setAttribute('aria-label', '編集するサービス');
  const renderLabels = () => {
    [...tabs.querySelectorAll('[data-service-id]')].forEach((link, index) => {
      link.querySelector('span').textContent = String(index + 1).padStart(2, '0');
    });
  };
  services.forEach(({id, title, published}) => {
    const link = document.createElement('a');
    link.href = `service-admin.php?id=${encodeURIComponent(id)}`;
    link.dataset.serviceId = id;
    link.draggable = true;
    link.className = `${id === current ? 'active ' : ''}${published ? 'is-published' : 'is-unpublished'}`.trim();
    if (id === current) link.setAttribute('aria-current', 'page');
    const number = document.createElement('span');
    const name = document.createElement('strong');
    name.textContent = title;
    const status = document.createElement('small');
    status.className = 'service-status';
    status.textContent = published ? '公開' : '非公開';
    link.append(number, name, status);
    tabs.append(link);
  });
  const addLink = document.createElement('a');
  addLink.className = 'service-add-tab';
  addLink.href = 'service-admin.php?id=new';
  addLink.textContent = '＋ 新規サービス登録';
  tabs.append(addLink);
  renderLabels();
  wrapper.append(tabs);
  form.before(wrapper);

  let dragged = null;
  tabs.addEventListener('dragstart', event => {
    dragged = event.target.closest('[data-service-id]');
    if (!dragged) return;
    dragged.classList.add('dragging');
    event.dataTransfer.effectAllowed = 'move';
  });
  tabs.addEventListener('dragover', event => {
    if (!dragged) return;
    event.preventDefault();
    const target = event.target.closest('[data-service-id]');
    if (!target || target === dragged) return;
    const box = target.getBoundingClientRect();
    tabs.insertBefore(dragged, event.clientX < box.left + box.width / 2 ? target : target.nextSibling);
    renderLabels();
  });
  tabs.addEventListener('dragend', async () => {
    if (!dragged) return;
    dragged.classList.remove('dragging');
    dragged = null;
    const message = wrapper.querySelector('p');
    message.textContent = '表示順を保存しています…';
    try {
      const response = await fetch('service-order.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
          csrf: document.querySelector('meta[name="csrf-token"]')?.content || '',
          order: [...tabs.querySelectorAll('[data-service-id]')].map(link => link.dataset.serviceId),
        }),
      });
      const result = await response.json();
      if (!response.ok || !result.ok) throw new Error(result.message || '保存できませんでした。');
      message.textContent = '表示順を保存しました。';
    } catch (error) {
      message.textContent = error.message || '表示順を保存できませんでした。ページを再読み込みしてください。';
    }
  });
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeServiceAdminTabs, {once: true});
} else {
  initializeServiceAdminTabs();
}
