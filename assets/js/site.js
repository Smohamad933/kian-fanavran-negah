(() => {
  document.body.classList.add('has-js');

  const menuButton = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.primary-nav');
  const closeMenu = () => {
    if (!menuButton || !nav) return;
    menuButton.setAttribute('aria-expanded', 'false');
    nav.classList.remove('is-open');
    document.body.classList.remove('menu-open');
  };

  if (menuButton && nav) {
    menuButton.addEventListener('click', () => {
      const isOpen = menuButton.getAttribute('aria-expanded') === 'true';
      menuButton.setAttribute('aria-expanded', String(!isOpen));
      nav.classList.toggle('is-open', !isOpen);
      document.body.classList.toggle('menu-open', !isOpen);
    });
    nav.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') closeMenu();
    });
    window.addEventListener('resize', () => {
      if (window.innerWidth > 900) closeMenu();
    });
  }

  const revealItems = document.querySelectorAll('[data-reveal]');
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries, activeObserver) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const delay = Number(entry.target.getAttribute('data-reveal-delay') || 0);
          entry.target.style.transitionDelay = `${Math.min(delay, 300)}ms`;
          entry.target.classList.add('is-visible');
          activeObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.11, rootMargin: '0px 0px -25px 0px' });
    revealItems.forEach((item) => observer.observe(item));
  } else {
    revealItems.forEach((item) => item.classList.add('is-visible'));
  }

  const filterButtons = document.querySelectorAll('.portfolio-filter');
  const projectCards = document.querySelectorAll('[data-project-card]');
  filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const filter = button.dataset.filter || 'all';
      filterButtons.forEach((item) => item.classList.toggle('is-active', item === button));
      projectCards.forEach((card) => {
        const matches = filter === 'all' || card.dataset.category === filter;
        card.classList.toggle('is-hidden', !matches);
      });
    });
  });

  const carousels = document.querySelectorAll('[data-carousel]');
  carousels.forEach((carousel) => {
    const track = carousel.querySelector('[data-carousel-track]');
    const slides = Array.from(carousel.querySelectorAll('.brand-carousel-slide'));
    const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
    const counter = carousel.querySelector('[data-carousel-counter]');
    if (!track || slides.length < 2) return;

    let activeIndex = 0;
    const localizeNumber = (value) => new Intl.NumberFormat('fa-IR').format(value);
    const showSlide = (requestedIndex) => {
      activeIndex = (requestedIndex + slides.length) % slides.length;
      track.style.transform = `translateX(-${activeIndex * 100}%)`;
      slides.forEach((slide, index) => {
        if (index === activeIndex) slide.removeAttribute('aria-hidden');
        else slide.setAttribute('aria-hidden', 'true');
      });
      dots.forEach((dot, index) => {
        if (index === activeIndex) dot.setAttribute('aria-current', 'true');
        else dot.removeAttribute('aria-current');
      });
      if (counter) counter.textContent = `${localizeNumber(activeIndex + 1)} / ${localizeNumber(slides.length)}`;
    };

    carousel.querySelector('[data-carousel-prev]')?.addEventListener('click', () => showSlide(activeIndex - 1));
    carousel.querySelector('[data-carousel-next]')?.addEventListener('click', () => showSlide(activeIndex + 1));
    dots.forEach((dot) => dot.addEventListener('click', () => showSlide(Number(dot.dataset.carouselDot) || 0)));
    carousel.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowLeft') {
        event.preventDefault();
        showSlide(activeIndex + 1);
      } else if (event.key === 'ArrowRight') {
        event.preventDefault();
        showSlide(activeIndex - 1);
      }
    });

    let pointerStartX = null;
    carousel.addEventListener('pointerdown', (event) => {
      if (!['touch', 'pen'].includes(event.pointerType) || event.target.closest('button')) return;
      pointerStartX = event.clientX;
      if (carousel.setPointerCapture) carousel.setPointerCapture(event.pointerId);
    });
    carousel.addEventListener('pointerup', (event) => {
      if (pointerStartX === null) return;
      const distance = event.clientX - pointerStartX;
      if (Math.abs(distance) > 45) showSlide(activeIndex + (distance < 0 ? 1 : -1));
      pointerStartX = null;
    });
    carousel.addEventListener('pointercancel', () => { pointerStartX = null; });
    showSlide(0);
  });

  const progress = document.querySelector('.scroll-progress span');
  let ticking = false;
  const updateProgress = () => {
    if (!progress) return;
    const scrollable = document.documentElement.scrollHeight - window.innerHeight;
    const value = scrollable > 0 ? (window.scrollY / scrollable) * 100 : 0;
    progress.style.width = `${Math.min(100, Math.max(0, value))}%`;
    ticking = false;
  };
  window.addEventListener('scroll', () => {
    if (!ticking) {
      window.requestAnimationFrame(updateProgress);
      ticking = true;
    }
  }, { passive: true });
  updateProgress();
})();
