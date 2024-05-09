document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".favorito").forEach((button) => {
    button.addEventListener("click", registrarProducto);
  });
});

function registrarProducto(event) {
  let card = event.target.closest(".product-card");

  if (!card) {
    console.error("No se pudo encontrar el contenedor de la tarjeta de producto.");
    return;
  }

  let nombre = card.querySelector(".nombre") ? card.querySelector(".nombre").textContent : "Elemento nombre no encontrado";
  let descripcion = card.querySelector(".descripcion") ? card.querySelector(".descripcion").textContent : "Elemento descripción no encontrado";
  let precio = card.querySelector(".precio") ? card.querySelector(".precio").getAttribute("value") : "Elemento precio no encontrado";
  let categoria = card.querySelector(".categoria") ? card.querySelector(".categoria").textContent : "Elemento categoria no encontrado";

  console.log("nombre:", nombre);
  console.log("descripcion:", descripcion);
  console.log("precio:", precio);
  console.log("categoria:", categoria);

  fetch("http://localhost/GoCan/src/modules/php/catalogo.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      nombre: nombre,
      descripcion: descripcion,
      precio: precio,
      categoria: categoria,
    }),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.text().then(text => {
        try {
          return JSON.parse(text);
        } catch (error) {
          throw new Error("Response was not JSON: " + text);
        }
      });
    })
    .then((data) => {
      console.log(data);
      alert("Producto registrado correctamente. ID de producto: " + data.id_producto);
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error al registrar el producto: " + error.message);
    });
}
