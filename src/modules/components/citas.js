export function loadDoctorCitas(id_doctor, tbodySelector, searchText = "") {
    fetch("http://localhost/GoCanSeguridadSistemas/src/modules/php/doctores.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id_doctor=${encodeURIComponent(id_doctor)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.estado === "success") {
            const citas = searchText
                ? data.citas.filter(cita =>
                      cita.propietario.toLowerCase().includes(searchText)
                  )
                : data.citas;

            const tbody = document.querySelector(tbodySelector);
            tbody.innerHTML = "";

            citas.forEach(cita => {
                const tr = document.createElement("tr");

                const tdIcon = document.createElement("td");
                tdIcon.classList.add("icon");
                tdIcon.innerHTML = '<i class="fi fi-sr-paw"></i>';

                const tdName = document.createElement("td");
                tdName.textContent = `Cita con ${cita.propietario}`;

                const tdExtension = document.createElement("td");
                tdExtension.textContent = `${cita.fecha}, ${cita.horario}`;

                const tdCheckbox = document.createElement("td");
                const checkbox = document.createElement("input");
                checkbox.type = "checkbox";
                checkbox.setAttribute("data-id", cita.id_cita);
                checkbox.addEventListener("change", function () {
                    if (this.checked) {
                        if (confirm("¿La cita ya ha sido completada?")) {
                            deleteDoctorCita(cita.id_cita, tr);
                        } else {
                            this.checked = false;
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
        } else {
            console.error("Error al cargar citas:", data.mensaje);
            alert("Error al cargar las citas.");
        }
    })
    .catch(error => console.error("Error al procesar la solicitud:", error));
}

export function deleteDoctorCita(citaId, fila) {
    fetch("http://localhost/GoCanSeguridadSistemas/src/modules/php/eliminar_cita.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id_cita=${encodeURIComponent(citaId)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.estado === "success") {
            fila.remove();
            alert("Cita eliminada exitosamente.");
        } else {
            console.error("Error al eliminar cita:", data.mensaje);
            alert("Error al eliminar la cita: " + data.mensaje);
        }
    })
    .catch(error => {
        console.error("Error al procesar la solicitud:", error);
        alert("Error al eliminar la cita.");
    });
}
