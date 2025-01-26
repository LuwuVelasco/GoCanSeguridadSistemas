export function loadMascotasTable(url, tbodySelector) {
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.estado === 'success') {
                const tbody = document.querySelector(tbodySelector);
                tbody.innerHTML = ''; // Limpiar la tabla antes de rellenarla

                data.mascotas.forEach(mascota => {
                    const tr = document.createElement('tr');

                    const tdCodigo = document.createElement('td');
                    tdCodigo.textContent = mascota.id_mascota;

                    const tdNombre = document.createElement('td');
                    tdNombre.textContent = mascota.nombre_mascota;

                    const tdFechaNacimiento = document.createElement('td');
                    tdFechaNacimiento.textContent = mascota.fecha_nacimiento || "Sin fecha registrada";

                    const tdTipo = document.createElement('td');
                    tdTipo.textContent = mascota.tipo;

                    const tdRaza = document.createElement('td');
                    tdRaza.textContent = mascota.raza;

                    const tdPropietario = document.createElement('td');
                    tdPropietario.textContent = mascota.nombre_propietario;

                    // Botón para editar
                    const tdEditar = document.createElement('td');
                    const btnEditar = document.createElement('button');
                    btnEditar.textContent = 'Editar';
                    btnEditar.addEventListener('click', () => {
                        openEditForm(mascota.id_mascota);
                    });
                    tdEditar.appendChild(btnEditar);

                    // Botón para eliminar
                    const tdEliminar = document.createElement('td');
                    const btnEliminar = document.createElement('button');
                    btnEliminar.textContent = 'Eliminar';
                    btnEliminar.addEventListener('click', () => {
                        if (confirm(`¿Seguro que deseas eliminar a ${mascota.nombre_mascota}?`)) {
                            deleteMascota(mascota.id_mascota, tr);
                        }
                    });
                    tdEliminar.appendChild(btnEliminar);

                    // Agregar celdas a la fila
                    tr.appendChild(tdCodigo);
                    tr.appendChild(tdNombre);
                    tr.appendChild(tdFechaNacimiento);
                    tr.appendChild(tdTipo);
                    tr.appendChild(tdRaza);
                    tr.appendChild(tdPropietario);
                    tr.appendChild(tdEditar);
                    tr.appendChild(tdEliminar);

                    // Agregar la fila al tbody
                    tbody.appendChild(tr);
                });
            } else {
                console.error('Error al obtener las mascotas:', data.mensaje);
                alert('Error al cargar las mascotas.');
            }
        })
        .catch(error => {
            console.error('Error al procesar la solicitud:', error);
            alert('Error al cargar las mascotas.');
        });
}

// Función para abrir el formulario de edición
function openEditForm(idMascota) {
    fetch(`http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_mascota.php?id_mascota=${idMascota}`)
        .then(response => response.json())
        .then(data => {
            if (data.estado === 'success') {
                const mascota = data.mascota;

                // Llenar los campos del formulario
                document.getElementById('edit_id_mascota').value = mascota.id_mascota;
                document.getElementById('edit_nombre_mascota').value = mascota.nombre_mascota;
                document.getElementById('edit_fecha_nacimiento').value = mascota.fecha_nacimiento;
                document.getElementById('edit_tipo').value = mascota.tipo;
                document.getElementById('edit_raza').value = mascota.raza;
                document.getElementById('edit_nombre_propietario').value = mascota.nombre_propietario;

                // Abrir el modal
                openModal('editModal');
            } else {
                alert('Error al cargar los datos de la mascota: ' + data.mensaje);
            }
        })
        .catch(error => {
            console.error('Error al obtener la mascota:', error);
            alert('Error al obtener la mascota.');
        });
}

// Función para eliminar mascota
function deleteMascota(idMascota, rowElement) {
    fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/eliminar_mascota.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_mascota=${encodeURIComponent(idMascota)}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.estado === 'success') {
                alert('Mascota eliminada exitosamente');
                rowElement.remove(); // Eliminar la fila de la tabla
            } else {
                alert('Error al eliminar la mascota: ' + data.mensaje);
            }
        })
        .catch(error => {
            console.error('Error al procesar la solicitud:', error);
            alert('Error al eliminar la mascota.');
        });
}

// Función para enviar cambios en edición
document.getElementById('editForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/editar_mascota.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.estado === 'success') {
                alert('Mascota actualizada exitosamente');
                closeModal('editModal');
                // Recargar la tabla después de editar
                loadMascotasTable('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_mascotas.php', '#petTable tbody');
            } else {
                alert('Error al actualizar la mascota: ' + data.mensaje);
            }
        })
        .catch(error => {
            console.error('Error al procesar la solicitud:', error);
            alert('Error al actualizar la mascota.');
        });
});

document.getElementById('petForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const formData = new FormData(event.target);

    fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/registrar_mascota.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.estado === 'success') {
                alert('Mascota registrada exitosamente');
                closeModal('petModal');
                // Recargar la tabla para mostrar la nueva mascota
                loadMascotasTable('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_mascotas.php', '#petTable tbody');
            } else {
                alert('Error al registrar la mascota: ' + data.mensaje);
            }
        })
        .catch(error => {
            console.error('Error al registrar la mascota:', error);
            alert('Error al registrar la mascota.');
        });
});
