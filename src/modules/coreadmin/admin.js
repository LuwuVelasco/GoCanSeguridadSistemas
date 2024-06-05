document.addEventListener("DOMContentLoaded", function() {
    let citas = [];

    fetchCitas();

    function fetchCitas() {
        fetch('http://localhost/GoCan/src/modules/php/admin_citas.php', {
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

    function eliminarCitaAdmin(citaId, fila) {
        fetch('http://localhost/GoCan/src/modules/php/eliminar_citaAdmin.php', {
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
    


    window.onclick = function(event) {
        if (event.target == document.getElementById('reportModal')) {
            document.getElementById('reportModal').style.display = 'none';
        }
        if (event.target == document.getElementById('historyModal')) {
            document.getElementById('historyModal').style.display = 'none';
        }
    }


    const reportForm = document.getElementById('reportForm');
    reportForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const propietario = document.getElementById('propietario').value;
        const sintomas = document.getElementById('sintomas').value;
        const diagnostico = document.getElementById('diagnostico').value;
        const receta = document.getElementById('receta').value;
        const fecha = document.getElementById('fecha').value;
        const nombre_mascota = document.getElementById('nombre_mascota').value;

        const formData = new URLSearchParams();
        formData.append('propietario', propietario);
        formData.append('sintomas', sintomas);
        formData.append('diagnostico', diagnostico);
        formData.append('receta', receta);
        formData.append('fecha', fecha);
        formData.append('nombre_mascota', nombre_mascota);

        fetch('http://localhost/GoCan/src/modules/php/registrar_reporte.php', {
            method: 'POST',
            body: formData
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
        fetch('http://localhost/GoCan/src/modules/php/obtener_reporte.php', {
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

            detalle.appendChild(sintomas);
            detalle.appendChild(diagnostico);
            detalle.appendChild(receta);
            detalle.appendChild(fecha);

            div.appendChild(resumen);
            div.appendChild(detalle);

            reportHistory.appendChild(div);
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

    window.openPetModal = function() {
        document.getElementById('petModal').style.display = 'block';
    }
    
    window.closePetModal = function() {
        document.getElementById('petModal').style.display = 'none';
    }
    
    const petForm = document.getElementById('petForm');
    const idUsuario = localStorage.getItem('id_usuario'); // Asegúrate de que el ID del usuario esté almacenado en localStorage
    if (idUsuario) {
        document.getElementById('id_usuario').value = idUsuario;
    }

    petForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(petForm);
        const data = new URLSearchParams();
        for (const pair of formData) {
            data.append(pair[0], pair[1]);
        }

        fetch('http://localhost/GoCan/src/modules/php/registrar_mascota.php', {
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

    
});


function openModal() {
    var modal = document.getElementById('reserveModal');
    modal.style.display = 'block';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    modal.style.display = 'flex';
}

function closeModal() {
    var modal = document.getElementById('reserveModal');
    modal.style.display = 'none';
}

function toggleDropdown() {
    var dropdown = document.getElementById('profileDropdown');
    dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
}

document.addEventListener('DOMContentLoaded', function() {
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
    const sidebarItems = document.querySelectorAll('.sidebar .item');
    const tableRows = document.querySelectorAll('.main table tbody tr');
    const modal = document.getElementById('reserveModal');
    const closeModalButton = modal.querySelector('.close');
    const form = document.getElementById('registroV');

    // Función para abrir el modal
    function openModal() {
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
    }

    // Función para cerrar el modal
    function closeModal() {
        modal.style.display = 'none';
    }

    // Evento para abrir el modal al hacer clic en el botón de registro de veterinarios
    document.getElementById('bt3').addEventListener('click', openModal);

    // Evento para cerrar el modal al hacer clic en la 'X'
    closeModalButton.addEventListener('click', closeModal);

    // Manejador del evento submit para el formulario
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);

        fetch('http://localhost/GoCan/src/modules/php/registrar_veterinario.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.estado === 'success') {
                alert(data.mensaje);
                closeModal();
                location.reload();
            } else {
                throw new Error(data.mensaje);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Ha ocurrido un error al intentar registrar: " + error.message);
        });
    });

    // Función para cerrar el modal si se hace clic fuera de él
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    };

    // Función para alternar la visibilidad del dropdown del perfil
    function toggleDropdown() {
        var dropdown = document.getElementById('profileDropdown');
        dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
    }

    // Evento para el dropdown del perfil
    document.querySelector('.profile').addEventListener('click', toggleDropdown);

    // Eventos de clic para elementos de la barra lateral
    sidebarItems.forEach(item => {
        item.addEventListener('click', () => {
            sidebarItems.forEach(innerItem => innerItem.classList.remove('active'));
            item.classList.add('active');
        });
    });

    // Eventos de selección para filas de la tabla
    tableRows.forEach(row => {
        row.addEventListener('click', () => {
            tableRows.forEach(innerRow => innerRow.classList.remove('selected'));
            row.classList.add('selected');
        });
    });

    // Carga dinámica de datos para actividades y doctores
    function loadData(url, tbodySelector) {
        fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector(tbodySelector);
            tbody.innerHTML = ''; // Limpia la tabla antes de añadir nuevos datos
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.nombre}</td>
                    <td>${item.cargo}</td>
                    <td>${item.especialidad || 'No asignada'}</td>
                    <td>${item.estado}</td>

                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error al cargar los datos:', error);
            alert('Error al cargar los datos. Verifica la consola para más detalles.');
        });
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
    

    loadActivities('http://localhost/GoCan/src/modules/php/get_actividades.php', '#actividades-table tbody');
    loadData('http://localhost/GoCan/src/modules/php/listadoctores.php', '#lista-veterinarios');
});
