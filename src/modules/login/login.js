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
            // En tu login.js, después de establecer el localStorage
localStorage.setItem('id_usuario', data.id_usuario);
console.log('id_usuario guardado:', localStorage.getItem('id_usuario'));
            // Redirigir al usuario basado en el valor de cargo
            if (!data.cargo) {
                window.location.href = 'http://localhost/GoCan/src/modules/core/index.html';
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
