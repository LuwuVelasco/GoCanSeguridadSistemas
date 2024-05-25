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
  const modal = document.getElementById('reserveModal');
  modal.style.display = 'flex'; // Asegurarse de que se usa flex para mantenerlo centrado.
  modal.style.alignItems = 'center'; // Alineación vertical centrada
  modal.style.justifyContent = 'center'; // Alineación horizontal centrada
});

document.querySelector('.modal .close').addEventListener('click', function() {
  this.closest('.modal').style.display = 'none';
});

document.getElementById('reserveForm').addEventListener('submit', function(event) {
  event.preventDefault();
  
  // Recolectar datos del formulario
  const formData = new FormData(this);

  // Enviar los datos al servidor usando fetch
  fetch('../php/citas.php', {
      method: 'POST',
      body: formData
  })
  .then(response => response.text())
  .then(data => {
      console.log('Respuesta del servidor:', data);
      this.closest('.modal').style.display = 'none';
      alert('Cita reservada con éxito!');
      // Aquí puedes actualizar tu lista de reservaciones o lo que necesites
  })
  .catch(error => console.error('Error:', error));
});


// Evento para abrir la ventana modal de Reservas
document.querySelector('[data-nav-link="reservas"]').addEventListener('click', function(event) {
  event.preventDefault();
  fetchReservations(); // Función para cargar las reservas desde la BDD
  document.getElementById('viewReservationsModal').style.display = 'flex';
});

// Cerrar la ventana modal de Reservas
document.querySelector('#viewReservationsModal .close').addEventListener('click', function() {
  this.closest('.modal').style.display = 'none';
});

// Función para cargar las reservas desde la BDD (simulación)
function fetchReservations() {
  let id_usuario = localStorage.getItem('id_usuario');
  fetch('http://localhost/GoCan/src/modules/php/reservas.php', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json'
      },
      body: JSON.stringify({ id_usuario: id_usuario })
  })
  .then(response => response.json())
  .then(data => {
      var reservasList = document.getElementById('reservationsList');
      reservasList.innerHTML = '';

      data.forEach(function(reserva) {
          var div = document.createElement('div');
          div.textContent = `Propietario: ${reserva.propietario}, Servicio: ${reserva.servicio}, Fecha: ${reserva.fecha}, Hora: ${reserva.horario}`;
          reservasList.appendChild(div);
      });
  });
}

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
  const slides = document.querySelectorAll('.product-card');
  const slideWidth = document.querySelector('.product-card').clientWidth;
  const totalWidth = slideContainer.scrollWidth;
  const visibleWidth = slideContainer.offsetWidth;
  const maxSlide = Math.floor((totalWidth - visibleWidth) / (slideWidth + 15)); // 15 es el margen entre tarjetas

  currentSlide += step;

  if (currentSlide < 0) {
    currentSlide = 0; // Evitar ir más allá del primer elemento
  } else if (currentSlide > maxSlide) {
    currentSlide = maxSlide; // Evitar ir más allá del último elemento visible
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
