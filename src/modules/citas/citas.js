document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('servicio').addEventListener('change', actualizarDoctores);
    document.getElementById('reservar').addEventListener('click', function(event) {
        event.preventDefault();
        registrarcita();
    });

    actualizarDoctores(); // Inicializar la lista de doctores cuando la página se carga
  });

  async function actualizarDoctores() {
    const especialidad = document.getElementById('servicio').value;
    const doctorSelect = document.getElementById('doctor');
    doctorSelect.innerHTML = '<option value="">Seleccionar doctor...</option>'; // Reset doctor select

    let url = 'http://localhost/GoCan/src/modules/php/citas.php';
    if (especialidad !== "Sin especificar" && especialidad !== "") {
        const especialidadQuery = especialidad === "Consulta General" ? "Medicina General" : especialidad;
        url += `?especialidad=${especialidadQuery}`;
    }

    try {
      const response = await fetch(url);
      const doctores = await response.json();

      if (doctores.length > 0) {
        doctores.forEach(doctor => {
          const option = document.createElement('option');
          option.value = doctor.nombre;
          option.textContent = doctor.nombre;
          doctorSelect.appendChild(option);
        });
      } else {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'No hay doctores disponibles';
        doctorSelect.appendChild(option);
      }
    } catch (error) {
      console.error('Error fetching doctors:', error);
      const option = document.createElement('option');
      option.value = '';
      option.textContent = 'Error al cargar doctores';
      doctorSelect.appendChild(option);
    }
  }

  function registrarcita() {
    let propietario = document.getElementById('propietario').value;
    let horario = document.getElementById('hora').value;
    let fecha = document.getElementById('fecha').value;
    let servicio = document.getElementById('servicio').value;
    let doctor = document.getElementById('doctor').value;
    let id_usuario = localStorage.getItem('id_usuario');

    console.log("propietario:", propietario);
    console.log("hora:", horario);
    console.log("fecha:", fecha);
    console.log("servicio:", servicio);
    console.log("doctor:", doctor);
    console.log("id_usuario:", id_usuario);

    fetch("http://localhost/GoCan/src/modules/php/citas.php", {
      method: "POST",
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        propietario: propietario,
        servicio: servicio,
        doctor: doctor,
        id_usuario: id_usuario,
        fecha: fecha,
        horario: horario
      }),
    })
    .then(response => {
      if (response.ok) {
        return response.json();
      } else {
        throw new Error('Error al registrar la cita');
      }
    })
    .then(data => {
      console.log(data);
      alert('Cita registrada correctamente. ID de cita: ' + data.id_cita);
    })
    .catch(error => console.error('Error:', error));
  }