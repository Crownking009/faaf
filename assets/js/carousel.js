/* =====================================================
   FAAF Collections & Souvenirs — carousel.js
   1) initScrollCarousel — horizontal scroll-snap track with arrows + dots (Featured Products)
   2) initFadeCarousel   — fading full-width slides with autoplay + swipe (Featured Collections)
   ===================================================== */

function initScrollCarousel(trackEl, { prevBtn, nextBtn, dotsWrap, autoplayMs = 0 } = {}) {
  if (!trackEl) return;

  function itemWidth() {
    const first = trackEl.querySelector(':scope > *');
    if (!first) return trackEl.clientWidth;
    const style = getComputedStyle(trackEl);
    const gap = parseFloat(style.gap) || 0;
    return first.getBoundingClientRect().width + gap;
  }

  function updateArrows() {
    if (!prevBtn || !nextBtn) return;
    const maxScroll = trackEl.scrollWidth - trackEl.clientWidth - 4;
    prevBtn.disabled = trackEl.scrollLeft <= 4;
    nextBtn.disabled = trackEl.scrollLeft >= maxScroll;
  }

  function updateDots() {
    if (!dotsWrap) return;
    const children = [...dotsWrap.children];
    if (!children.length) return;
    const maxScroll = trackEl.scrollWidth - trackEl.clientWidth;
    const ratio = maxScroll > 0 ? trackEl.scrollLeft / maxScroll : 0;
    const activeIndex = Math.min(children.length - 1, Math.round(ratio * (children.length - 1)));
    children.forEach((d, i) => d.classList.toggle('active', i === activeIndex));
  }

  function buildDots(count) {
    if (!dotsWrap) return;
    dotsWrap.innerHTML = '';
    for (let i = 0; i < count; i++) {
      const dot = document.createElement('button');
      dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
      dot.addEventListener('click', () => {
        const maxScroll = trackEl.scrollWidth - trackEl.clientWidth;
        trackEl.scrollTo({ left: (maxScroll / (count - 1)) * i, behavior: 'smooth' });
      });
      dotsWrap.appendChild(dot);
    }
  }

  prevBtn?.addEventListener('click', () => trackEl.scrollBy({ left: -itemWidth() * (window.innerWidth < 760 ? 1 : 2), behavior: 'smooth' }));
  nextBtn?.addEventListener('click', () => trackEl.scrollBy({ left: itemWidth() * (window.innerWidth < 760 ? 1 : 2), behavior: 'smooth' }));

  let scrollTimer;
  let autoplayTimer;
  let autoplayActive = autoplayMs > 0;

  function advanceAutoplay() {
    if (!autoplayActive) return;
    const maxScroll = Math.max(0, trackEl.scrollWidth - trackEl.clientWidth - 4);
    if (trackEl.scrollLeft >= maxScroll) {
      trackEl.scrollTo({ left: 0, behavior: 'smooth' });
    } else {
      const step = itemWidth() * (window.innerWidth < 760 ? 1 : 2);
      trackEl.scrollBy({ left: step, behavior: 'smooth' });
    }
    updateArrows();
    updateDots();
  }

  function startAutoplay() {
    if (!autoplayActive) return;
    clearInterval(autoplayTimer);
    autoplayTimer = setInterval(advanceAutoplay, autoplayMs);
  }

  function stopAutoplay() {
    clearInterval(autoplayTimer);
  }

  trackEl.addEventListener('mouseenter', stopAutoplay);
  trackEl.addEventListener('mouseleave', startAutoplay);
  window.addEventListener('resize', startAutoplay);
  trackEl.addEventListener('scroll', () => {
    clearTimeout(scrollTimer);
    scrollTimer = setTimeout(() => { updateArrows(); updateDots(); }, 60);
  }, { passive: true });

  startAutoplay();

  return {
    refresh(dotCount = 5) {
      buildDots(dotCount);
      updateArrows();
      updateDots();
      startAutoplay();
    },
  };
}

function initFadeCarousel(containerEl, { autoplayMs = 5500 } = {}) {
  if (!containerEl) return;
  const slides = [...containerEl.querySelectorAll('.collection-slide')];
  const dotsWrap = containerEl.querySelector('.collections-dots');
  const prevBtn = containerEl.querySelector('.collections-nav .prev');
  const nextBtn = containerEl.querySelector('.collections-nav .next');
  let index = 0;
  let timer;

  function render() {
    slides.forEach((s, i) => s.classList.toggle('active', i === index));
    if (dotsWrap) {
      [...dotsWrap.children].forEach((d, i) => d.classList.toggle('active', i === index));
    }
  }

  function goTo(i) {
    index = (i + slides.length) % slides.length;
    render();
    resetAutoplay();
  }

  function next() { goTo(index + 1); }
  function prev() { goTo(index - 1); }

  function resetAutoplay() {
    clearInterval(timer);
    timer = setInterval(next, autoplayMs);
  }

  if (dotsWrap) {
    dotsWrap.innerHTML = '';
    slides.forEach((_, i) => {
      const dot = document.createElement('button');
      dot.setAttribute('aria-label', `Go to collection ${i + 1}`);
      dot.addEventListener('click', () => goTo(i));
      dotsWrap.appendChild(dot);
    });
  }

  prevBtn?.addEventListener('click', prev);
  nextBtn?.addEventListener('click', next);

  containerEl.addEventListener('mouseenter', () => clearInterval(timer));
  containerEl.addEventListener('mouseleave', resetAutoplay);

  let touchStartX = 0;
  containerEl.addEventListener('touchstart', (e) => { touchStartX = e.touches[0].clientX; }, { passive: true });
  containerEl.addEventListener('touchend', (e) => {
    const delta = e.changedTouches[0].clientX - touchStartX;
    if (Math.abs(delta) > 40) { delta > 0 ? prev() : next(); }
  }, { passive: true });

  render();
  resetAutoplay();
}

function initAutoScrollTestimonials() {
  const track = document.querySelector('.testimonial-track');
  if (!track || window.innerWidth > 760) return;

  const children = [...track.children];
  if (!children.length) return;

  children.forEach((child) => track.appendChild(child.cloneNode(true)));
  let step = 0;
  let timer;

  function advance() {
    const cardWidth = children[0]?.getBoundingClientRect().width || 320;
    const gap = parseFloat(getComputedStyle(track).gap) || 24;
    const distance = cardWidth + gap;
    const maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
    step += distance;
    if (step >= maxScroll) {
      step = 0;
      track.scrollTo({ left: 0, behavior: 'smooth' });
      return;
    }
    track.scrollTo({ left: step, behavior: 'smooth' });
  }

  function start() {
    clearInterval(timer);
    timer = setInterval(advance, 1800);
  }

  track.addEventListener('mouseenter', () => clearInterval(timer));
  track.addEventListener('mouseleave', start);
  start();
}

/* ---------- Count-up numbers (hero stats) ---------- */
function initCountUp() {
  const els = document.querySelectorAll('[data-count-to]');
  if (!els.length) return;
  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el = entry.target;
      const target = parseFloat(el.dataset.countTo);
      const suffix = el.dataset.suffix || '';
      const decimals = el.dataset.countTo.includes('.') ? 1 : 0;
      const duration = 1200;
      const start = performance.now();
      function step(now) {
        const progress = Math.min(1, (now - start) / duration);
        const eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = (target * eased).toFixed(decimals) + suffix;
        if (progress < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
      io.unobserve(el);
    });
  }, { threshold: 0.6 });
  els.forEach(el => io.observe(el));
}

/* ---------- Subtle tilt-on-hover for cards (desktop / fine-pointer only) ---------- */
function initTiltHover(selector) {
  if (!window.matchMedia('(pointer: fine)').matches) return;
  document.querySelectorAll(selector).forEach(card => {
    if (card.dataset.tiltBound) return;
    card.dataset.tiltBound = '1';
    card.classList.add('tilt-card');
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width - 0.5;
      const y = (e.clientY - rect.top) / rect.height - 0.5;
      card.style.transform = `perspective(700px) rotateY(${x * 5}deg) rotateX(${-y * 5}deg) translateY(-2px)`;
    });
    card.addEventListener('mouseleave', () => { card.style.transform = ''; });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initCountUp();
  initAutoScrollTestimonials();
});
