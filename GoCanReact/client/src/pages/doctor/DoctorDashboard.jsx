import { Routes, Route, Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { Home, Calendar, FileText, PawPrint, User, LogOut } from 'lucide-react';
import CitasDoctor from './CitasDoctor';
import Reportes from './Reportes';
import MascotasRegistradas from './MascotasRegistradas';
import '../cliente/ClienteDashboard.css';

function DoctorDashboard() {
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
          <p>Doctor</p>
        </div>

        <nav className="sidebar-menu">
          <ul>
            <li>
              <Link to="/doctor" className="sidebar-link">
                <Home size={20} />
                <span>Inicio</span>
              </Link>
            </li>
            <li>
              <Link to="/doctor/citas" className="sidebar-link">
                <Calendar size={20} />
                <span>Mis Citas</span>
              </Link>
            </li>
            <li>
              <Link to="/doctor/reportes" className="sidebar-link">
                <FileText size={20} />
                <span>Reportes</span>
              </Link>
            </li>
            <li>
              <Link to="/doctor/mascotas" className="sidebar-link">
                <PawPrint size={20} />
                <span>Mascotas</span>
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
          <h1>Bienvenido, Dr(a). {user?.nombre}</h1>
        </div>

        <Routes>
          <Route index element={<DoctorHome />} />
          <Route path="citas" element={<CitasDoctor />} />
          <Route path="reportes" element={<Reportes />} />
          <Route path="mascotas" element={<MascotasRegistradas />} />
        </Routes>
      </main>
    </div>
  );
}

function DoctorHome() {
  return (
    <div className="cliente-home">
      <div className="stats-grid">
        <div className="stat-card">
          <Calendar size={40} />
          <h3>Citas Hoy</h3>
          <p className="stat-number">0</p>
        </div>

        <div className="stat-card">
          <FileText size={40} />
          <h3>Reportes</h3>
          <p className="stat-number">0</p>
        </div>

        <div className="stat-card">
          <PawPrint size={40} />
          <h3>Pacientes</h3>
          <p className="stat-number">0</p>
        </div>
      </div>

      <div className="quick-actions">
        <h2>Acciones Rápidas</h2>
        <div className="actions-grid">
          <Link to="/doctor/citas" className="action-card">
            <Calendar size={30} />
            <span>Ver Citas</span>
          </Link>

          <Link to="/doctor/reportes" className="action-card">
            <FileText size={30} />
            <span>Crear Reporte</span>
          </Link>

          <Link to="/doctor/mascotas" className="action-card">
            <PawPrint size={30} />
            <span>Ver Mascotas</span>
          </Link>
        </div>
      </div>
    </div>
  );
}

export default DoctorDashboard;
