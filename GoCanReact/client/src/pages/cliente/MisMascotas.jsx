import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { mascotasAPI } from '../../services/api';
import { PawPrint, Plus, Edit, Trash2 } from 'lucide-react';
import Swal from 'sweetalert2';

function MisMascotas() {
  const { user } = useAuth();
  const [mascotas, setMascotas] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [editando, setEditando] = useState(null);
  const [formData, setFormData] = useState({
    nombre_mascota: '',
    tipo: '',
    raza: '',
    fecha_nacimiento: ''
  });

  useEffect(() => {
    cargarMascotas();
  }, []);

  const cargarMascotas = async () => {
    try {
      const response = await mascotasAPI.getMascotasUsuario(user.id_usuario);
      setMascotas(response.data);
    } catch (error) {
      console.error('Error al cargar mascotas:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!formData.nombre_mascota || !formData.tipo) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Por favor complete los campos obligatorios'
      });
      return;
    }

    try {
      if (editando) {
        await mascotasAPI.actualizarMascota(editando, formData);
        Swal.fire('¡Éxito!', 'Mascota actualizada correctamente', 'success');
      } else {
        await mascotasAPI.crearMascota({
          ...formData,
          id_usuario: user.id_usuario
        });
        Swal.fire('¡Éxito!', 'Mascota registrada correctamente', 'success');
      }

      setShowModal(false);
      setEditando(null);
      setFormData({ nombre_mascota: '', tipo: '', raza: '', fecha_nacimiento: '' });
      cargarMascotas();
    } catch (error) {
      Swal.fire('Error', 'No se pudo guardar la mascota', 'error');
    }
  };

  const editarMascota = (mascota) => {
    setEditando(mascota.id_mascota);
    setFormData({
      nombre_mascota: mascota.nombre_mascota,
      tipo: mascota.tipo,
      raza: mascota.raza || '',
      fecha_nacimiento: mascota.fecha_nacimiento || ''
    });
    setShowModal(true);
  };

  const eliminarMascota = async (id) => {
    const result = await Swal.fire({
      title: '¿Está seguro?',
      text: 'Esta acción no se puede deshacer',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
      try {
        await mascotasAPI.eliminarMascota(id);
        Swal.fire('¡Eliminado!', 'La mascota ha sido eliminada', 'success');
        cargarMascotas();
      } catch (error) {
        Swal.fire('Error', 'No se pudo eliminar la mascota', 'error');
      }
    }
  };

  const cerrarModal = () => {
    setShowModal(false);
    setEditando(null);
    setFormData({ nombre_mascota: '', tipo: '', raza: '', fecha_nacimiento: '' });
  };

  if (loading) {
    return <div className="loading">Cargando...</div>;
  }

  return (
    <div className="mis-mascotas">
      <div className="page-header">
        <h2>Mis Mascotas</h2>
        <button onClick={() => setShowModal(true)} className="btn btn-primary">
          <Plus size={20} />
          Nueva Mascota
        </button>
      </div>

      {mascotas.length === 0 ? (
        <div className="empty-state">
          <PawPrint size={60} />
          <p>No tienes mascotas registradas</p>
          <button onClick={() => setShowModal(true)} className="btn btn-secondary">
            Registrar Primera Mascota
          </button>
        </div>
      ) : (
        <div className="mascotas-grid">
          {mascotas.map((mascota) => (
            <div key={mascota.id_mascota} className="mascota-card">
              <div className="mascota-icon">
                {mascota.tipo === 'Perro' && '🐕'}
                {mascota.tipo === 'Gato' && '🐈'}
                {mascota.tipo === 'Pájaro' && '🦜'}
                {mascota.tipo === 'Conejo' && '🐰'}
                {!['Perro', 'Gato', 'Pájaro', 'Conejo'].includes(mascota.tipo) && '🐾'}
              </div>
              
              <h3>{mascota.nombre_mascota}</h3>
              
              <div className="mascota-info">
                <p><strong>Tipo:</strong> {mascota.tipo}</p>
                {mascota.raza && <p><strong>Raza:</strong> {mascota.raza}</p>}
                {mascota.fecha_nacimiento && (
                  <p><strong>Nacimiento:</strong> {new Date(mascota.fecha_nacimiento).toLocaleDateString()}</p>
                )}
              </div>

              <div className="mascota-actions">
                <button onClick={() => editarMascota(mascota)} className="btn-icon">
                  <Edit size={18} />
                </button>
                <button onClick={() => eliminarMascota(mascota.id_mascota)} className="btn-icon btn-danger">
                  <Trash2 size={18} />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Modal */}
      {showModal && (
        <div className="modal-overlay" onClick={cerrarModal}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>{editando ? 'Editar Mascota' : 'Nueva Mascota'}</h2>
              <button onClick={cerrarModal} className="close-btn">&times;</button>
            </div>

            <form onSubmit={handleSubmit}>
              <div className="form-group">
                <label>Nombre *</label>
                <input
                  type="text"
                  value={formData.nombre_mascota}
                  onChange={(e) => setFormData({ ...formData, nombre_mascota: e.target.value })}
                  className="form-control"
                  required
                />
              </div>

              <div className="form-group">
                <label>Tipo *</label>
                <select
                  value={formData.tipo}
                  onChange={(e) => setFormData({ ...formData, tipo: e.target.value })}
                  className="form-control"
                  required
                >
                  <option value="">Seleccione un tipo</option>
                  <option value="Perro">Perro</option>
                  <option value="Gato">Gato</option>
                  <option value="Pájaro">Pájaro</option>
                  <option value="Conejo">Conejo</option>
                  <option value="Otro">Otro</option>
                </select>
              </div>

              <div className="form-group">
                <label>Raza</label>
                <input
                  type="text"
                  value={formData.raza}
                  onChange={(e) => setFormData({ ...formData, raza: e.target.value })}
                  className="form-control"
                />
              </div>

              <div className="form-group">
                <label>Fecha de Nacimiento</label>
                <input
                  type="date"
                  value={formData.fecha_nacimiento}
                  onChange={(e) => setFormData({ ...formData, fecha_nacimiento: e.target.value })}
                  className="form-control"
                  max={new Date().toISOString().split('T')[0]}
                />
              </div>

              <div className="modal-actions">
                <button type="button" onClick={cerrarModal} className="btn btn-secondary">
                  Cancelar
                </button>
                <button type="submit" className="btn btn-primary">
                  {editando ? 'Actualizar' : 'Registrar'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

export default MisMascotas;
