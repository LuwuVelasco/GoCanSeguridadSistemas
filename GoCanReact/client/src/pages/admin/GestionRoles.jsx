import { useState, useEffect } from 'react';
import { rolesAPI } from '../../services/api';
import { Shield } from 'lucide-react';

function GestionRoles() {
  const [roles, setRoles] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    cargarRoles();
  }, []);

  const cargarRoles = async () => {
    try {
      const response = await rolesAPI.getTodosRoles();
      setRoles(response.data);
    } catch (error) {
      console.error('Error al cargar roles:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="loading">Cargando...</div>;
  }

  return (
    <div className="gestion-roles">
      <div className="page-header">
        <h2>Gestión de Roles y Permisos</h2>
      </div>

      <div className="roles-grid">
        {roles.map((rol) => (
          <div key={rol.id_rol} className="rol-card">
            <div className="rol-header">
              <Shield size={30} />
              <h3>{rol.nombre_rol}</h3>
            </div>

            <div className="permisos-list">
              <h4>Permisos:</h4>
              <ul>
                {rol.registro_mascotas && <li>✓ Registro de mascotas</li>}
                {rol.registro_funcionarios && <li>✓ Registro de funcionarios</li>}
                {rol.registro_receta && <li>✓ Registro de recetas</li>}
                {rol.ver_reportes_recetas && <li>✓ Ver reportes y recetas</li>}
                {rol.ver_mascotas_registradas && <li>✓ Ver mascotas registradas</li>}
                {rol.editar_datos_mascota && <li>✓ Editar datos de mascota</li>}
                {rol.eliminar_mascotas && <li>✓ Eliminar mascotas</li>}
                {rol.eliminar_usuarios && <li>✓ Eliminar usuarios</li>}
                {rol.editar_configuracion && <li>✓ Editar configuración</li>}
                {rol.vista_log_usuarios && <li>✓ Ver logs de usuarios</li>}
                {rol.vista_log_aplicacion && <li>✓ Ver logs de aplicación</li>}
              </ul>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

export default GestionRoles;
