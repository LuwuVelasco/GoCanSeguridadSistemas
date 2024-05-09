const sidebarItems = document.querySelectorAll('.sidebar .item');
const tableRows = document.querySelectorAll('.main table tbody tr');

const menuBtn = document.getElementById('menu-btn');
const leftSection = document.querySelector('.left-section');

let isMenuOpen = false;

sidebarItems.forEach(sideItem => {
    sideItem.addEventListener('click', () => {
        sidebarItems.forEach(item => {
            item.classList.remove('active');
        });
        sideItem.classList.add('active');
    });
});

tableRows.forEach(tableTr => {
    tableTr.addEventListener('click', () => {
        tableRows.forEach(item => {
            item.classList.remove('selected');
        });
        tableTr.classList.add('selected');
    });
});

menuBtn.addEventListener('click', () => {
    if (!isMenuOpen) {
        leftSection.style.left = '0';
    } else {
        leftSection.style.left = '-160px';
    }
    isMenuOpen = !isMenuOpen;
});

function toggleDropdown() {
    var dropdown = document.getElementById('profileDropdown');
    // Alternar la visibilidad del menú desplegable
    dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
}

// Aseguramos que el manejador de eventos se ejecute después de que la página haya cargado completamente
document.addEventListener('DOMContentLoaded', function() {
    var dropdowns = document.querySelectorAll('.profile-dropdown');

    // Función para cerrar el menú si se hace clic fuera de él
    window.onclick = function(event) {
        // Verificar si el clic fue fuera de cualquier área del perfil y del menú desplegable
        if (!event.target.closest('.profile') && !event.target.closest('.profile-dropdown')) {
            // Iterar sobre cada menú desplegable para ocultarlo si está abierto
            dropdowns.forEach(function(dropdown) {
                if (dropdown.style.display === 'block') {
                    dropdown.style.display = 'none';
                }
            });
        }
    };
});
