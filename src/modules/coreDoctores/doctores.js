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
    
    const petForm = document.getElementById('petForm');

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
        .then(response => response.text())
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

    window.openPetModal = function() {
        document.getElementById('petModal').style.display = 'block';
    }
    
    window.closePetModal = function() {
        document.getElementById('petModal').style.display = 'none';
    }

    window.closeEditModal = function() {
        document.getElementById('editModal').style.display = 'none';
    }

    window.openEditModal = function() {
        fetch('http://localhost/GoCan/src/modules/php/obtener_mascotas.php')
            .then(response => response.json())
            .then(data => {
                if (data.estado === "success") {
                    llenarTablaMascotas(data.mascotas);
                    document.getElementById('tablaModal').style.display = 'block';
                } else {
                    console.error("Error:", data.mensaje);
                }
            })
            .catch(error => console.error("Error:", error));
    };

    function llenarTablaMascotas(mascotas) {
        const tbody = document.querySelector("#petTable tbody");
        tbody.innerHTML = ''; // Limpiar la tabla
    
        mascotas.forEach(mascota => {
            const tr = document.createElement("tr");
    
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
                // Lógica para editar la mascota
            };
            tdEditar.appendChild(btnEditar);
    
            tr.appendChild(tdNombre);
            tr.appendChild(tdEdad);
            tr.appendChild(tdTipo);
            tr.appendChild(tdRaza);
            tr.appendChild(tdPropietario);
            tr.appendChild(tdEditar);
    
            tbody.appendChild(tr);
        });
    }

    window.onclick = function(event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    }
        
});