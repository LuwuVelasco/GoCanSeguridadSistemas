document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('crearCuentaBtn').addEventListener('click', registrarUsuario);
});

function registrarUsuario() {
    let email = document.getElementById('email').value;
    let nombre = document.getElementById('nombre').value;
    let password = document.getElementById('password').value;
    let token = null;

    console.log("email:", email);
    console.log("nombre:", nombre);
    console.log("password:", password);
    console.log("token:", token);

    fetch("http://localhost/GoCan/src/modules/php/registro.php", {
        method: "POST",
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email: email, nombre: nombre, password: password, token: token }),
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        } else {
            throw new Error('Error al registrar el usuario');
        }
    })
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
}
