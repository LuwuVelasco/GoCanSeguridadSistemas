import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { citasAPI } from '../../services/api';
import { Calendar, Clock, User, Mail } from 'lucide-react';

function CitasDoctor() {
  const { user } = useAuth();
  const [citas, setCitas] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    cargarCitas();
  }, []);

  const cargarCitas = async () => {
    try {
      if (user.id_doctores) {
        const response = await citasAPI.getCitasDoctor(user.id_doctores);
        setCitas(response.data);
      }
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
    <div className="citas-doctor">
      <div className="page-header">
        <h2>Mis Citas Programadas</h2>
      </div>

      {citas.length === 0 ? (
        <div className="empty-state">
          <Calendar size={60} />
          <p>No tienes citas programadas</p>
        </div>
      ) : (
        <div className="table-container">
          <table>
            <thead>
              <tr>
                <th>Paciente</th>
                <th>Propietario</th>
                <th>Servicio</th>
                <th>Fecha</th>
                <th>Horario</th>
                <th>Contacto</th>
              </tr>
            </thead>
            <tbody>
              {citas.map((cita) => (
                <tr key={cita.id_cita}>
                  <td>{cita.nombre_mascota || 'N/A'}</td>
                  <td>{cita.propietario}</td>
                  <td>{cita.servicio}</td>
                  <td>{new Date(cita.fecha).toLocaleDateString()}</td>
                  <td>{cita.horario}</td>
                  <td>{cita.email || 'N/A'}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

export default CitasDoctor;
