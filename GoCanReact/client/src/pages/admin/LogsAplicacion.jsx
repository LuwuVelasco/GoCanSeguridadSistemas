import { useState, useEffect } from 'react';
import { logsAPI } from '../../services/api';
import { FileText } from 'lucide-react';

function LogsAplicacion() {
  const [logs, setLogs] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    cargarLogs();
  }, []);

  const cargarLogs = async () => {
    try {
      const response = await logsAPI.getLogsAplicacion();
      setLogs(response.data);
    } catch (error) {
      console.error('Error al cargar logs:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="loading">Cargando...</div>;
  }

  return (
    <div className="logs-aplicacion">
      <div className="page-header">
        <h2>Logs de Aplicación</h2>
      </div>

      {logs.length === 0 ? (
        <div className="empty-state">
          <FileText size={60} />
          <p>No hay logs registrados</p>
        </div>
      ) : (
        <div className="table-container">
          <table>
            <thead>
              <tr>
                <th>Fecha/Hora</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Función</th>
                <th>Descripción</th>
              </tr>
            </thead>
            <tbody>
              {logs.map((log) => (
                <tr key={log.id_log}>
                  <td>{new Date(log.fecha_hora).toLocaleString()}</td>
                  <td>{log.nombre_usuario || 'N/A'}</td>
                  <td>{log.accion}</td>
                  <td>{log.funcion_afectada || 'N/A'}</td>
                  <td>{log.descripcion}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

export default LogsAplicacion;
