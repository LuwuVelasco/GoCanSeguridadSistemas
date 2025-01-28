import { openModal, closeModal, setupModalCloseOnOutsideClick } from '../components/modals.js';
import { loadLogUsuarios } from '../components/log_usuarios.js';
import { loadFuncionarios, initFuncionarioForm } from '../components/funcionarios.js';
import { loadEspecialidades, loadRolesFuncionario } from '../components/loadSelects.js';
import { initConfigPasswordForm } from '../components/configuracion_password.js';
import { loadRoles } from '../components/roles.js';

document.addEventListener("DOMContentLoaded", () => {
    const url = 'http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_logs_usuarios.php';
    // Selector del tbody de la tabla de log de usuarios
    const tbodySelector = '#log-usuarios-table tbody';

    const url2 = 'http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_roles.php';
    loadRoles(url2, '#rolesTable');

    document.querySelector('#addRoleButton').addEventListener('click', () => {
        loadNewRolePermissions('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_todos_los_permisos.php');
        openModal('addRoleModal');
    });

    // Cargar los logs de usuarios al cargar la página
    loadLogUsuarios(url, tbodySelector);

    // Configuración de modales
    setupModalCloseOnOutsideClick();

    // 2) Inicializar el formulario de configuración de contraseñas
    initConfigPasswordForm();
    
    // 6) Configurar el formulario para registrar doctores
    initFuncionarioForm(
        "#registroFuncionario",
        "http://localhost/GoCanSeguridadSistemas/src/modules/php/cargar_especialidades.php"
    );

    const funcionariosUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/php/listadoctores.php";
    loadFuncionarios(funcionariosUrl, "#lista-veterinarios");
    
    const especialidadesUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/php/citas.php";
    loadEspecialidades(especialidadesUrl, "#especialidad");

    loadRolesFuncionario('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_roles.php', '#rol');

    // Hacer disponibles las funciones de abrir/cerrar modal en el ámbito global
    window.openModal = openModal;
    window.closeModal = closeModal;
    document.querySelector('#addRoleForm').addEventListener('submit', function (event) {
        event.preventDefault();
    
        const roleName = document.querySelector('#roleName').value;
        const checkboxes = document.querySelectorAll('#newRolePermissionsTable tbody input[type="checkbox"]');
        const permissions = Array.from(checkboxes).map(checkbox => ({
            id_permiso: checkbox.getAttribute('data-permission-id'),
            habilitado: checkbox.checked,
        }));
    
        const url = 'http://localhost/GoCanSeguridadSistemas/src/modules/php/crear_rol.php';
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ nombre_rol: roleName, permisos: permissions }),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Rol creado con éxito.');
                    closeModal('addRoleModal');
                    loadRoles('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_roles.php', '#rolesTable');
                } else {
                    alert('Error al crear el rol.');
                }
            })
            .catch(error => {
                console.error('Error al crear el rol:', error);
                alert('Error al crear el rol. Revisa la consola para más detalles.');
            });
    });
    
});
