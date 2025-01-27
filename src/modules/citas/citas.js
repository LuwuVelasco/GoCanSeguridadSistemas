import { loadEspecialidades, loadDoctores } from '../components/loadSelects.js';
import { registrarCita } from '../components/citasRegistro.js';
import { openModal, closeModal, setupModalCloseOnOutsideClick } from '../components/modals.js';
import { cargarCitas } from '../components/cargarCitas.js';
import { eliminarCita } from '../components/eliminarCitas.js';

document.addEventListener("DOMContentLoaded", () => {
    const propietarioInput = document.getElementById("propietario");
    const idUsuario = localStorage.getItem("id_usuario");

    if (!idUsuario) {
        console.error("No se encontró el ID del usuario en localStorage.");
        return;
    }

    // Obtener el nombre del propietario desde el backend
    fetch("http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_usuario.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id_usuario: idUsuario }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.estado === "success") {
                propietarioInput.value = data.nombre; // Prellenar el campo con el nombre del usuario
            } else {
                console.error("Error al obtener el nombre del usuario:", data.mensaje);
            }
        })
        .catch(error => {
            console.error("Error en la solicitud:", error);
        });

    const especialidadSelect = document.querySelector("#especialidad");
    const doctorContainer = document.querySelector("#doctorContainer");
    const doctorSelect = document.querySelector("#doctor");
    const reservarButton = document.getElementById("reservar"); // Agregado correctamente

    const especialidadesUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/php/citas.php";
    const doctoresUrlBase = "http://localhost/GoCanSeguridadSistemas/src/modules/php/citas.php?especialidad_id=";

    console.log("Cargando URL de especialidades:", especialidadesUrl);
    console.log("Cargando URL base de doctores:", doctoresUrlBase);
    cargarCitas();

    // Cargar especialidades
    loadEspecialidades(especialidadesUrl, "#especialidad");

    document.getElementById('bt0').addEventListener('click', () => {openModal('reserveModal'); cargarCitas();});
    document.getElementById('bt1').addEventListener('click', () => {openModal('viewReservationsModal'); cargarCitas();});    

    setupModalCloseOnOutsideClick();

    window.openModal = openModal;
    window.closeModal = closeModal;
    window.eliminarCita = eliminarCita;

    function renderCitas(citas) {
        const reservationsList = document.getElementById("reservationsList");
        reservationsList.innerHTML = ""; // Limpiar contenido previo
        citas.forEach(cita => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${cita.propietario}</td>
                <td>${cita.servicio}</td>
                <td>${cita.fecha}</td>
                <td>${cita.horario}</td>
                <td><button class="delete-btn" data-id="${cita.id_cita}">Eliminar</button></td>
            `;
            reservationsList.appendChild(row);
        });
    
        // Asignar eventos a los botones de eliminación
        document.querySelectorAll(".delete-btn").forEach(button => {
            button.addEventListener("click", (e) => {
                const idCita = e.target.dataset.id;
                eliminarCita(idCita); // Asegúrate de que eliminarCita esté definida aquí
            });
        });
    }
    

    // Evento al cambiar la especialidad
    especialidadSelect.addEventListener("change", (event) => {
      const especialidadId = event.target.value.trim();
  
      if (especialidadId && !isNaN(especialidadId)) {
          // Mostrar el contenedor de doctores
          doctorContainer.style.display = "block";
  
          // Cargar doctores de la especialidad seleccionada
          const doctoresUrl = `${doctoresUrlBase}${especialidadId}`;
          console.log("Cargando doctores desde URL:", doctoresUrl); // Depuración
          loadDoctores(doctoresUrl, "#doctor");
      } else {
          // Ocultar el contenedor de doctores si no hay una especialidad válida
          doctorContainer.style.display = "none";
          doctorSelect.innerHTML = '<option value="">Seleccionar doctor...</option>';
      }
    });

    // Registrar cita al hacer clic en el botón
    reservarButton.addEventListener("click", (event) => {
        const fecha = document.getElementById("fecha").value;
        const hora = document.getElementById("hora").value;
        const now = new Date();
        const fechaHoraIngresada = new Date(`${fecha}T${hora}`);

        if (fechaHoraIngresada <= now) {
            alert("Solo se pueden registrar citas en fechas y horas futuras.");
            return;
        }

        event.preventDefault();
        const propietario = document.getElementById("propietario").value;
        const especialidadId = especialidadSelect.value;
        const especialidadNombre = especialidadSelect.options[especialidadSelect.selectedIndex].textContent;
        const doctor = doctorSelect.value;
        const idUsuario = localStorage.getItem("id_usuario");

        // Validar datos antes de enviar
        if (!propietario || !especialidadId || !doctor || !fecha || !hora) {
            alert("Por favor, completa todos los campos.");
            return;
        }

        const citaData = { propietario, especialidadId, especialidadNombre, doctor, fecha, horario: hora, id_usuario: idUsuario };
        registrarCita(especialidadesUrl, citaData);
        cargarCitas();
        closeModal("reserveModal");
    });
});
