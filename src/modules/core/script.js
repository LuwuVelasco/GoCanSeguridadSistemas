'use strict';

/**
 * add event on element
 */

const addEventOnElem = function (elem, type, callback) {
  if (elem.length > 1) {
    for (let i = 0; i < elem.length; i++) {
      elem[i].addEventListener(type, callback);
    }
  } else {
    elem.addEventListener(type, callback);
  }
}

/**
 * navbar toggle
 */

const navToggler = document.querySelector("[data-nav-toggler]");
const navbar = document.querySelector("[data-navbar]");
const navbarLinks = document.querySelectorAll("[data-nav-link]");

const toggleNavbar = function () {
  navbar.classList.toggle("active");
  navToggler.classList.toggle("active");
}

addEventOnElem(navToggler, "click", toggleNavbar);


const closeNavbar = function () {
  navbar.classList.remove("active");
  navToggler.classList.remove("active");
}

addEventOnElem(navbarLinks, "click", closeNavbar);

/**
 * active header when window scroll down to 100px
 */

const header = document.querySelector("[data-header]");
const backTopBtn = document.querySelector("[data-back-top-btn]");

const activeElemOnScroll = function () {
  if (window.scrollY > 100) {
    header.classList.add("active");
    backTopBtn.classList.add("active");
  } else {
    header.classList.remove("active");
    backTopBtn.classList.remove("active");
  }
}

addEventOnElem(window, "scroll", activeElemOnScroll);

// Función básica para mover el carrusel
let currentSlide = 0;
function moveCarousel(step) {
  const slideContainer = document.querySelector('.carousel-slide');
  const slideWidth = document.querySelector('.product-card').clientWidth;
  currentSlide += step;

  if (currentSlide < 0) {
    currentSlide = 0; // Evitar ir más allá del primer elemento
  }

  // Calcular el nuevo desplazamiento
  const newTransform = -currentSlide * (slideWidth + 15); // 15 es el margen derecho de .product-card
  slideContainer.style.transform = `translateX(${newTransform}px)`;
}
document.querySelectorAll('.product-card').forEach(card => {
  card.querySelectorAll('.rating .star').forEach(star => {
      star.addEventListener('click', function() {
          let currentRating = this.getAttribute('data-value');
          let stars = this.parentElement.querySelectorAll('.star');
          
          stars.forEach(innerStar => {
              let ratingValue = innerStar.getAttribute('data-value');
              if (ratingValue <= currentRating) {
                  innerStar.classList.add('active');
              } else {
                  innerStar.classList.remove('active');
              }
          });
      });
  });
});
document.addEventListener('DOMContentLoaded', function() {
  // Selecciona cada tarjeta de producto dentro del carrusel
  const productCards = document.querySelectorAll('.product-card');

  productCards.forEach(card => {
    const stars = card.querySelectorAll('.star');
    const reviewCount = card.querySelector('.review-count');

    stars.forEach(star => {
      star.addEventListener('click', function() {
        const ratingValue = parseInt(star.getAttribute('data-value'), 10);
        reviewCount.textContent = `(${ratingValue})`;
      });
    });
  });
});