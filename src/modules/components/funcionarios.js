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

// Función para cargar datos de doctores en la tabla
export function loadData(url, tbodySelector) {
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector(tbodySelector);
            tbody.innerHTML = '';
            data.forEach(item => {
                const especialidad = item.especialidad || "Sin especialidad"; // Manejo de undefined
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.id_doctores}</td>
                    <td>${item.nombre}</td>
                    <td>${especialidad}</td>
                    <td>
                        <button class="delete-button" onclick="eliminarDoctor(${item.id_doctores})">
                            <i class="ri-delete-bin-6-line"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error al cargar los datos:', error);
            alert('Error al cargar los datos. Verifica la consola para más detalles.');
        });
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
        console.log("Correo enviado exitosamente");
        alert("El correo con la contraseña ha sido enviado correctamente.");
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
    const rolSelect = form.querySelector("#rol");
    const confirmAdministrador = form.querySelector("#confirmAdministrador");
    const passwordField = form.querySelector("#password");

    // Generar contraseña autogenerada basada en el nombre
    form.nombre.addEventListener("input", () => {
        const nombre = form.nombre.value.trim().toLowerCase();
        const [firstName, lastName] = nombre.split(" ");
        passwordField.value = firstName && lastName ? `${firstName}_${lastName}.123` : "Autogenerada.123";
    });

    // Mostrar/Ocultar especialidad si es veterinario
    esVeterinarioCheckbox.addEventListener("change", () => {
        especialidadContainer.style.display = esVeterinarioCheckbox.checked ? "block" : "none";
    });

    // Mostrar advertencia si se selecciona "Administrador"
    rolSelect.addEventListener("change", () => {
        confirmAdministrador.style.display = rolSelect.value === "1" ? "block" : "none";
    });

    // Enviar formulario
    form.addEventListener("submit", (e) => {
        e.preventDefault();
    
        const esVeterinario = esVeterinarioCheckbox.checked;
        const url = esVeterinario
            ? "http://localhost/GoCanSeguridadSistemas/src/modules/php/registrar_veterinario.php"
            : "http://localhost/GoCanSeguridadSistemas/src/modules/php/registrar_funcionario.php";
    
        const data = new FormData(form);
        const email = form.querySelector("#correo").value;
        const nombre = form.querySelector("#nombre").value;
        const password = passwordField.value;
    
        fetch(url, {
            method: "POST",
            body: data,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.estado === "success") {
                    alert(data.mensaje);
                    enviarCorreoPassword(email, password, nombre); // Llamar a la función para enviar el correo
                    closeModal("doctorModal");
                } else {
                    alert("Error: " + data.mensaje);
                }
            })
            .catch((error) => {
                console.error("Error:", error);
                alert("Ocurrió un error al registrar el funcionario.");
            });
    });
}