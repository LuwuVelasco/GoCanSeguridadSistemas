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

            data.forEach(permission => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${permission.permiso}</td>
                    <td>
                        <input type="checkbox" data-permission-id="${permission.id_permiso}" ${permission.habilitado ? 'checked' : ''}>
                    </td>
                `;
                permissionsTable.appendChild(row);
            });

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

    const checkboxes = document.querySelectorAll('#permissionsTable tbody input[type="checkbox"]');
    const permissions = Array.from(checkboxes).map(checkbox => ({
        id_permiso: checkbox.getAttribute('data-permission-id'),
        habilitado: checkbox.checked,
    }));

    const url = 'http://localhost/GoCanSeguridadSistemas/src/modules/php/actualizar_permisos.php';
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ permisos: permissions }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Permisos actualizados con éxito.');
                closeModal('editPermissionsModal');
            } else {
                alert('Error al actualizar permisos.');
            }
        })
        .catch(error => {
            console.error('Error al actualizar permisos:', error);
            alert('Error al actualizar permisos. Revisa la consola para más detalles.');
        });
});

// Eliminar un rol
function deleteRole(roleId) {
    const confirmDelete = confirm('¿Estás seguro de que quieres eliminar este rol?');
    if (!confirmDelete) return;

    const url = `http://localhost/GoCanSeguridadSistemas/src/modules/php/eliminar_rol.php?id_rol=${roleId}`;
    fetch(url, { method: 'DELETE' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Rol eliminado con éxito.');
                loadRoles('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_roles.php', '#rolesTable');
            } else {
                alert('Error al eliminar rol.');
            }
        })
        .catch(error => {
            console.error('Error al eliminar rol:', error);
            alert('Error al eliminar rol. Revisa la consola para más detalles.');
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