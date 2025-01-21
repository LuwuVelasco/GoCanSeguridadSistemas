export function loadMascotasTable(url, tbodySelector) {
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.estado === 'success') {
                const tbody = document.querySelector(tbodySelector);
                tbody.innerHTML = ''; // Limpiar la tabla antes de rellenarla

                data.mascotas.forEach(mascota => {
                    const tr = document.createElement('tr');

                    const tdCodigo = document.createElement('td');
                    tdCodigo.textContent = mascota.id_mascota;

                    const tdNombre = document.createElement('td');
                    tdNombre.textContent = mascota.nombre_mascota;

                    const tdEdad = document.createElement('td');
                    let edadTexto = '';
                    if (mascota.edad_day && mascota.edad_day != 0) {
                        edadTexto = `${mascota.edad_day} día(s)`;
                    } else if (mascota.edad_month && mascota.edad_month != 0) {
                        edadTexto = `${mascota.edad_month} mes(es)`;
                    } else if (mascota.edad_year && mascota.edad_year != 0) {
                        edadTexto = `${mascota.edad_year} año(s)`;
                    }
                    tdEdad.textContent = edadTexto;

                    const tdTipo = document.createElement('td');
                    tdTipo.textContent = mascota.tipo;

                    const tdRaza = document.createElement('td');
                    tdRaza.textContent = mascota.raza;

                    const tdPropietario = document.createElement('td');
                    tdPropietario.textContent = mascota.nombre_propietario;

                    // Agregar las celdas a la fila
                    tr.appendChild(tdCodigo);
                    tr.appendChild(tdNombre);
                    tr.appendChild(tdEdad);
                    tr.appendChild(tdTipo);
                    tr.appendChild(tdRaza);
                    tr.appendChild(tdPropietario);

                    // Agregar la fila al tbody
                    tbody.appendChild(tr);
                });
            } else {
                console.error('Error al obtener las mascotas:', data.mensaje);
                alert('Error al cargar las mascotas.');
            }
        })
        .catch(error => {
            console.error('Error al procesar la solicitud:', error);
            alert('Error al cargar las mascotas.');
        });
}
