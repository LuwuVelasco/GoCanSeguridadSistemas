import { useState, useEffect } from 'react';
import { citasAPI } from '../../services/api';
import { Calendar } from 'lucide-react';

function TodasCitas() {
  const [citas, setCitas] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    cargarCitas();
  }, []);

  const cargarCitas = async () => {
    try {
      const response = await citasAPI.getTodasCitas();
      setCitas(response.data);
    } catch (error) {
      console.error('Error al cargar citas:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="loading">Cargando...</div>;
  }

  return (
    <div className="todas-citas">
      <div className="page-header">
        <h2>Todas las Citas</h2>
      </div>

      {citas.length === 0 ? (
        <div className="empty-state">
          <Calendar size={60} />
          <p>No hay citas registradas</p>
        </div>
      ) : (
        <div className="table-container">
          <table>
            <thead>
              <tr>
                <th>Propietario</th>
                <th>Email</th>
                <th>Servicio</th>
                <th>Doctor</th>
                <th>Fecha</th>
                <th>Horario</th>
              </tr>
            </thead>
            <tbody>
              {citas.map((cita) => (
                <tr key={cita.id_cita}>
                  <td>{cita.propietario}</td>
                  <td>{cita.email || 'N/A'}</td>
                  <td>{cita.servicio}</td>
                  <td>{cita.nombre_doctor || cita.doctor}</td>
                  <td>{new Date(cita.fecha).toLocaleDateString()}</td>
                  <td>{cita.horario}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

export default TodasCitas;
