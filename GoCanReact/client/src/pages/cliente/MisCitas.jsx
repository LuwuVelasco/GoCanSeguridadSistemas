import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { citasAPI } from '../../services/api';
import { Calendar, Clock, User, Trash2, Plus } from 'lucide-react';
import Swal from 'sweetalert2';

function MisCitas() {
  const { user } = useAuth();
  const [citas, setCitas] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [especialidades, setEspecialidades] = useState([]);
  const [doctores, setDoctores] = useState([]);
  const [formData, setFormData] = useState({
    especialidad: '',
    doctor: '',
    fecha: '',
    horario: ''
  });

  useEffect(() => {
    cargarCitas();
    cargarEspecialidades();
  }, []);

  const cargarCitas = async () => {
    try {
      const response = await citasAPI.getCitasUsuario(user.id_usuario);
      setCitas(response.data);
    } catch (error) {
      console.error('Error al cargar citas:', error);
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

  const cargarDoctores = async (especialidadId) => {
    try {
      const response = await citasAPI.getDoctoresPorEspecialidad(especialidadId);
      setDoctores(response.data);
    } catch (error) {
      console.error('Error al cargar doctores:', error);
    }
  };

  const handleEspecialidadChange = (e) => {
    const especialidadId = e.target.value;
    const especialidad = especialidades.find(e => e.id_especialidad == especialidadId);
    
    setFormData({
      ...formData,
      especialidad: especialidad?.nombre_especialidad || '',
      doctor: ''
    });

    if (especialidadId) {
      cargarDoctores(especialidadId);
    } else {
      setDoctores([]);
    }
  };

  const handleDoctorChange = (e) => {
    const doctorId = e.target.value;
    const doctor = doctores.find(d => d.id_doctores == doctorId);
    
    setFormData({
      ...formData,
      doctor: doctor?.nombre || ''
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!formData.especialidad || !formData.doctor || !formData.fecha || !formData.horario) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Por favor complete todos los campos'
      });
      return;
    }

    try {
      await citasAPI.crearCita({
        propietario: user.nombre,
        especialidadNombre: formData.especialidad,
        doctor: formData.doctor,
        id_usuario: user.id_usuario,
        fecha: formData.fecha,
        horario: formData.horario
      });

      Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: 'Cita registrada correctamente'
      });

      setShowModal(false);
      setFormData({ especialidad: '', doctor: '', fecha: '', horario: '' });
      cargarCitas();
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.mensaje || 'Error al registrar la cita'
      });
    }
  };

  const eliminarCita = async (id) => {
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
        await citasAPI.eliminarCita(id);
        Swal.fire('¡Eliminado!', 'La cita ha sido eliminada', 'success');
        cargarCitas();
      } catch (error) {
        Swal.fire('Error', 'No se pudo eliminar la cita', 'error');
      }
    }
  };

  if (loading) {
    return <div className="loading">Cargando...</div>;
  }

  return (
    <div className="mis-citas">
      <div className="page-header">
        <h2>Mis Citas</h2>
        <button onClick={() => setShowModal(true)} className="btn btn-primary">
          <Plus size={20} />
          Nueva Cita
        </button>
      </div>

      {citas.length === 0 ? (
        <div className="empty-state">
          <Calendar size={60} />
          <p>No tienes citas registradas</p>
          <button onClick={() => setShowModal(true)} className="btn btn-secondary">
            Agendar Primera Cita
          </button>
        </div>
      ) : (
        <div className="citas-grid">
          {citas.map((cita) => (
            <div key={cita.id_cita} className="cita-card">
              <div className="cita-header">
                <h3>{cita.servicio}</h3>
                <button onClick={() => eliminarCita(cita.id_cita)} className="btn-icon">
                  <Trash2 size={18} />
                </button>
              </div>
              
              <div className="cita-info">
                <div className="info-item">
                  <User size={18} />
                  <span>{cita.doctor}</span>
                </div>
                <div className="info-item">
                  <Calendar size={18} />
                  <span>{new Date(cita.fecha).toLocaleDateString()}</span>
                </div>
                <div className="info-item">
                  <Clock size={18} />
                  <span>{cita.horario}</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Modal Nueva Cita */}
      {showModal && (
        <div className="modal-overlay" onClick={() => setShowModal(false)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>Nueva Cita</h2>
              <button onClick={() => setShowModal(false)} className="close-btn">&times;</button>
            </div>

            <form onSubmit={handleSubmit}>
              <div className="form-group">
                <label>Especialidad</label>
                <select onChange={handleEspecialidadChange} className="form-control" required>
                  <option value="">Seleccione una especialidad</option>
                  {especialidades.map((esp) => (
                    <option key={esp.id_especialidad} value={esp.id_especialidad}>
                      {esp.nombre_especialidad}
                    </option>
                  ))}
                </select>
              </div>

              <div className="form-group">
                <label>Doctor</label>
                <select 
                  onChange={handleDoctorChange} 
                  className="form-control" 
                  required
                  disabled={!doctores.length}
                >
                  <option value="">Seleccione un doctor</option>
                  {doctores.map((doc) => (
                    <option key={doc.id_doctores} value={doc.id_doctores}>
                      {doc.nombre}
                    </option>
                  ))}
                </select>
              </div>

              <div className="form-group">
                <label>Fecha</label>
                <input
                  type="date"
                  value={formData.fecha}
                  onChange={(e) => setFormData({ ...formData, fecha: e.target.value })}
                  className="form-control"
                  min={new Date().toISOString().split('T')[0]}
                  required
                />
              </div>

              <div className="form-group">
                <label>Horario</label>
                <input
                  type="time"
                  value={formData.horario}
                  onChange={(e) => setFormData({ ...formData, horario: e.target.value })}
                  className="form-control"
                  required
                />
              </div>

              <div className="modal-actions">
                <button type="button" onClick={() => setShowModal(false)} className="btn btn-secondary">
                  Cancelar
                </button>
                <button type="submit" className="btn btn-primary">
                  Agendar Cita
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

export default MisCitas;
