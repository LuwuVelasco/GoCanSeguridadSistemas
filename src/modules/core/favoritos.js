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
document.addEventListener('DOMContentLoaded', function() {
    // Obtener elementos
    var fav = document.getElementById('modal');
    var openfavBtn = document.getElementById('openFavBtn');
    var closeModalBtn = document.querySelector('.close-btn');
  
    // Abrir el modal al hacer clic en el botón
    openfavBtn.addEventListener('click', function() {
      fetchFavoritos();
      fav.style.display = 'block';
    });
  
    // Cerrar el modal al hacer clic en el botón de cerrar
    closeModalBtn.addEventListener('click', function() {
      fav.style.display = 'none';
    });
  
    // Cerrar el modal al hacer clic fuera del contenido del modal
    window.addEventListener('click', function(event) {
      if (event.target === fav) {
        fav.style.display = 'none';
      }
    });
    function fetchFavoritos() {
      fetch('http://localhost/GoCan/src/modules/php/favoritos.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id_usuario: localStorage.getItem('id_usuario') })
      })
      .then(response => response.json())
      .then(data => {
        favoritosBody.innerHTML = ''; // Limpiar el contenido anterior
        data.forEach(function(producto) {
          var productoDiv = document.createElement('div');
          productoDiv.classList.add('producto');
  
          var nombre = document.createElement('h3');
          nombre.textContent = producto.nombre;
  
          var descripcion = document.createElement('p');
          descripcion.textContent = producto.descripcion;
  
          var precio = document.createElement('p');
          precio.textContent = 'Costo: ' + parseFloat(producto.precio).toFixed(2);
          precio.classList.add('precio');
  
          var imagen = document.createElement('img');
          imagen.src = producto.imagen;
          imagen.classList.add('producto-imagen');

          var infoDiv = document.createElement('div');
          infoDiv.classList.add('producto-info');
          infoDiv.appendChild(nombre);
          infoDiv.appendChild(descripcion);
          infoDiv.appendChild(precio);

          productoDiv.appendChild(imagen);
          productoDiv.appendChild(infoDiv);

          favoritosBody.appendChild(productoDiv);
        });
      })
      .catch(error => console.error('Error fetching favoritos:', error));
    }
  });