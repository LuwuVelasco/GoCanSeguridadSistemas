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
});
