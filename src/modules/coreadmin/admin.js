import { loadMascotasTable } from '../components/mascotas.js';
import { openModal, closeModal, setupModalCloseOnOutsideClick } from '../components/modals.js';
import { loadRatings } from '../components/ratings.js';
import { loadActivities } from '../components/actividades.js';
import { eliminarDoctor, loadData, initFuncionarioForm } from '../components/funcionarios.js';
import { loadDoctorReports } from '../components/reportes.js';
import { loadEspecialidades, loadRolesFuncionario } from '../components/loadSelects.js';

document.addEventListener("DOMContentLoaded", () => {
    // Configuración de modales
    setupModalCloseOnOutsideClick();

    // Cargar calificaciones
    loadRatings();

    // Cargar la tabla de mascotas
    loadMascotasTable('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_mascotas.php', '#lista-mascotas');   

    // Cargar los reportes en el modal
    loadDoctorReports('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_reporte.php', '#reportHistory');

    document.getElementById('historySearchInput').addEventListener('input', function () {
        const searchText = this.value.toLowerCase();
        const reportes = document.querySelectorAll('#reportHistory .reporte');
    
        reportes.forEach(reporte => {
            const resumen = reporte.querySelector('.reporte-resumen').textContent.toLowerCase();
            reporte.style.display = resumen.includes(searchText) ? 'block' : 'none';
        });
    });
    
    // Configurar el formulario para registrar doctores
    initFuncionarioForm(
        "#registroFuncionario",
        "http://localhost/GoCanSeguridadSistemas/src/modules/php/cargar_especialidades.php"
    );

    // Cargar actividades y doctores
    loadActivities('http://localhost/GoCanSeguridadSistemas/src/modules/php/get_actividades.php', '#actividades-table tbody');
    loadData('http://localhost/GoCanSeguridadSistemas/src/modules/php/listadoctores.php', '#lista-veterinarios');
    
    const especialidadesUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/php/citas.php";
    loadEspecialidades(especialidadesUrl, "#especialidad");

    loadRolesFuncionario('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_roles.php', '#rol');

    // Configuración de botones
    document.getElementById('bt1').addEventListener('click', () => openModal('historyModal'));
    document.getElementById('bt2').addEventListener('click', () => openModal('reserveModal'));
    document.getElementById('bt3').addEventListener('click', () => openModal('tablaModal'));

    window.openModal = openModal;
    window.closeModal = closeModal;
});
