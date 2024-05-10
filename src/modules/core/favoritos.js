document.addEventListener('DOMContentLoaded', function() {
    // Obtener el ID de usuario desde localStorage
    const idUsuario = localStorage.getItem('id_usuario');
  
    // Inicializar el contador al cargar la página
    if (idUsuario) {
        fetch(`http://localhost/GoCan/src/modules/php/favoritos.php?id_usuario=${idUsuario}`)
        .then(response => response.json())
        .then(data => {
            document.querySelector('.btn-badge').textContent = data.cantidad;
        })
        .catch(error => console.error('Error al cargar la cantidad de productos:', error));
    }
});