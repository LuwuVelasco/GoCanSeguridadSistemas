document.addEventListener('DOMContentLoaded', function() {
    const sidebarItems = document.querySelectorAll('.sidebar .item');
    const tableRows = document.querySelectorAll('.main table tbody tr');
    const modal = document.getElementById('reserveModal');
    const closeModalButton = modal.querySelector('.close');
    const form = document.getElementById('registroV');

    // Función para abrir el modal
    window.openModal = function() {
        document.getElementById('reserveModal').style.display = 'block';
    }

    window.closeModal = function() {
        document.getElementById('reserveModal').style.display = 'none';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('reserveModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    // Evento para abrir el modal al hacer clic en el botón de registro de veterinarios
    document.getElementById('bt3').addEventListener('click', openModal);

    // Evento para cerrar el modal al hacer clic en la 'X'
    closeModalButton.addEventListener('click', closeModal);

    const registroV = document.getElementById('registroV');
    registroV.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(registroV);
        console.log(Array.from(formData.entries())); // Esto mostrará todos los valores del formulario
        const data = new URLSearchParams(formData);
        
        fetch('http://localhost/GoCan/src/modules/php/registrar_veterinario.php', {
            method: 'POST',
            body: data
        })
        .then(response => response.text())  // Cambiado a .text() para ver la respuesta cruda
        .then(text => {
            console.log("Respuesta cruda del servidor:", text);  // Muestra la respuesta completa en la consola
            const data = JSON.parse(text); // Intenta parsear el texto como JSON
            console.log(data);
        })
        .catch(error => {
            console.error('Error:', error);
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
    function toggleEstado(element) {
        const estados = ["Activo", "Inactivo", "Con Permiso"];
        const clases = ["estado-activo", "estado-inactivo", "estado-permiso"];
        let id = element.getAttribute('data-id');  // Suponiendo que cada span tiene un data-id
        let estadoActual = estados.indexOf(element.textContent);
        estadoActual = (estadoActual + 1) % estados.length; // Cambia al siguiente estado
    
        element.textContent = estados[estadoActual];
        element.className = "estado " + clases[estadoActual];
    
        // Enviar el nuevo estado al servidor
        fetch('http://localhost/GoCan/src/modules/php/actualizar_estado.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `estado=${encodeURIComponent(estados[estadoActual])}&id=${encodeURIComponent(id)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Estado actualizado con éxito');
            } else {
                console.error('Error al actualizar el estado:', data.message);
            }
        })
        .catch(error => console.error('Error en la red:', error));
    }
    
    
    
    function getClassForEstado(estado) {
        switch (estado.toLowerCase()) {
            case 'activo':
                return 'estado-activo';
            case 'inactivo':
                return 'estado-inactivo';
            case 'con permiso':
                return 'estado-permiso';
            default:
                return '';
        }
    }
    
    // Carga dinámica de datos para actividades y doctores
    function loadData(url, tbodySelector) {
        fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector(tbodySelector);
            tbody.innerHTML = '';
            data.forEach(item => {
                const row = document.createElement('tr');
                const estadoSpan = document.createElement('span');
                estadoSpan.className = `estado ${getClassForEstado(item.estado)}`;
                estadoSpan.textContent = item.estado;
                estadoSpan.onclick = function() { toggleEstado(this); }; // Asignar evento aquí
    
                row.innerHTML = `
                    <td>${item.nombre}</td>
                    <td>${item.cargo}</td>
                    <td>${item.especialidad || 'No asignada'}</td>
                    
                `;
                row.appendChild(estadoSpan);
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
   // Función para cargar especialidades en el select
   /*function loadEspecialidades() {
    const selectEspecialidad = document.getElementById('especialidad');
    fetch('http://localhost/GoCan/src/modules/php/get_especialidades.php')
    .then(response => response.json())
    .then(especialidades => {
        especialidades.forEach(especialidad => {
            const option = document.createElement('option');
            option.value = especialidad.id_especialidad;
            option.textContent = especialidad.nombre_especialidad;
            selectEspecialidad.appendChild(option);
        });
    })
    .catch(error => console.error('Error al cargar especialidades:', error));
}*/

    //loadEspecialidades('http://localhost/GoCan/src/modules/php/get_especialidades.php,#especialidad-container')
    loadActivities('http://localhost/GoCan/src/modules/php/get_actividades.php', '#actividades-table tbody');
    loadData('http://localhost/GoCan/src/modules/php/listadoctores.php', '#lista-veterinarios');
});
