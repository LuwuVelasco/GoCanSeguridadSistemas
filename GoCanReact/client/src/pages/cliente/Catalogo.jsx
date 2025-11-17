import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { catalogoAPI, favoritosAPI, calificacionesAPI } from '../../services/api';
import { ShoppingCart, Heart } from 'lucide-react';
import Swal from 'sweetalert2';

function Catalogo() {
  const { user } = useAuth();
  const [productos, setProductos] = useState([]);
  const [loading, setLoading] = useState(true);
  const [categoriaFiltro, setCategoriaFiltro] = useState('Todos');
  const [favoritos, setFavoritos] = useState(new Set());
  const [calificaciones, setCalificaciones] = useState({});

  useEffect(() => {
    cargarProductos();
    if (user) {
      cargarFavoritos();
    }
  }, [user]);

  const cargarProductos = async () => {
    try {
      const response = await catalogoAPI.getTodosProductos();
      const productosConCalificacion = await Promise.all(
        response.data.map(async (producto) => {
          try {
            const calResponse = await calificacionesAPI.getCalificacionProducto(producto.id_producto);
            return {
              ...producto,
              calificacion_promedio: calResponse.data.promedio || 0,
              total_calificaciones: calResponse.data.total_calificaciones || 0
            };
          } catch (error) {
            return { ...producto, calificacion_promedio: 0, total_calificaciones: 0 };
          }
        })
      );
      setProductos(productosConCalificacion);
    } catch (error) {
      console.error('Error al cargar productos:', error);
    } finally {
      setLoading(false);
    }
  };

  const cargarFavoritos = async () => {
    try {
      const response = await favoritosAPI.getFavoritosUsuario(user.id_usuario);
      const favIds = new Set(response.data.map(f => f.id_producto));
      setFavoritos(favIds);
    } catch (error) {
      console.error('Error al cargar favoritos:', error);
    }
  };

  const toggleFavorito = async (idProducto) => {
    if (!user) {
      Swal.fire('Atención', 'Debes iniciar sesión para agregar favoritos', 'warning');
      return;
    }

    try {
      if (favoritos.has(idProducto)) {
        await favoritosAPI.eliminarFavorito(user.id_usuario, idProducto);
        setFavoritos(prev => {
          const newSet = new Set(prev);
          newSet.delete(idProducto);
          return newSet;
        });
        Swal.fire({
          icon: 'success',
          title: 'Eliminado',
          text: 'Producto removido de favoritos',
          timer: 1500,
          showConfirmButton: false
        });
      } else {
        await favoritosAPI.agregarFavorito({
          id_usuario: user.id_usuario,
          id_producto: idProducto
        });
        setFavoritos(prev => new Set([...prev, idProducto]));
        Swal.fire({
          icon: 'success',
          title: '¡Agregado!',
          text: 'Producto agregado a favoritos',
          timer: 1500,
          showConfirmButton: false
        });
      }
    } catch (error) {
      Swal.fire('Error', 'No se pudo actualizar favoritos', 'error');
    }
  };

  const calificarProducto = async (idProducto, puntuacion) => {
    if (!user) {
      Swal.fire('Atención', 'Debes iniciar sesión para calificar', 'warning');
      return;
    }

    try {
      await calificacionesAPI.calificarProducto({
        id_usuario: user.id_usuario,
        id_producto: idProducto,
        puntuacion
      });

      Swal.fire({
        icon: 'success',
        title: '¡Gracias!',
        text: 'Tu calificación ha sido registrada',
        timer: 1500,
        showConfirmButton: false
      });

      // Recargar productos para actualizar calificación
      cargarProductos();
    } catch (error) {
      Swal.fire('Error', 'No se pudo registrar la calificación', 'error');
    }
  };

  const productosFiltrados = categoriaFiltro === 'Todos'
    ? productos
    : productos.filter(p => p.categoria === categoriaFiltro);

  const categorias = ['Todos', ...new Set(productos.map(p => p.categoria))];

  if (loading) {
    return <div className="loading">Cargando...</div>;
  }

  return (
    <div className="catalogo">
      <div className="page-header">
        <h2>Catálogo de Productos</h2>
      </div>

      <div className="filtros">
        <div className="categorias-filtro">
          {categorias.map((cat) => (
            <button
              key={cat}
              onClick={() => setCategoriaFiltro(cat)}
              className={`filtro-btn ${categoriaFiltro === cat ? 'active' : ''}`}
            >
              {cat}
            </button>
          ))}
        </div>
      </div>

      {productosFiltrados.length === 0 ? (
        <div className="empty-state">
          <ShoppingCart size={60} />
          <p>No hay productos disponibles</p>
        </div>
      ) : (
        <div className="productos-grid">
          {productosFiltrados.map((producto) => (
            <div key={producto.id_producto} className="producto-card">
              <div className="producto-imagen">
                {producto.imagen ? (
                  <img src={producto.imagen} alt={producto.nombre} />
                ) : (
                  <div className="placeholder-imagen">
                    <ShoppingCart size={40} />
                  </div>
                )}
                <button 
                  className={`btn-favorito ${favoritos.has(producto.id_producto) ? 'active' : ''}`}
                  onClick={() => toggleFavorito(producto.id_producto)}
                >
                  <Heart size={20} fill={favoritos.has(producto.id_producto) ? 'currentColor' : 'none'} />
                </button>
              </div>

              <div className="producto-info">
                <h3>{producto.nombre}</h3>
                <p className="producto-descripcion">{producto.descripcion}</p>
                
                <div className="producto-rating">
                  {[1, 2, 3, 4, 5].map((star) => (
                    <span
                      key={star}
                      className={`star ${star <= Math.round(producto.calificacion_promedio) ? 'filled' : ''}`}
                      onClick={() => calificarProducto(producto.id_producto, star)}
                      style={{ cursor: user ? 'pointer' : 'default' }}
                    >
                      ★
                    </span>
                  ))}
                  <span className="rating-count">
                    ({producto.total_calificaciones})
                  </span>
                </div>

                <div className="producto-footer">
                  <span className="producto-precio">Bs. {producto.precio}</span>
                  <span className="producto-categoria">{producto.categoria}</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

export default Catalogo;
