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
        console.log("imagen:", imagen);
        const productData = {
          nombre: nombre,
          descripcion: descripcion,
          precio: precio,
          categoria: categoria,
          id_usuario: id_usuario,
          imagen: imagen
        };

          fetch('http://localhost/GoCan/src/modules/php/catalogo.php', {
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
});
