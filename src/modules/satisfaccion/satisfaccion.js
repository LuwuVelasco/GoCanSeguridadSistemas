document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById("ratingModal");
    const openModalBtn = document.getElementById("openModal");
    const closeBtn = document.getElementsByClassName("close")[0];
    const stars = document.querySelectorAll('.star');
    const submitBtn = document.getElementById('submitRating');
    let selectedRating = 0;

    // Asegúrate de que el botón de apertura del modal esté presente antes de asignar el evento
    if (openModalBtn) {
        openModalBtn.addEventListener('click', function(event) {
            event.preventDefault();
            modal.style.display = "block";
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = "none";
        });
    }

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            selectedRating = index + 1;
            stars.forEach((s, i) => {
                if (i < selectedRating) {
                    s.classList.add('filled');
                } else {
                    s.classList.remove('filled');
                }
            });
        });

        star.addEventListener('mouseover', function() {
            stars.forEach((s, i) => {
                if (i < index + 1) {
                    s.classList.add('selected');
                } else {
                    s.classList.remove('selected');
                }
            });
        });

        star.addEventListener('mouseout', function() {
            stars.forEach(s => s.classList.remove('selected'));
        });
    });

    submitBtn.addEventListener('click', function() {
        if (selectedRating > 0) {
            alert(`Gracias por tu calificación de ${selectedRating} estrellas`);
            modal.style.display = "none";
        } else {
            alert('Por favor selecciona una calificación');
        }
    });
});
