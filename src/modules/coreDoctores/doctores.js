document.addEventListener("DOMContentLoaded", function() {
    let citas = [];
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
            checkbox.addEventListener("change", () => {
                if (checkbox.checked) {
                    eliminarCita(cita.id_cita, tr);
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

    window.sortCitas = function() {
        citas.sort((a, b) => new Date(a.fecha) - new Date(b.fecha));
        mostrarCitas(citas);
    }

    function eliminarCita(id_cita, row) {
        fetch(`http://localhost/GoCan/src/modules/php/citafinalizada.php?id_cita=${id_cita}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.estado === "success") {
                row.remove();
            } else {
                console.error("Error:", data.mensaje);
            }
        })
        .catch(error => console.error("Error:", error));
    }

    window.openReportModal = function() {
        document.getElementById('reportModal').style.display = 'block';
    }

    window.closeReportModal = function() {
        document.getElementById('reportModal').style.display = 'none';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('reportModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    const reportForm = document.getElementById('reportForm');
    reportForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(reportForm);
        const data = new URLSearchParams();
        for (const pair of formData) {
            data.append(pair[0], pair[1]);
        }

        fetch('http://localhost/GoCan/src/modules/php/registrar_reporte.php', {
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

    window.openPetModal = function() {
        document.getElementById('petModal').style.display = 'block';
    }
    
    window.closePetModal = function() {
        document.getElementById('petModal').style.display = 'none';
    }
    
    document.addEventListener("DOMContentLoaded", function() {
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
});
