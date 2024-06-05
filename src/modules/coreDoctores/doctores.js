document.addEventListener("DOMContentLoaded", function() {
    let citas = [];
    let reportes = [];
    const id_doctor = localStorage.getItem('id_doctores');

    fetchCitas(id_doctor);

    function fetchCitas(id_doctor) {
        fetch('http://localhost/GoCan/src/modules/php/doctores.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id_doctor=${encodeURIComponent(id_doctor)}`
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
                        eliminarCita(citaId, tr); // Eliminar la cita de la base de datos y ocultar la fila
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

    function eliminarCita(citaId, fila) {
        fetch('http://localhost/GoCan/src/modules/php/eliminar_cita.php', {
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
    
            div.appendChild(resumen);
            div.appendChild(detalle);
    
            reportHistory.appendChild(div);
        });
    }
    
    
    function eliminarReporte(propietario, nombreMascota, reporteDiv) {
        console.log(`Eliminando reporte de ${propietario} para la mascota ${nombreMascota}`);
    
        fetch('http://localhost/GoCan/src/modules/php/eliminar_reporte.php', {
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
