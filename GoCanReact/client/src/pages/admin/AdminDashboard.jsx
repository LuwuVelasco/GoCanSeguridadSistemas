import { Routes, Route, Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { Home, Users, UserPlus, Shield, Calendar, FileText, Settings, LogOut } from 'lucide-react';
import GestionUsuarios from './GestionUsuarios';
import GestionRoles from './GestionRoles';
import TodasCitas from './TodasCitas';
import LogsUsuarios from './LogsUsuarios';
import LogsAplicacion from './LogsAplicacion';
import Configuracion from './Configuracion';
import '../cliente/ClienteDashboard.css';

function AdminDashboard() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <div className="dashboard">
      <aside className="sidebar">
        <div className="sidebar-header">
          <h2>GoCan</h2>
          <p>Administrador</p>
        </div>

        <nav className="sidebar-menu">
          <ul>
            <li>
              <Link to="/admin" className="sidebar-link">
                <Home size={20} />
                <span>Inicio</span>
              </Link>
            </li>
            <li>
              <Link to="/admin/usuarios" className="sidebar-link">
                <Users size={20} />
                <span>Usuarios</span>
              </Link>
            </li>
            <li>
              <Link to="/admin/roles" className="sidebar-link">
                <Shield size={20} />
                <span>Roles</span>
              </Link>
            </li>
            <li>
              <Link to="/admin/citas" className="sidebar-link">
                <Calendar size={20} />
                <span>Citas</span>
              </Link>
            </li>
            <li>
              <Link to="/admin/logs-usuarios" className="sidebar-link">
                <FileText size={20} />
                <span>Logs Usuarios</span>
              </Link>
            </li>
            <li>
              <Link to="/admin/logs-aplicacion" className="sidebar-link">
                <FileText size={20} />
                <span>Logs Aplicación</span>
              </Link>
            </li>
            <li>
              <Link to="/admin/configuracion" className="sidebar-link">
                <Settings size={20} />
                <span>Configuración</span>
              </Link>
            </li>
          </ul>
        </nav>

        <button onClick={handleLogout} className="logout-btn">
          <LogOut size={20} />
          <span>Cerrar Sesión</span>
        </button>
      </aside>

      <main className="main-content">
        <div className="dashboard-header">
          <h1>Panel de Administración</h1>
        </div>

        <Routes>
          <Route index element={<AdminHome />} />
          <Route path="usuarios" element={<GestionUsuarios />} />
          <Route path="roles" element={<GestionRoles />} />
          <Route path="citas" element={<TodasCitas />} />
          <Route path="logs-usuarios" element={<LogsUsuarios />} />
          <Route path="logs-aplicacion" element={<LogsAplicacion />} />
          <Route path="configuracion" element={<Configuracion />} />
        </Routes>
      </main>
    </div>
  );
}

function AdminHome() {
  return (
    <div className="cliente-home">
      <div className="stats-grid">
        <div className="stat-card">
          <Users size={40} />
          <h3>Total Usuarios</h3>
          <p className="stat-number">0</p>
        </div>

        <div className="stat-card">
          <Calendar size={40} />
          <h3>Citas Hoy</h3>
          <p className="stat-number">0</p>
        </div>

        <div className="stat-card">
          <Shield size={40} />
          <h3>Roles Activos</h3>
          <p className="stat-number">3</p>
        </div>
      </div>

      <div className="quick-actions">
        <h2>Acciones Rápidas</h2>
        <div className="actions-grid">
          <Link to="/admin/usuarios" className="action-card">
            <Users size={30} />
            <span>Gestionar Usuarios</span>
          </Link>

          <Link to="/admin/roles" className="action-card">
            <Shield size={30} />
            <span>Gestionar Roles</span>
          </Link>

          <Link to="/admin/citas" className="action-card">
            <Calendar size={30} />
            <span>Ver Citas</span>
          </Link>

          <Link to="/admin/configuracion" className="action-card">
            <Settings size={30} />
            <span>Configuración</span>
          </Link>
        </div>
      </div>
    </div>
  );
}

export default AdminDashboard;
