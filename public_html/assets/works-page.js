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
      popup.previousElementSibling?.classList.remove('pin-open');
    });
  };
  const activate = id => {
    tabs.forEach(tab => tab.setAttribute('aria-selected', String(tab.dataset.mapTab === id)));
    panels.forEach(panel => panel.hidden = panel.dataset.mapPanel !== id);
    if (select) select.value = id;
    closePopups();
    panels.forEach(panel => panel.dispatchEvent(new CustomEvent('mapchange')));
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
    panel.querySelectorAll('[data-cluster-toggle], [data-single-toggle]').forEach(toggle => {
      toggle.addEventListener('click', event => {
        event.stopPropagation();
        const popup = toggle.nextElementSibling;
        const opening = popup.hidden;
        closePopups(opening ? popup : null);
        panel.querySelectorAll('.project-pin.active').forEach(pin => pin.classList.remove('active'));
        popup.hidden = !opening;
        toggle.setAttribute('aria-expanded', String(opening));
        toggle.classList.toggle('cluster-open', opening);
        toggle.classList.toggle('pin-open', opening);
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
    const requestedWorkId = decodeURIComponent(location.hash.replace(/^#work-/, ''));
    if (location.hash.startsWith('#work-') && panel.querySelector(`[data-work-card="${CSS.escape(requestedWorkId)}"]`)) {
      activate(panel.dataset.mapPanel);
      window.setTimeout(() => showWork(requestedWorkId), 80);
    }

    const viewport = panel.querySelector('[data-map-viewport]');
    const canvas = viewport?.querySelector('.map-canvas');
    if (viewport && canvas) {
      let scale = 1;
      let x = 0;
      let y = 0;
      let pinchDistance = 0;
      let pinchScale = 1;
      let panStart = null;
      let moved = false;
      const midpoint = touches => ({
        x: (touches[0].clientX + touches[1].clientX) / 2,
        y: (touches[0].clientY + touches[1].clientY) / 2
      });
      const distance = touches => Math.hypot(
        touches[0].clientX - touches[1].clientX,
        touches[0].clientY - touches[1].clientY
      );
      const constrain = () => {
        const maxX = viewport.clientWidth * (scale - 1);
        const maxY = viewport.clientHeight * (scale - 1);
        x = Math.min(0, Math.max(-maxX, x));
        y = Math.min(0, Math.max(-maxY, y));
      };
      const render = () => {
        constrain();
        canvas.style.setProperty('--map-zoom-inverse', String(1 / scale));
        canvas.style.setProperty('--map-popup-offset', `${42 / scale}px`);
        canvas.style.transform = `translate(${x}px, ${y}px) scale(${scale})`;
        viewport.classList.toggle('is-zoomed', scale > 1.01);
      };
      const reset = () => {
        scale = 1;
        x = 0;
        y = 0;
        render();
      };
      viewport.addEventListener('touchstart', event => {
        moved = false;
        if (event.touches.length === 2) {
          event.preventDefault();
          pinchDistance = distance(event.touches);
          pinchScale = scale;
          panStart = {...midpoint(event.touches), x, y};
        } else if (event.touches.length === 1 && scale > 1.01) {
          panStart = {x: event.touches[0].clientX, y: event.touches[0].clientY, offsetX: x, offsetY: y};
        }
      }, {passive: false});
      viewport.addEventListener('touchmove', event => {
        if (event.touches.length === 2 && pinchDistance > 0) {
          event.preventDefault();
          const center = midpoint(event.touches);
          const nextScale = Math.max(1, Math.min(3, pinchScale * distance(event.touches) / pinchDistance));
          const ratio = nextScale / scale;
          x = center.x - viewport.getBoundingClientRect().left - ((center.x - viewport.getBoundingClientRect().left) - x) * ratio;
          y = center.y - viewport.getBoundingClientRect().top - ((center.y - viewport.getBoundingClientRect().top) - y) * ratio;
          scale = nextScale;
          moved = true;
          render();
        } else if (event.touches.length === 1 && scale > 1.01 && panStart) {
          event.preventDefault();
          x = panStart.offsetX + event.touches[0].clientX - panStart.x;
          y = panStart.offsetY + event.touches[0].clientY - panStart.y;
          moved = true;
          render();
        }
      }, {passive: false});
      viewport.addEventListener('touchend', () => {
        pinchDistance = 0;
        panStart = null;
      });
      viewport.addEventListener('wheel', event => {
        if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;
        event.preventDefault();
        const rect = viewport.getBoundingClientRect();
        const pointerX = event.clientX - rect.left;
        const pointerY = event.clientY - rect.top;
        const nextScale = Math.max(1, Math.min(3, scale * Math.exp(-event.deltaY * .0015)));
        const ratio = nextScale / scale;
        x = pointerX - (pointerX - x) * ratio;
        y = pointerY - (pointerY - y) * ratio;
        scale = nextScale;
        render();
      }, {passive: false});
      viewport.addEventListener('click', event => {
        if (moved) {
          event.preventDefault();
          event.stopPropagation();
          moved = false;
        }
      }, true);
      panel.addEventListener('mapchange', reset);
      window.addEventListener('resize', render);
    }
  });
  document.addEventListener('click', event => {
    if (!event.target.closest('.pin-group')) closePopups();
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closePopups();
  });
});
