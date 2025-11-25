// Cargar roles en el modal principal
export function loadRoles(url, tableSelector) {
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector(`${tableSelector} tbody`);
            tableBody.innerHTML = ''; // Limpiar tabla

            if (!Array.isArray(data) || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="2">No hay roles disponibles.</td></tr>';
                return;
            }

            data.forEach(role => {
                // Asegúrate de que los valores existen
                const idRol = role.id_rol || 'N/A';
                const nombreRol = role.nombre_rol || 'Sin nombre';

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${idRol}</td>
                    <td>${nombreRol}</td>
                    <td><button type="editar" onclick="openEditPermissionsModal(${idRol})">Editar</button></td>
                    <td><button type="eliminar" onclick="deleteRole(${idRol})">Eliminar</button>                    </td>
                `;
                tableBody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error al cargar roles:', error);
            alert('Error al cargar roles. Revisa la consola para más detalles.');
        });
}

// Abrir modal para editar permisos de un rol
function openEditPermissionsModal(roleId) {
    if (!roleId || roleId === 'N/A') {
        alert('ID de rol no válido.');
        return;
    }

    // Guardar el ID del rol en el formulario para poder usarlo después
    document.querySelector('#permissionsForm').setAttribute('data-role-id', roleId);

    const url = `http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_permisos.php?id_rol=${roleId}`;
    const permissionsTable = document.querySelector('#permissionsTable tbody');

    permissionsTable.innerHTML = '<tr><td colspan="2">Cargando permisos...</td></tr>';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            permissionsTable.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
                permissionsTable.innerHTML = '<tr><td colspan="2">No hay permisos disponibles.</td></tr>';
                return;
            }

            // Renderizar los permisos correctamente
            data.forEach(permission => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${permission.permiso.replace(/_/g, ' ')}</td>
                    <td>
                        <input type="checkbox" data-permission-id="${permission.permiso}" ${permission.habilitado ? 'checked' : ''}>
                    </td>
                `;
                permissionsTable.appendChild(row);
            });

            // Abrir el modal
            openModal('editPermissionsModal');
        })
        .catch(error => {
            console.error('Error al cargar permisos:', error);
            alert('Error al cargar permisos. Revisa la consola para más detalles.');
        });
}

// Hacer disponible globalmente
window.openEditPermissionsModal = openEditPermissionsModal;

// Guardar cambios en permisos
document.querySelector('#permissionsForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const roleId = document.querySelector('#permissionsForm').getAttribute('data-role-id'); // Obtener ID del rol
    if (!roleId) {
        alert('Error: No se encontró el ID del rol.');
        return;
    }

    const checkboxes = document.querySelectorAll('#permissionsTable tbody input[type="checkbox"]');
    const permissions = Array.from(checkboxes).map(checkbox => ({
        permiso: checkbox.getAttribute('data-permission-id'),
        habilitado: checkbox.checked
    }));

    const url = 'http://localhost/GoCanSeguridadSistemas/src/modules/php/actualizar_permisos.php';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id_rol: roleId, permisos: permissions }) // Se incluye `id_rol`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Permisos actualizados con éxito.');
            closeModal('editPermissionsModal');

            // 🔔 Refrescar log de aplicación
            window.dispatchEvent(new CustomEvent('log:aplicacion:changed'));
        } else {
            alert('Error al actualizar permisos: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error al actualizar permisos:', error);
        alert('Error al actualizar permisos. Revisa la consola para más detalles.');
    });
});

// Eliminar un rol con SweetAlert
function deleteRole(roleId) {
    if (!roleId) {
        alert('ID de rol no válido.');
        return;
    }

    Swal.fire({
        title: '¿Estás seguro?',
        text: "No podrás revertir esta acción.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = `http://localhost/GoCanSeguridadSistemas/src/modules/php/eliminar_rol.php?id_rol=${roleId}`;
            fetch(url, { method: 'DELETE' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Eliminado',
                            'El rol ha sido eliminado exitosamente.',
                            'success'
                        );
                        loadRoles('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_roles.php', '#rolesTable');

                        // 🔔 Refrescar log de aplicación
                        window.dispatchEvent(new CustomEvent('log:aplicacion:changed'));
                    } else {
                        Swal.fire(
                            'Error',
                            'Hubo un problema al intentar eliminar el rol.',
                            'error'
                        );
                    }
                })
                .catch(error => {
                    console.error('Error al eliminar rol:', error);
                    Swal.fire(
                        'Error',
                        'Ocurrió un error al eliminar el rol. Revisa la consola para más detalles.',
                        'error'
                    );
                });
        }
    });
}

// Hacer disponible globalmente
window.deleteRole = deleteRole;

// Cargar permisos en el modal de Añadir Rol
function loadNewRolePermissions(url) {
    const permissionsTable = document.querySelector('#newRolePermissionsTable tbody');
    permissionsTable.innerHTML = '<tr><td colspan="2">Cargando permisos...</td></tr>';

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error en la solicitud: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log("Permisos recibidos:", data); // Depuración
            permissionsTable.innerHTML = ''; // Limpiar tabla

            if (!Array.isArray(data) || data.length === 0) {
                permissionsTable.innerHTML = '<tr><td colspan="2">No hay permisos disponibles.</td></tr>';
                return;
            }

            // Crear filas con permisos
            data.forEach(permission => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${permission.permiso}</td>
                    <td>
                        <input type="checkbox" data-permission-id="${permission.permiso}">
                    </td>
                `;
                permissionsTable.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error al cargar permisos:', error);
            permissionsTable.innerHTML = '<tr><td colspan="2">Error al cargar permisos.</td></tr>';
        });
}

window.loadNewRolePermissions = loadNewRolePermissions;