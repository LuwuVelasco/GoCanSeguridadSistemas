import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { favoritosAPI } from '../../services/api';
import { Heart, Search, SlidersHorizontal, X } from 'lucide-react';
import Swal from 'sweetalert2';
import './Favoritos.css';

function Favoritos({ isOpen, onClose }) {
  const { user } = useAuth();
  const [favoritos, setFavoritos] = useState([]);
  const [favoritosFiltrados, setFavoritosFiltrados] = useState([]);
  const [loading, setLoading] = useState(true);
  const [busqueda, setBusqueda] = useState('');
  const [ordenamiento, setOrdenamiento] = useState('');
  const [mostrarOrden, setMostrarOrden] = useState(false);

  useEffect(() => {
    if (isOpen && user) {
      cargarFavoritos();
    }
  }, [isOpen, user]);

  useEffect(() => {
    aplicarFiltros();
  }, [favoritos, busqueda, ordenamiento]);

  const cargarFavoritos = async () => {
    try {
      setLoading(true);
      const response = await favoritosAPI.getFavoritosUsuario(user.id_usuario);
      setFavoritos(response.data);
    } catch (error) {
      console.error('Error al cargar favoritos:', error);
      Swal.fire('Error', 'No se pudieron cargar los favoritos', 'error');
    } finally {
      setLoading(false);
    }
  };

  const aplicarFiltros = () => {
    let resultado = [...favoritos];

    // Filtrar por búsqueda
    if (busqueda) {
      resultado = resultado.filter(fav =>
        fav.nombre.toLowerCase().includes(busqueda.toLowerCase())
      );
    }

    // Ordenar
    if (ordenamiento === 'precio-asc') {
      resultado.sort((a, b) => parseFloat(a.precio) - parseFloat(b.precio));
    } else if (ordenamiento === 'precio-desc') {
      resultado.sort((a, b) => parseFloat(b.precio) - parseFloat(a.precio));
    } else if (ordenamiento === 'nombre-asc') {
      resultado.sort((a, b) => a.nombre.localeCompare(b.nombre));
    } else if (ordenamiento === 'nombre-desc') {
      resultado.sort((a, b) => b.nombre.localeCompare(a.nombre));
    }

    setFavoritosFiltrados(resultado);
  };

  const eliminarFavorito = async (idProducto) => {
    const result = await Swal.fire({
      title: '¿Eliminar de favoritos?',
      text: 'El producto será removido de tu lista',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
      try {
        await favoritosAPI.eliminarFavorito(user.id_usuario, idProducto);
        Swal.fire('¡Eliminado!', 'Producto removido de favoritos', 'success');
        cargarFavoritos();
      } catch (error) {
        Swal.fire('Error', 'No se pudo eliminar el favorito', 'error');
      }
    }
  };

  if (!isOpen) return null;

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="favoritos-modal" onClick={(e) => e.stopPropagation()}>
        <div className="favoritos-header">
          <h2>
            <Heart size={24} fill="currentColor" />
            Mis Favoritos
          </h2>
          <button onClick={onClose} className="close-btn">
            <X size={24} />
          </button>
        </div>

        <div className="favoritos-toolbar">
          <div className="search-box">
            <Search size={20} />
            <input
              type="text"
              placeholder="Buscar producto..."
              value={busqueda}
              onChange={(e) => setBusqueda(e.target.value)}
            />
          </div>

          <div className="sort-container">
            <button
              className="sort-btn"
              onClick={() => setMostrarOrden(!mostrarOrden)}
            >
              <SlidersHorizontal size={20} />
              Ordenar
            </button>

            {mostrarOrden && (
              <div className="sort-dropdown">
                <div className="sort-section">
                  <h4>Por Precio</h4>
                  <button onClick={() => { setOrdenamiento('precio-asc'); setMostrarOrden(false); }}>
                    Menor a Mayor
                  </button>
                  <button onClick={() => { setOrdenamiento('precio-desc'); setMostrarOrden(false); }}>
                    Mayor a Menor
                  </button>
                </div>

                <div className="sort-section">
                  <h4>Por Nombre</h4>
                  <button onClick={() => { setOrdenamiento('nombre-asc'); setMostrarOrden(false); }}>
                    A - Z
                  </button>
                  <button onClick={() => { setOrdenamiento('nombre-desc'); setMostrarOrden(false); }}>
                    Z - A
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>

        <div className="favoritos-content">
          {loading ? (
            <div className="loading">Cargando favoritos...</div>
          ) : favoritosFiltrados.length === 0 ? (
            <div className="empty-state">
              <Heart size={60} />
              <p>{busqueda ? 'No se encontraron productos' : 'No tienes productos favoritos'}</p>
            </div>
          ) : (
            <div className="favoritos-grid">
              {favoritosFiltrados.map((favorito) => (
                <div key={favorito.id_favorito} className="favorito-card">
                  <button
                    className="remove-favorito"
                    onClick={() => eliminarFavorito(favorito.id_producto)}
                  >
                    <Heart size={20} fill="currentColor" />
                  </button>

                  <div className="favorito-imagen">
                    {favorito.imagen ? (
                      <img src={favorito.imagen} alt={favorito.nombre} />
                    ) : (
                      <div className="placeholder-imagen">📦</div>
                    )}
                  </div>

                  <div className="favorito-info">
                    <h3>{favorito.nombre}</h3>
                    <p className="favorito-descripcion">{favorito.descripcion}</p>

                    <div className="favorito-footer">
                      <div className="favorito-rating">
                        {[1, 2, 3, 4, 5].map((star) => (
                          <span
                            key={star}
                            className={star <= Math.round(favorito.calificacion_promedio) ? 'star filled' : 'star'}
                          >
                            ★
                          </span>
                        ))}
                        <span className="rating-count">
                          ({parseFloat(favorito.calificacion_promedio).toFixed(1)})
                        </span>
                      </div>

                      <span className="favorito-precio">Bs. {favorito.precio}</span>
                    </div>

                    <span className="favorito-categoria">{favorito.categoria}</span>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default Favoritos;
