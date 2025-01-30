import { loadLogUsuarios } from "../components/log_usuarios.js";
import { loadLogAplicacion } from "../components/log_aplicacion.js";
import { loadDoctorCitas, deleteDoctorCita } from "../components/citas.js";

document.addEventListener("DOMContentLoaded", () => {
    const idRol = localStorage.getItem("id_rol"); // Obtener el ID del rol del usuario
    const idDoctor = localStorage.getItem("id_doctores") || null;

    if (!idRol) {
        alert("No se encontró un rol asignado. Contacta al administrador.");
        return;
    }

    // Validar la existencia de contenedores
    const opciones = document.getElementById("opciones");
    const secciones = document.getElementById("secciones");

    if (!opciones || !secciones) {
        console.error("No se encontraron los contenedores necesarios en el DOM.");
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
                generarInterfaz(data.permisos, opciones, secciones);
            } else {
                alert("Error al obtener permisos del rol: " + data.message);
            }
        })
        .catch((error) => console.error("Error al obtener permisos del rol:", error));
});

function generarInterfaz(permisos, opciones, secciones, idDoctor) {
    if (permisos.registro_mascotas) { //REGISTRO MASCOTAS
        opciones.innerHTML += `
            <div id="bt0" class="button" onclick="openModal('petModal')">
                <i class="fi fi-sr-paw-heart"></i>
                <h5>Registro mascotas</h5>
            </div>`;
    }

    if (permisos.ver_mascotas_registradas) { //VER MASCOTAS REGISTRADAS
        opciones.innerHTML += `
            <div id="bt1" class="button" onclick="openModal('tablaModal')">
                <i class="fi fi-sr-pets"></i>
                <h5>Información Mascotas</h5>
            </div>`;
    }

    if (permisos.registro_receta) { //REGISTRO RECETAS
        opciones.innerHTML += `
            <div id="bt2" class="button" onclick="openModal('reportModal')">
                <i class="fi fi-sr-clipboard-list"></i>
                <h5>Registro Reportes</h5>
            </div>`;
    }

    if (permisos.ver_reportes_recetas) { //VER HISTORIAL DE RECETAS
        opciones.innerHTML += `
            <div id="bt3" class="button" onclick="openModal('historyModal')">
                <i class="fi fi-sr-time-past"></i>
                <h5>Historial Reportes</h5>
            </div>`;
    }

    if (permisos.ver_clientes_registrados) { //VER CLIENTES REGISTRADOS
        opciones.innerHTML += `
            <div id="bt4" class="button" onclick="openModal('clientesModal')">
                <i class="fi fi-sr-target-audience"></i>
                <h5>Clientes Registrados</h5>
            </div>`;
    }

    if (permisos.gestion_roles) { //GESTION DE ROLES
        opciones.innerHTML += `
            <div id="bt5" class="button">
                <i class="fi fi-sr-cogs"></i>
                <h5>Gestión de Roles</h5>
                <button onclick="gestionarRoles()">Administrar Roles</button>
            </div>`;
    }

    if (permisos.editar_configuracion) { //EDITAR CONFIGURACION DE CONTRASEÑAS
        opciones.innerHTML += `
            <div id="bt6" class="button">
                <i class="fi fi-sr-settings"></i>
                <h5>Editar Configuración</h5>
                <button onclick="editarConfiguracion()">Configurar</button>
            </div>`;
    }

    if (permisos.registro_funcionarios) { //REGISTRO FUNCIONARIOS 
        opciones.innerHTML += `
            <div id="bt7" class="button" onclick="openModal('doctorModal')">
                <i class="fi fi-sr-stethoscope"></i>
                <h5>Registro funcionarios</h5>
            </div>`;
    }

    if (permisos.administracion_mascota) { //ADMINISTRACION MASCOTAS
        opciones.innerHTML += `
            <div id="bt8" class="button" onclick="openModal('tablaModal')">
                <i class="fi fi-sr-pets"></i>
                <h5>Gestión Mascotas</h5>
            </div>`;
    }

    if (permisos.registrar_citas) { //AGENTAR CITA
        opciones.innerHTML += `
            <div id="bt9" class="button" onclick="openModal('reserveModal')">
                <i class="fi fi-sr-notebook-alt"></i>
                <h5>Agendar Cita</h5>
            </div>`;
    }

    if (idDoctor && permisos.ver_citas) { //VER CITAS DOCTORES
        secciones.innerHTML += `
            <div class="separator">
                    <h3>Citas Próximas</h3>
                    <button id="sortButton" onclick="sortCitas()">Ordenar por Fecha</button>
                    <input type="text" id="searchInput" oninput="filtrarCitas()" placeholder="Buscar por propietario...">
                </div>
                
                <table>
                    <tbody>
                        <!-- Las filas de citas se agregarán aquí dinámicamente -->
                    </tbody>
                </table>`;
        const searchText = this.value.toLowerCase();
        loadDoctorCitas(
            `http://localhost/GoCanSeguridadSistemas/src/modules/php/doctores.php?id_doctor=${id_doctor}`,
            "table tbody",
            searchText
        );
    } else if (permisos.ver_citas) { //VER CITAS CLIENTES
        opciones.innerHTML += `
        <div id="bt10" class="button" onclick="openModal('viewReservationsModal');">
            <i class="fi fi-sr-ballot"></i>
            <h5>Reservas</h5>
        </div>`;
    }

    if (permisos.vista_log_usuarios) { //VISTA LOG USUARIOS
        secciones.innerHTML += `
            <section>
                <h3 class="separator">Registro de Usuarios en la Página</h3>
                <div style="max-height: 280px; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px;">
                    <table id="log-usuarios-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Nombre del Usuario</th>
                                <th>Acción</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Las filas se añadirán aquí mediante JavaScript -->
                        </tbody>
                    </table>
                </div>
            </section>`;
            const urlLogUsuarios = 'http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_logs_usuarios.php';
        loadLogUsuarios(urlLogUsuarios, "#log-usuarios-table tbody");
    }

    if (permisos.vista_log_aplicacion) { //VISTA LOG APLICACION
        secciones.innerHTML += `
            <section>
                <h3 class="separator">Registro de Acciones en la Aplicación</h3>
                <div style="max-height: 280px; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px;">
                    <table id="log-aplicacion-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Nombre del Usuario</th>
                                <th>Acción</th>
                                <th>Función Afectada</th>
                                <th>Dato Modificado</th>
                                <th>Descripción</th>
                                <th>Valor Original</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Las filas se añadirán aquí mediante JavaScript -->
                        </tbody>
                    </table>
                </div>
            </section>`;

        const urlLogAplicacion = "http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_log_aplicacion.php";
        loadLogAplicacion(urlLogAplicacion, "#log-aplicacion-table tbody");
    }

    // Si no hay permisos, mostrar mensaje
    if (opciones.innerHTML.trim() === "" && secciones.innerHTML.trim() === "") {
        secciones.innerHTML = "<p>No tienes permisos para acceder a esta funcionalidad.</p>";
    }
}

// Funciones de botones
function verMascotasRegistradas() {
    alert("Accediendo a la Información de Mascotas...");
}

function gestionarRoles() {
    alert("Accediendo a Gestión de Roles...");
}

function editarConfiguracion() {
    alert("Accediendo a Configuración...");
}

// Funciones de botones
function sortCitas() {
    alert("Ordenando Citas...");
}

function filtrarCitas() {
    const searchText = document.getElementById("searchInput").value.toLowerCase();
    const idDoctor = localStorage.getItem("id_doctores");
    if (idDoctor) {
        loadDoctorCitas("http://localhost/GoCanSeguridadSistemas/src/modules/php/doctores.php", "#citasTable tbody", searchText);
    }
}
