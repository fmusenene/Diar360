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
   * Advanced version with throttling and hysteresis to prevent flickering
   */
  function toggleScrolled() {
    const selectBody = document.querySelector('body');
    const selectHeader = document.querySelector('#header');
    if (!selectHeader.classList.contains('scroll-up-sticky') && !selectHeader.classList.contains('sticky-top') && !selectHeader.classList.contains('fixed-top')) return;
    
    const scrollY = window.scrollY || document.documentElement.scrollTop;
    const currentScrollState = selectBody.classList.contains('scrolled');
    
    // Hysteresis thresholds to prevent rapid toggling
    const scrollDownThreshold = 120;  // Add scrolled class when scrolling down past 120px
    const scrollUpThreshold = 80;     // Remove scrolled class when scrolling up past 80px
    
    // Determine if we should add or remove the scrolled class
    let shouldAddScrolled = false;
    
    if (scrollY > scrollDownThreshold) {
      shouldAddScrolled = true;
    } else if (scrollY < scrollUpThreshold) {
      shouldAddScrolled = false;
    } else {
      // Between thresholds - maintain current state
      shouldAddScrolled = currentScrollState;
    }
    
    // Only update if state needs to change
    if (shouldAddScrolled !== currentScrollState) {
      if (shouldAddScrolled) {
        selectBody.classList.add('scrolled');
      } else {
        selectBody.classList.remove('scrolled');
      }
    }
  }

  // Advanced scroll handler with throttling to prevent performance issues
  let scrollTicking = false;
  let lastScrollY = 0;
  let scrollDirection = 'down';
  
  function handleScroll() {
    if (scrollTicking) return;
    
    scrollTicking = true;
    requestAnimationFrame(() => {
      const currentScrollY = window.scrollY || document.documentElement.scrollTop;
      
      // Track scroll direction for better hysteresis
      if (currentScrollY > lastScrollY) {
        scrollDirection = 'down';
      } else if (currentScrollY < lastScrollY) {
        scrollDirection = 'up';
      }
      
      lastScrollY = currentScrollY;
      toggleScrolled();
      scrollTicking = false;
    });
  }

  document.addEventListener('scroll', handleScroll, { passive: true });
  window.addEventListener('load', toggleScrolled);

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