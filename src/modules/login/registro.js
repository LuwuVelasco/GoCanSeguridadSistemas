document.addEventListener('DOMContentLoaded', function() {
    emailjs.init("CgWcVJlbFn_MVTJRc");
    document.getElementById('crearCuentaBtn').addEventListener('click', registrarUsuario);
});

function registrarUsuario() {
    let email = document.getElementById('email').value;
    let nombre = document.getElementById('nombre').value;
    let password = document.getElementById('password').value;
    let token = generateToken();
    let cargo = true;

    // Validar que la contraseña cumpla con los requisitos
    if (password.length < 8 || !/[A-Z]/.test(password)) {
        alert('La contraseña debe tener al menos 8 caracteres y contener al menos una letra mayúscula.');
        return; // Detener el proceso de registro si la contraseña no cumple los requisitos
    }

    console.log("Datos capturados:", email, nombre, password, token, cargo);

    emailjs.send("service_zfiz7yd", "template_g2k2wgi", {
        to_email: email,
        nombre: nombre,
        token: token
    }).then(function(response) {
        console.log('Correo electrónico enviado con éxito:', response);
        promptForToken(token, email, nombre, password, cargo);
    }, function(error) {
        console.error('Error al enviar el correo electrónico:', error);
        alert('Error al enviar el correo electrónico');
    });
}

function promptForToken(sentToken, email, nombre, password, cargo) {
    let userToken = prompt('Por favor, ingrese el token que ha sido enviado a su correo electrónico:');
    console.log("Token enviado:", sentToken); // Log del token enviado
    console.log("Token ingresado:", userToken); // Log del token ingresado

    if (userToken.trim() === sentToken.toString().trim()) { 
        fetch("http://localhost/GoCan/src/modules/php/registro.php", {
            method: "POST",
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                email: email,
                nombre: nombre,
                password: password,
                token: sentToken,
                cargo: cargo,
                verified: true
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            alert('Usuario registrado correctamente. ID de usuario: ' + data.id_usuario);
            document.getElementById('email').value = '';
            document.getElementById('nombre').value = '';
            document.getElementById('password').value = '';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Registro exitosamente');
            document.getElementById('email').value = '';
            document.getElementById('nombre').value = '';
            document.getElementById('password').value = '';
        });
    } else {
        alert('El token ingresado no es correcto. Intente nuevamente.');
    }
}

function generateToken() {
    return Math.floor(Math.random() * 900000) + 100000;
}

