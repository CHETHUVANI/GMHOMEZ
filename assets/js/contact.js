// Contact form AJAX → api/contact.php
(() => {
  const selectors = ['#contactMain', '#contactForm', '#contact-form', 'form[data-contact="1"]'];
  let form = null;
  for (const q of selectors) { const el = document.querySelector(q); if (el) { form = el; break; } }
  if (!form) return;

  const status = document.createElement('div'); status.className = 'muted'; status.style.marginTop = '10px'; form.appendChild(status);

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    status.textContent = 'Sending...';
    try {
      const fd = new FormData(form);
      const res = await fetch((window.BASE_URL||'') + '/api/contact.php', { method: 'POST', body: fd });
      const out = await res.json();
      status.textContent = out.ok ? 'Thanks! We will contact you shortly.' : (out.message || 'Failed to send.');
      if (out.ok) form.reset();
    } catch (err) {
      status.textContent = 'Network error. Please try again.';
    }
  });
})();