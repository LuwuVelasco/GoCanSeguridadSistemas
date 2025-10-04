document.addEventListener('DOMContentLoaded', function () {
    emailjs.init({ publicKey: 'lxBqvP8DcEyWXTcxi' });
    document.getElementById('crearCuentaBtn').addEventListener('click', registrarUsuario);
    
    function validarEmail(email) {
        const emailRegex = /^[^\s@]+@gmail\.com$/;
        if (!emailRegex.test(email)) {
            Swal.fire({
                title: 'Correo electrónico no válido',
                text: 'Por favor, ingrese un correo electrónico válido que contenga un "@" y un dominio correcto.',
                icon: 'error'
            });
            return false;
        }
        return true;
    }
    function verificarEmail(email) {
        return fetch("http://localhost/GoCanSeguridadSistemas/src/modules/php/verificar_email.php", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: email })
        })
            .then(response => response.json())
            .then(data => {
                if (data.estado === "error") {
                    Swal.fire({
                        title: 'Correo en uso',
                        text: data.mensaje,
                        icon: 'error'
                    });
                    return false;
                }
                return true;
            })
            .catch(error => {
                console.error('Error al verificar el correo:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al verificar el correo.',
                    icon: 'error'
                });
                return false;
            });
    }
    

    function registrarUsuario() {
        let email = document.getElementById('email').value;
        let nombre = document.getElementById('nombre').value;
        let password = document.getElementById('password').value;
        let token = generateToken();
        // Validar el formato del email
        if (!validarEmail(email)) {
            return;
        }
        // Validar la contraseña
        const validacionPassword = validarPassword(password);
        if (!validacionPassword.isValid) {
            Swal.fire({
                title: 'Contraseña no válida',
                html: 'La contraseña debe tener:<br>' + validacionPassword.requisitos.map(req => `- ${req}`).join('<br>'),
                icon: 'error'
            });
            return;
        }
        // Verificar si el correo ya existe
        verificarEmail(email).then(isUnique => {
            if (!isUnique) {
                return; // Detener si el correo ya existe
            }
            // Enviar el token al correo usando EmailJS
            emailjs.send('service_l3j8jvq', 'template_6vb9c17', {
            to_email: email,
            nombre: nombre,
            token: token
            })
            .then(() => {
            console.log('Correo enviado');
            promptForToken(token, email, nombre, password);
            })
            .catch(err => {
            console.error('Error al enviar el correo electrónico:', err);
            Swal.fire({ title: 'Error', text: 'Error al enviar el correo electrónico', icon: 'error' });
            });
        });
    }    

    function promptForToken(sentToken, email, nombre, password) {
        Swal.fire({
            title: 'Verificación',
            text: 'Por favor, ingrese el token que ha sido enviado a su correo electrónico:',
            input: 'text',
            showCancelButton: true,
            confirmButtonText: 'Verificar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const userToken = result.value;
                if (userToken.trim() === sentToken.toString().trim()) {
                    fetch("http://localhost/GoCanSeguridadSistemas/src/modules/php/registro.php", {
                        method: "POST",
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            email: email,
                            nombre: nombre,
                            password: password,
                            verified: true
                        })
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.estado === "success") {
                                Swal.fire({
                                    title: 'Éxito',
                                    text: 'Usuario registrado correctamente. Inicie sesión para continuar.',
                                    icon: 'success'
                                }).then(() => {
                                    document.getElementById('email').value = '';
                                    document.getElementById('nombre').value = '';
                                    document.getElementById('password').value = '';
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: data.mensaje || 'Error en el registro',
                                    icon: 'error'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error al procesar la solicitud:', error);
                            Swal.fire({
                                title: 'Error',
                                text: 'Hubo un problema al conectar con el servidor. Verifique los datos enviados.',
                                icon: 'error'
                            });
                        });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'El token ingresado no es correcto. Intente nuevamente.',
                        icon: 'error'
                    });
                }
            }
        });
    }

    function generateToken() {
        return Math.floor(Math.random() * 900000) + 100000;
    }
});
