(() => {
  const carousel = document.querySelector('[data-carousel]');

  if (!carousel) {
    return;
  }

  const slides = Array.from(carousel.querySelectorAll('[data-slide]'));
  const dots = Array.from(carousel.querySelectorAll('[data-go-to]'));
  const count = carousel.querySelector('[data-slide-count]');
  const autoplayDelay = 5000;
  let activeIndex = 0;
  let autoplayTimer = null;

  const showSlide = (index) => {
    activeIndex = (index + slides.length) % slides.length;

    slides.forEach((slide, slideIndex) => {
      const isActive = slideIndex === activeIndex;
      slide.hidden = !isActive;
      slide.classList.toggle('is-active', isActive);
    });

    dots.forEach((dot, dotIndex) => {
      const isActive = dotIndex === activeIndex;
      dot.classList.toggle('is-active', isActive);

      if (isActive) {
        dot.setAttribute('aria-current', 'true');
      } else {
        dot.removeAttribute('aria-current');
      }
    });

    if (count) {
      count.textContent = `${String(activeIndex + 1).padStart(2, '0')} / ${String(slides.length).padStart(2, '0')}`;
    }
  };

  const stopAutoplay = () => {
    if (autoplayTimer !== null) {
      window.clearInterval(autoplayTimer);
      autoplayTimer = null;
    }
  };

  const startAutoplay = () => {
    stopAutoplay();

    if (document.hidden) {
      return;
    }

    autoplayTimer = window.setInterval(() => {
      showSlide(activeIndex + 1);
    }, autoplayDelay);
  };

  const resetAutoplay = () => {
    stopAutoplay();
    startAutoplay();
  };

  carousel.querySelector('[data-previous]')?.addEventListener('click', () => {
    showSlide(activeIndex - 1);
    resetAutoplay();
  });
  carousel.querySelector('[data-next]')?.addEventListener('click', () => {
    showSlide(activeIndex + 1);
    resetAutoplay();
  });
  dots.forEach((dot) => {
    dot.addEventListener('click', () => {
      showSlide(Number(dot.dataset.goTo));
      resetAutoplay();
    });
  });

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      stopAutoplay();
    } else {
      startAutoplay();
    }
  });

  carousel.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowLeft') {
      event.preventDefault();
      showSlide(activeIndex - 1);
      resetAutoplay();
    } else if (event.key === 'ArrowRight') {
      event.preventDefault();
      showSlide(activeIndex + 1);
      resetAutoplay();
    }
  });

  showSlide(activeIndex);
  startAutoplay();
})();
