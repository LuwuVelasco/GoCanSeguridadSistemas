document.addEventListener('DOMContentLoaded', function() {
    emailjs.init("CgWcVJlbFn_MVTJRc");
    document.getElementById('crearCuentaBtn').addEventListener('click', registrarUsuario);
});

function registrarUsuario() {
    let email = document.getElementById('email').value;
    let nombre = document.getElementById('nombre').value;
    let password = document.getElementById('password').value;
    let token = generateToken(); // Generar el token aquí
    let cargo = null;

    console.log("Datos capturados:", email, nombre, password, token, cargo);

    // Envía primero el token por correo
    emailjs.send("service_zfiz7yd", "template_g2k2wgi", {
        to_email: email,
        nombre: nombre,
        token: token
    }).then(function(response) {
        console.log('Correo electrónico enviado con éxito:', response);
        promptForToken(token, email, nombre, password); // Solicita al usuario que ingrese el token
    }, function(error) {
        console.error('Error al enviar el correo electrónico:', error);
        alert('Error al enviar el correo electrónico');
    });
}

function promptForToken(sentToken, email, nombre, password, cargo) {
    let userToken = prompt('Por favor, ingrese el token que ha sido enviado a su correo electrónico:');
    if (userToken === sentToken) {
        // Si el token coincide, realiza el registro en la base de datos
        fetch("http://localhost/GoCan/src/modules/php/registro.php", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email: email, nombre: nombre, password: password, token: sentToken, cargo: cargo }),
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
            alert('Error al registrar el usuario');
        });
    } else {
        alert('El token ingresado no es correcto. Intente nuevamente.');
    }
}

function generateToken() {
    // Generar y devolver un token aleatorio (puedes usar cualquier método aquí)
    return Math.random().toString(36).substring(2, 15);
}


