import { useState, useEffect } from 'react';
import { configAPI } from '../../services/api';
import { Settings } from 'lucide-react';
import Swal from 'sweetalert2';

function Configuracion() {
  const [config, setConfig] = useState(null);
  const [loading, setLoading] = useState(true);
  const [formData, setFormData] = useState({
    tiempo_vida_util: '',
    numero_historico: ''
  });

  useEffect(() => {
    cargarConfiguracion();
  }, []);

  const cargarConfiguracion = async () => {
    try {
      const response = await configAPI.getConfigPasswords();
      setConfig(response.data);
      setFormData({
        tiempo_vida_util: response.data.tiempo_vida_util,
        numero_historico: response.data.numero_historico
      });
    } catch (error) {
      console.error('Error al cargar configuración:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      await configAPI.actualizarConfigPasswords(formData);
      Swal.fire('¡Éxito!', 'Configuración actualizada correctamente', 'success');
      cargarConfiguracion();
    } catch (error) {
      Swal.fire('Error', 'No se pudo actualizar la configuración', 'error');
    }
  };

  if (loading) {
    return <div className="loading">Cargando...</div>;
  }

  return (
    <div className="configuracion">
      <div className="page-header">
        <h2>Configuración del Sistema</h2>
      </div>

      <div className="config-card">
        <div className="config-header">
          <Settings size={30} />
          <h3>Configuración de Contraseñas</h3>
        </div>

        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label>Tiempo de Vida Útil (días)</label>
            <input
              type="number"
              value={formData.tiempo_vida_util}
              onChange={(e) => setFormData({ ...formData, tiempo_vida_util: e.target.value })}
              className="form-control"
              min="1"
              required
            />
            <small>Número de días antes de que expire una contraseña</small>
          </div>

          <div className="form-group">
            <label>Número de Contraseñas en Historial</label>
            <input
              type="number"
              value={formData.numero_historico}
              onChange={(e) => setFormData({ ...formData, numero_historico: e.target.value })}
              className="form-control"
              min="1"
              required
            />
            <small>Cantidad de contraseñas anteriores que no se pueden reutilizar</small>
          </div>

          <button type="submit" className="btn btn-primary">
            Guardar Configuración
          </button>
        </form>

        {config && (
          <div className="config-info">
            <p><strong>Última actualización:</strong> {new Date(config.fecha_configuracion).toLocaleString()}</p>
          </div>
        )}
      </div>
    </div>
  );
}

export default Configuracion;
