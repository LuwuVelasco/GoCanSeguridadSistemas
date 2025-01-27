import { openModal, closeModal, setupModalCloseOnOutsideClick } from '../components/modals.js';
import { loadLogUsuarios } from '../components/log_usuarios.js';
import { loadData, initFuncionarioForm } from '../components/funcionarios.js';
import { loadEspecialidades, loadRolesFuncionario } from '../components/loadSelects.js';

document.addEventListener("DOMContentLoaded", () => {
    const url = 'http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_logs_usuarios.php';
    // Selector del tbody de la tabla de log de usuarios
    const tbodySelector = '#log-usuarios-table tbody';

    // Cargar los logs de usuarios al cargar la página
    loadLogUsuarios(url, tbodySelector);

    // Configuración de modales
    setupModalCloseOnOutsideClick();
    
    // Configurar el formulario para registrar doctores
    initFuncionarioForm(
        "#registroFuncionario",
        "http://localhost/GoCanSeguridadSistemas/src/modules/php/cargar_especialidades.php"
    );

    loadData('http://localhost/GoCanSeguridadSistemas/src/modules/php/listadoctores.php', '#lista-veterinarios');
    
    const especialidadesUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/php/citas.php";
    loadEspecialidades(especialidadesUrl, "#especialidad");

    loadRolesFuncionario('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_roles.php', '#rol');

    // Configuración de botones
    document.getElementById('bt1').addEventListener('click', () => openModal('reserveModal'));

    window.openModal = openModal;
    window.closeModal = closeModal;
});
