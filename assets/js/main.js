/**
* Project: Diar 360 Corporate Website
* Description: Custom JS for Diar 360 bilingual (EN/AR) construction site
* Updated: Jan 2026
* Author: Diar 360 Web Team
*/

(function() {
  "use strict";

  /**
   * Apply .scrolled class to the body as the page is scrolled down
   */
  function toggleScrolled() {
    const selectBody = document.querySelector('body');
    const selectHeader = document.querySelector('#header');
    if (!selectHeader.classList.contains('scroll-up-sticky') && !selectHeader.classList.contains('sticky-top') && !selectHeader.classList.contains('fixed-top')) return;

    // Hysteresis to prevent header flicker/jiggle around the threshold.
    const y = window.scrollY || document.documentElement.scrollTop || 0;
    const shouldAdd = y > 120;   // add when clearly scrolled
    const shouldRemove = y < 80; // remove only when clearly back

    if (shouldAdd) selectBody.classList.add('scrolled');
    else if (shouldRemove) selectBody.classList.remove('scrolled');
  }

  // Throttle scroll handler with rAF so we don't toggle classes many times per frame.
  let ticking = false;
  document.addEventListener('scroll', () => {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(() => {
      toggleScrolled();
      ticking = false;
    });
  }, { passive: true });

  window.addEventListener('load', toggleScrolled);

  /**
   * Scroll-based fade out functionality
   */
  function handleScrollFade() {
    const scrollElements = document.querySelectorAll('[data-scroll-fade]');
    const scrollPosition = window.scrollY || document.documentElement.scrollTop;
    
    scrollElements.forEach(element => {
      const elementTop = element.offsetTop;
      const elementHeight = element.offsetHeight;
      const fadeStart = element.dataset.fadeStart || 0.2; // Start fading at 20% of element height
      const fadeEnd = element.dataset.fadeEnd || 0.8; // End fading at 80% of element height
      
      // Calculate fade based on scroll position relative to element
      const elementBottom = elementTop + elementHeight;
      const viewportHeight = window.innerHeight;
      
      // Element is in viewport
      const isInViewport = elementBottom > scrollPosition && elementTop < scrollPosition + viewportHeight;
      
      if (isInViewport) {
        // Calculate how much of the element has been scrolled past
        const scrollProgress = (scrollPosition - elementTop + viewportHeight) / (elementHeight + viewportHeight);
        
        // Calculate opacity based on scroll progress
        let opacity = 1;
        
        if (scrollProgress < fadeStart) {
          // Element is just coming into view - fade in
          opacity = scrollProgress / fadeStart;
        } else if (scrollProgress > fadeEnd) {
          // Element is being scrolled past - fade out
          opacity = 1 - ((scrollProgress - fadeEnd) / (1 - fadeEnd));
        }
        
        // Ensure opacity stays within bounds
        opacity = Math.max(0, Math.min(1, opacity));
        
        // Apply opacity with smooth transition
        element.style.opacity = opacity;
        element.style.transform = `translateY(${(1 - opacity) * 20}px)`;
      }
    });
  }

  // Throttled scroll handler for fade effects
  let fadeTicking = false;
  document.addEventListener('scroll', () => {
    if (fadeTicking) return;
    fadeTicking = true;
    window.requestAnimationFrame(() => {
      handleScrollFade();
      fadeTicking = false;
    });
  }, { passive: true });

  // Initialize fade effects on load
  window.addEventListener('load', handleScrollFade);
  window.addEventListener('resize', handleScrollFade);

  /**
   * Mobile nav toggle
   */
  const mobileNavToggleBtn = document.querySelector('.mobile-nav-toggle');

  function mobileNavToogle() {
    document.querySelector('body').classList.toggle('mobile-nav-active');
    mobileNavToggleBtn.classList.toggle('bi-list');
    mobileNavToggleBtn.classList.toggle('bi-x');
  }
  if (mobileNavToggleBtn) {
    mobileNavToggleBtn.addEventListener('click', mobileNavToogle);
  }

  /**
   * Hide mobile nav on same-page/hash links
   */
  document.querySelectorAll('#navmenu a').forEach(navmenu => {
    navmenu.addEventListener('click', () => {
      if (document.querySelector('.mobile-nav-active')) {
        mobileNavToogle();
      }
    });
  });

  /**
   * Close mobile nav when clicking outside the menu content
   * (e.g. on the dark overlay or anywhere else on the page)
   */
  document.addEventListener('click', (event) => {
    const body = document.querySelector('body');
    const navMenu = document.querySelector('#navmenu');
    if (!body.classList.contains('mobile-nav-active') || !navMenu) return;

    const isToggle = event.target.closest('.mobile-nav-toggle');
    const isMenuContent = event.target.closest('#navmenu ul');

    // If click is not on the toggle button and not inside the menu list, close the mobile nav
    if (!isToggle && !isMenuContent) {
      mobileNavToogle();
    }
  });

  /**
   * Toggle mobile nav dropdowns
   */
  document.querySelectorAll('.navmenu .toggle-dropdown').forEach(navmenu => {
    navmenu.addEventListener('click', function(e) {
      e.preventDefault();
      this.parentNode.classList.toggle('active');
      this.parentNode.nextElementSibling.classList.toggle('dropdown-active');
      e.stopImmediatePropagation();
    });
  });

  /**
   * Preloader
   */
  const preloader = document.querySelector('#preloader');
  if (preloader) {
    window.addEventListener('load', () => {
      preloader.remove();
    });
  }

  /**
   * Scroll top button
   */
  let scrollTop = document.querySelector('.scroll-top');

  function toggleScrollTop() {
    if (scrollTop) {
      window.scrollY > 100 ? scrollTop.classList.add('active') : scrollTop.classList.remove('active');
    }
  }
  scrollTop.addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });

  window.addEventListener('load', toggleScrollTop);
  document.addEventListener('scroll', toggleScrollTop);

  /**
   * Animation on scroll function and init
   */
  function aosInit() {
    AOS.init({
      duration: 600,
      easing: 'ease-in-out',
      once: true,
      mirror: false
    });
  }
  window.addEventListener('load', aosInit);

  /**
   * Init swiper sliders
   */
  function initSwiper() {
    document.querySelectorAll(".init-swiper").forEach(function(swiperElement) {
      let config = JSON.parse(
        swiperElement.querySelector(".swiper-config").innerHTML.trim()
      );

      if (swiperElement.classList.contains("swiper-tab")) {
        initSwiperWithCustomPagination(swiperElement, config);
      } else {
        new Swiper(swiperElement, config);
      }
    });
  }

  window.addEventListener("load", initSwiper);

  /**
   * Initiate glightbox
   */
  const glightbox = GLightbox({
    selector: '.glightbox'
  });

})();