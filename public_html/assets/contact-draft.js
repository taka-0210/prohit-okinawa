document.addEventListener('DOMContentLoaded', () => {
  const storageKey = 'prohit-okinawa-contact-draft';
  const draftLifetime = 2 * 60 * 60 * 1000;

  if (document.body.dataset.contactComplete === '1') {
    localStorage.removeItem(storageKey);
    return;
  }

  const form = document.querySelector('.contact-form');
  if (!form) return;

  const fields = ['type', 'name', 'company', 'phone', 'email', 'message', 'privacy'];

  const saveDraft = () => {
    const draft = {};
    fields.forEach((name) => {
      const field = form.elements.namedItem(name);
      if (!field) return;
      draft[name] = field.type === 'checkbox' ? field.checked : field.value;
    });
    localStorage.setItem(storageKey, JSON.stringify({
      values: draft,
      expiresAt: Date.now() + draftLifetime,
    }));
  };

  try {
    const saved = JSON.parse(localStorage.getItem(storageKey) || '{}');
    if (!saved.expiresAt || saved.expiresAt < Date.now()) {
      localStorage.removeItem(storageKey);
      return;
    }
    const draft = saved.values || {};
    fields.forEach((name) => {
      const field = form.elements.namedItem(name);
      if (!field || draft[name] === undefined) return;
      if (field.type === 'checkbox') {
        field.checked = Boolean(draft[name]);
      } else if (!field.value) {
        field.value = String(draft[name]);
      }
    });
  } catch {
    localStorage.removeItem(storageKey);
  }

  form.addEventListener('input', saveDraft);
  form.addEventListener('change', saveDraft);

  const privacyLink = form.querySelector('.privacy-check a');
  if (privacyLink) privacyLink.addEventListener('click', saveDraft);
});
