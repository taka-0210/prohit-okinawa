document.addEventListener('DOMContentLoaded', () => {
  const storageKey = 'prohit-okinawa-contact-draft';

  if (document.body.dataset.contactComplete === '1') {
    sessionStorage.removeItem(storageKey);
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
    sessionStorage.setItem(storageKey, JSON.stringify(draft));
  };

  try {
    const draft = JSON.parse(sessionStorage.getItem(storageKey) || '{}');
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
    sessionStorage.removeItem(storageKey);
  }

  form.addEventListener('input', saveDraft);
  form.addEventListener('change', saveDraft);

  const privacyLink = form.querySelector('.privacy-check a');
  if (privacyLink) privacyLink.addEventListener('click', saveDraft);
});
