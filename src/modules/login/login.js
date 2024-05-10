document.addEventListener('DOMContentLoaded', function() {
    const botonIngresar = document.getElementById('ingresarBtn');
    if (botonIngresar) {
        botonIngresar.addEventListener('click', function(event) {
            event.preventDefault(); // Prevenir el comportamiento por defecto del botón de submit
            iniciarSesion();
        });
    } else {
        console.error('El botón de ingreso no se encontró en el DOM');
    }
});

function iniciarSesion() {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    fetch('http://localhost/GoCan/src/modules/php/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.estado === "success") {
            // Redirigir al usuario basado en el valor de cargo
            if (!data.cargo) {
                window.location.href = 'http://localhost/GoCan/src/modules/citas/citas.html';
            } else {
                window.location.href = 'http://localhost/GoCan/src/modules/coreadmin/indexadmin.html';
            }
        } else {
            alert(data.mensaje); // Mostrar mensaje de error
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    emailjs.init("qzlkC2mOywaQA8mot"); // Asegúrate de usar tu userID correcto aquí

    
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

function sendVerificationCode() {
    const email = document.getElementById('forgotEmail').value;
    const verificationCode = generateRandomCode(); // Generar código aleatorio
    console.log('Código de verificación:', verificationCode);

    // Aquí debes enviar el correo electrónico con el código de verificación
    emailjs.send("service_kck40bs", "template_hi16edm", {
        to_email: email,
        verification_code: verificationCode
    }).then(function(response) {    
        console.log('Correo electrónico enviado con éxito:', response);
        document.getElementById('forgotPasswordModal').style.display = 'none';
        document.getElementById('resetPasswordModal').style.display = 'block';
    }, function(error) {
        console.error('Error al enviar el correo electrónico:', error);
        alert('Error al enviar el correo electrónico');
    });
}


function resetPassword() {
    const email = document.getElementById('forgotEmail').value;
    const verificationCode = document.getElementById('verificationCode').value;
    const newPassword = document.getElementById('newPassword').value;

    // Primero, cambiamos la contraseña del correo electrónico
    fetch('http://localhost/GoCan/src/modules/php/new_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `email=${encodeURIComponent(email)}&new_password=${encodeURIComponent(newPassword)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.estado === "success") {
            // Si el cambio de contraseña del correo electrónico es exitoso, procedemos a cambiarla en la base de datos
            fetch('http://localhost/GoCan/src/modules/php/new_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `email=${encodeURIComponent(email)}&verification_code=${encodeURIComponent(verificationCode)}&new_password=${encodeURIComponent(newPassword)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.estado === "success") {
                    alert('Contraseña cambiada exitosamente');
                    document.getElementById('resetPasswordModal').style.display = 'none';
                } else {
                    alert(data.mensaje); // Mostrar mensaje de error
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        } else {
            alert(data.mensaje); // Mostrar mensaje de error
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    });
}



function generateRandomCode() {
    // Función para generar un código aleatorio de 5 caracteres
    return Math.random().toString(36).substring(2, 7).toUpperCase();
}