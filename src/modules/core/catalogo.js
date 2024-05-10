document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.card-action-btn.favorito').forEach(button => {
      button.addEventListener('click', function() {
          const productCard = this.closest('.product-card');
          const productData = {
              nombre: productCard.querySelector('.nombre').textContent.trim(),
              descripcion: productCard.querySelector('.descripcion').textContent.trim(),
              precio: parseFloat(productCard.querySelector('.precio').getAttribute('value')),
              categoria: productCard.querySelector('.categoria').textContent.trim(),
              id_usuario: localStorage.getItem('id_usuario')
          };

          fetch('http://localhost/GoCan/src/modules/php/catalogo.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json'
              },
              body: JSON.stringify(productData)
          })
          .then(response => response.text())
          .then(data => console.log('Respuesta del servidor:', data))
          .catch(error => console.error('Error:', error));
      });
  });
});
