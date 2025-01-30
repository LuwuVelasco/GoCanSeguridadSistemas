document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.card-action-btn.favorito').forEach(button => {
      button.addEventListener('click', function() {
        const productCard = this.closest('.product-card');
        const nombre = productCard.querySelector('.nombre').textContent.trim();
        const descripcion = productCard.querySelector('.descripcion').textContent.trim();
        const precio = productCard.querySelector('.precio').getAttribute('value');
        const categoria = productCard.querySelector('.categoria').textContent.trim();
        const imagen = productCard.querySelector('.imagen').textContent.trim();
        const id_usuario = localStorage.getItem('id_usuario'); // Asumiendo que tienes el ID del usuario almacenado en localStorage
        const productData = {
          nombre: nombre,
          descripcion: descripcion,
          precio: precio,
          categoria: categoria,
          id_usuario: id_usuario,
          imagen: imagen
        };

          fetch('http://localhost/GoCanSeguridadSistemas/src/modules/php/catalogo.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json'
              },
              body: JSON.stringify(productData)
          })
          .then(response => response.text())
          .then(data => {
            console.log('Respuesta del servidor:', data);
            // Actualizar el span aquí después de la respuesta del servidor
            const contadorSpan = document.querySelector('.btn-badge');
            contadorSpan.textContent = parseInt(contadorSpan.textContent) + 1;
        })
          .catch(error => console.error('Error:', error));
      });
  });


  // Evento para desplegar el modal de aviso de inicio de sesión para guardar a favoritos
  if (localStorage.getItem("id_rol") == 3){
    document.querySelectorAll('.card-action-btn.favorito').forEach(button => {
      button.addEventListener('click', function() {
        // Mostrar la ventana modal
        document.getElementById('loginModal').style.display = 'flex';
      });
    });

    // Cerrar la ventana modal
    document.querySelector('.close-button').addEventListener('click', function() {
        document.getElementById('loginModal').style.display = 'none';
    });

    // También puedes cerrar el modal si se hace clic fuera del contenido
    window.onclick = function(event) {
      let modal = document.getElementById('loginModal');
      if (event.target === modal) {
        modal.style.display = "none";
      }
    }
  }
});
