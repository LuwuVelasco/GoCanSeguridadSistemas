document.addEventListener('DOMContentLoaded', function() {
    const BASE_URL = "https://gocan.onrender.com/GoCanSeguridadSistemas/src/modules/php/";
    const botonIngresar = document.getElementById('ingresarBtn');
    let intentosFallidos = 0;
    let bloqueado = false;

    if (botonIngresar) {
        botonIngresar.addEventListener('click', function(event) {
            event.preventDefault();
            if (!bloqueado) {
                iniciarSesion();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Acceso bloqueado',
                    text: 'Contraseña incorrecta, demasiados intentos. Por favor, espere 5 minutos.'
                });
            }
        });
    } else {
        console.error('El botón de ingreso no se encontró en el DOM');
    }

    function iniciarSesion() {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const recaptchaResponse = grecaptcha.getResponse();

        fetch(`${BASE_URL}login.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&g-recaptcha-response=${encodeURIComponent(recaptchaResponse)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.estado === "success") {
                localStorage.setItem('id_usuario', data.id_usuario);
                localStorage.setItem('id_doctores', data.id_doctores);

                fetch(`${BASE_URL}verificar_password.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_usuario: data.id_usuario })
                })
                .then(resp => resp.json())
                .then(passwordCheck => {
                    if (passwordCheck.estado === "expired" || passwordCheck.estado === "change_required") {
                        const expiredModal = new bootstrap.Modal(document.getElementById('passwordExpiredModal'));
                        expiredModal.show();

                        document.getElementById('updateExpiredPasswordBtn').addEventListener('click', () => {
                            const newPassword = document.getElementById('expiredNewPassword').value;
                            const validacion = validarPassword(newPassword);
                            if (!validacion.isValid) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Contraseña inválida',
                                    html: `La contraseña no cumple con los siguientes requisitos:<br><ul>${validacion.requisitos.map(req => `<li>${req}</li>`).join('')}</ul>`
                                });
                                return;
                            }

                            fetch(`${BASE_URL}new_password.php`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `email=${encodeURIComponent(email)}&new_password=${encodeURIComponent(newPassword)}`
                            })
                            .then(updateResp => updateResp.json())
                            .then(updateData => {
                                if (updateData.estado === "success") {
                                    const logData = {
                                        id_usuario: data.id_usuario,
                                        accion: 'actualizacion_contrasena',
                                        descripcion: `El usuario con ID ${data.id_usuario} ha actualizado su contraseña.`
                                    };

                                    fetch(`${BASE_URL}registrar_log_usuario.php`, {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                        body: new URLSearchParams(logData).toString()
                                    });

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Contraseña actualizada',
                                        text: 'Tu contraseña ha sido actualizada con éxito.'
                                    }).then(() => {
                                        expiredModal.hide();
                                        redirigirUsuario(data);
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: updateData.mensaje
                                    });
                                }
                            });
                        });
                    } else {
                        redirigirUsuario(data);
                    }
                });
            } else {
                grecaptcha.reset();
                intentosFallidos++;
                if (intentosFallidos >= 3) {
                    const logData = {
                        accion: 'bloqueo_usuario',
                        descripcion: 'Bloqueo por demasiados intentos fallidos'
                    };

                    fetch(`${BASE_URL}registrar_log_usuario.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams(logData)
                    });

                    bloquearBoton();
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error de inicio de sesión',
                    text: data.mensaje
                });
            }
        });
    }

    function redirigirUsuario(data) {
        localStorage.setItem('id_usuario', data.id_usuario);
        fetch(`${BASE_URL}obtener_rol_usuario.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id_usuario=${encodeURIComponent(data.id_usuario)}`
        })
        .then(response => response.json())
        .then(rolData => {
            localStorage.setItem('id_rol', rolData.id_rol);
            localStorage.setItem('nombre_rol', rolData.nombre_rol);

            let destino;
            if (rolData.nombre_rol === "Doctor") destino = 'coreDoctores/indexdoctores.html';
            else if (rolData.nombre_rol === "Administrador") destino = 'coreadmin/indexadmin.html';
            else if (rolData.nombre_rol === "Cliente") destino = 'citas/citas.html';
            else destino = 'coreVariable/index.html';

            window.location.href = `https://gocan.onrender.com/GoCanSeguridadSistemas/src/modules/${destino}`;
        });
    }

    function bloquearBoton() {
        bloqueado = true;
        setTimeout(() => {
            bloqueado = false;
            intentosFallidos = 0;
        }, 300000);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    emailjs.init("XhWMaSqNfASzICac5");
});

function resetPassword() {
    const BASE_URL = "https://gocan.onrender.com/GoCanSeguridadSistemas/src/modules/php/";
    const email = document.getElementById('forgotEmail').value;
    const verificationCode = document.getElementById('verificationCode').value;
    const newPassword = document.getElementById('newPassword').value;

    if (verificationCode !== sessionStorage.getItem('verificationCode')) {
        Swal.fire({
            icon: 'error',
            title: 'Código incorrecto',
            text: 'El código de verificación es incorrecto'
        });
        return;
    }

    fetch(`${BASE_URL}new_password.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `email=${encodeURIComponent(email)}&new_password=${encodeURIComponent(newPassword)}`
    })
    .then(response => response.json())
    .then(data => {
        Swal.fire({
            icon: 'success',
            title: 'Contraseña cambiada',
            text: 'Contraseña cambiada exitosamente'
        });
    });
}

function sendVerificationCode() {
    const email = document.getElementById('forgotEmail').value;
    const verificationCode = generateRandomCode();
    sessionStorage.setItem('verificationCode', verificationCode);

    emailjs.send("service_nhpwkm8", "template_48zopgh", {
        to_email: email,
        verification_code: verificationCode
    }).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Código enviado',
            text: 'Revisa tu correo electrónico para el código de verificación'
        });
    });
}

function generateRandomCode() {
    return Math.random().toString(36).substring(2, 7).toUpperCase();
}
