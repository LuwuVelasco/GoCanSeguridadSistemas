import { cargarCitas } from "./cargarCitas.js";

export async function eliminarCita(idCita) {
    const idUsuario = localStorage.getItem("id_usuario");

    if (!idUsuario) {
        alert("No se encontró el ID del usuario. Inicia sesión nuevamente.");
        return;
    }

    if (!confirm("¿Estás seguro de que deseas eliminar esta cita?")) {
        return;
    }

    try {
        const bodyData = { id_cita: idCita, id_usuario: idUsuario };
        console.log("Enviando datos:", bodyData); // Depuración

        const response = await fetch("http://localhost/GoCanSeguridadSistemas/src/modules/php/eliminar_cita_cliente.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(bodyData),
        });

        const result = await response.json();
        if (result.estado === "success") {
            alert(result.mensaje);
            cargarCitas(); // Recargar las citas después de eliminar
        } else {
            alert(`Error: ${result.mensaje}`);
        }
    } catch (error) {
        console.error("Error al eliminar la cita:", error);
        alert("Error al eliminar la cita. Intenta nuevamente.");
    }
}
