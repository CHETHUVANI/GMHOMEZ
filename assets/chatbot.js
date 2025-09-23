// gm-homez/assets/chatbot.js
(() => {
  'use strict';

  /* ================== CONFIG ================== */
  const WHATSAPP_NUMBER = '7676536261';
  const GREETING =
    "Hi! I'm GM HOMEZ assistant. Ask me anything — I can search properties (e.g., '2 bhk in Koramangala under 60L'), help plan site visits, or answer general questions.";

  /* ============== UTILITIES & BASE ============== */
  const $  = (sel, el = document) => el.querySelector(sel);
  const el = (tag, attrs = {}) => Object.assign(document.createElement(tag), attrs);

  function computeBase() {
    const p = location.pathname;
    return p.endsWith('/') ? p.slice(0, -1) : p.replace(/\/[^/]*$/, '');
  }
  const BASE = computeBase();

  function norm(s) {
    return String(s || '')
      .toLowerCase()
      .normalize('NFKD')
      .replace(/[^\w\s]/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();
  }

  // very small Markdown → HTML (bold, italics, code, links, newlines)
  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }
  function renderMD(s) {
    let t = escapeHtml(String(s));
    t = t.replace(/\[([^\]]+)]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');
    t = t.replace(/`([^`]+)`/g, '<code>$1</code>');
    t = t.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
    t = t.replace(/\*([^*]+)\*/g, '<em>$1</em>');
    t = t.replace(/\n/g, '<br>');
    return t;
  }

  // Parse numbers like 60L / 1.2Cr / 85,00,000 / 8500000
  function parsePriceToINR(txt) {
    if (!txt) return null;
    const t = txt.toLowerCase().replace(/[, ]+/g, '');
    const mCr = t.match(/([\d.]+)\s*(cr|crore)/i);
    if (mCr) return Math.round(parseFloat(mCr[1]) * 1e7);
    const mL = t.match(/([\d.]+)\s*(l|lac|lakh)/i);
    if (mL) return Math.round(parseFloat(mL[1]) * 1e5);
    const digits = t.match(/\d{3,}/);
    if (digits) return parseInt(digits[0], 10);
    return null;
  }
  const extractBhk = (s) => (s.match(/(\d+)\s*bhk/i)?.[1] ? parseInt(s.match(/(\d+)\s*bhk/i)[1],10) : null);
  function extractLocality(s) {
    const m = String(s || '').match(/\b(?:in|at)\s+([a-z][a-z\s-]{2,})$/i);
    return m ? m[1].trim() : null;
  }

  // Fuzzy helpers
  function levenshtein(a, b) {
    a = norm(a); b = norm(b);
    const m = a.length, n = b.length;
    if (!m) return n; if (!n) return m;
    const dp = new Array(n + 1);
    for (let j = 0; j <= n; j++) dp[j] = j;
    for (let i = 1; i <= m; i++) {
      let prev = dp[0]; dp[0] = i;
      for (let j = 1; j <= n; j++) {
        const tmp = dp[j];
        dp[j] = Math.min(dp[j] + 1, dp[j - 1] + 1, prev + (a[i - 1] === b[j - 1] ? 0 : 1));
        prev = tmp;
      }
    }
    return dp[n];
  }
  const ratio = (a,b) => {
    a=norm(a); b=norm(b); if(!a||!b) return 0;
    const d=levenshtein(a,b); return 1 - d/Math.max(a.length,b.length);
  };
  const tokenSetRatio = (a,b) => {
    const A=new Set(norm(a).split(' ').filter(Boolean));
    const B=new Set(norm(b).split(' ').filter(Boolean));
    if(!A.size||!B.size) return 0; let inter=0; for(const t of A) if(B.has(t)) inter++;
    return inter/Math.max(A.size,B.size);
  };
  function scoreProperty(q,p){
    const title=p.title||'', city=p.city||'', loc=p.location||'';
    const bhk = p.bedrooms!=null ? `${p.bedrooms} bhk` : '';
    const hay = `${title} ${loc} ${city} ${bhk}`;
    const s1=ratio(q,hay), s2=tokenSetRatio(q,hay), s3=norm(hay).includes(norm(q))?1:0;
    return 0.55*s1+0.35*s2+0.10*s3;
  }

  /* ============== WIDGET UI ============== */
  function injectStyles() {
    if ($('#gmh-chat-style')) return;
    const css = `
    .gmh-chat-launcher{ position:fixed; right:18px; bottom:18px; z-index:9999; width:56px; height:56px; border-radius:50%;
      background:linear-gradient(135deg,#0ea5e9,#22d3ee); box-shadow:0 8px 24px rgba(0,0,0,.35); display:grid; place-items:center; cursor:pointer; border:none; }
    .gmh-chat-launcher svg{ width:28px; height:28px; fill:white }
    .gmh-chat-panel{ position:fixed; right:18px; bottom:84px; z-index:10000; width:360px; max-width:calc(100vw - 36px); height:520px; max-height:70vh;
      background:#0f2a37; color:#e6f0f6; border:1px solid rgba(148,163,184,.12); border-radius:16px; box-shadow:0 16px 40px rgba(0,0,0,.5);
      overflow:hidden; display:none; flex-direction:column; }
    .gmh-chat-header{ padding:12px 14px; background:linear-gradient(180deg,rgba(2,6,23,.4),rgba(2,6,23,.7)); border-bottom:1px solid rgba(148,163,184,.12);
      font-weight:600; display:flex; justify-content:space-between; align-items:center; }
    .gmh-chat-body{ padding:12px; overflow:auto; flex:1; }
    .gmh-row{ display:flex; margin:8px 0; }
    .gmh-bot{ max-width:84%; background:#0b1620; border:1px solid rgba(148,163,184,.18); border-radius:12px; padding:10px 12px; }
    .gmh-user{ margin-left:auto; max-width:84%; background:#1e293b; border-radius:12px; padding:10px 12px; }
    .gmh-chat-input{ display:flex; gap:8px; border-top:1px solid rgba(148,163,184,.12); padding:10px; }
    .gmh-chat-input input{ flex:1; padding:10px 12px; border-radius:10px; border:1px solid rgba(148,163,184,.25); background:#0b1620; color:#e6f0f6; }
    .gmh-btn{ padding:8px 10px; border-radius:10px; border:1px solid rgba(148,163,184,.25); background:#0b1620; color:#e6f0f6; cursor:pointer; }
    .gmh-chip{ display:inline-block; padding:6px 10px; border-radius:999px; border:1px solid rgba(148,163,184,.25); margin:4px 6px 0 0; cursor:pointer; font-size:12px; }
    .gmh-suggestions li{ margin:6px 0; } .gmh-suggestions a{ color:#8df; text-decoration:none;} .gmh-suggestions a:hover{ text-decoration:underline; }
    .muted{ color:#9fb2c0 }
    `;
    const tag = el('style', { id: 'gmh-chat-style' }); tag.textContent = css; document.head.appendChild(tag);
  }

  function buildWidget() {
    injectStyles();

    const launcher = el('button', { className: 'gmh-chat-launcher', ariaLabel: 'Chat' });
    launcher.innerHTML = `<svg viewBox="0 0 24 24"><path d="M12 3C6.48 3 2 6.94 2 11.8c0 2.52 1.28 4.79 3.35 6.35l-.77 3.53 3.24-1.97c1.25.35 2.59.54 3.98.54 5.52 0 10-3.94 10-8.8S17.52 3 12 3z"></path></svg>`;

    const panel = el('div', { className: 'gmh-chat-panel' });
    panel.innerHTML = `
      <div class="gmh-chat-header">
        <div>GM HOMEZ Assistant</div>
        <button class="gmh-btn" id="gmh-close">×</button>
      </div>
      <div class="gmh-chat-body" id="gmh-body"></div>
      <div class="gmh-chat-input">
        <input id="gmh-in" type="text" placeholder="Ask me anything…">
        <button class="gmh-btn" id="gmh-send">Send</button>
      </div>
    `;
    document.body.append(launcher, panel);

    const body  = $('#gmh-body', panel);
    const input = $('#gmh-in',  panel);
    const send  = $('#gmh-send', panel);
    const close = $('#gmh-close',panel);

    launcher.addEventListener('click', () => {
      panel.style.display = (panel.style.display === 'none' || !panel.style.display) ? 'flex' : 'none';
      if (panel.style.display === 'flex') input.focus();
    });
    close.addEventListener('click', () => (panel.style.display = 'none'));

    // greeting + chips (chips are optional; user can type anything)
    botMsg(GREETING);
    quickChips([
      '2 bhk in Koramangala under 60L',
      'Flats in Whitefield under 1 Cr',
      'Book a site visit',
      'FAQs'
    ]);

    // handlers
    send.addEventListener('click', () => {
      const txt = input.value.trim();
      if (!txt) return;
      userMsg(txt); input.value = ''; handleMessage(txt);
    });
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); send.click(); }
    });

    // helpers to print messages
    function userMsg(text) {
      const row = el('div', { className: 'gmh-row' });
      const bubble = el('div', { className: 'gmh-user', innerText: text });
      row.appendChild(bubble); body.appendChild(row); body.scrollTop = body.scrollHeight;
    }
    function botMsg(textOrHtml, {md=false} = {}) {
      const row = el('div', { className: 'gmh-row' });
      const bubble = el('div', { className: 'gmh-bot' });
      bubble.innerHTML = md ? renderMD(textOrHtml) : textOrHtml;
      row.appendChild(bubble); body.appendChild(row); body.scrollTop = body.scrollHeight;
    }
    function quickChips(labels) {
      const row = el('div', { className: 'gmh-row' });
      const bubble = el('div', { className: 'gmh-bot' });
      labels.forEach((lab) => {
        const c = el('span', { className: 'gmh-chip', innerText: lab });
        c.addEventListener('click', () => { userMsg(lab); handleMessage(lab); });
        bubble.appendChild(c);
      });
      row.appendChild(bubble); body.appendChild(row);
    }

    /* ============== AI CHAT (fallback) ============== */
    async function aiChat(text) {
      botMsg('Thinking…');
      try {
        const res = await fetch(`${BASE}/api/ai.php`, {
          method: 'POST',
          headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
          body: JSON.stringify({ message: text })
        });
        const raw = await res.text();
        if (!res.ok) { botMsg(`AI error ${res.status}: ${raw.slice(0,180)}…`); return; }
        let out; try { out = JSON.parse(raw); } catch { botMsg(`AI server did not return JSON: ${raw.slice(0,180)}…`); return; }
        if (!out.ok) { botMsg(`AI unavailable: ${out.error || 'unknown error'}`); return; }
        botMsg(out.text || '…', { md:true });
      } catch (e) {
        botMsg('Network error: ' + e.message);
      }
    }

    /* ============== CORE HANDLER (keeps search + lead flow) ============== */
    window.__gmhLead = { active:false, step:0, data:{} };

    async function handleMessage(message) {
      const S = window.__gmhLead;
      const m = message.trim();
      const low = norm(m);

      // ======== lead flow steps ========
      if (S.active) {
        if (S.step === 1) {
          S.data.name = m;
          botMsg('Great, please share your phone number (10 digits).');
          S.step = 2; return;
        }
        if (S.step === 2) {
          const phone = m.replace(/\D+/g, '');
          if (phone.length < 10) { botMsg('That doesn’t look like a valid number. Try again.'); return; }
          S.data.phone = phone;
          botMsg('Optional: any note or locality/budget? (or say "skip")');
          S.step = 3; return;
        }
        if (S.step === 3) {
          if (low !== 'skip') S.data.message = m;
          botMsg('Saving your request…');
          try {
            const res = await fetch(`${BASE}/api/lead.php`, {
              method: 'POST',
              headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
              body: JSON.stringify({
                name: S.data.name,
                phone: S.data.phone,
                message: S.data.message || 'Site visit enquiry via chatbot',
                source: 'chatbot'
              })
            });
            const raw = await res.text();
            if (!res.ok) { botMsg(`Couldn’t save lead (${res.status}). ${raw.slice(0,120)}…`); S.active=false; return; }
            let out; try { out = JSON.parse(raw); } catch { out=null; }
            if (!out || !out.ok) { botMsg('Server error while saving.'); S.active=false; return; }
            const waText = encodeURIComponent(`Hi, this is ${S.data.name}. I'm interested in a property.\nPhone: ${S.data.phone}\nNote: ${S.data.message || ''}`);
            botMsg(`Thanks ${escapeHtml(S.data.name)}! Our team will contact you shortly.<br><br>
              <a class="gmh-btn" target="_blank" rel="noopener"
                 href="https://wa.me/91${WHATSAPP_NUMBER}?text=${waText}">Message us on WhatsApp now</a>`);
          } catch (e) {
            botMsg('Network error while saving: ' + e.message);
          }
          S.active = false; S.step = 0; S.data = {};
          return;
        }
      }

      // ======== quick commands ========
      if (/\b(help|menu|options)\b/.test(low)) {
        botMsg("I can search properties, book site visits, help with loans/legal, and answer general questions.");
        quickChips(['2 bhk in Koramangala under 60L','Flats in Whitefield','Book a site visit','FAQs']);
        return;
      }
      if (/\bfaq|faqs\b/.test(low)) { showFaqs(); return; }
      if (/site\s*visit|book\s*visit|interested|contact\s*agent/.test(low)) {
        window.__gmhLead = { active:true, step:1, data:{} };
        botMsg('Awesome — let’s set this up. What’s your name?');
        return;
      }
      if (/\b(whatsapp|agent|contact)\b/.test(low)) {
        const text = encodeURIComponent("Hi, I’m interested in a property on GM HOMEZ.");
        botMsg(`You can chat with us on WhatsApp:<br><br>
          <a class="gmh-btn" target="_blank" rel="noopener" href="https://wa.me/91${WHATSAPP_NUMBER}?text=${text}">Open WhatsApp</a>`);
        return;
      }

      // ======== property-search intent OR AI fallback ========
      const bhk = extractBhk(m);
      const loc = extractLocality(m);
      const max = parsePriceToINR(m);

      // very light heuristic: if the message looks like a property query, run site search; else ask AI
      const looksLikeProperty =
        bhk != null || max != null ||
        /\b(flat|apartment|villa|plot|rent|sale|buy|budget|locality|sq ?ft|under|in\b)/i.test(m);

      if (!looksLikeProperty) {
        // general question → AI
        await aiChat(m);
        return;
      }

      // property search flow
      const params = new URLSearchParams();
      params.set('q', m); if (bhk != null) params.set('bhk', String(bhk));
      if (loc) params.set('locality', loc); if (max) params.set('max', String(max));

      botMsg('Searching properties…');
      try {
        const res = await fetch(`${BASE}/api/search.php?` + params.toString(), { headers: { 'Accept': 'application/json' } });
        const raw = await res.text();
        if (!res.ok) { botMsg(`Search error ${res.status}: ${raw.slice(0,150)}…`); return; }
        let out; try { out = JSON.parse(raw); } catch { botMsg('Server did not return JSON.'); return; }

        if (!out || !out.ok || !Array.isArray(out.items) || out.items.length === 0) {
          botMsg("No results. Try a different area or budget.");
          return;
        }

        const ranked = out.items.map(it => ({ ...it, _score: scoreProperty(m, it) }))
                                .sort((a, b) => b._score - a._score);

        const top = ranked[0], second = ranked[1];
        const topScore = top?._score ?? 0, secondScore = second?._score ?? 0;

        if (top && (topScore >= 0.82 && (topScore - secondScore >= 0.12 || !second))) {
          botMsg(`I found a strong match:<br>
            <div class="gmh-suggestions">
              <li><a href="${BASE}/property.php?id=${encodeURIComponent(top.id)}">${escapeHtml(top.title || 'Property')}</a>
              ${top.location || top.city ? ` — ${escapeHtml([top.location, top.city].filter(Boolean).join(', '))}` : ''}</li>
            </div>`);
          return;
        }

        const list = ranked.slice(0, 5).map(it => {
          const sub = [it.location, it.city].filter(Boolean).join(', ');
          return `<li><a href="${BASE}/property.php?id=${encodeURIComponent(it.id)}">${escapeHtml(it.title || 'Property')}</a>${sub ? ` — ${escapeHtml(sub)}` : ''}</li>`;
        }).join('');
        botMsg(`I found multiple matches:<br><ul class="gmh-suggestions" style="margin:8px 0 0 18px;padding:0">${list}</ul>`);
      } catch (e) {
        botMsg('Network error: ' + e.message);
      }
    }

    function showFaqs() {
      const faqs = [
        ['How do I book a site visit?', 'Click "Book Site Visit" in End-to-End Assistance or ask me "Book a site visit".'],
        ['Do you charge brokerage?', 'We aim for 0% brokerage on most listings.'],
        ['Can you help with home loans?', 'Yes! Use "Get Home Loan" form; we’ll contact you with offers.'],
        ['Do you offer legal assistance?', 'Yes. Use "Legal Advice" form; our team will reach out.'],
        ['How do I contact an agent?', 'Open a property and tap "Call Agent" or message us on WhatsApp.']
      ];
      let html = '<div><strong>FAQs</strong><ul style="margin:8px 0 0 18px">';
      faqs.forEach(([q, a]) => { html += `<li><strong>${escapeHtml(q)}</strong><br>${escapeHtml(a)}</li>`; });
      html += '</ul></div>';
      botMsg(html);
    }
  }

  document.addEventListener('DOMContentLoaded', buildWidget);
})();
