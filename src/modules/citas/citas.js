document.addEventListener("DOMContentLoaded", function () {
  const especialidadSelect = document.getElementById("especialidad");
  const doctorSelect = document.getElementById("doctor");
  const reservarButton = document.getElementById("reservar");

  if (especialidadSelect && doctorSelect && reservarButton) {
    especialidadSelect.addEventListener("change", actualizarDoctores);
    reservarButton.addEventListener("click", function (event) {
      event.preventDefault();
      registrarcita();
    });

    actualizarEspecialidades();
  } else {
    console.error(
      "No se pudieron encontrar los elementos necesarios en el DOM"
    );
  }
});

async function actualizarEspecialidades() {
  const especialidadSelect = document.getElementById("especialidad");
  especialidadSelect.innerHTML =
    '<option value="">Seleccionar especialidad...</option>'; // Reset especialidad select

  try {
    const response = await fetch(
      "http://localhost/GoCan/src/modules/php/citas.php"
    );
    console.log("Solicitando especialidades...");
    const especialidad = await response.json();
    console.log("Especialidades recibidas:", especialidad);

    if (especialidad.length > 0) {
      especialidad.forEach((especialidad) => {
        const option = document.createElement("option");
        option.value = especialidad.id_especialidad;
        option.textContent = especialidad.nombre_especialidad;
        especialidadSelect.appendChild(option);
      });
    } else {
      appendOption(especialidadSelect, "", "No hay especialidades disponibles");
    }
  } catch (error) {
    console.error("Error fetching specialities:", error);
    appendOption(especialidadSelect, "", "Error al cargar especialidades");
  }
}

async function actualizarDoctores() {
  const especialidadId = document.getElementById("especialidad").value;
  const doctorSelect = document.getElementById("doctor");
  doctorSelect.innerHTML = '<option value="">Seleccionar doctor...</option>'; // Reset doctor select

  if (!especialidadId) {
    appendOption(doctorSelect, "", "Seleccione una especialidad primero");
    return;
  }

  let url = `http://localhost/GoCan/src/modules/php/citas.php?especialidad_id=${especialidadId}`;

  try {
    const response = await fetch(url);
    const doctores = await response.json();

    if (doctores.length > 0) {
      doctores.forEach((doctor) => {
        const option = document.createElement("option");
        option.value = doctor.nombre;
        option.textContent = doctor.nombre;
        doctorSelect.appendChild(option);
      });
    } else {
      appendOption(doctorSelect, "", "No hay doctores disponibles");
    }
  } catch (error) {
    console.error("Error fetching doctors:", error);
    appendOption(doctorSelect, "", "Error al cargar doctores");
  }
}

function appendOption(selectElement, value, text) {
  const option = document.createElement("option");
  option.value = value;
  option.textContent = text;
  selectElement.appendChild(option);
}

function registrarcita() {
    const propietario = document.getElementById("propietario").value;
    const horario = document.getElementById("hora").value;
    const fecha = document.getElementById("fecha").value;
    const especialidadSelect = document.getElementById("especialidad");
    const especialidadId = especialidadSelect.value;
    const especialidadNombre = especialidadSelect.options[especialidadSelect.selectedIndex].text;
    const doctor = document.getElementById("doctor").value;
    const id_usuario = localStorage.getItem("id_usuario");
  
    console.log("propietario:", propietario);
    console.log("hora:", horario);
    console.log("fecha:", fecha);
    console.log("especialidadId:", especialidadId);
    console.log("especialidadNombre:", especialidadNombre);
    console.log("doctor:", doctor);
    console.log("id_usuario:", id_usuario);
  
    fetch("http://localhost/GoCan/src/modules/php/citas.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        propietario: propietario,
        especialidadId: especialidadId,
        especialidadNombre: especialidadNombre,
        doctor: doctor,
        id_usuario: id_usuario,
        fecha: fecha,
        horario: horario,
      }),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        if (data.error) {
          throw new Error(data.mensaje);
        }
        console.log(data);
        alert("Cita registrada correctamente. ID de cita: " + data.id_cita);
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Error: " + error.message);
      });
  }
