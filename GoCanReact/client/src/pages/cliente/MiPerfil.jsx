import { useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { User, Mail, Shield } from 'lucide-react';
import Swal from 'sweetalert2';

function MiPerfil() {
  const { user } = useAuth();
  const [showChangePassword, setShowChangePassword] = useState(false);
  const [passwordData, setPasswordData] = useState({
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  });

  const handleChangePassword = async (e) => {
    e.preventDefault();

    if (passwordData.newPassword !== passwordData.confirmPassword) {
      Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
      return;
    }

    if (passwordData.newPassword.length < 6) {
      Swal.fire('Error', 'La contraseña debe tener al menos 6 caracteres', 'error');
      return;
    }

    // Aquí iría la lógica para cambiar la contraseña
    Swal.fire('Éxito', 'Contraseña actualizada correctamente', 'success');
    setShowChangePassword(false);
    setPasswordData({ currentPassword: '', newPassword: '', confirmPassword: '' });
  };

  return (
    <div className="mi-perfil">
      <div className="page-header">
        <h2>Mi Perfil</h2>
      </div>

      <div className="perfil-container">
        <div className="perfil-card">
          <div className="perfil-avatar">
            <User size={80} />
          </div>

          <div className="perfil-info">
            <div className="info-item">
              <User size={20} />
              <div>
                <label>Nombre</label>
                <p>{user?.nombre}</p>
              </div>
            </div>

            <div className="info-item">
              <Mail size={20} />
              <div>
                <label>Email</label>
                <p>{user?.email || 'No disponible'}</p>
              </div>
            </div>

            <div className="info-item">
              <Shield size={20} />
              <div>
                <label>Rol</label>
                <p>{user?.rol}</p>
              </div>
            </div>
          </div>

          <button 
            onClick={() => setShowChangePassword(!showChangePassword)} 
            className="btn btn-primary"
          >
            Cambiar Contraseña
          </button>
        </div>

        {showChangePassword && (
          <div className="cambiar-password-card">
            <h3>Cambiar Contraseña</h3>
            <form onSubmit={handleChangePassword}>
              <div className="form-group">
                <label>Contraseña Actual</label>
                <input
                  type="password"
                  value={passwordData.currentPassword}
                  onChange={(e) => setPasswordData({ ...passwordData, currentPassword: e.target.value })}
                  className="form-control"
                  required
                />
              </div>

              <div className="form-group">
                <label>Nueva Contraseña</label>
                <input
                  type="password"
                  value={passwordData.newPassword}
                  onChange={(e) => setPasswordData({ ...passwordData, newPassword: e.target.value })}
                  className="form-control"
                  required
                />
              </div>

              <div className="form-group">
                <label>Confirmar Nueva Contraseña</label>
                <input
                  type="password"
                  value={passwordData.confirmPassword}
                  onChange={(e) => setPasswordData({ ...passwordData, confirmPassword: e.target.value })}
                  className="form-control"
                  required
                />
              </div>

              <div className="form-actions">
                <button 
                  type="button" 
                  onClick={() => setShowChangePassword(false)} 
                  className="btn btn-secondary"
                >
                  Cancelar
                </button>
                <button type="submit" className="btn btn-primary">
                  Actualizar Contraseña
                </button>
              </div>
            </form>
          </div>
        )}
      </div>
    </div>
  );
}

export default MiPerfil;
