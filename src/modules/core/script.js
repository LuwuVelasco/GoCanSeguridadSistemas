'use strict';

const addEventOnElem = (elems, type, callback) => {
  if (Array.isArray(elems) || elems.length > 1) {
    elems.forEach(elem => elem.addEventListener(type, callback));
  } else {
    elems.addEventListener(type, callback);
  }
};

const toggleModal = (modalId, displayStyle = 'flex') => {
  const modal = document.getElementById(modalId);
  modal.style.display = displayStyle;
  if (displayStyle === 'flex') {
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
  }
};

const initEvents = () => {
  document.getElementById('navReserveLink').addEventListener('click', event => {
    event.preventDefault();
    toggleModal('reserveModal');
  });

  document.getElementById('openReserveModal').addEventListener('click', () => {
    toggleModal('reserveModal');
  });

  document.querySelector('.modal .close').addEventListener('click', function() {
    this.closest('.modal').style.display = 'none';
  });

  document.getElementById('reserveForm').addEventListener('submit', function(event) {
    event.preventDefault();
    submitReservationForm(this);
  });

  document.querySelector('[data-nav-link="reservas"]').addEventListener('click', event => {
    event.preventDefault();
    fetchReservations();
    toggleModal('viewReservationsModal');
  });

  document.querySelector('#viewReservationsModal .close').addEventListener('click', function() {
    this.closest('.modal').style.display = 'none';
  });
};

const submitReservationForm = form => {
  const formData = new FormData(form);
  fetch('../php/citas.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(data => {
    console.log('Respuesta del servidor:', data);
    form.closest('.modal').style.display = 'none';
    alert('Cita reservada con éxito!');
  })
  .catch(error => console.error('Error:', error));
};

const fetchReservations = () => {
  const idUsuario = localStorage.getItem('id_usuario');
  fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/reservas.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ id_usuario: idUsuario })
  })
  .then(response => response.json())
  .then(data => {
    const reservasList = document.getElementById('reservationsList');
    reservasList.innerHTML = '';
    data.forEach(reserva => {
      const row = document.createElement('tr');
      row.appendChild(createEditableCell(reserva.propietario));
      row.appendChild(createEditableCell(reserva.servicio));
      row.appendChild(createEditableCell(reserva.fecha));
      row.appendChild(createEditableCell(reserva.horario));

      const cellEliminar = document.createElement('td');
      const deleteIcon = document.createElement('span');
      deleteIcon.innerHTML = '&#128465;'; // Icono de basurero
      deleteIcon.classList.add('delete-icon');
      deleteIcon.addEventListener('click', () => {
        eliminarReserva(reserva.id_cita, row);
      });
      cellEliminar.appendChild(deleteIcon);
      row.appendChild(cellEliminar);

      reservasList.appendChild(row);
    });
  })
  .catch(error => console.error('Error fetching reservations:', error));
};

const createEditableCell = value => {
  const cell = document.createElement('td');
  cell.textContent = value;
  cell.addEventListener('dblclick', () => {
    const input = document.createElement('input');
    input.type = 'text';
    input.value = cell.textContent;
    input.style.width = '100%';

    input.addEventListener('blur', () => {
      cell.textContent = input.value;
    });

    input.addEventListener('keypress', event => {
      if (event.key === 'Enter') {
        input.blur();
      }
    });

    cell.innerHTML = '';
    cell.appendChild(input);
    input.focus();
  });
  return cell;
};

const eliminarReserva = (idCita, row) => {
  fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/reservas.php', {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ id_cita: idCita })
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.json();
  })
  .then(data => {
    if (data.error) {
      throw new Error(data.mensaje);
    }
    console.log(data);
    alert('Reserva eliminada correctamente');
    row.remove();
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Error: ' + error.message);
  });
};

const setupNavbar = () => {
  const navToggler = document.querySelector("[data-nav-toggler]");
  const navbar = document.querySelector("[data-navbar]");
  const navbarLinks = document.querySelectorAll("[data-nav-link]");

  const toggleNavbar = () => {
    navbar.classList.toggle("active");
    navToggler.classList.toggle("active");
  };

  const closeNavbar = () => {
    navbar.classList.remove("active");
    navToggler.classList.remove("active");
  };

  addEventOnElem(navToggler, "click", toggleNavbar);
  addEventOnElem(navbarLinks, "click", closeNavbar);
};

const setupScrollEffects = () => {
  const header = document.querySelector("[data-header]");
  const backTopBtn = document.querySelector("[data-back-top-btn]");

  const activeElemOnScroll = () => {
    if (window.scrollY > 100) {
      header.classList.add("active");
      backTopBtn.classList.add("active");
    } else {
      header.classList.remove("active");
      backTopBtn.classList.remove("active");
    }
  };

  addEventOnElem(window, "scroll", activeElemOnScroll);
};

let currentSlide = 0;

const moveCarousel = step => {
  const slideContainer = document.querySelector('.carousel-slide');
  const slides = document.querySelectorAll('.product-card');
  const slideWidth = document.querySelector('.product-card').clientWidth;
  const totalWidth = slideContainer.scrollWidth;
  const visibleWidth = slideContainer.offsetWidth;
  const maxSlide = Math.floor((totalWidth - visibleWidth) / (slideWidth )); // 15 es el margen entre tarjetas

  currentSlide += step;
  currentSlide = Math.max(0, Math.min(currentSlide, maxSlide)); // Limitar currentSlide entre 0 y maxSlide

  const newTransform = -currentSlide * (slideWidth + 25); // 15 es el margen derecho de .product-card
  slideContainer.style.transform = `translateX(${newTransform}px)`;
};

document.querySelector('.carousel-button.prev').addEventListener('click', () => moveCarousel(-1));
document.querySelector('.carousel-button.next').addEventListener('click', () => moveCarousel(1));

const setupCarousel = () => {
  document.querySelectorAll('.product-card').forEach(card => {
    card.querySelectorAll('.rating .star').forEach(star => {
      star.addEventListener('click', function() {
        const currentRating = this.getAttribute('data-value');
        const stars = this.parentElement.querySelectorAll('.star');
        stars.forEach(innerStar => {
          const ratingValue = innerStar.getAttribute('data-value');
          innerStar.classList.toggle('active', ratingValue <= currentRating);
        });
      });
    });
  });
};

document.addEventListener('DOMContentLoaded', () => {
  initEvents();
  setupNavbar();
  setupScrollEffects();
  setupCarousel();
});

document.addEventListener('DOMContentLoaded', function() {
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
