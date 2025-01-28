export function loadClientes() {
    const url = "http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_clientes.php";
    const clientesTable = document.querySelector("#clientesTable tbody");
    const searchInput = document.getElementById("clienteSearchInput");

    clientesTable.innerHTML = '<tr><td colspan="3">Cargando...</td></tr>';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            clientesTable.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
                clientesTable.innerHTML = '<tr><td colspan="3">No hay clientes registrados.</td></tr>';
                return;
            }

            data.forEach(cliente => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${cliente.id_usuario}</td>
                    <td>${cliente.nombre}</td>
                    <td>${cliente.email}</td>
                `;
                clientesTable.appendChild(row);
            });

            // Filtrar clientes por nombre al escribir en la barra de búsqueda
            searchInput.addEventListener("input", function () {
                const searchText = this.value.toLowerCase();
                const rows = clientesTable.querySelectorAll("tr");
                rows.forEach(row => {
                    const nombre = row.children[1].textContent.toLowerCase();
                    row.style.display = nombre.includes(searchText) ? "" : "none";
                });
            });
        })
        .catch(error => {
            console.error("Error al cargar clientes:", error);
            clientesTable.innerHTML = '<tr><td colspan="3">Error al cargar clientes.</td></tr>';
        });
}

// Asignar evento al botón de clientes
document.getElementById("bt4").addEventListener("click", loadClientes);
