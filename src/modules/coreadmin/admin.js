import { loadMascotasTable } from '../components/mascotas.js';
import { openModal, closeModal, setupModalCloseOnOutsideClick } from '../components/modals.js';
import { loadRatings } from '../components/ratings.js';
import { loadActivities } from '../components/actividades.js';
import { eliminarDoctor, loadData, initFuncionarioForm } from '../components/funcionarios.js';
import { loadDoctorReports } from '../components/reportes.js';
import { loadEspecialidades, loadRolesFuncionario } from '../components/loadSelects.js';
import { initConfigPasswordForm } from '../components/configuracion_password.js';

document.addEventListener("DOMContentLoaded", () => {
    // 1) Configuración de modales
    setupModalCloseOnOutsideClick();

    // 2) Inicializar el formulario de configuración de contraseñas
    initConfigPasswordForm();

    // 3) Cargar calificaciones
    loadRatings();

    // 4) Cargar la tabla de mascotas
    loadMascotasTable('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_mascotas.php', '#lista-mascotas');   

    // 5) Cargar los reportes en el modal
    loadDoctorReports('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_reporte.php', '#reportHistory');

    document.getElementById('historySearchInput').addEventListener('input', function () {
        const searchText = this.value.toLowerCase();
        const reportes = document.querySelectorAll('#reportHistory .reporte');
    
        reportes.forEach(reporte => {
            const resumen = reporte.querySelector('.reporte-resumen').textContent.toLowerCase();
            reporte.style.display = resumen.includes(searchText) ? 'block' : 'none';
        });
    });
    
    // 6) Configurar el formulario para registrar doctores
    initFuncionarioForm(
        "#registroFuncionario",
        "http://localhost/GoCanSeguridadSistemas/src/modules/php/cargar_especialidades.php"
    );

    // 7) Cargar actividades y doctores
    loadActivities('http://localhost/GoCanSeguridadSistemas/src/modules/php/get_actividades.php', '#actividades-table tbody');
    loadData('http://localhost/GoCanSeguridadSistemas/src/modules/php/listadoctores.php', '#lista-veterinarios');
    
    const especialidadesUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/php/citas.php";
    loadEspecialidades(especialidadesUrl, "#especialidad");

    loadRolesFuncionario('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_roles.php', '#rol');

    // 8) Configuración de botones
    document.getElementById('bt1').addEventListener('click', () => openModal('historyModal'));
    document.getElementById('bt2').addEventListener('click', () => openModal('reserveModal'));
    document.getElementById('bt3').addEventListener('click', () => openModal('tablaModal'));

    // Hacer disponibles las funciones de abrir/cerrar modal en el ámbito global
    window.openModal = openModal;
    window.closeModal = closeModal;
});
