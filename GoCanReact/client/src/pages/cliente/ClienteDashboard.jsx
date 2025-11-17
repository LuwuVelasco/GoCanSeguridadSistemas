import { useState } from 'react';
import { Routes, Route, Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { Home, Calendar, PawPrint, ShoppingCart, User, LogOut, Heart } from 'lucide-react';
import MisCitas from './MisCitas';
import MisMascotas from './MisMascotas';
import Catalogo from './Catalogo';
import MiPerfil from './MiPerfil';
import Favoritos from './Favoritos';
import './ClienteDashboard.css';

function ClienteDashboard() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [showFavoritos, setShowFavoritos] = useState(false);

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <div className="dashboard">
      <aside className="sidebar">
        <div className="sidebar-header">
          <h2>GoCan</h2>
          <p>Cliente</p>
        </div>

        <nav className="sidebar-menu">
          <ul>
            <li>
              <Link to="/cliente" className="sidebar-link">
                <Home size={20} />
                <span>Inicio</span>
              </Link>
            </li>
            <li>
              <Link to="/cliente/citas" className="sidebar-link">
                <Calendar size={20} />
                <span>Mis Citas</span>
              </Link>
            </li>
            <li>
              <Link to="/cliente/mascotas" className="sidebar-link">
                <PawPrint size={20} />
                <span>Mis Mascotas</span>
              </Link>
            </li>
            <li>
              <Link to="/cliente/catalogo" className="sidebar-link">
                <ShoppingCart size={20} />
                <span>Catálogo</span>
              </Link>
            </li>
            <li>
              <Link to="/cliente/perfil" className="sidebar-link">
                <User size={20} />
                <span>Mi Perfil</span>
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
          <h1>Bienvenido, {user?.nombre}</h1>
          <button 
            className="btn-favoritos-header"
            onClick={() => setShowFavoritos(true)}
            title="Ver Favoritos"
          >
            <Heart size={24} />
            Favoritos
          </button>
        </div>

        <Routes>
          <Route index element={<ClienteHome />} />
          <Route path="citas" element={<MisCitas />} />
          <Route path="mascotas" element={<MisMascotas />} />
          <Route path="catalogo" element={<Catalogo />} />
          <Route path="perfil" element={<MiPerfil />} />
        </Routes>

        <Favoritos isOpen={showFavoritos} onClose={() => setShowFavoritos(false)} />
      </main>
    </div>
  );
}

function ClienteHome() {
  return (
    <div className="cliente-home">
      <div className="stats-grid">
        <div className="stat-card">
          <Calendar size={40} />
          <h3>Próximas Citas</h3>
          <p className="stat-number">0</p>
        </div>

        <div className="stat-card">
          <PawPrint size={40} />
          <h3>Mis Mascotas</h3>
          <p className="stat-number">0</p>
        </div>

        <div className="stat-card">
          <ShoppingCart size={40} />
          <h3>Favoritos</h3>
          <p className="stat-number">0</p>
        </div>
      </div>

      <div className="quick-actions">
        <h2>Acciones Rápidas</h2>
        <div className="actions-grid">
          <Link to="/cliente/citas" className="action-card">
            <Calendar size={30} />
            <span>Agendar Cita</span>
          </Link>

          <Link to="/cliente/mascotas" className="action-card">
            <PawPrint size={30} />
            <span>Registrar Mascota</span>
          </Link>

          <Link to="/cliente/catalogo" className="action-card">
            <ShoppingCart size={30} />
            <span>Ver Catálogo</span>
          </Link>
        </div>
      </div>
    </div>
  );
}

export default ClienteDashboard;
