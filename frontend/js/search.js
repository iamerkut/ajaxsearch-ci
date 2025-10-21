(function () {
  const root = document.getElementById('cc-ajax-search');
  if (!root) return;

  const input = root.querySelector('.cc-ajax-search__input');
  const box   = root.querySelector('.cc-ajax-search__dropdown');

  let timer = null;
  let current = -1;
  let items = [];

  const t = {
    empty: root.dataset.tEmpty || 'Sonuç yok',
    error: root.dataset.tError || 'Bir hata oluştu',
  };

  const onInput = debounce(e => search(e.target.value.trim()), 200);

  input.addEventListener('input', onInput);
  input.addEventListener('focus', () => {
    if (box.innerHTML) box.setAttribute('aria-hidden', 'false');
  });
  input.addEventListener('keydown', e => {
    const max = items.length - 1;
    if (e.key === 'ArrowDown') { e.preventDefault(); current = Math.min(max, current + 1); highlight(current); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); current = Math.max(-1, current - 1); highlight(current); }
    else if (e.key === 'Enter') { if (current >= 0 && items[current]) window.location.href = items[current].url; }
    else if (e.key === 'Escape') { box.setAttribute('aria-hidden', 'true'); }
  });
  document.addEventListener('click', (e) => { if (!root.contains(e.target)) box.setAttribute('aria-hidden', 'true'); });

  function debounce(fn, ms) {
    return function (...args) { clearTimeout(timer); timer = setTimeout(() => fn.apply(this, args), ms); };
  }

  async function search(q) {
    if (!q || q.length < 2) { box.setAttribute('aria-hidden', 'true'); box.innerHTML=''; return; }
    const url = new URL(window.location.href);
    url.searchParams.set('cc_ajax_search', '1');
    url.searchParams.set('q', q);

    try {
      const res = await fetch(url.toString(), { credentials: 'same-origin' });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const json = await res.json();
      if (json.ok) { render(json.items || []); } else { throw new Error(json.error || 'error'); }
    } catch {
      box.innerHTML = `<div class="cc-ajax-search__error">${t.error}</div>`;
      box.setAttribute('aria-hidden', 'false');
    }
  }

  function render(list) {
    items = list || [];
    current = -1;
    if (!items.length) {
      box.innerHTML = `<div class="cc-ajax-search__empty">${t.empty}</div>`;
      box.setAttribute('aria-hidden', 'false');
      return;
    }
    box.innerHTML = items.map((it, i) => `
      <a class="cc-ajax-search__item" role="option" data-index="${i}" href="${it.url}">
        ${it.image ? `<img class="cc-ajax-search__thumb" src="${it.image}" alt="">` : `<div class="cc-ajax-search__thumb cc-ajax-search__thumb--ph"></div>`}
        <div class="cc-ajax-search__meta">
          <div class="cc-ajax-search__name">${escapeHtml(it.name || '')}</div>
          <div class="cc-ajax-search__sku-price">
            ${it.sku ? `<span class="cc-ajax-search__sku">${escapeHtml(it.sku)}</span>` : ''}
            ${Number.isFinite(it.priceGross) ? `<span class="cc-ajax-search__price">${formatPrice(it.priceGross, it.currency || 'EUR')}</span>` : ''}
          </div>
        </div>
      </a>
    `).join('');
    box.setAttribute('aria-hidden', 'false');
  }

  function highlight(idx) {
    const nodes = box.querySelectorAll('.cc-ajax-search__item');
    nodes.forEach(n => n.classList.remove('is-active'));
    if (idx >= 0 && idx < nodes.length) {
      nodes[idx].classList.add('is-active');
      nodes[idx].scrollIntoView({ block: 'nearest' });
    }
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
  }

  function formatPrice(v, iso) {
    try {
      const lang = document.documentElement.lang || 'tr-TR';
      return new Intl.NumberFormat(lang, { style: 'currency', currency: iso }).format(v);
    } catch { return Number(v).toFixed(2); }
  }
})();
