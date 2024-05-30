const sidebarItems = document.querySelectorAll('.sidebar .item');
const tableRows = document.querySelectorAll('.main table tbody tr');

const menuBtn = document.getElementById('menu-btn');
const leftSection = document.querySelector('.left-section');

let isMenuOpen = false;

document.getElementById('reserveForm').addEventListener('submit', function(event) {
    event.preventDefault();  // Previene el envío normal del formulario.

    const formData = new FormData(this);  // Recolecta los datos del formulario.

    // Realiza la petición al servidor
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())  // Convierte la respuesta a texto.
    .then(text => {
        alert(text);  // Muestra la respuesta como una alerta del navegador.
        closeModal();  // Cierra el modal después de mostrar la alerta.
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Ha ocurrido un error al intentar registrar.");  // Muestra un mensaje de error si la petición falla.
    });
});




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

function openModal() {
    var modal = document.getElementById('reserveModal');
    modal.style.display = 'block';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    modal.style.display = 'flex';
}

function closeModal() {
    var modal = document.getElementById('reserveModal');
    modal.style.display = 'none';
}
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('#actividades-table tbody');
    if (!tableBody) {
        console.error('No se encontró el cuerpo de la tabla en el documento.');
        return;
    }

    fetch('http://localhost/GoCan/src/modules/php/get_actividades.php')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        tableBody.innerHTML = ''; // Limpia la tabla antes de añadir nuevos datos
        data.forEach(actividad => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${actividad.id_actividad}</td>
                <td>${actividad.id_usuario}</td>
                <td>${actividad.hora_ingreso}</td>
                <td>${actividad.nombre_usuario || 'Sin nombre'}</td>
            `;
            tableBody.appendChild(row);
        });
    })
    .catch(error => {
        console.error('Error al cargar los datos:', error);
        alert('Error al cargar las actividades. Verifica la consola para más detalles.');
    });
});
