document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('reservar').addEventListener('click', function(event) {
        event.preventDefault();
        registrarcita();
    });
});
function registrarcita() {
    let propietario = document.getElementById('propietario').value;
    let horario = document.getElementById('hora').value;
    let fecha = document.getElementById('fecha').value;
    let servicio = document.getElementById('servicio').value;
    let doctor = document.getElementById('doctor').value;
    let id_usuario = localStorage.getItem('id_usuario')

    console.log("propietario:", propietario);
    console.log("hora:", horario);
    console.log("fecha:", fecha);
    console.log("servicio:", servicio);
    console.log("doctor",doctor);
    console.log("id_usuario",id_usuario);

    fetch("http://localhost/GoCan/src/modules/php/citas.php", {
        method: "POST",
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            propietario: propietario,
            servicio: servicio,
            doctor: doctor,
            id_usuario: id_usuario,
            fecha: fecha,
            horario: horario
        }),
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        } else {
            throw new Error('Error al registrar la cita');
        }
    })
    .then(data => {
        console.log(data);
        alert('Cita registrada correctamente. ID de cita: ' + data.id_cita);
    })
    .catch(error => console.error('Error:', error));
}