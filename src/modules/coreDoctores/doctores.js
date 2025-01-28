import { loadMascotasTable } from "../components/mascotasEdit.js";
import { loadDoctorReports } from "../components/reportes.js";
import { loadDoctorCitas, deleteDoctorCita } from "../components/citas.js";
import { openModal, closeModal, setupModalCloseOnOutsideClick } from "../components/modals.js";
import { loadClientes } from "../components/loadClientes.js";

document.addEventListener("DOMContentLoaded", () => {
    const id_doctor = localStorage.getItem("id_doctores");

    loadClientes();

    // Configuración de modales
    setupModalCloseOnOutsideClick();

    // Cargar citas del doctor
    const citasTableBody = "table tbody";
    loadDoctorCitas(
        `http://localhost/GoCanSeguridadSistemas/src/modules/php/doctores.php?id_doctor=${id_doctor}`,
        citasTableBody
    );

    document.getElementById("searchInput").addEventListener("input", function () {
        const searchText = this.value.toLowerCase();
        loadDoctorCitas(
            `http://localhost/GoCanSeguridadSistemas/src/modules/php/doctores.php?id_doctor=${id_doctor}`,
            citasTableBody,
            searchText
        );
    });

    // Configurar botones de acción
    document.getElementById("bt2").addEventListener("click", () => {
        loadDoctorReports(
            "http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_reporte.php",
            "#reportHistory"
        );
        openModal("historyModal");
    });

    document.getElementById("bt3").addEventListener("click", () => {
        loadMascotasTable(
            "http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_mascotas.php",
            "#petTable tbody"
        );
        openModal("tablaModal");
    });

    // Exponer funciones de modales globalmente
    window.openModal = openModal;
    window.closeModal = closeModal;
});
