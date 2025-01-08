document.addEventListener("DOMContentLoaded", function() {
    let citas = [];
    let reportes = [];
    let mascotaIdToDelete = null;
    let editingMascotaData = null;

    fetchCitas();
    loadActivities('http://localhost/GoCanSeguridadSistemas/src/modules/php/get_actividades.php', '#actividades-table tbody');
    loadData('http://localhost/GoCanSeguridadSistemas/src/modules/php/listadoctores.php', '#lista-veterinarios');

    function fetchCitas() {
        fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/admin_citas.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.estado === "success") {
                citas = data.citas;
                mostrarCitas(citas);
            } else {
                console.error("Error:", data.mensaje);
            }
        })
        .catch(error => console.error("Error:", error));
    }

    function mostrarCitas(citas) {
        const tbody = document.querySelector("table tbody");
        tbody.innerHTML = '';

        citas.forEach(cita => {
            const tr = document.createElement("tr");
            tr.classList.add("selected");

            const tdIcon = document.createElement("td");
            tdIcon.classList.add("icon");
            tdIcon.innerHTML = '<i class="fi fi-sr-paw"></i>';

            const tdName = document.createElement("td");
            tdName.classList.add("name");
            tdName.textContent = `Cita con ${cita.propietario}`;

            const tdExtension = document.createElement("td");
            tdExtension.classList.add("extension");
            tdExtension.textContent = `${cita.fecha}, ${cita.horario}`;

            const tdCheckbox = document.createElement("td");
            const checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.setAttribute("data-id", cita.id_cita); // Asignar ID de la cita al checkbox
            checkbox.addEventListener("change", () => {
                const citaId = checkbox.getAttribute("data-id");
                console.log(`ID de la cita: ${citaId}`); // Mostrar el ID por consola
                if (checkbox.checked) {
                    if (confirm("¿La cita ya ha sido completada?")) {
                        eliminarCitaAdmin(citaId, tr); // Eliminar la cita de la base de datos y ocultar la fila
                    } else {
                        checkbox.checked = false;
                    }
                }
            });
            tdCheckbox.appendChild(checkbox);

            tr.appendChild(tdIcon);
            tr.appendChild(tdName);
            tr.appendChild(tdExtension);
            tr.appendChild(tdCheckbox);

            tbody.appendChild(tr);
        });
    }

    function eliminarDoctor(id_doctores) {
        if (confirm('¿Estás seguro de que deseas eliminar este doctor?')) {
            fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/eliminarDoctor.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ id: id_doctores })
            })
            .then(response => response.text()) // Cambiado a text() para debug
            .then(text => {
                console.log('Respuesta del servidor:', text); // Agrega esta línea para ver la respuesta del servidor
                try {
                    const data = JSON.parse(text);
                    if (data.estado === "success") {
                        alert('Doctor eliminado correctamente');
                        loadData('http://localhost/GoCanSeguridadSistemas/src/modules/php/listadoctores.php', '#lista-veterinarios'); // Recargar la lista
                    } else {
                        alert('Error al eliminar el doctor: ' + data.mensaje);
                    }
                } catch (e) {
                    console.error('Error al analizar JSON:', e);
                    console.error('Respuesta recibida:', text);
                    alert('Error al procesar la solicitud. Verifique la consola para más detalles.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        }
    }

    window.eliminarDoctor = eliminarDoctor; // Exponer al ámbito global

    // Carga dinámica de datos para actividades y doctores
    function loadData(url, tbodySelector) {
        fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector(tbodySelector);
            tbody.innerHTML = '';
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.id_doctores}</td>
                    <td>${item.nombre}</td>
                    <td>${item.cargo}</td>
                    <td>${item.especialidad || 'No asignada'}</td>
                    <td class="estado ${getClassForEstado(item.estado)}">${item.estado}</td>
                    <td><button class="delete-button" onclick="eliminarDoctor(${item.id_doctores})"><i class="ri-delete-bin-6-line"></i></button></td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error al cargar los datos:', error);
            alert('Error al cargar los datos. Verifica la consola para más detalles.');
        });
    }

    function getClassForEstado(estado) {
        switch (estado.toLowerCase()) {
            case 'activo':
                return 'estado-activo';
            case 'inactivo':
                return 'estado-inactivo';
            case 'con permiso':
                return 'estado-permiso';
            default:
                return 'estado-desconocido'; // Asegúrate de manejar estados desconocidos
        }
    }

    function loadActivities(url, tbodySelector) {
        fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector(tbodySelector);
            tbody.innerHTML = ''; // Limpia la tabla antes de añadir nuevos datos
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.hora_ingreso}</td>
                    <td>${item.nombre_usuario}</td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error al cargar los datos:', error);
            alert('Error al cargar los datos. Verifica la consola para más detalles.');
        });
    }

    function eliminarCitaAdmin(citaId, fila) {
        fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/eliminar_citaAdmin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id_cita=${encodeURIComponent(citaId)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.estado === "success") {
                fila.style.display = "none"; // Ocultar la fila de la tabla
            } else {
                console.error("Error:", data.mensaje);
                alert("Error al eliminar la cita: " + data.mensaje);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error al procesar la solicitud");
        });
    }

    window.sortCitas = function() {
        citas.sort((a, b) => new Date(a.fecha) - new Date(b.fecha));
        mostrarCitas(citas);
    }

    window.filtrarCitas = function() {
        const searchText = document.getElementById('searchInput').value.toLowerCase();
        const filteredCitas = citas.filter(cita => cita.propietario.toLowerCase().includes(searchText));
        mostrarCitas(filteredCitas);
    }

    window.openReportModal = function() {
        document.getElementById('reportModal').style.display = 'block';
    }

    window.closeReportModal = function() {
        document.getElementById('reportModal').style.display = 'none';
    }

    window.openHistoryModal = function() {
        document.getElementById('historyModal').style.display = 'block';
        fetchReportHistory();
    }

    window.closeHistoryModal = function() {
        document.getElementById('historyModal').style.display = 'none';
    }

    window.openEditModal = function() {
        fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_mascotas.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.estado === "success") {
                    llenarTablaMascotas(data.mascotas);
                    document.getElementById('tablaModal').style.display = 'block';
                } else {
                    console.error("Error:", data.mensaje);
                }
            })
            .catch(error => {
                console.error("Error:", error);
            });
    };

    window.closeTablaModal = function() {
        document.getElementById('tablaModal').style.display = 'none';
    }

    window.onclick = function(event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    }

    const reportForm = document.getElementById('reportForm');
    reportForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(reportForm);
        const data = new URLSearchParams();
        for (const pair of formData) {
            data.append(pair[0], pair[1]);
        }

        fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/registrar_reporte.php', {
            method: 'POST',
            body: data
        })
        .then(response => response.text()) // Cambiar a text() temporalmente para depuración
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.estado === 'success') {
                    alert('Reporte registrado exitosamente');
                    closeReportModal();
                    reportForm.reset();
                } else {
                    alert('Error al registrar el reporte: ' + data.mensaje);
                }
            } catch (e) {
                console.error('Error al analizar JSON:', e);
                console.error('Respuesta recibida:', text);
                alert('Error al procesar la solicitud. Verifique la consola para más detalles.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    });

    function fetchReportHistory() {
        fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_reporte.php', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.estado === "success") {
                reportes = data.reportes;
                mostrarReportes(reportes);
            } else {
                console.error("Error:", data.mensaje);
            }
        })
        .catch(error => console.error("Error:", error));
    }

    function mostrarReportes(reportes) {
        const reportHistory = document.getElementById('reportHistory');
        reportHistory.innerHTML = '';

        reportes.forEach(reporte => {
            const div = document.createElement("div");
            div.classList.add("reporte");

            const resumen = document.createElement("div");
            resumen.classList.add("reporte-resumen");
            resumen.textContent = `Propietario: ${reporte.propietario}, Mascota: ${reporte.nombre_mascota}`;
            resumen.addEventListener("click", function() {
                const detalle = this.nextElementSibling;
                if (detalle.style.display === "none" || detalle.style.display === "") {
                    detalle.style.display = "block";
                } else {
                    detalle.style.display = "none";
                }
            });

            const detalle = document.createElement("div");
            detalle.classList.add("reporte-detalle");

            const sintomas = document.createElement("p");
            sintomas.textContent = `Síntomas: ${reporte.sintomas}`;

            const diagnostico = document.createElement("p");
            diagnostico.textContent = `Diagnóstico: ${reporte.diagnostico}`;

            const receta = document.createElement("p");
            receta.textContent = `Receta: ${reporte.receta}`;

            const fecha = document.createElement("p");
            fecha.textContent = `Fecha: ${reporte.fecha}`;

            const editButton = document.createElement("button");
            editButton.textContent = "Editar";
            editButton.addEventListener("click", function() {
                abrirFormularioEdicion(reporte);
            });

            const deleteButton = document.createElement("button");
            deleteButton.textContent = "Eliminar";
            deleteButton.addEventListener("click", function() {
                if (confirm("¿Estás seguro de que deseas eliminar este reporte?")) {
                    eliminarReporte(reporte.propietario, reporte.nombre_mascota, div);
                }
            });

            detalle.appendChild(sintomas);
            detalle.appendChild(diagnostico);
            detalle.appendChild(receta);
            detalle.appendChild(fecha);
            detalle.appendChild(deleteButton);
            detalle.appendChild(editButton);

            div.appendChild(resumen);
            div.appendChild(detalle);

            reportHistory.appendChild(div);
        });
    }

    function abrirFormularioEdicion(reporte) {
        document.getElementById('id_reporte').value = reporte.id_reporte;
        const modal = document.getElementById('reportModal');
        modal.style.display = 'block';
        document.getElementById('id_reporte').value = reporte.id_reporte;
        document.getElementById('propietario').value = reporte.propietario;
        document.getElementById('nombre_mascota').value = reporte.nombre_mascota;
        document.getElementById('sintomas').value = reporte.sintomas;
        document.getElementById('diagnostico').value = reporte.diagnostico;
        document.getElementById('receta').value = reporte.receta;
        document.getElementById('fecha').value = reporte.fecha;
    }

    function eliminarReporte(propietario, nombreMascota, reporteDiv) {
        console.log(`Eliminando reporte de ${propietario} para la mascota ${nombreMascota}`);

        fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/eliminar_reporte.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `propietario=${encodeURIComponent(propietario)}&nombre_mascota=${encodeURIComponent(nombreMascota)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.estado === "success") {
                alert("Reporte eliminado exitosamente");
                reporteDiv.remove();
            } else {
                console.error("Error:", data.mensaje);
                alert("Error al eliminar el reporte: " + data.mensaje);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error al procesar la solicitud");
        });
    }

    document.getElementById('historySearchInput').addEventListener('input', function() {
        const searchText = this.value.toLowerCase();
        const filteredReportes = reportes.filter(reporte =>
            reporte.propietario.toLowerCase().includes(searchText) ||
            reporte.nombre_mascota.toLowerCase().includes(searchText)
        );
        mostrarReportes(filteredReportes);
    });

    document.getElementById('registroV').addEventListener('submit', function(event) {
        event.preventDefault();  // Evita el envío normal del formulario

        const formData = new FormData(this);  // Recoge los datos del formulario
        const searchParams = new URLSearchParams();

        for (const pair of formData) {
            searchParams.append(pair[0], pair[1]);
        }

        fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/registrar_veterinario.php', {
            method: 'POST',
            body: searchParams,
        })
        .then(response => response.text()) // Cambiar a text() temporalmente para depuración
        .then(text => {
            console.log('Respuesta del servidor:', text); // Agrega esta línea para ver la respuesta del servidor
            try {
                const data = JSON.parse(text);
                if (data.estado === "success") {
                    alert('Veterinario registrado correctamente');
                    closeModal(); // Cierra el modal si el registro es exitoso
                    loadData('http://localhost/GoCanSeguridadSistemas/src/modules/php/listadoctores.php', '#lista-veterinarios'); // Recarga la lista de veterinarios
                } else {
                    alert('Error al registrar veterinario: ' + data.mensaje);
                }
            } catch (e) {
                console.error('Error al analizar JSON:', e);
                console.error('Respuesta recibida:', text);
                alert('Error al procesar la solicitud. Verifique la consola para más detalles.');
            }
        })
        .catch(error => {
            console.error('Error al registrar veterinario:', error);
            alert('Error al procesar la solicitud');
        });
    });

    window.openPetModal = function() {
        document.getElementById('petModal').style.display = 'block';
    }

    window.closePetModal = function() {
        document.getElementById('petModal').style.display = 'none';
    }

    const petForm = document.getElementById('petForm');
    petForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(petForm);
        const data = new URLSearchParams();
        for (const pair of formData) {
            data.append(pair[0], pair[1]);
        }

        const edad = formData.get('edad');
        if (edad === '0' || edad === 0) {
            alert('La edad no puede ser 0');
            return;
        }

        fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/registrar_mascota.php', {
            method: 'POST',
            body: data
        })
        .then(response => response.text()) // Cambiar a text() temporalmente para depuración
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.estado === 'success') {
                    alert('Mascota registrada exitosamente');
                    closePetModal();
                    petForm.reset();
                } else {
                    alert('Error al registrar la mascota: ' + data.mensaje);
                }
            } catch (e) {
                console.error('Error al analizar JSON:', e);
                console.error('Respuesta recibida:', text);
                alert('Error al procesar la solicitud. Verifique la consola para más detalles.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    });

    window.closeConfirmModal = function() {
        document.getElementById('confirmModal').style.display = 'none';
    }

    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (mascotaIdToDelete !== null) {
            eliminarMascota(mascotaIdToDelete);
        }
    });

    function llenarTablaMascotas(mascotas) {
        const tbody = document.querySelector("#petTable tbody");
        tbody.innerHTML = ''; // Limpiar la tabla

        mascotas.forEach(mascota => {
            const tr = document.createElement("tr");

            const tdCodigo = document.createElement("td");
            tdCodigo.textContent = mascota.id_mascota;

            const tdNombre = document.createElement("td");
            tdNombre.textContent = mascota.nombre_mascota;

            const tdEdad = document.createElement("td");
            let edadTexto = '';
            if (mascota.edad_day && mascota.edad_day != 0) {
                edadTexto = `${mascota.edad_day} día(s)`;
            } else if (mascota.edad_month && mascota.edad_month != 0) {
                edadTexto = `${mascota.edad_month} mes(es)`;
            } else if (mascota.edad_year && mascota.edad_year != 0) {
                edadTexto = `${mascota.edad_year} año(s)`;
            }
            tdEdad.textContent = edadTexto;

            const tdTipo = document.createElement("td");
            tdTipo.textContent = mascota.tipo;

            const tdRaza = document.createElement("td");
            tdRaza.textContent = mascota.raza;

            const tdPropietario = document.createElement("td");
            tdPropietario.textContent = mascota.nombre_propietario;

            const tdEditar = document.createElement("td");
            const btnEditar = document.createElement("button");
            btnEditar.innerHTML = '<i class="fi fi-sr-pen-square"></i>';
            btnEditar.onclick = function() {
                console.log(mascota.id_mascota); // Enviar el ID de la mascota a la consola
                openEditForm(mascota.id_mascota);
            };
            tdEditar.appendChild(btnEditar);

            const tdEliminar = document.createElement("td");
            const btnEliminar = document.createElement("button");
            btnEliminar.innerHTML = '<i class="fi fi-sr-trash-xmark"></i>';
            btnEliminar.onclick = function() {
                mascotaIdToDelete = mascota.id_mascota;
                document.getElementById('confirmModal').style.display = 'block';
            };
            tdEliminar.appendChild(btnEliminar);

            tr.appendChild(tdCodigo);
            tr.appendChild(tdNombre);
            tr.appendChild(tdEdad);
            tr.appendChild(tdTipo);
            tr.appendChild(tdRaza);
            tr.appendChild(tdPropietario);
            tr.appendChild(tdEditar);
            tr.appendChild(tdEliminar);

            tbody.appendChild(tr);
        });
    }

    function openEditForm(id_mascota) {
        fetch(`http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_mascota.php?id_mascota=${id_mascota}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.estado === "success") {
                    const mascota = data.mascota;
                    document.getElementById('edit_id_mascota').value = mascota.id_mascota;
                    document.getElementById('edit_nombre_mascota').value = mascota.nombre_mascota;

                    const editEdadInput = document.getElementById('edit_edad');
                    const editPeriodSelect = document.getElementById('edit_period');

                    editEdadInput.value = 0; // Por defecto, establece la edad en 0
                    editPeriodSelect.value = ''; // Por defecto, establece el período vacío

                    if (mascota.edad_day && mascota.edad_day != 0) {
                        editEdadInput.value = mascota.edad_day;
                        editPeriodSelect.value = 'dia';
                    } else if (mascota.edad_month && mascota.edad_month != 0) {
                        editEdadInput.value = mascota.edad_month;
                        editPeriodSelect.value = 'mes';
                    } else if (mascota.edad_year && mascota.edad_year != 0) {
                        editEdadInput.value = mascota.edad_year;
                        editPeriodSelect.value = 'ano';
                    }

                    document.getElementById('edit_tipo').value = mascota.tipo;
                    document.getElementById('edit_raza').value = mascota.raza;
                    document.getElementById('edit_nombre_propietario').value = mascota.nombre_propietario;

                    // Almacenar los datos originales
                    editingMascotaData = {
                        id_mascota: mascota.id_mascota,
                        nombre_mascota: mascota.nombre_mascota,
                        edad: editEdadInput.value,
                        period: editPeriodSelect.value,
                        tipo: mascota.tipo,
                        raza: mascota.raza,
                        nombre_propietario: mascota.nombre_propietario
                    };

                    document.getElementById('editModal').style.display = 'block';
                    document.getElementById('tablaModal').style.display = 'none';

                    // Añade un evento para manejar los cambios en el período
                    editPeriodSelect.addEventListener('change', function() {
                        editEdadInput.value = 0;
                    });
                } else {
                    console.error("Error:", data.mensaje);
                    alert("Error: " + data.mensaje);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Error al procesar la solicitud.");
            });
    }

    const editForm = document.getElementById('editForm');
    editForm.addEventListener('submit', function(event) {
        event.preventDefault();

        // Verificar si hay cambios
        const formData = new FormData(editForm);
        const data = new URLSearchParams();
        for (const pair of formData) {
            data.append(pair[0], pair[1]);
        }

        const edad = formData.get('edad');
        const period = formData.get('period');
        const nombre_mascota = formData.get('nombre_mascota');
        const tipo = formData.get('tipo');
        const raza = formData.get('raza');
        const nombre_propietario = formData.get('nombre_propietario');

        const hasChanges =
            editingMascotaData.nombre_mascota !== nombre_mascota ||
            editingMascotaData.edad !== edad ||
            editingMascotaData.period !== period ||
            editingMascotaData.tipo !== tipo ||
            editingMascotaData.raza !== raza ||
            editingMascotaData.nombre_propietario !== nombre_propietario;

        if (hasChanges) {
            document.getElementById('confirmEditModal').style.display = 'block';
        } else {
            alert("No hubo cambios a realizar.");
            closeEditModal();
        }
    });

    document.getElementById('confirmEdit').addEventListener('click', function() {
        const formData = new FormData(editForm);
        const data = new URLSearchParams();
        for (const pair of formData) {
            data.append(pair[0], pair[1]);
        }

        const edad = formData.get('edad');
        const nombre_propietario = formData.get('nombre_propietario');

        if (edad === '0' || edad === 0) {
            alert('La edad no puede ser 0');
            document.getElementById('confirmEditModal').style.display = 'none';
            document.getElementById('editModal').style.display = 'block';
            return;
        }

        if (!nombre_propietario || data.mensaje == "El propietario no existe") {
            alert('No hay propietario');
            document.getElementById('confirmEditModal').style.display = 'none';
            document.getElementById('editModal').style.display = 'block';
            return;
        }

        fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/editar_mascota.php', {
            method: 'POST',
            body: data
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.estado === 'success') {
                alert('Mascota actualizada exitosamente');
                closeConfirmEditModal();
                closeEditModal();
                openEditModal();
            } else {
                alert('Error al actualizar la mascota: ' + data.mensaje);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    });

    window.closeConfirmEditModal = function() {
        document.getElementById('confirmEditModal').style.display = 'none';
    }

    window.closeEditModal = function() {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('tablaModal').style.display = 'block';
    }

    function eliminarMascota(id_mascota) {
        const data = new URLSearchParams();
        data.append('id_mascota', id_mascota);

        fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/eliminar_mascota.php', {
            method: 'POST',
            body: data
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.estado === 'success') {
                alert('Mascota eliminada exitosamente');
                closeConfirmModal();
                openEditModal(); // Refresh the table
            } else {
                alert('Error al eliminar la mascota: ' + data.mensaje);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    }

    // Remueve el segundo event listener para confirmDelete, ya que se está registrando dos veces
    // document.getElementById('confirmDelete').addEventListener('click', function() {
    //     if (mascotaIdToDelete !== null) {
    //         eliminarMascota(mascotaIdToDelete);
    //     }
    // });

    window.closeConfirmModal = function() {
        document.getElementById('confirmModal').style.display = 'none';
    }

    window.openModal = function() {
        var modal = document.getElementById('reserveModal');
        modal.style.display = 'block';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        modal.style.display = 'flex';
    }

    window.closeModal = function() {
        var modal = document.getElementById('reserveModal');
        modal.style.display = 'none';
    }

    window.toggleDropdown = function() {
        var dropdown = document.getElementById('profileDropdown');
        dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
    }
    var dropdowns = document.querySelectorAll('.profile-dropdown');

    window.onclick = function(event) {
        if (!event.target.closest('.profile') && !event.target.closest('.profile-dropdown')) {
            dropdowns.forEach(function(dropdown) {
                if (dropdown.style.display === 'block') {
                    dropdown.style.display = 'none';
                }
            });
        }
    };
});

document.addEventListener("DOMContentLoaded", function () {
    loadRatings();

    function loadRatings() {
        fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/get_ratings.php')
        .then(response => response.json())
        .then(data => {
            if (data.estado === "success") {
                updateRatingDisplay(data.ratings);
            } else {
                console.error("Error:", data.mensaje);
            }
        })
        .catch(error => console.error("Error:", error));
    }

    function updateRatingDisplay(ratings) {
        const totalRatings = ratings.reduce((sum, rating) => sum + rating.count, 0);
        const averageRating = ratings.reduce((sum, rating) => sum + rating.stars * rating.count, 0) / totalRatings;
        
        document.getElementById('average-rating').textContent = averageRating.toFixed(1);
        document.getElementById('rating-count').textContent = `(${totalRatings})`;

        ratings.forEach(rating => {
            const bar = document.getElementById(`bar-${rating.stars}`);
            const count = document.getElementById(`count-${rating.stars}`);
            const percentage = (rating.count / totalRatings) * 100;
            bar.style.width = `${percentage}%`;
            count.textContent = rating.count;
        });
    }
});
