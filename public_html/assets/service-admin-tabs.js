const initializeServiceAdminTabs = async () => {
  const form = document.querySelector('.admin-main form');
  if (!form) return;
  let services = [
    ['kitchen-design-opening', '01 厨房設計・開業支援'],
    ['equipment-sales-purchase', '02 厨房機器 販売・買取'],
    ['interior-exterior', '03 内装・外装工事'],
    ['uriten', '04 ウリテン事業'],
    ['okinawa-opening', '05 沖縄での飲食店開業サポート'],
    ['rational', '06 ラショナル製品の導入支援'],
  ];
  try {
    const response = await fetch('service-tabs-data.php', { cache: 'no-store' });
    if (response.ok) {
      const storedServices = await response.json();
      if (Array.isArray(storedServices) && storedServices.length) {
        services = storedServices.map((service, index) => [service.id, `${String(index + 1).padStart(2, '0')} ${service.title}`]);
      }
    }
  } catch (_) {
    // 通信できない場合も既定のタブで編集を続けられるようにする。
  }
  const current = new URLSearchParams(location.search).get('id') || services[0][0];
  const tabs = document.createElement('nav');
  tabs.className = 'service-admin-tabs';
  tabs.setAttribute('aria-label', '編集するサービス');
  tabs.innerHTML = services.map(([id, label]) => `<a href="service-admin.php?id=${id}" class="${id === current ? 'active' : ''}"${id === current ? ' aria-current="page"' : ''}>${label}</a>`).join('');
  form.before(tabs);
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeServiceAdminTabs, { once: true });
} else {
  initializeServiceAdminTabs();
}
