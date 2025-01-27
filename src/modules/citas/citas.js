import { loadEspecialidades, loadDoctores } from '../components/especialidades.js';
import { registrarCita } from '..components/citasRegistro.js';

document.addEventListener("DOMContentLoaded", () => {
    const especialidadSelect = document.querySelector("#especialidad");
    const doctorContainer = document.querySelector("#doctorContainer");
    const doctorSelect = document.querySelector("#doctor");
    const reservarButton = document.getElementById("reservar"); // Agregado correctamente

    const especialidadesUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/php/citas.php";
    const doctoresUrlBase = "http://localhost/GoCanSeguridadSistemas/src/modules/php/citas.php?especialidad_id=";

    console.log("Cargando URL de especialidades:", especialidadesUrl);
    console.log("Cargando URL base de doctores:", doctoresUrlBase);

    // Cargar especialidades
    loadEspecialidades(especialidadesUrl, "#especialidad");

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
        event.preventDefault();
        const propietario = document.getElementById("propietario").value;
        const especialidadId = especialidadSelect.value;
        const especialidadNombre = especialidadSelect.options[especialidadSelect.selectedIndex].textContent;
        const doctor = doctorSelect.value;
        const fecha = document.getElementById("fecha").value;
        const hora = document.getElementById("hora").value;
        const idUsuario = localStorage.getItem("id_usuario");

        // Validar datos antes de enviar
        if (!propietario || !especialidadId || !doctor || !fecha || !hora) {
            alert("Por favor, completa todos los campos.");
            return;
        }

        const citaData = { propietario, especialidadId, especialidadNombre, doctor, fecha, horario: hora, id_usuario: idUsuario };
        registrarCita(especialidadesUrl, citaData);
    });
});
