document.addEventListener("DOMContentLoaded", () => {
    const idRol = localStorage.getItem("id_rol"); // Obtener el ID del rol del usuario

    if (!idRol) {
        alert("No se encontró un rol asignado. Contacta al administrador.");
        return;
    }

    // Obtener los permisos del rol desde el backend
    fetch("http://localhost/GoCanSeguridadSistemas/src/modules/php/validar_permisos.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id_rol=${encodeURIComponent(idRol)}`,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                generarInterfaz(data.permisos);
            } else {
                alert("Error al obtener permisos del rol: " + data.message);
            }
        })
        .catch((error) => console.error("Error al obtener permisos del rol:", error));
});

function generarInterfaz(permisos) {
    const contenido = document.getElementById("contenido-dinamico");

    // Crear la interfaz según los permisos
    if (permisos.registro_mascotas) {
        contenido.innerHTML += `
            <div>
                <h2>Registro de Mascotas</h2>
                <button onclick="abrirRegistroMascotas()">Registrar Mascotas</button>
            </div>`;
    }

    if (permisos.ver_mascotas_registradas) {
        contenido.innerHTML += `
            <div>
                <h2>Información de Mascotas</h2>
                <button onclick="verMascotasRegistradas()">Ver Mascotas</button>
            </div>`;
    }

    if (permisos.gestion_roles) {
        contenido.innerHTML += `
            <div>
                <h2>Gestión de Roles</h2>
                <button onclick="gestionarRoles()">Administrar Roles</button>
            </div>`;
    }

    if (permisos.vista_log_usuarios) {
        contenido.innerHTML += `
            <div>
                <h2>Logs de Usuarios</h2>
                <button onclick="verLogsUsuarios()">Ver Logs</button>
            </div>`;
    }

    // Si no tiene permisos, mostrar mensaje
    if (contenido.innerHTML.trim() === "") {
        contenido.innerHTML = "<p>No tienes permisos para acceder a esta funcionalidad.</p>";
    }
}

function abrirRegistroMascotas() {
    alert("Accediendo a Registro de Mascotas...");
}

function verMascotasRegistradas() {
    alert("Accediendo a la Información de Mascotas...");
}

function gestionarRoles() {
    alert("Accediendo a Gestión de Roles...");
}

function verLogsUsuarios() {
    alert("Accediendo a Logs de Usuarios...");
}
