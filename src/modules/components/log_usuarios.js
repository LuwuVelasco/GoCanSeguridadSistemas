export function loadLogUsuarios(url, tbodySelector) {
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector(tbodySelector);
            tbody.innerHTML = ''; // Limpiar la tabla

            data.forEach(log => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${log.fecha_hora}</td>
                    <td>${log.nombre_usuario || 'Desconocido'}</td>
                    <td>${log.accion}</td>
                    <td>${log.descripcion || 'Sin descripción'}</td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error al cargar el log de usuarios:', error);
            alert('Error al cargar el log de usuarios. Verifica la consola para más detalles.');
        });
}
