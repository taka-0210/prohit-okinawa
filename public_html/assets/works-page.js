document.addEventListener('DOMContentLoaded', () => {
  const tabs = [...document.querySelectorAll('[data-map-tab]')];
  const panels = [...document.querySelectorAll('[data-map-panel]')];
  const select = document.querySelector('[data-map-select]');
  const closePopups = except => {
    document.querySelectorAll('[data-pin-popup]').forEach(popup => {
      if (popup === except) return;
      popup.hidden = true;
      popup.previousElementSibling?.setAttribute('aria-expanded', 'false');
      popup.previousElementSibling?.classList.remove('cluster-open');
    });
  };
  const activate = id => {
    tabs.forEach(tab => tab.setAttribute('aria-selected', String(tab.dataset.mapTab === id)));
    panels.forEach(panel => panel.hidden = panel.dataset.mapPanel !== id);
    if (select) select.value = id;
    closePopups();
  };
  tabs.forEach(tab => tab.addEventListener('click', () => activate(tab.dataset.mapTab)));
  select?.addEventListener('change', () => activate(select.value));

  panels.forEach(panel => {
    const pins = [...panel.querySelectorAll('[data-work-pin]')];
    const groups = [...panel.querySelectorAll('[data-cluster-ids]')];
    const cards = [...panel.querySelectorAll('[data-work-card]')];
    const highlight = id => {
      pins.forEach(pin => pin.classList.toggle('active', pin.dataset.workPin === id));
      groups.forEach(group => {
        const ids = JSON.parse(group.dataset.clusterIds || '[]');
        group.querySelector('.project-pin')?.classList.toggle('active', ids.includes(id));
      });
      cards.forEach(card => card.classList.toggle('active', card.dataset.workCard === id));
    };
    const showWork = id => {
      highlight(id);
      const card = panel.querySelector(`[data-work-card="${CSS.escape(id)}"]`);
      card?.scrollIntoView({behavior: 'smooth', block: 'center'});
    };
    pins.forEach(pin => pin.addEventListener('click', () => {
      closePopups();
      showWork(pin.dataset.workPin);
    }));
    panel.querySelectorAll('[data-cluster-toggle]').forEach(toggle => {
      toggle.addEventListener('click', event => {
        event.stopPropagation();
        const popup = toggle.nextElementSibling;
        const opening = popup.hidden;
        closePopups(opening ? popup : null);
        panel.querySelectorAll('.project-pin.active').forEach(pin => pin.classList.remove('active'));
        popup.hidden = !opening;
        toggle.setAttribute('aria-expanded', String(opening));
        toggle.classList.toggle('cluster-open', opening);
      });
    });
    panel.querySelectorAll('[data-popup-work]').forEach(button => {
      button.addEventListener('click', () => {
        showWork(button.dataset.popupWork);
        closePopups();
      });
    });
    cards.forEach(card => {
      card.addEventListener('mouseenter', () => highlight(card.dataset.workCard));
      card.addEventListener('mouseleave', () => highlight(''));
    });
  });
  document.addEventListener('click', event => {
    if (!event.target.closest('.pin-group')) closePopups();
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closePopups();
  });
});
