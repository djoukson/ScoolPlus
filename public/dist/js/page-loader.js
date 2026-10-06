(function () {
  var pl = document.getElementById('pl');
  if (!pl) return;

  var fill = pl.querySelector('.pl-fill');
  var MAX_TIME = 8000;   // sécurité : la barre ne reste jamais plus longtemps
  var p = 0, raf = null, running = false, startTime = 0, safety;

  function render() {
    fill.style.transform = 'scaleX(' + (p / 100) + ')';
    pl.setAttribute('aria-valuenow', Math.round(p));
  }

  // Progression "trickle" : avance vite puis ralentit vers ~90 %
  function tick(now) {
    if (!running) return;
    p += (90 - p) * 0.04;
    render();
    if (now - startTime >= MAX_TIME) return done();
    raf = requestAnimationFrame(tick);
  }

  function start() {
    clearTimeout(safety);
    running = true; p = 0;
    startTime = performance.now();
    pl.style.transition = 'none';
    pl.classList.remove('is-done');
    pl.getBoundingClientRect();        // force le reflow
    pl.style.transition = '';
    render();
    cancelAnimationFrame(raf);
    raf = requestAnimationFrame(tick);
  }

  function done() {
    if (!running) return;
    running = false;
    cancelAnimationFrame(raf);
    p = 100; render();
    setTimeout(function () { pl.classList.add('is-done'); }, 200);
  }

  function hide() {
    running = false;
    cancelAnimationFrame(raf);
    pl.classList.add('is-done');
  }

  // Démarre dès l'arrivée sur la page, se termine au chargement complet
  start();
  if (document.readyState === 'complete') done();
  else window.addEventListener('load', done);

  // Page restaurée depuis le cache (bouton retour)
  window.addEventListener('pageshow', function (e) {
    if (e.persisted) hide();
  });

  // Au clic sur un lien interne : la barre repart, sans retarder la navigation
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href]');
    if (!a || e.defaultPrevented || e.button !== 0) return;
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    if (a.target && a.target !== '_self') return;
    if (a.hasAttribute('download') || a.hasAttribute('data-no-loader')) return;
    if (a.hasAttribute('data-toggle') || a.hasAttribute('data-bs-toggle') || a.hasAttribute('data-widget')) return;

    var href = a.getAttribute('href');
    if (!href || href.charAt(0) === '#' || /^(javascript:|mailto:|tel:)/i.test(href)) return;

    var url = new URL(a.href, location.href);
    if (url.origin !== location.origin) return;
    if (url.pathname === location.pathname && url.search === location.search && url.hash) return;

    start();
    safety = setTimeout(hide, 4500); // si la navigation n'a pas lieu (ex. téléchargement)
  });

  // API conservée : PageLoader.leave() / PageLoader.hide()
  window.PageLoader = { start: start, done: done, hide: hide, leave: function (go) { start(); if (go) go(); } };
})();