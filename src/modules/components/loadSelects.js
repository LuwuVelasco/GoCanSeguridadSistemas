export async function loadEspecialidades(url, selectSelector) {
    const select = document.querySelector(selectSelector);
    select.innerHTML = '<option value="">Cargando servicios...</option>';

    try {
        const response = await fetch(url);
        const responseText = await response.text(); // Obtenemos el texto sin analizar

        try {
            const especialidades = JSON.parse(responseText); // Intentamos convertir a JSON

            if (!Array.isArray(especialidades) || especialidades.length === 0) {
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
        } catch (jsonError) {
            console.error("Error al analizar JSON:", responseText, jsonError);
            select.innerHTML = '<option value="">Error al cargar servicios</option>';
        }
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

// Cargar roles desde un endpoint y rellenar un selector
export async function loadRoles(url, selectSelector) {
    const select = document.querySelector(selectSelector);
    select.innerHTML = '<option value="">Cargando roles...</option>';

    try {
        const response = await fetch(url);
        const roles = await response.json();

        console.log("Roles obtenidos:", roles); // Depuración

        if (!Array.isArray(roles)) {
            throw new Error("El formato de la respuesta no es un array.");
        }

        if (roles.length === 0) {
            select.innerHTML = '<option value="">No hay roles disponibles</option>';
            return;
        }

        select.innerHTML = '<option value="">Seleccionar rol...</option>';
        roles.forEach(rol => {
            const option = document.createElement("option");
            option.value = rol.id_rol;
            option.textContent = rol.nombre_rol;
            select.appendChild(option);
        });
    } catch (error) {
        console.error("Error al cargar roles:", error);
        select.innerHTML = '<option value="">Error al cargar roles</option>';
    }
}

export async function loadRolesFuncionario(url, selectSelector) {
    const select = document.querySelector(selectSelector);
    select.innerHTML = '<option value="">Cargando roles...</option>';

    try {
        const response = await fetch(url);
        const responseText = await response.text(); // Obtenemos el texto sin analizar

        try {
            const roles = JSON.parse(responseText); // Intentamos convertir a JSON
            console.log("Roles obtenidos:", roles); // Depuración

            const rolesFiltrados = roles.filter(
                rol => rol.nombre_rol && typeof rol.nombre_rol === 'string' && rol.nombre_rol.toLowerCase() !== 'cliente'
            );

            if (rolesFiltrados.length === 0) {
                select.innerHTML = '<option value="">No hay roles disponibles</option>';
                return;
            }

            select.innerHTML = '<option value="">Seleccionar rol...</option>';
            rolesFiltrados.forEach(rol => {
                const option = document.createElement("option");
                option.value = rol.id_rol;
                option.textContent = rol.nombre_rol;
                select.appendChild(option); 
            });
        } catch (jsonError) {
            console.error("Error al analizar JSON:", responseText, jsonError);
            select.innerHTML = '<option value="">Error al cargar roles</option>';
        }
    } catch (error) {
        console.error("Error al cargar roles:", error.message);
        select.innerHTML = '<option value="">Error al cargar roles</option>';
    }
}
