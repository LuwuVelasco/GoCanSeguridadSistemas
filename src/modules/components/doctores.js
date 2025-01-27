// Función para eliminar un doctor
export function eliminarDoctor(id_doctores) {
    if (confirm('¿Estás seguro de que deseas eliminar este doctor?')) {
        fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/eliminarDoctor.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ id: id_doctores })
        })
        .then(response => response.json())
        .then(data => {
            if (data.estado === "success") {
                alert(data.mensaje);
                loadData('http://localhost/GoCanSeguridadSistemas/src/modules/php/listadoctores.php', '#lista-veterinarios');
            } else {
                alert('Error al eliminar el doctor: ' + data.mensaje);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud.');
        });
    }
}

// Función para cargar datos de doctores en la tabla
export function loadData(url, tbodySelector) {
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector(tbodySelector);
            tbody.innerHTML = '';
            data.forEach(item => {
                const especialidad = item.especialidad || "Sin especialidad"; // Manejo de undefined
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.id_doctores}</td>
                    <td>${item.nombre}</td>
                    <td>${especialidad}</td>
                    <td>
                        <button class="delete-button" onclick="eliminarDoctor(${item.id_doctores})">
                            <i class="ri-delete-bin-6-line"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error al cargar los datos:', error);
            alert('Error al cargar los datos. Verifica la consola para más detalles.');
        });
}

export function registrarDoctor(formSelector, url, refreshUrl, tbodySelector) {
    const form = document.querySelector(formSelector);
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(form);

        fetch(url, {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.estado === "success") {
                alert(data.mensaje);
                closeModal('doctorModal'); // Cerrar el modal
                loadData(refreshUrl, tbodySelector); // Actualizar la tabla de doctores
            } else {
                alert('Error al registrar el doctor: ' + data.mensaje);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al registrar el doctor.');
        });
    });
}