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
document.getElementById('navReserveLink').addEventListener('click', function(event) {
  event.preventDefault();  // Esto evita que la página se desplace hacia arriba cuando hagas clic en el enlace
  document.getElementById('reserveModal').style.display = 'flex';
});

document.getElementById('openReserveModal').addEventListener('click', function() {
  document.getElementById('reserveModal').style.display = 'block';
});

document.querySelector('.modal .close').addEventListener('click', function() {
  this.closest('.modal').style.display = 'none';
});

document.getElementById('reserveForm').addEventListener('submit', function(event) {
  event.preventDefault();
  // Aquí podrías agregar el código para enviar los datos al servidor
  console.log('Reserva hecha');
  this.closest('.modal').style.display = 'none';
});


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