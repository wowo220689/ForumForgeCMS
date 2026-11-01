'use strict';

document.getElementById('forum-hosting-probe')?.addEventListener('submit', async (event) => {
  event.preventDefault();
  const form = event.currentTarget;
  const button = form.querySelector('button');
  const output = form.querySelector('output');
  const data = new FormData(form);
  button.disabled = true;
  output.textContent = '';
  try {
    const response = await fetch(form.action, { method: 'POST', body: data, cache: 'no-store' });
    if (!response.ok) throw new Error('probe');
    const probe = await response.json();
    const url = new URL(probe.url, window.location.href);
    if (url.origin !== window.location.origin) throw new Error('origin');
    const result = await fetch(url, { cache: 'no-store', redirect: 'error' });
    output.textContent = [403, 404].includes(result.status) ? form.dataset.safe
      : result.ok ? form.dataset.exposed : form.dataset.unknown;
  } catch {
    output.textContent = form.dataset.unknown;
  } finally {
    data.set('action', 'hosting_probe_cleanup');
    try { await fetch(form.action, { method: 'POST', body: data, cache: 'no-store' }); } catch { /* Expired probes are also cleaned on the next check. */ }
    button.disabled = false;
  }
});
