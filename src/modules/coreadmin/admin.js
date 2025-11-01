// Admin.js
import { openModal, closeModal, setupModalCloseOnOutsideClick } from '../components/modals.js';
import { loadLogUsuarios } from '../components/log_usuarios.js';
import { loadFuncionarios, initFuncionarioForm } from '../components/funcionarios.js';
import { loadEspecialidades, loadRolesFuncionario } from '../components/loadSelects.js';
import { initConfigPasswordForm } from '../components/configuracion_password.js';
import { loadRoles } from '../components/roles.js';
import { loadLogAplicacion } from '../components/log_aplicacion.js';

document.addEventListener("DOMContentLoaded", () => {
  const urlLogUsuarios = 'http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_logs_usuarios.php';
  const tbodyLogUsuarios = '#log-usuarios-table tbody';

  const urlRoles = 'http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_roles.php';
  loadRoles(urlRoles, '#rolesTable');

  // Logs de usuarios (inicial)
  loadLogUsuarios(urlLogUsuarios, tbodyLogUsuarios);

  // === Log de aplicación ===
  const URL_LOG_APP = 'http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_log_aplicacion.php';
  const TBY_LOG_APP = '#log-aplicacion-table tbody';

  // Función centralizada para refrescar (con cache-buster)
  function refreshLogAplicacion() {
    loadLogAplicacion(`${URL_LOG_APP}?t=${Date.now()}`, TBY_LOG_APP);
  }

  // Carga inicial
  refreshLogAplicacion();

  // Suscríbete a un evento global que dispararemos después de cada cambio
  window.addEventListener('log:aplicacion:changed', refreshLogAplicacion);

  // === Resto de inicializaciones ===
  setupModalCloseOnOutsideClick();
  initConfigPasswordForm();

  initFuncionarioForm(
    "#registroFuncionario",
    "http://localhost/GoCanSeguridadSistemas/src/modules/php/cargar_especialidades.php"
  );

  const funcionariosUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/php/listadoctores.php";
  loadFuncionarios(funcionariosUrl, "#lista-veterinarios");

  const especialidadesUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/php/citas.php";
  loadEspecialidades(especialidadesUrl, "#especialidad");

  loadRolesFuncionario('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_roles.php', '#rol');

  window.openModal = openModal;
  window.closeModal = closeModal;

  document.querySelector('#addRoleButton').addEventListener('click', () => {
    loadNewRolePermissions('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_todos_los_permisos.php');
    openModal('addRoleModal');
  });

  // Crear rol => refrescar log al terminar
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
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ nombre_rol: roleName, permisos: permissions }),
    })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          alert('Rol creado con éxito.');
          closeModal('addRoleModal');
          loadRoles(urlRoles, '#rolesTable');

          // 🔔 Refrescar tabla del log de aplicación
          refreshLogAplicacion();

          // (opcional) emitir el evento global por si otras pantallas lo usan
          window.dispatchEvent(new CustomEvent('log:aplicacion:changed'));
        } else {
          alert('Error al crear el rol.');
        }
      })
      .catch(err => {
        console.error('Error al crear el rol:', err);
        alert('Error al crear el rol. Revisa la consola para más detalles.');
      });
  });
});
