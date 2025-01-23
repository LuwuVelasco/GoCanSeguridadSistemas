document.addEventListener('DOMContentLoaded', function() {
    const botonIngresar = document.getElementById('ingresarBtn');
    let intentosFallidos = 0;
    let bloqueado = false;

    if (botonIngresar) {
        botonIngresar.addEventListener('click', function(event) {
            event.preventDefault(); // Prevenir el comportamiento por defecto del botón de submit
            if (!bloqueado) {
                iniciarSesion();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Acceso bloqueado',
                    text: 'Contraseña incorrecta, demasiados intentos. Por favor, espere 30 segundos.'
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
        fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&g-recaptcha-response=${encodeURIComponent(recaptchaResponse)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.estado === "success") {
                localStorage.setItem('id_usuario', data.id_usuario);
                localStorage.setItem('id_doctores', data.id_doctores);           
                if (data.rol === "Doctor") {
                    if (data.id_doctores) {
                        window.location.href = 'http://localhost/GoCanSeguridadSistemas/src/modules/coreDoctores/indexdoctores.html';
                    } else {
                        window.location.href = 'http://localhost/GoCanSeguridadSistemas/src/modules/citas/citas.html';
                    }
                } else if (data.rol === "Administrador") {
                    window.location.href = 'http://localhost/GoCanSeguridadSistemas/src/modules/coreadmin/indexadmin.html';
                } else if (data.rol === "Cliente") {
                    window.location.href = 'http://localhost/GoCanSeguridadSistemas/src/modules/citas/citas.html';
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rol no reconocido',
                        text: 'El rol proporcionado no es válido.'
                    });
                }
            } else {
                intentosFallidos++;
                if (intentosFallidos >= 3) {
                    bloquearBoton();
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error de inicio de sesión',
                    text: data.mensaje
                });
            }     
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de red',
                text: 'Error al procesar la solicitud'
            });
        });
    }

    function bloquearBoton() {
        bloqueado = true;
        setTimeout(function() {
            bloqueado = false;
            intentosFallidos = 0;
        }, 30000); // Bloquear durante 30 segundos
    }
});

document.addEventListener('DOMContentLoaded', function() {
    emailjs.init("XhWMaSqNfASzICac5");
    const forgotPasswordLink = document.getElementById('forgotPassword');
    const forgotPasswordModal = document.getElementById('forgotPasswordModal');
    const resetPasswordModal = document.getElementById('resetPasswordModal');

    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', function(event) {
            event.preventDefault();
            forgotPasswordModal.style.display = 'block';
        });
    }

    const sendVerificationCodeBtn = document.getElementById('sendVerificationCodeBtn');
    if (sendVerificationCodeBtn) {
        sendVerificationCodeBtn.addEventListener('click', function(event) {
            event.preventDefault();
            sendVerificationCode();
        });
    }

    const resetPasswordBtn = document.getElementById('resetPasswordBtn');
    if (resetPasswordBtn) {
        resetPasswordBtn.addEventListener('click', function(event) {
            event.preventDefault();
            resetPassword();
        });
    }

    // Cerrar el modal cuando se hace clic en la 'X'
    const closeButtons = document.querySelectorAll('.close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            forgotPasswordModal.style.display = 'none';
            resetPasswordModal.style.display = 'none';
        });
    });
});

function resetPassword() {
    const email = document.getElementById('forgotEmail').value;
    const verificationCode = document.getElementById('verificationCode').value;
    const newPassword = document.getElementById('newPassword').value;

    // Validar la contraseña
    const validacion = validarPassword(newPassword);

    if (!validacion.isValid) {
        // Mostrar los requisitos que no se cumplen
        Swal.fire({
            icon: 'error',
            title: 'Contraseña inválida',
            html: `La contraseña no cumple con los siguientes requisitos:<br><ul>${validacion.requisitos.map(req => `<li>${req}</li>`).join('')}</ul>`
        });
        return;
    }

    // Verificar que el código de verificación ingresado sea igual al código generado aleatoriamente
    if (verificationCode !== sessionStorage.getItem('verificationCode')) {
        Swal.fire({
            icon: 'error',
            title: 'Código incorrecto',
            text: 'El código de verificación es incorrecto'
        });
        return;
    }

    // Si el código de verificación es correcto y la contraseña es válida, procedemos a cambiar la contraseña
    fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/new_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `email=${encodeURIComponent(email)}&new_password=${encodeURIComponent(newPassword)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.estado === "success") {
            Swal.fire({
                icon: 'success',
                title: 'Contraseña cambiada',
                text: 'Contraseña cambiada exitosamente'
            });
            document.getElementById('resetPasswordModal').style.display = 'none';
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.mensaje
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de red',
            text: 'Error al procesar la solicitud'
        });
    });
}

// Función validarPassword
function validarPassword(password) {
    // Lista de validaciones ampliada y más precisa
    const validaciones = [
        { regex: /.{8,}/, mensaje: "Al menos 8 caracteres" },
        { regex: /[A-Z]/, mensaje: "Una letra mayúscula" },
        { regex: /[a-z]/, mensaje: "Una letra minúscula" },
        { regex: /\d/, mensaje: "Un número" },
        { regex: /[!@#$%^&*()_+\-=\[\]{};:,.<>?]/, mensaje: "Un carácter especial" },
        { regex: /^[^\s]+$/, mensaje: "No debe contener espacios en blanco" },
        { regex: /^(?!.*(.)\1{2})/, mensaje: "No debe tener caracteres repetidos más de dos veces seguidas" }
    ];

    const patronesComunes = ['123', '456', '789', 'abc', 'qwerty', 'password', 'admin', 'user'];

    const requisitos = [];
    validaciones.forEach(validacion => {
        if (!validacion.regex.test(password)) {
            requisitos.push(validacion.mensaje);
        }
    });

    if (patronesComunes.some(patron => password.toLowerCase().includes(patron))) {
        requisitos.push("No debe contener secuencias comunes (123, abc, qwerty, etc.)");
    }

    if (password.length > 50) {
        requisitos.push("No debe exceder los 50 caracteres");
    }

    return {
        isValid: requisitos.length === 0,
        requisitos: requisitos
    };
}

function sendVerificationCode() {
    const email = document.getElementById('forgotEmail').value;
    const verificationCode = generateRandomCode(); // Generar código aleatorio
    console.log('Código de verificación:', verificationCode);

    // Almacenar el código de verificación en sessionStorage para la comparación posterior
    sessionStorage.setItem('verificationCode', verificationCode);

    // Aquí debes enviar el correo electrónico con el código de verificación
    emailjs.send("service_nhpwkm8", "template_48zopgh", {
        to_email: email,
        verification_code: verificationCode
    }).then(function(response) {
        console.log('Correo electrónico enviado con éxito:', response);
        Swal.fire({
            icon: 'success',
            title: 'Código enviado',
            text: 'Revisa tu correo electrónico para el código de verificación'
        });
        document.getElementById('forgotPasswordModal').style.display = 'none';
        document.getElementById('resetPasswordModal').style.display = 'block';
    }, function(error) {
        console.error('Error al enviar el correo electrónico:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al enviar el correo electrónico'
        });
    });
}

function generateRandomCode() {
    // Función para generar un código aleatorio de 5 caracteres
    return Math.random().toString(36).substring(2, 7).toUpperCase();
}