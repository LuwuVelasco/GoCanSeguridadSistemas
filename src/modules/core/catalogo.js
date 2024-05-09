document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('favorito').addEventListener('click', registrarProducto);
});

function registrarProducto() {
    var nombre = document.getElementById('nombre').textContent;
    var descripcion = document.getElementById('descripcion').textContent;
    var precio = document.getElementById('precio').value;
    var categoria = document.getElementById('categoria').textContent;

    console.log("nombre:", nombre);
    console.log("descripcion:", descripcion);
    console.log("precio:", precio);
    console.log("categoria:", categoria);

    fetch("http://localhost/GoCan/src/modules/php/catalogo.php", {
        method: "POST",
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ nombre: nombre, descripcion: descripcion, precio: precio, categoria: categoria }),
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        } else {
            throw new Error('Error al registrar el producto');
        }
    })
    .then(data => {
        console.log(data);
        alert('Producto registrado correctamente. ID de producto: ' + data.id_producto);
        document.getElementById('nombre').textContent = '';
        document.getElementById('descripcion').textContent='';
        document.getElementById('precio').value='';
        document.getElementById('categoria').textContent='';
        data.id_usuario;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al registrar el producto');
    });
}
