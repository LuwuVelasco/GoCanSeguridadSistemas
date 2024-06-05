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
});

function openModal() {
    var modal = document.getElementById('reserveModal');
    modal.style.display = 'block';
    modal.style.alignItems = 'center'; // Alineación vertical centrada
    modal.style.justifyContent = 'center'; // Alineación horizontal centrada
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
});
