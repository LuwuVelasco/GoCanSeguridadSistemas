import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import { useState } from 'react';
import PasswordChangeModal from './components/PasswordChangeModal';
import Login from './pages/Login';
import Registro from './pages/Registro';
import Home from './pages/Home';
import ClienteDashboard from './pages/cliente/ClienteDashboard';
import DoctorDashboard from './pages/doctor/DoctorDashboard';
import AdminDashboard from './pages/admin/AdminDashboard';

// Componente para proteger rutas
function ProtectedRoute({ children, allowedRoles }) {
  const { user, loading } = useAuth();

  if (loading) {
    return <div className="loading">Cargando...</div>;
  }

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  if (allowedRoles && !allowedRoles.includes(user.rol)) {
    return <Navigate to="/" replace />;
  }

  return children;
}

function AppContent() {
  const { requirePasswordChange, setRequirePasswordChange } = useAuth();
  const [showPasswordModal, setShowPasswordModal] = useState(false);

  // Show password change modal when required
  if (requirePasswordChange && !showPasswordModal) {
    setShowPasswordModal(true);
  }

  const handlePasswordChangeSuccess = () => {
    setRequirePasswordChange(false);
    setShowPasswordModal(false);
  };

  return (
    <>
      {showPasswordModal && (
        <PasswordChangeModal 
          onClose={() => setShowPasswordModal(false)}
          onSuccess={handlePasswordChangeSuccess}
        />
      )}
      <Routes>
        {/* Rutas públicas */}
        <Route path="/" element={<Home />} />
        <Route path="/login" element={<Login />} />
        <Route path="/registro" element={<Registro />} />

        {/* Rutas protegidas - Cliente */}
        <Route
          path="/cliente/*"
          element={
            <ProtectedRoute allowedRoles={['Cliente']}>
              <ClienteDashboard />
            </ProtectedRoute>
          }
        />

        {/* Rutas protegidas - Doctor */}
        <Route
          path="/doctor/*"
          element={
            <ProtectedRoute allowedRoles={['Doctor']}>
              <DoctorDashboard />
            </ProtectedRoute>
          }
        />

        {/* Rutas protegidas - Administrador */}
        <Route
          path="/admin/*"
          element={
            <ProtectedRoute allowedRoles={['Administrador']}>
              <AdminDashboard />
            </ProtectedRoute>
          }
        />

        {/* Ruta 404 */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </>
  );
}

function App() {
  return (
    <AuthProvider>
      <Router>
        <Routes>
          <Route path="/*" element={<AppContent />} />
        </Routes>
      </Router>
    </AuthProvider>
  );
}

export default App;
