/* =========================================================
   School of Signals - Main JavaScript
   Cleaned & consolidated JS
   Use: assets/js/site.js
   ========================================================= */

(function () {
  'use strict';

document.addEventListener('DOMContentLoaded', function () {
  initActiveNavigation();
  initMobileMenu();
  initLanguageSwitch();
  initHeroCarousel();
  initHomeCourseTabs();
  initCourseCatalogue();
  initGalleryFilterAndLightbox();
  initImageFallbacks();
  initRevealAnimation();
  initScrollTop();
});
  function qs(selector, parent) {
    return (parent || document).querySelector(selector);
  }

  function qsa(selector, parent) {
    return Array.prototype.slice.call((parent || document).querySelectorAll(selector));
  }

  function getCurrentPage() {
    var page = window.location.pathname.split('/').pop() || 'index.php';
    page = page.split('?')[0].split('#')[0].replace('.php', '').replace('.html', '').toLowerCase();
    return page || 'index';
  }

  /* ---------- Active navigation ---------- */
  function initActiveNavigation() {
    var currentPage = getCurrentPage();

    qsa('.desktop-nav a, .mobile-menu a').forEach(function (link) {
      var href = (link.getAttribute('href') || '').split('#')[0];
      if (!href || href.indexOf('http') === 0) return;

      var linkPage = href.split('/').pop().replace('.php', '').replace('.html', '').toLowerCase() || 'index';
      if (linkPage === currentPage) {
        link.classList.add('active');

        var parentToggle = link.closest('.drop') ? qs('.drop-toggle', link.closest('.drop')) : null;
        if (parentToggle) parentToggle.classList.add('active');
      }
    });
  }

  /* ---------- Mobile menu ---------- */
  function initMobileMenu() {
    var mobileToggle = qs('#mobileToggle') || qs('[data-mobile-toggle]');
    var mobileMenu = qs('#mobileMenu') || qs('[data-mobile-menu]');
    if (!mobileToggle || !mobileMenu) return;

    mobileToggle.setAttribute('aria-expanded', 'false');

    mobileToggle.addEventListener('click', function () {
      mobileMenu.classList.remove('hidden');
      var isOpen = mobileMenu.classList.toggle('open');

      mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      mobileToggle.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');

      var icon = qs('i', mobileToggle);
      if (icon) {
        icon.classList.toggle('fa-bars', !isOpen);
        icon.classList.toggle('fa-xmark', isOpen);
      }
    });

    qsa('a', mobileMenu).forEach(function (link) {
      link.addEventListener('click', function () {
        mobileMenu.classList.remove('open');
        mobileToggle.setAttribute('aria-expanded', 'false');

        var icon = qs('i', mobileToggle);
        if (icon) {
          icon.classList.add('fa-bars');
          icon.classList.remove('fa-xmark');
        }
      });
    });
  }

  /* ---------- Language switch ---------- */
  function initLanguageSwitch() {
    var switchers = qsa('[data-language-switch]');
    var languageLinks = qsa('[data-language-option]');

    languageLinks.forEach(function (link) {
      link.addEventListener('click', function () {
        var language = link.getAttribute('data-lang') || 'en';
        try {
          window.localStorage.setItem('site_language', language);
          document.cookie = 'site_language=' + encodeURIComponent(language) + '; path=/; max-age=31536000; SameSite=Lax';
        } catch (error) {
          document.cookie = 'site_language=' + encodeURIComponent(language) + '; path=/; max-age=31536000; SameSite=Lax';
        }
      });
    });

    switchers.forEach(function (switcher) {
      var toggle = qs('[data-language-toggle]', switcher);
      if (!toggle) return;

      function close() {
        switcher.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      }

      toggle.addEventListener('click', function (event) {
        event.stopPropagation();
        var isOpen = switcher.classList.toggle('open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });

      document.addEventListener('click', function (event) {
        if (!switcher.contains(event.target)) close();
      });

      window.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') close();
      });
    });
  }

  /* ---------- Hero carousel ---------- */
  function initHeroCarousel() {
    var track = qs('#carouselTrack');
    if (!track) return;

    var slides = qsa('.slide', track);
    var dotsWrap = qs('#carouselDots');
    var prevSlide = qs('#prevSlide');
    var nextSlide = qs('#nextSlide');
    var carouselFrame = qs('[data-carousel]') || track.parentElement;
    var slideIndex = 0;
    var timer = null;
    var delay = 6000;

    if (!slides.length) return;

    function buildDots() {
      if (!dotsWrap) return;
      dotsWrap.innerHTML = '';

      slides.forEach(function (_, index) {
        var dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'carousel-dot' + (index === 0 ? ' active' : '');
        dot.setAttribute('aria-label', 'Go to slide ' + (index + 1));
        dot.setAttribute('aria-current', index === 0 ? 'true' : 'false');
        dot.addEventListener('click', function () {
          goToSlide(index);
        });
        dotsWrap.appendChild(dot);
      });
    }

    function render() {
      track.style.transform = 'translateX(-' + (slideIndex * 100) + '%)';

      if (dotsWrap) {
        qsa('.carousel-dot', dotsWrap).forEach(function (dot, index) {
          dot.classList.toggle('active', index === slideIndex);
          dot.setAttribute('aria-current', index === slideIndex ? 'true' : 'false');
        });
      }
    }

    function goToSlide(index) {
      slideIndex = (index + slides.length) % slides.length;
      render();
      resetTimer();
    }

    function next() {
      goToSlide(slideIndex + 1);
    }

    function prev() {
      goToSlide(slideIndex - 1);
    }

    function resetTimer() {
      window.clearInterval(timer);
      if (slides.length > 1) timer = window.setInterval(next, delay);
    }

    buildDots();
    render();
    resetTimer();

    if (prevSlide) prevSlide.addEventListener('click', prev);
    if (nextSlide) nextSlide.addEventListener('click', next);

    if (carouselFrame) {
      carouselFrame.addEventListener('mouseenter', function () { window.clearInterval(timer); });
      carouselFrame.addEventListener('mouseleave', resetTimer);
    }
  }

  /* ---------- Home course tabs ---------- */
  function initHomeCourseTabs() {
    var tabButtons = qsa('[data-tab]');
    var courseGroups = qsa('[data-course-group]');
    if (!tabButtons.length || !courseGroups.length) return;

    tabButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        var tab = button.getAttribute('data-tab');

        tabButtons.forEach(function (item) { item.classList.remove('active'); });
        button.classList.add('active');

        courseGroups.forEach(function (group) {
          group.classList.toggle('is-hidden', group.getAttribute('data-course-group') !== tab);
        });
      });
    });
  }

  /* ---------- Courses page search/filter ---------- */
  function initCourseCatalogue() {
    var courseCards = qsa('[data-course-card]');
    if (!courseCards.length) return;

    var filterButtons = qsa('[data-filter]');
    var searchInput = qs('#courseSearch') || qs('[data-course-search]');
    var countEl = qs('#courseCount') || qs('[data-course-count]');
    var emptyState = qs('#emptyState') || qs('[data-empty-state]');
    var activeFilter = 'all';

    function updateCourses() {
      var query = searchInput ? searchInput.value.trim().toLowerCase() : '';
      var visible = 0;

      courseCards.forEach(function (card) {
        var category = (card.getAttribute('data-category') || '').toLowerCase();
        var title = (card.getAttribute('data-title') || card.textContent || '').toLowerCase();
        var matchesFilter = activeFilter === 'all' || category === activeFilter;
        var matchesSearch = !query || title.indexOf(query) !== -1;
        var shouldShow = matchesFilter && matchesSearch;

        card.classList.toggle('is-hidden', !shouldShow);
        if (shouldShow) visible += 1;
      });

      if (countEl) countEl.innerHTML = '<i class="fa-solid fa-list"></i> ' + visible + ' courses';
      if (emptyState) emptyState.classList.toggle('show', visible === 0);
    }

    filterButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        activeFilter = (button.getAttribute('data-filter') || 'all').toLowerCase();
        filterButtons.forEach(function (item) { item.classList.remove('active', 'course-filter-active'); });
        button.classList.add('active');
        updateCourses();
      });
    });

    if (searchInput) searchInput.addEventListener('input', updateCourses);
    updateCourses();
  }

  /* ---------- Gallery filter + lightbox ---------- */
  function initGalleryFilterAndLightbox() {
    var galleryCards = qsa('.gallery-card, .simple-gallery-card, [data-gallery]');
    var visitItems = qsa('.visit-item');
    var featureItems = qsa('[data-feature-src]');
    var lightboxCards = galleryCards.concat(visitItems).concat(featureItems);

    initGalleryFilter(galleryCards);

    if (!lightboxCards.length) return;

    var lightboxData = ensureLightbox();
    if (!lightboxData) return;

    var lightbox = lightboxData.lightbox;
    var lightboxImg = lightboxData.image;
    var lightboxTitle = lightboxData.title;
    var closeBtn = lightboxData.close;
    var prevBtn = lightboxData.prev;
    var nextBtn = lightboxData.next;
    var currentIndex = 0;

    function cardSource(card) {
      var image = qs('img', card);
      return {
        src: card.getAttribute('data-src') || card.getAttribute('data-feature-src') || (image ? image.src : ''),
        title: card.getAttribute('data-title') || card.getAttribute('data-feature-title') || (image ? image.alt : 'Gallery image')
      };
    }

    function visibleCards() {
      return lightboxCards.filter(function (card) {
        return !card.classList.contains('hide') && !card.classList.contains('is-hidden') && window.getComputedStyle(card).display !== 'none';
      });
    }

    function openLightbox(card) {
      var cards = visibleCards();
      var data = cardSource(card);
      if (!data.src) return;

      currentIndex = Math.max(0, cards.indexOf(card));
      lightboxImg.src = data.src;
      lightboxImg.alt = data.title || 'Gallery image';
      if (lightboxTitle) lightboxTitle.textContent = data.title || 'Gallery image';

      lightbox.classList.add('open');
      lightbox.classList.remove('hidden');
      lightbox.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }

    function openByIndex(index) {
      var cards = visibleCards();
      if (!cards.length) return;
      currentIndex = (index + cards.length) % cards.length;
      openLightbox(cards[currentIndex]);
    }

    function closeLightbox() {
      lightbox.classList.remove('open');
      lightbox.classList.add('hidden');
      lightbox.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      lightboxImg.removeAttribute('src');
    }

    lightboxCards.forEach(function (card) {
      card.addEventListener('click', function (event) {
        if (card.matches('a')) event.preventDefault();
        openLightbox(card);
      });
    });

    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
    if (prevBtn) prevBtn.addEventListener('click', function (event) { event.stopPropagation(); openByIndex(currentIndex - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function (event) { event.stopPropagation(); openByIndex(currentIndex + 1); });

    lightbox.addEventListener('click', function (event) {
      if (event.target === lightbox) closeLightbox();
    });

    window.addEventListener('keydown', function (event) {
      if (!lightbox.classList.contains('open')) return;
      if (event.key === 'Escape') closeLightbox();
      if (event.key === 'ArrowLeft') openByIndex(currentIndex - 1);
      if (event.key === 'ArrowRight') openByIndex(currentIndex + 1);
    });
  }

  function initGalleryFilter(galleryCards) {
    if (!galleryCards.length) return;

    var searchInput = qs('#gallerySearch') || qs('[data-gallery-search]');
    var filterButtons = qsa('[data-filter]');
    var activeFilter = 'all';

    function applyFilter() {
      var query = searchInput ? searchInput.value.trim().toLowerCase() : '';

      galleryCards.forEach(function (card) {
        var title = (card.getAttribute('data-title') || card.textContent || '').toLowerCase();
        var category = (card.getAttribute('data-category') || '').toLowerCase();
        var matchesFilter = activeFilter === 'all' || category === activeFilter;
        var matchesSearch = !query || title.indexOf(query) !== -1 || category.indexOf(query) !== -1;
        card.classList.toggle('hide', !(matchesFilter && matchesSearch));
      });
    }

    filterButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        activeFilter = (button.getAttribute('data-filter') || 'all').toLowerCase();
        filterButtons.forEach(function (item) { item.classList.remove('active'); });
        button.classList.add('active');
        applyFilter();
      });
    });

    if (searchInput) searchInput.addEventListener('input', applyFilter);
    applyFilter();
  }

  function ensureLightbox() {
    var lightbox = qs('#lightbox') || qs('[data-lightbox]');

    if (!lightbox) {
      lightbox = document.createElement('div');
      lightbox.id = 'lightbox';
      lightbox.className = 'lightbox hidden';
      lightbox.setAttribute('aria-hidden', 'true');
      lightbox.innerHTML =
        '<button class="lightbox-arrow lightbox-prev" id="lightboxPrev" type="button" aria-label="Previous image"><i class="fa-solid fa-chevron-left"></i></button>' +
        '<div class="lightbox-panel">' +
          '<div class="lightbox-top">' +
            '<strong class="lightbox-title" id="lightboxTitle">Gallery image</strong>' +
            '<button class="lightbox-close" id="lightboxClose" type="button" aria-label="Close image"><i class="fa-solid fa-xmark"></i></button>' +
          '</div>' +
          '<img class="lightbox-img" id="lightboxImg" alt="Gallery image">' +
        '</div>' +
        '<button class="lightbox-arrow lightbox-next" id="lightboxNext" type="button" aria-label="Next image"><i class="fa-solid fa-chevron-right"></i></button>';
      document.body.appendChild(lightbox);
    }

    return {
      lightbox: lightbox,
      image: qs('#lightboxImg') || qs('#lightboxImage') || qs('[data-lightbox-img]'),
      title: qs('#lightboxTitle') || qs('[data-lightbox-title]'),
      close: qs('#lightboxClose') || qs('[data-lightbox-close]'),
      prev: qs('#lightboxPrev') || qs('[data-lightbox-prev]'),
      next: qs('#lightboxNext') || qs('[data-lightbox-next]')
    };
  }

  /* ---------- Contact form ---------- */
  function initContactForm() {
    var form = qs('[data-contact-form]');
    if (!form) return;

    var status = qs('#contactFormStatus', form);

    function setStatus(message, isError) {
      if (!status) return;
      status.textContent = message;
      status.classList.toggle('error', Boolean(isError));
      status.classList.add('show');
    }

    form.addEventListener('submit', function (event) {
      event.preventDefault();

      if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
        if (typeof form.reportValidity === 'function') form.reportValidity();
        setStatus('Please complete the required fields before sending.', true);
        return;
      }

      setStatus('Inquiry prepared. Please forward it through the official school office channel.', false);
      form.reset();
    });
  }

  /* ---------- Remote image fallback ---------- */
  function initImageFallbacks() {
    qsa('img').forEach(function (image) {
      image.addEventListener('error', function () {
        if (image.dataset.fallbackApplied === 'true') return;
        image.dataset.fallbackApplied = 'true';
        image.src = 'assets/images/image-placeholder.svg';
        image.classList.add('image-fallback');
      });
    });
  }

  /* ---------- Reveal animation ---------- */
  function initRevealAnimation() {
    var revealItems = qsa('.reveal');
    if (!revealItems.length) return;

    if (!('IntersectionObserver' in window)) {
      revealItems.forEach(function (item) { item.classList.add('is-visible'); });
      return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });

    revealItems.forEach(function (item) { observer.observe(item); });
  }

  /* ---------- Scroll to top ---------- */
  function initScrollTop() {
    var scrollTop = qs('#scrollTop');

    if (!scrollTop) {
      scrollTop = document.createElement('button');
      scrollTop.id = 'scrollTop';
      scrollTop.className = 'scroll-top';
      scrollTop.type = 'button';
      scrollTop.setAttribute('aria-label', 'Scroll to top');
      scrollTop.innerHTML = '<i class="fa-solid fa-arrow-up"></i>';
      document.body.appendChild(scrollTop);
    }

    window.addEventListener('scroll', function () {
      scrollTop.classList.toggle('show', window.scrollY > 500);
    });

    scrollTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
})();
