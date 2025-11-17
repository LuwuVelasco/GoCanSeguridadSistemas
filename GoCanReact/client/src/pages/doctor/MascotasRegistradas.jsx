import { useState, useEffect } from 'react';
import { mascotasAPI } from '../../services/api';
import { PawPrint } from 'lucide-react';

function MascotasRegistradas() {
  const [mascotas, setMascotas] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    cargarMascotas();
  }, []);

  const cargarMascotas = async () => {
    try {
      const response = await mascotasAPI.getTodasMascotas();
      setMascotas(response.data);
    } catch (error) {
      console.error('Error al cargar mascotas:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="loading">Cargando...</div>;
  }

  return (
    <div className="mascotas-registradas">
      <div className="page-header">
        <h2>Mascotas Registradas</h2>
      </div>

      {mascotas.length === 0 ? (
        <div className="empty-state">
          <PawPrint size={60} />
          <p>No hay mascotas registradas</p>
        </div>
      ) : (
        <div className="table-container">
          <table>
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Raza</th>
                <th>Propietario</th>
                <th>Email</th>
                <th>Fecha Nacimiento</th>
              </tr>
            </thead>
            <tbody>
              {mascotas.map((mascota) => (
                <tr key={mascota.id_mascota}>
                  <td>{mascota.nombre_mascota}</td>
                  <td>{mascota.tipo}</td>
                  <td>{mascota.raza || 'N/A'}</td>
                  <td>{mascota.nombre_propietario}</td>
                  <td>{mascota.email}</td>
                  <td>{mascota.fecha_nacimiento ? new Date(mascota.fecha_nacimiento).toLocaleDateString() : 'N/A'}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

export default MascotasRegistradas;
