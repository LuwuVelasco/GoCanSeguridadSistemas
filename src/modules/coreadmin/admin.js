import { loadMascotasTable } from './modules/mascotas.js';
import { openModal, closeModal, setupModalCloseOnOutsideClick } from './modules/modals.js';
import { loadRatings } from './modules/ratings.js';
import { loadActivities } from './modules/actividades.js';
import { eliminarDoctor, loadData } from './modules/doctores.js';
import { loadDoctorReports } from './modules/reportes.js';

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

    // Cargar actividades y doctores
    loadActivities('http://localhost/GoCanSeguridadSistemas/src/modules/php/get_actividades.php', '#actividades-table tbody');
    loadData('http://localhost/GoCanSeguridadSistemas/src/modules/php/listadoctores.php', '#lista-veterinarios');

    // Configuración de botones
    document.getElementById('bt1').addEventListener('click', () => openModal('historyModal'));
    document.getElementById('bt2').addEventListener('click', () => openModal('reserveModal'));
    document.getElementById('bt3').addEventListener('click', () => openModal('tablaModal'));

    window.openModal = openModal;
    window.closeModal = closeModal;
});
