// Función para eliminar un doctor
export function eliminarDoctor(id_doctores) {
    if (confirm('¿Estás seguro de que deseas eliminar este doctor?')) {
        fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/eliminarDoctor.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ id: id_doctores })
        })
        .then(response => response.json())
        .then(data => {
            if (data.estado === "success") {
                alert(data.mensaje);
                loadData('http://localhost/GoCanSeguridadSistemas/src/modules/php/listadoctores.php', '#lista-veterinarios');
            } else {
                alert('Error al eliminar el doctor: ' + data.mensaje);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud.');
        });
    }
}

// Función para enviar el correo con la contraseña generada
function enviarCorreoPassword(email, password, nombre) {
    emailjs.init("ij9YfbRaftvgLVSFc"); // Inicializar EmailJS con la clave pública

    emailjs.send("service_r6padno", "template_84cbdhr", {
        to_name: nombre, // Nombre del usuario
        to_email: email, // Email del usuario
        password: password // Contraseña generada
    })
    .then(() => {
    })
    .catch((error) => {
        console.error("Error al enviar el correo:", error);
        alert("Ocurrió un error al enviar el correo con la contraseña.");
    });
}

// Función para inicializar el formulario de registro de funcionarios
export function initFuncionarioForm(formSelector, especialidadUrl) {
    const form = document.querySelector(formSelector);
    const esVeterinarioCheckbox = form.querySelector("#esVeterinario");
    const especialidadContainer = form.querySelector(".especialidad-container");
    const especialidadSelect = form.querySelector("#especialidad");
    const rolSelect = form.querySelector("#rol");
    const emailField = form.querySelector("#correo");
    const nameField = form.querySelector("#nombre");
    const passwordField = form.querySelector("#password");

    const confirmModal = document.getElementById("confirmModal");
    const confirmActionBtn = document.getElementById("confirmAction");
    const cancelConfirmationBtn = document.getElementById("cancelConfirmation");

    let isAdmin = false; // Variable para verificar si es rol de administrador

    // Generar contraseña autogenerada basada en el nombre
    nameField.addEventListener("input", () => {
        const nombre = nameField.value.trim().toLowerCase();
        const [firstName, lastName] = nombre.split(" ");
        passwordField.value = firstName && lastName ? `${firstName}_${lastName}.123` : "Autogenerada.123";
    });

    // Mostrar/Ocultar especialidad si es veterinario
    esVeterinarioCheckbox.addEventListener("change", () => {
        especialidadContainer.style.display = esVeterinarioCheckbox.checked ? "block" : "none";
    });

    // Detectar si se selecciona el rol de Administrador
    rolSelect.addEventListener("change", () => {
        isAdmin = rolSelect.value === "1"; // Asume que el rol de Administrador tiene el ID 1
    });

    // Validar formato del email
    function validateEmail(email) {
        const emailPattern = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
        return emailPattern.test(email);
    }

    // Validar nombre completo (nombre y apellido)
    function validateFullName(name) {
        const nameParts = name.trim().split(" ");
        return nameParts.length >= 2 && nameParts[0] !== "" && nameParts[1] !== "";
    }

    // Validar que la especialidad esté seleccionada si es veterinario
    function validateEspecialidad() {
        return especialidadSelect.value !== "";
    }

    // Enviar formulario
    form.addEventListener("submit", (e) => {
        e.preventDefault();

        const email = emailField.value.trim();
        const nombre = nameField.value.trim();

        // Verificar que el nombre tenga al menos dos palabras (nombre y apellido)
        if (!validateFullName(nombre)) {
            alert("El nombre debe incluir al menos un nombre y un apellido.");
            nameField.focus();
            return;
        }

        // Verificar formato del email
        if (!validateEmail(email)) {
            alert("El correo electrónico debe ser válido y terminar en @gmail.com.");
            emailField.focus();
            return;
        }

        // Verificar que la especialidad esté seleccionada si es veterinario
        if (esVeterinarioCheckbox.checked && !validateEspecialidad()) {
            alert("Debes seleccionar una especialidad si el usuario es veterinario.");
            especialidadSelect.focus();
            return;
        }

        const data = new FormData(form);
        const url = esVeterinarioCheckbox.checked
            ? "http://localhost/GoCanSeguridadSistemas/src/modules/php/registrar_veterinario.php"
            : "http://localhost/GoCanSeguridadSistemas/src/modules/php/registrar_funcionario.php";

        if (isAdmin) {
            // Mostrar el modal de confirmación
            confirmModal.style.display = "flex";

            // Manejar la confirmación
            confirmActionBtn.onclick = () => {
                confirmModal.style.display = "none";
                registrarFuncionario(url, data, form, email, passwordField.value, nombre);
            };

            // Manejar la cancelación
            cancelConfirmationBtn.onclick = () => {
                confirmModal.style.display = "none";
            };
        } else {
            // Registrar directamente si no es administrador
            registrarFuncionario(url, data, form, email, passwordField.value, nombre);
        }
    });
}

// Función para registrar al funcionario
function registrarFuncionario(url, data, form, email, password, nombre) {
    fetch(url, {
        method: "POST",
        body: data,
    })
    .then((response) => response.json())
    .then((res) => {
        if (res.estado === "success") {
            // Mostrar mensaje de éxito con SweetAlert2
            Swal.fire({
                title: 'Éxito',
                text: res.mensaje,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                // Enviar correo con la contraseña
                enviarCorreoPassword(email, password, nombre);
                // Cerrar modal y resetear formulario
                closeModal("doctorModal");
                form.reset();
            });
        } else {
            // Mostrar mensaje de error con SweetAlert2
            Swal.fire({
                title: 'Error',
                text: res.mensaje,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch((error) => {
        console.error("Error:", error);
        // Mostrar error genérico con SweetAlert2
        Swal.fire({
            title: 'Error',
            text: 'Ocurrió un error al registrar el funcionario.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
}

export function loadFuncionarios(url, tableSelector) {
    const tableElement = document.querySelector(tableSelector); // Quita `tbody` extra
    if (!tableElement) {
        console.error("El selector de la tabla no coincide con ningún elemento en el DOM:", tableSelector);
        return;
    }

    fetch(url)
        .then(response => response.json())
        .then(data => {
            tableElement.innerHTML = ''; // Limpiar tabla antes de cargar los datos

            if (!Array.isArray(data) || data.length === 0) {
                tableElement.innerHTML = '<tr><td colspan="4">No hay funcionarios registrados.</td></tr>';
                return;
            }

            data.forEach(funcionario => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${funcionario.id_usuario || 'N/A'}</td>
                    <td>${funcionario.nombre || 'N/A'}</td>
                    <td>${funcionario.especialidad || '—'}</td>
                    <td>
                        <button type="eliminar" onclick="deleteFuncionario(${funcionario.id_usuario})">Eliminar</button>
                    </td>
                `;
                tableElement.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error al cargar funcionarios:', error);
            alert('Error al cargar funcionarios. Revisa la consola para más detalles.');
        });
}

function deleteFuncionario(idFuncionario) {
    if (!idFuncionario) {
        alert('ID de funcionario no válido.');
        return;
    }

    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/eliminar_funcionario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_usuario=${encodeURIComponent(idFuncionario)}`,
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        Swal.fire('Eliminado', data.message, 'success');
                        loadFuncionarios('http://localhost/GoCanSeguridadSistemas/src/modules/php/listadoctores.php', '#lista-veterinarios');
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch((error) => {
                    console.error('Error al eliminar funcionario:', error);
                    Swal.fire('Error', 'Hubo un error al intentar eliminar el funcionario.', 'error');
                });
        }
    });
}

window.deleteFuncionario = deleteFuncionario;