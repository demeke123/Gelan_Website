/* Small enhancements: mobile menu, photo lightbox, admin helpers. The site works without JavaScript. */
(function () {
  // Mobile menu
  var btn = document.querySelector('.menu-btn'), nav = document.getElementById('mainnav');
  if (btn && nav) {
    btn.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      btn.setAttribute('aria-expanded', String(open));
    });
  }

  // Gallery lightbox
  var lb = document.getElementById('lightbox');
  if (lb) {
    var img = document.getElementById('lbImg'), cap = document.getElementById('lbCap'), close = document.getElementById('lbClose'), last = null;
    function hide() { lb.hidden = true; img.src = ''; if (last) last.focus(); }
    document.addEventListener('click', function (e) {
      var a = e.target.closest('a.lb');
      if (!a) return;
      e.preventDefault();
      last = a;
      img.src = a.href;
      img.alt = a.dataset.cap || '';
      cap.textContent = a.dataset.cap || '';
      lb.hidden = false;
      close.focus();
    });
    close.addEventListener('click', hide);
    lb.addEventListener('click', function (e) { if (e.target === lb) hide(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !lb.hidden) hide(); });
  }

  // Confirm before destructive admin actions
  document.addEventListener('submit', function (e) {
    var f = e.target.closest('[data-confirm]');
    if (f && !window.confirm(f.dataset.confirm)) e.preventDefault();
  });

  // Auto-save role dropdowns
  document.querySelectorAll('select[data-autosubmit]').forEach(function (s) {
    s.addEventListener('change', function () { s.form.submit(); });
  });

  // Show the event date field only for events
  var type = document.getElementById('postType'), wrap = document.getElementById('eventDateWrap');
  if (type && wrap) {
    var sync = function () { wrap.style.visibility = type.value === 'event' ? 'visible' : 'hidden'; };
    type.addEventListener('change', sync);
    sync();
  }
})();
