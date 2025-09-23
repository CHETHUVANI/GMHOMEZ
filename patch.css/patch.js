// gm-homez patch.js
(function(){
  // Derive BASE from current path (works for http://localhost/gm-homez/)
  var path = window.location.pathname;
  // If path ends with a file, drop it; else keep as folder
  var BASE = path.replace(/\/[^\/]*$/, '');
  if (BASE === '/' || BASE === '\\') BASE = '';

  // Ensure only one modal opens on Login/Signup
  var loginBtn  = document.getElementById('btnLogin');
  var signupBtn = document.getElementById('btnSignup');
  var loginM = document.getElementById('loginModal');
  var signupM = document.getElementById('signupModal');
  var leadM = document.getElementById('leadModal');
  var npM = document.getElementById('npModal');
  var keyM = document.getElementById('keyModal');

  function hide(m){ if(!m) return; m.style.display='none'; m.classList && m.classList.remove('show'); }
  function show(m){ if(!m) return; m.style.display='flex'; }

  function closeAll(){ [loginM, signupM, leadM, npM, keyM].forEach(hide); }

  if (loginBtn) {
    loginBtn.addEventListener('click', function(e){
      e.preventDefault(); e.stopImmediatePropagation();
      closeAll(); show(loginM);
    }, true);
  }
  if (signupBtn) {
    signupBtn.addEventListener('click', function(e){
      e.preventDefault(); e.stopImmediatePropagation();
      closeAll(); show(signupM);
    }, true);
  }

  // Intercept View Details to ensure correct image BASE path
  document.addEventListener('click', function(e){
    var a = e.target.closest('.np-link'); if(!a) return;
    e.preventDefault(); e.stopImmediatePropagation();
    try {
      var id = String(a.dataset.id || '');
      var list = (window.GM_PROPS || []);
      var byId = {};
      for (var i=0;i<list.length;i++){ byId[String(list[i].id||'')] = list[i]; }
      var p = byId[id]; if(!p) return;

      var img = (p.image ? (BASE + '/uploads/' + p.image)
               : (p.images && p.images[0] ? (BASE + '/uploads/' + p.images[0]) 
               : (BASE + '/assets/back.png')));

      var fmt = function(n){ try { return Number(n||0).toLocaleString('en-IN'); } catch(_){ return n; } };

      document.getElementById('npModalTitle').textContent = p.title || 'Property';
      document.getElementById('npImg').src   = img;
      document.getElementById('npPrice').textContent = '₹ ' + fmt(p.price || 0);
      document.getElementById('npLoc').textContent   = p.location || '—';
      document.getElementById('npBeds').textContent  = (p.beds != null ? p.beds : '—');
      document.getElementById('npBaths').textContent = (p.baths != null ? p.baths : '—');
      document.getElementById('npArea').textContent  = p.area || '—';
      document.getElementById('npDesc').textContent  = p.description || '';
      show(npM);
    } catch(err){
      console.warn('np-link patch error', err);
    }
  }, true);
})();
