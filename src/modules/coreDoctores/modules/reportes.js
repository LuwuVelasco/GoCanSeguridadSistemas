export function fetchReportHistory(mostrarReportes) {
    fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_reporte.php')
        .then(response => response.json())
        .then(data => {
            if (data.estado === "success") {
                mostrarReportes(data.reportes);
            } else {
                console.error("Error:", data.mensaje);
            }
        })
        .catch(error => console.error("Error:", error));
}

export function mostrarReportes(reportes) {
    const reportHistory = document.getElementById('reportHistory');
    reportHistory.innerHTML = '';

    reportes.forEach(reporte => {
        const div = document.createElement("div");
        div.classList.add("reporte");

        const resumen = document.createElement("div");
        resumen.classList.add("reporte-resumen");
        resumen.textContent = `Propietario: ${reporte.propietario}, Mascota: ${reporte.nombre_mascota}`;
        resumen.addEventListener("click", function () {
            const detalle = this.nextElementSibling;
            detalle.style.display = detalle.style.display === "block" ? "none" : "block";
        });

        const detalle = document.createElement("div");
        detalle.classList.add("reporte-detalle");

        detalle.innerHTML = `
            <p>Síntomas: ${reporte.sintomas}</p>
            <p>Diagnóstico: ${reporte.diagnostico}</p>
            <p>Receta: ${reporte.receta}</p>
            <p>Fecha: ${reporte.fecha}</p>
        `;

        div.appendChild(resumen);
        div.appendChild(detalle);

        reportHistory.appendChild(div);
    });
}
