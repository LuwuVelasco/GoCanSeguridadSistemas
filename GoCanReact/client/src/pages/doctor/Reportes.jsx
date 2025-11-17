import { useState, useEffect } from 'react';
import { reportesAPI } from '../../services/api';
import { FileText, Plus, Edit, Trash2 } from 'lucide-react';
import Swal from 'sweetalert2';

function Reportes() {
  const [reportes, setReportes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [editando, setEditando] = useState(null);
  const [formData, setFormData] = useState({
    propietario: '',
    fecha: new Date().toISOString().split('T')[0],
    nombre_mascota: '',
    sintomas: '',
    diagnostico: '',
    receta: ''
  });

  useEffect(() => {
    cargarReportes();
  }, []);

  const cargarReportes = async () => {
    try {
      const response = await reportesAPI.getTodosReportes();
      setReportes(response.data);
    } catch (error) {
      console.error('Error al cargar reportes:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      if (editando) {
        await reportesAPI.actualizarReporte(editando, formData);
        Swal.fire('¡Éxito!', 'Reporte actualizado correctamente', 'success');
      } else {
        await reportesAPI.crearReporte(formData);
        Swal.fire('¡Éxito!', 'Reporte creado correctamente', 'success');
      }

      setShowModal(false);
      setEditando(null);
      setFormData({ propietario: '', fecha: new Date().toISOString().split('T')[0], nombre_mascota: '', sintomas: '', diagnostico: '', receta: '' });
      cargarReportes();
    } catch (error) {
      Swal.fire('Error', 'No se pudo guardar el reporte', 'error');
    }
  };

  const editarReporte = (reporte) => {
    setEditando(reporte.id_reporte);
    setFormData({
      propietario: reporte.propietario,
      fecha: reporte.fecha,
      nombre_mascota: reporte.nombre_mascota,
      sintomas: reporte.sintomas || '',
      diagnostico: reporte.diagnostico || '',
      receta: reporte.receta || ''
    });
    setShowModal(true);
  };

  const eliminarReporte = async (id) => {
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
        await reportesAPI.eliminarReporte(id);
        Swal.fire('¡Eliminado!', 'El reporte ha sido eliminado', 'success');
        cargarReportes();
      } catch (error) {
        Swal.fire('Error', 'No se pudo eliminar el reporte', 'error');
      }
    }
  };

  if (loading) {
    return <div className="loading">Cargando...</div>;
  }

  return (
    <div className="reportes">
      <div className="page-header">
        <h2>Reportes Médicos</h2>
        <button onClick={() => setShowModal(true)} className="btn btn-primary">
          <Plus size={20} />
          Nuevo Reporte
        </button>
      </div>

      {reportes.length === 0 ? (
        <div className="empty-state">
          <FileText size={60} />
          <p>No hay reportes registrados</p>
        </div>
      ) : (
        <div className="table-container">
          <table>
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Propietario</th>
                <th>Mascota</th>
                <th>Diagnóstico</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              {reportes.map((reporte) => (
                <tr key={reporte.id_reporte}>
                  <td>{new Date(reporte.fecha).toLocaleDateString()}</td>
                  <td>{reporte.propietario}</td>
                  <td>{reporte.nombre_mascota}</td>
                  <td>{reporte.diagnostico || 'Sin diagnóstico'}</td>
                  <td>
                    <button onClick={() => editarReporte(reporte)} className="btn-icon">
                      <Edit size={18} />
                    </button>
                    <button onClick={() => eliminarReporte(reporte.id_reporte)} className="btn-icon btn-danger">
                      <Trash2 size={18} />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {/* Modal */}
      {showModal && (
        <div className="modal-overlay" onClick={() => setShowModal(false)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>{editando ? 'Editar Reporte' : 'Nuevo Reporte'}</h2>
              <button onClick={() => setShowModal(false)} className="close-btn">&times;</button>
            </div>

            <form onSubmit={handleSubmit}>
              <div className="form-group">
                <label>Propietario *</label>
                <input
                  type="text"
                  value={formData.propietario}
                  onChange={(e) => setFormData({ ...formData, propietario: e.target.value })}
                  className="form-control"
                  required
                />
              </div>

              <div className="form-group">
                <label>Mascota *</label>
                <input
                  type="text"
                  value={formData.nombre_mascota}
                  onChange={(e) => setFormData({ ...formData, nombre_mascota: e.target.value })}
                  className="form-control"
                  required
                />
              </div>

              <div className="form-group">
                <label>Fecha *</label>
                <input
                  type="date"
                  value={formData.fecha}
                  onChange={(e) => setFormData({ ...formData, fecha: e.target.value })}
                  className="form-control"
                  required
                />
              </div>

              <div className="form-group">
                <label>Síntomas</label>
                <textarea
                  value={formData.sintomas}
                  onChange={(e) => setFormData({ ...formData, sintomas: e.target.value })}
                  className="form-control"
                  rows="3"
                />
              </div>

              <div className="form-group">
                <label>Diagnóstico</label>
                <textarea
                  value={formData.diagnostico}
                  onChange={(e) => setFormData({ ...formData, diagnostico: e.target.value })}
                  className="form-control"
                  rows="3"
                />
              </div>

              <div className="form-group">
                <label>Receta</label>
                <textarea
                  value={formData.receta}
                  onChange={(e) => setFormData({ ...formData, receta: e.target.value })}
                  className="form-control"
                  rows="3"
                />
              </div>

              <div className="modal-actions">
                <button type="button" onClick={() => setShowModal(false)} className="btn btn-secondary">
                  Cancelar
                </button>
                <button type="submit" className="btn btn-primary">
                  {editando ? 'Actualizar' : 'Crear'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

export default Reportes;
