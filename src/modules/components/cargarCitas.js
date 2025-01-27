import { eliminarCita } from './eliminarCitas.js';

export async function cargarCitas() {
    const idUsuario = localStorage.getItem("id_usuario");
    if (!idUsuario) {
        alert("No se encontró el ID del usuario. Inicia sesión nuevamente.");
        return;
    }

    try {
        const response = await fetch("http://localhost/GoCanSeguridadSistemas/src/modules/php/reservas.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_usuario: idUsuario }),
        });

        const citas = await response.json();

        if (citas.error) {
            console.error("Error al cargar citas:", citas.error);
            return;
        }

        // Renderizar citas en la tabla
        const reservationsList = document.getElementById("reservationsList");
        reservationsList.innerHTML = ""; // Limpiar contenido previo

        citas.forEach(cita => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${cita.propietario}</td>
                <td>${cita.servicio}</td>
                <td>${cita.fecha}</td>
                <td>${cita.horario}</td>
                <td><button type="eliminar" class="delete-btn" data-id="${cita.id_cita}">Eliminar</button></td>
            `;
            reservationsList.appendChild(row);
        });

        // Agregar eventos a botones de eliminar
        document.querySelectorAll(".delete-btn").forEach(button => {
            button.addEventListener("click", (e) => eliminarCita(e.target.dataset.id));
        });

    } catch (error) {
        console.error("Error al cargar las citas:", error);
    }
}
