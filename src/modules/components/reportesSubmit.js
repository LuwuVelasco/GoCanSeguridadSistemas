// Manejar el evento submit del formulario de reportes
document.getElementById('reportForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Evitar que el formulario se envíe de forma predeterminada

    const formData = new FormData(event.target);

    // Realizar la solicitud POST al servidor para registrar el reporte
    fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/registrar_reporte.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.estado === 'success') {
                alert('Reporte registrado exitosamente');
                closeModal('reportModal'); // Cerrar el modal después del registro
                event.target.reset(); // Resetear el formulario
            } else {
                alert('Error al registrar el reporte: ' + data.mensaje);
            }
        })
        .catch(error => {
            console.error('Error al registrar el reporte:', error);
            alert('Error al registrar el reporte.');
        });
});
