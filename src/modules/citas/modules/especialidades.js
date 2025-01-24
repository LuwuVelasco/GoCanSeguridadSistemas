export async function loadEspecialidades(url, selectSelector) {
  const select = document.querySelector(selectSelector);
  select.innerHTML = '<option value="">Cargando servicios...</option>';

  try {
      const response = await fetch(url);
      const especialidades = await response.json();

      console.log("Especialidades obtenidas:", especialidades); // Depuración

      if (!especialidades || especialidades.length === 0) {
          select.innerHTML = '<option value="">No hay servicios disponibles</option>';
          return;
      }

      select.innerHTML = '<option value="">Seleccionar servicio...</option>';
      especialidades.forEach(especialidad => {
          const option = document.createElement("option");
          option.value = especialidad.id_especialidad;
          option.textContent = especialidad.nombre_especialidad;
          select.appendChild(option);
      });
  } catch (error) {
      console.error("Error al cargar especialidades:", error);
      select.innerHTML = '<option value="">Error al cargar servicios</option>';
  }
}

export async function loadDoctores(url, selectSelector) {
  const select = document.querySelector(selectSelector);
  select.innerHTML = '<option value="">Cargando doctores...</option>';

  try {
      const response = await fetch(url);
      const doctores = await response.json();

      console.log("Doctores obtenidos:", doctores); // Depuración

      if (!Array.isArray(doctores)) {
          throw new Error("El formato de la respuesta no es un array.");
      }

      if (doctores.length === 0) {
          select.innerHTML = '<option value="">No hay doctores disponibles</option>';
          return;
      }

      select.innerHTML = '<option value="">Seleccionar doctor...</option>';
      doctores.forEach(doctor => {
          const option = document.createElement("option");
          option.value = doctor.nombre;
          option.textContent = doctor.nombre;
          select.appendChild(option);
      });
  } catch (error) {
      console.error("Error al cargar doctores:", error.message);
      select.innerHTML = '<option value="">Error al cargar doctores</option>';
  }
}
