export function loadActivities(url, tbodySelector) {
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector(tbodySelector);
            tbody.innerHTML = ''; 
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.hora_ingreso}</td>
                    <td>${item.nombre_usuario}</td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error al cargar los datos:', error);
            alert('Error al cargar los datos. Verifica la consola para más detalles.');
        });
}
