(() => {
  const about = document.querySelector('#about');
  if (!about) return;

  const buttons = [...about.querySelectorAll('[data-about-language]')];
  const panels = [...about.querySelectorAll('[data-about-panel]')];
  buttons.forEach((button) => {
    button.addEventListener('click', () => {
      const language = button.dataset.aboutLanguage;
      buttons.forEach((item) => {
        const active = item === button;
        item.classList.toggle('is-active', active);
        item.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
      panels.forEach((panel) => panel.classList.toggle('is-active', panel.dataset.aboutPanel === language));
    });
  });
})();
