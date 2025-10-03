const API_BASE = 'http://localhost/GoCanSeguridadSistemas/src/modules/php';
const api = (p) => `${API_BASE}/${p}`;

// ====== Utils ======
async function toJSONSafe(resp) {
  const text = await resp.text();
  try { return JSON.parse(text); }
  catch {
    console.error('Respuesta no JSON:', text);
    throw new Error('Respuesta del servidor no válida.');
  }
}

function swSuccess(title = 'Éxito', text = '') {
  return Swal.fire({ icon: 'success', title, text });
}
function swError(title = 'Error', text = 'Ocurrió un problema') {
  return Swal.fire({ icon: 'error', title, text });
}
function swWarn(title = 'Atención', text = '') {
  return Swal.fire({ icon: 'warning', title, text });
}

// ====== Tabla de mascotas ======
export function loadMascotasTable(url, tbodySelector) {
  fetch(url)
    .then(toJSONSafe)
    .then((data) => {
      if (data.estado === 'success') {
        const tbody = document.querySelector(tbodySelector);
        if (!tbody) return;
        tbody.innerHTML = '';

        data.mascotas.forEach((mascota) => {
          const tr = document.createElement('tr');

          const tdCodigo = document.createElement('td');
          tdCodigo.textContent = mascota.id_mascota;

          const tdNombre = document.createElement('td');
          tdNombre.textContent = mascota.nombre_mascota;

          const tdFechaNacimiento = document.createElement('td');
          tdFechaNacimiento.textContent = mascota.fecha_nacimiento || 'Sin fecha registrada';

          const tdTipo = document.createElement('td');
          tdTipo.textContent = mascota.tipo;

          const tdRaza = document.createElement('td');
          tdRaza.textContent = mascota.raza;

          const tdPropietario = document.createElement('td');
          tdPropietario.textContent = mascota.nombre_propietario;

          // Botón editar
          const tdEditar = document.createElement('td');
          const btnEditar = document.createElement('button');
          btnEditar.textContent = 'Editar';
          btnEditar.type = 'button';
          btnEditar.className = 'btn btn-sm btn-primary';
          btnEditar.addEventListener('click', () => openEditForm(mascota.id_mascota));
          tdEditar.appendChild(btnEditar);

          // Botón eliminar
          const tdEliminar = document.createElement('td');
          const btnEliminar = document.createElement('button');
          btnEliminar.textContent = 'Eliminar';
          btnEliminar.type = 'button';
          btnEliminar.className = 'btn btn-sm btn-danger';
          btnEliminar.addEventListener('click', async () => {
            const res = await Swal.fire({
              icon: 'warning',
              title: '¿Eliminar mascota?',
              text: `Se eliminará "${mascota.nombre_mascota}". Esta acción no se puede deshacer.`,
              showCancelButton: true,
              confirmButtonText: 'Sí, eliminar',
              cancelButtonText: 'Cancelar'
            });
            if (res.isConfirmed) {
              deleteMascota(mascota.id_mascota, tr);
            }
          });
          tdEliminar.appendChild(btnEliminar);

          tr.appendChild(tdCodigo);
          tr.appendChild(tdNombre);
          tr.appendChild(tdFechaNacimiento);
          tr.appendChild(tdTipo);
          tr.appendChild(tdRaza);
          tr.appendChild(tdPropietario);
          tr.appendChild(tdEditar);
          tr.appendChild(tdEliminar);

          tbody.appendChild(tr);
        });
      } else {
        console.error('Error al obtener mascotas:', data.mensaje);
        swError('Error', 'No se pudieron cargar las mascotas.');
      }
    })
    .catch((err) => {
      console.error('Error al procesar la solicitud:', err);
      swError('Error', 'No se pudieron cargar las mascotas.');
    });
}

// ====== Abrir formulario de edición ======
function openEditForm(idMascota) {
  fetch(api(`obtener_mascota.php?id_mascota=${encodeURIComponent(idMascota)}`))
    .then(toJSONSafe)
    .then((data) => {
      if (data.estado === 'success') {
        const m = data.mascota;
        document.getElementById('edit_id_mascota').value = m.id_mascota;
        document.getElementById('edit_nombre_mascota').value = m.nombre_mascota;
        document.getElementById('edit_fecha_nacimiento').value = m.fecha_nacimiento;
        document.getElementById('edit_tipo').value = m.tipo;
        document.getElementById('edit_raza').value = m.raza;
        document.getElementById('edit_nombre_propietario').value = m.nombre_propietario;
        // Tu función global que abre el modal
        openModal('editModal');
      } else {
        swError('Error', data.mensaje || 'No se pudo cargar la mascota.');
      }
    })
    .catch((err) => {
      console.error('Error al obtener la mascota:', err);
      swError('Error', 'No se pudo obtener la mascota.');
    });
}

// ====== Eliminar mascota ======
function deleteMascota(idMascota, rowElement) {
  fetch(api('eliminar_mascota.php'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id_mascota=${encodeURIComponent(idMascota)}`
  })
    .then(toJSONSafe)
    .then((data) => {
      if (data.estado === 'success') {
        swSuccess('Eliminado', 'La mascota fue eliminada correctamente.');
        rowElement?.remove();
      } else {
        swError('Error', data.mensaje || 'No se pudo eliminar la mascota.');
      }
    })
    .catch((err) => {
      console.error('Error al eliminar la mascota:', err);
      swError('Error', 'No se pudo eliminar la mascota.');
    });
}

// ====== Envío de edición ======
const editFormEl = document.getElementById('editForm');
if (editFormEl) {
  editFormEl.addEventListener('submit', (event) => {
    event.preventDefault();
    const formData = new FormData(event.target);

    fetch(api('editar_mascota.php'), { method: 'POST', body: formData })
      .then(toJSONSafe)
      .then((data) => {
        if (data.estado === 'success') {
          swSuccess('Actualizado', 'Mascota actualizada exitosamente.');
          closeModal('editModal');
          loadMascotasTable(api('obtener_mascotas.php'), '#petTable tbody');
        } else {
          swError('Error', data.mensaje || 'No se pudo actualizar la mascota.');
        }
      })
      .catch((err) => {
        console.error('Error al actualizar la mascota:', err);
        swError('Error', 'No se pudo actualizar la mascota.');
      });
  });
}

// ====== Envío de registro ======
const petFormEl = document.getElementById('petForm');
if (petFormEl) {
  petFormEl.addEventListener('submit', (event) => {
    event.preventDefault();
    const formData = new FormData(event.target);

    fetch(api('registrar_mascota.php'), { method: 'POST', body: formData })
      .then(toJSONSafe)
      .then((data) => {
        if (data.estado === 'success') {
          swSuccess('Registrado', 'Mascota registrada exitosamente.');
          closeModal('petModal');
          loadMascotasTable(api('obtener_mascotas.php'), '#petTable tbody');
          petFormEl.reset();
          return;
        }

        // Mensaje específico cuando el usuario/propietario no coincide (del backend)
        const msg = (data.mensaje || '').toLowerCase();
        if (msg.includes('propietario no existe')) {
          swWarn('Usuario no encontrado', 'El nombre del propietario no coincide con ningún usuario.');
        } else if (msg.includes('fecha')) {
          swWarn('Fecha inválida', data.mensaje);
        } else {
          swError('Error', data.mensaje || 'No se pudo registrar la mascota.');
        }
      })
      .catch((err) => {
        console.error('Error al registrar la mascota:', err);
        swError('Error', 'No se pudo registrar la mascota.');
      });
  });
}