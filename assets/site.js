document.documentElement.classList.add('js-ready');
// Animasi reveal saat scroll dengan fallback agar konten tidak pernah tertinggal tersembunyi.
(() => {
  const selectors = [
    '.hero-copy', '.hero-content', '.hero .eyebrow', '.hero .actions', '.preview', '.section-head', '.service-card',
    '.service-group', '.service-group-head', '.project', '.step', '.why-ruangweb-card', '.cta-panel', '.faq-list details',
    '.contact-grid', '.contact-copy', '.contact-form', '.contact-card', '.project-detail', '.detail-contact-card',
    '.project-detail-meta', '.detail-content-main', '.footer-brand', '.footer-social-area'
  ].join(',');
  const init = () => {
    const items = [...document.querySelectorAll(selectors)];
    if (!items.length) return;
    const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!('IntersectionObserver' in window) || reduced) {
      items.forEach(item => item.classList.add('is-visible'));
      return;
    }
    try {
      const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.01, rootMargin: '0px 0px 80px 0px' });
      items.forEach((item, index) => {
        item.classList.add('reveal-on-scroll');
        if (item.matches('.service-card,.project,.step,.why-ruangweb-card,.faq-list details')) item.classList.add('reveal-card');
        if (item.getBoundingClientRect().top < window.innerHeight * 0.92) item.classList.add('is-visible');
        item.style.transitionDelay = `${Math.min(index % 5, 4) * 60}ms`;
        observer.observe(item);
      });
      // Failsafe untuk kondisi lokal/browser yang tidak memicu observer seperti biasa.
      window.setTimeout(() => items.forEach(item => {
        if (!item.classList.contains('is-visible') && item.getBoundingClientRect().top < window.innerHeight + 60) {
          item.classList.add('is-visible');
        }
      }), 500);
    } catch (error) {
      items.forEach(item => item.classList.add('is-visible'));
    }
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();
