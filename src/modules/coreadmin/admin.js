document.addEventListener('DOMContentLoaded', function() {
    const sidebarItems = document.querySelectorAll('.sidebar .item');
    const tableRows = document.querySelectorAll('.main table tbody tr');
    const modal = document.getElementById('reserveModal');
    const closeModalButton = modal.querySelector('.close');
    const form = document.getElementById('registroV');

    // Función para abrir el modal
    function openModal() {
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
    }

    // Función para cerrar el modal
    function closeModal() {
        modal.style.display = 'none';
    }

    // Evento para abrir el modal al hacer clic en el botón de registro de veterinarios
    document.getElementById('bt3').addEventListener('click', openModal);

    // Evento para cerrar el modal al hacer clic en la 'X'
    closeModalButton.addEventListener('click', closeModal);

    // Manejador del evento submit para el formulario
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);

        fetch('http://localhost/GoCan/src/modules/php/registrar_veterinario.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.estado === 'success') {
                alert(data.mensaje);
                closeModal();
                location.reload();
            } else {
                throw new Error(data.mensaje);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Ha ocurrido un error al intentar registrar: " + error.message);
        });
    });

    // Función para cerrar el modal si se hace clic fuera de él
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    };

    // Función para alternar la visibilidad del dropdown del perfil
    function toggleDropdown() {
        var dropdown = document.getElementById('profileDropdown');
        dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
    }

    // Evento para el dropdown del perfil
    document.querySelector('.profile').addEventListener('click', toggleDropdown);

    // Eventos de clic para elementos de la barra lateral
    sidebarItems.forEach(item => {
        item.addEventListener('click', () => {
            sidebarItems.forEach(innerItem => innerItem.classList.remove('active'));
            item.classList.add('active');
        });
    });

    // Eventos de selección para filas de la tabla
    tableRows.forEach(row => {
        row.addEventListener('click', () => {
            tableRows.forEach(innerRow => innerRow.classList.remove('selected'));
            row.classList.add('selected');
        });
    });

    // Carga dinámica de datos para actividades y doctores
    function loadData(url, tbodySelector) {
        fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector(tbodySelector);
            tbody.innerHTML = ''; // Limpia la tabla antes de añadir nuevos datos
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.nombre}</td>
                    <td>${item.cargo}</td>
                    <td>${item.especialidad || 'No asignada'}</td>
                    <td>${item.estado}</td>

                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error al cargar los datos:', error);
            alert('Error al cargar los datos. Verifica la consola para más detalles.');
        });
    }
    function loadActivities(url, tbodySelector) {
        fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector(tbodySelector);
            tbody.innerHTML = ''; // Limpia la tabla antes de añadir nuevos datos
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.hora_ingreso}</td>
                    <td>${item.nombre_usuario}</td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error al cargar los datos:', error);
            alert('Error al cargar los datos. Verifica la consola para más detalles.');
        });
    }
    

    loadActivities('http://localhost/GoCan/src/modules/php/get_actividades.php', '#actividades-table tbody');
    loadData('http://localhost/GoCan/src/modules/php/listadoctores.php', '#lista-veterinarios');
});
