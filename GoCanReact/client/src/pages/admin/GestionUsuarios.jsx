import { useState, useEffect } from 'react';
import { usuariosAPI, citasAPI } from '../../services/api';
import { Users, Plus, Trash2 } from 'lucide-react';
import Swal from 'sweetalert2';

function GestionUsuarios() {
  const [usuarios, setUsuarios] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [especialidades, setEspecialidades] = useState([]);
  const [formData, setFormData] = useState({
    nombre: '',
    correo: '',
    password: '',
    rol: '',
    especialidad: '',
    nombreDoctor: ''
  });

  useEffect(() => {
    cargarUsuarios();
    cargarEspecialidades();
  }, []);

  const cargarUsuarios = async () => {
    try {
      const response = await usuariosAPI.getTodosUsuarios();
      setUsuarios(response.data);
    } catch (error) {
      console.error('Error al cargar usuarios:', error);
    } finally {
      setLoading(false);
    }
  };

  const cargarEspecialidades = async () => {
    try {
      const response = await citasAPI.getEspecialidades();
      setEspecialidades(response.data);
    } catch (error) {
      console.error('Error al cargar especialidades:', error);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      await usuariosAPI.registrarFuncionario(formData);
      Swal.fire('¡Éxito!', 'Usuario registrado correctamente', 'success');
      setShowModal(false);
      setFormData({ nombre: '', correo: '', password: '', rol: '', especialidad: '', nombreDoctor: '' });
      cargarUsuarios();
    } catch (error) {
      Swal.fire('Error', error.response?.data?.mensaje || 'No se pudo registrar el usuario', 'error');
    }
  };

  const eliminarUsuario = async (id) => {
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
        await usuariosAPI.eliminarUsuario(id);
        Swal.fire('¡Eliminado!', 'El usuario ha sido eliminado', 'success');
        cargarUsuarios();
      } catch (error) {
        Swal.fire('Error', 'No se pudo eliminar el usuario', 'error');
      }
    }
  };

  if (loading) {
    return <div className="loading">Cargando...</div>;
  }

  return (
    <div className="gestion-usuarios">
      <div className="page-header">
        <h2>Gestión de Usuarios</h2>
        <button onClick={() => setShowModal(true)} className="btn btn-primary">
          <Plus size={20} />
          Nuevo Usuario
        </button>
      </div>

      <div className="table-container">
        <table>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Email</th>
              <th>Rol</th>
              <th>Fecha Registro</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {usuarios.map((usuario) => (
              <tr key={usuario.id_usuario}>
                <td>{usuario.nombre}</td>
                <td>{usuario.email}</td>
                <td>{usuario.nombre_rol}</td>
                <td>{usuario.fecha_registro ? new Date(usuario.fecha_registro).toLocaleDateString() : 'N/A'}</td>
                <td>
                  <button onClick={() => eliminarUsuario(usuario.id_usuario)} className="btn-icon btn-danger">
                    <Trash2 size={18} />
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Modal */}
      {showModal && (
        <div className="modal-overlay" onClick={() => setShowModal(false)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>Nuevo Funcionario</h2>
              <button onClick={() => setShowModal(false)} className="close-btn">&times;</button>
            </div>

            <form onSubmit={handleSubmit}>
              <div className="form-group">
                <label>Nombre *</label>
                <input
                  type="text"
                  value={formData.nombre}
                  onChange={(e) => setFormData({ ...formData, nombre: e.target.value })}
                  className="form-control"
                  required
                />
              </div>

              <div className="form-group">
                <label>Email *</label>
                <input
                  type="email"
                  value={formData.correo}
                  onChange={(e) => setFormData({ ...formData, correo: e.target.value })}
                  className="form-control"
                  required
                />
              </div>

              <div className="form-group">
                <label>Contraseña *</label>
                <input
                  type="password"
                  value={formData.password}
                  onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                  className="form-control"
                  required
                />
              </div>

              <div className="form-group">
                <label>Rol *</label>
                <select
                  value={formData.rol}
                  onChange={(e) => setFormData({ ...formData, rol: e.target.value })}
                  className="form-control"
                  required
                >
                  <option value="">Seleccione un rol</option>
                  <option value="1">Administrador</option>
                  <option value="2">Doctor</option>
                </select>
              </div>

              {formData.rol === '2' && (
                <>
                  <div className="form-group">
                    <label>Nombre del Doctor *</label>
                    <input
                      type="text"
                      value={formData.nombreDoctor}
                      onChange={(e) => setFormData({ ...formData, nombreDoctor: e.target.value })}
                      className="form-control"
                      required
                    />
                  </div>

                  <div className="form-group">
                    <label>Especialidad *</label>
                    <select
                      value={formData.especialidad}
                      onChange={(e) => setFormData({ ...formData, especialidad: e.target.value })}
                      className="form-control"
                      required
                    >
                      <option value="">Seleccione una especialidad</option>
                      {especialidades.map((esp) => (
                        <option key={esp.id_especialidad} value={esp.id_especialidad}>
                          {esp.nombre_especialidad}
                        </option>
                      ))}
                    </select>
                  </div>
                </>
              )}

              <div className="modal-actions">
                <button type="button" onClick={() => setShowModal(false)} className="btn btn-secondary">
                  Cancelar
                </button>
                <button type="submit" className="btn btn-primary">
                  Registrar
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

export default GestionUsuarios;
