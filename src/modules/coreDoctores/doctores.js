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
                    if (confirm("¿La cita ya ha sido completada?")) {
                        tr.style.display = "none"; // Ocultar la fila de la tabla
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

    window.sortCitas = function() {
        citas.sort((a, b) => new Date(a.fecha) - new Date(b.fecha));
        mostrarCitas(citas);
    }

    window.filtrarCitas = function() {
        const searchText = document.getElementById('searchInput').value.toLowerCase();
        const filteredCitas = citas.filter(cita => cita.propietario.toLowerCase().includes(searchText));
        mostrarCitas(filteredCitas);
    }
});
