export function eliminarDoctor(id_doctores) {
    if (confirm('¿Estás seguro de que deseas eliminar este doctor?')) {
        fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/eliminarDoctor.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ id: id_doctores })
        })
        .then(response => response.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.estado === "success") {
                    alert('Doctor eliminado correctamente');
                    loadData('http://localhost/GoCanSeguridadSistemas/src/modules/php/listadoctores.php', '#lista-veterinarios');
                } else {
                    alert('Error al eliminar el doctor: ' + data.mensaje);
                }
            } catch (e) {
                console.error('Error al analizar JSON:', e);
                alert('Error al procesar la solicitud.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud.');
        });
    }
}

export function loadData(url, tbodySelector) {
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector(tbodySelector);
            tbody.innerHTML = '';
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.id_doctores}</td>
                    <td>${item.nombre}</td>
                    <td>${item.cargo}</td>
                    <td>${item.especialidad || 'No asignada'}</td>
                    <td><button class="delete-button" onclick="eliminarDoctor(${item.id_doctores})"><i class="ri-delete-bin-6-line"></i></button></td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error al cargar los datos:', error);
            alert('Error al cargar los datos. Verifica la consola para más detalles.');
        });
}
