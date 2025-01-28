export function loadDoctorReports(url, containerSelector) {
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.estado === "success") {
                const container = document.querySelector(containerSelector);
                container.innerHTML = ""; // Limpiar contenido previo

                data.reportes.forEach(reporte => {
                    const div = document.createElement("div");
                    div.classList.add("reporte");

                    const resumen = document.createElement("div");
                    resumen.classList.add("reporte-resumen");
                    resumen.textContent = `Propietario: ${reporte.propietario}, Mascota: ${reporte.nombre_mascota}`;
                    resumen.setAttribute("data-propietario", reporte.propietario.toLowerCase());
                    resumen.setAttribute("data-mascota", reporte.nombre_mascota.toLowerCase());

                    const detalle = document.createElement("div");
                    detalle.classList.add("reporte-detalle");
                    detalle.innerHTML = `
                        <p>Síntomas: ${reporte.sintomas}</p>
                        <p>Diagnóstico: ${reporte.diagnostico}</p>
                        <p>Receta: ${reporte.receta}</p>
                        <p>Fecha: ${reporte.fecha}</p>
                    `;

                    resumen.addEventListener("click", () => {
                        detalle.style.display = detalle.style.display === "none" || detalle.style.display === "" 
                            ? "block" 
                            : "none";
                    });

                    div.appendChild(resumen);
                    div.appendChild(detalle);
                    container.appendChild(div);
                });

                // Conectar el evento de búsqueda
                const searchInput = document.getElementById("historySearchInput");
                searchInput.addEventListener("input", () => filterReports(searchInput.value, containerSelector));
            } else {
                console.error("Error al cargar los reportes:", data.mensaje);
                alert("Error al cargar los reportes.");
            }
        })
        .catch(error => {
            console.error("Error al procesar la solicitud:", error);
            alert("Error al cargar los reportes.");
        });
}

// Función para filtrar los reportes
function filterReports(searchText, containerSelector) {
    const container = document.querySelector(containerSelector);
    const searchValue = searchText.trim().toLowerCase();
    const reportes = container.querySelectorAll(".reporte");

    reportes.forEach(reporte => {
        const propietario = reporte.querySelector(".reporte-resumen").getAttribute("data-propietario");
        const mascota = reporte.querySelector(".reporte-resumen").getAttribute("data-mascota");

        if (propietario.includes(searchValue) || mascota.includes(searchValue)) {
            reporte.style.display = "block";
        } else {
            reporte.style.display = "none";
        }
    });
}
