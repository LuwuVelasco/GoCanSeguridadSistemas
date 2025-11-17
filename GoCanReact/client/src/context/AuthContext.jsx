import { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';

const AuthContext = createContext();

export function useAuth() {
  return useContext(AuthContext);
}

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [requirePasswordChange, setRequirePasswordChange] = useState(false);

  // Verificar sesión al cargar
  useEffect(() => {
    checkSession();
  }, []);

  const checkSession = async () => {
    try {
      const response = await axios.get('/api/auth/session', {
        withCredentials: true
      });
      if (response.data.estado === 'success') {
        setUser(response.data.usuario);
        if (response.data.require_change) {
          setRequirePasswordChange(true);
        }
      }
    } catch (error) {
      console.error('Error al verificar sesión:', error);
    } finally {
      setLoading(false);
    }
  };

  const login = async (email, password, captcha) => {
    try {
      const response = await axios.post('/api/auth/login', {
        email,
        password,
        'g-recaptcha-response': captcha
      }, {
        withCredentials: true
      });

      if (response.data.estado === 'success') {
        const userData = {
          id_usuario: response.data.id_usuario,
          nombre: response.data.nombre,
          rol: response.data.rol,
          rol_id: response.data.rol_id,
          id_doctores: response.data.id_doctores
        };
        setUser(userData);
        
        if (response.data.require_change) {
          setRequirePasswordChange(true);
          return { success: true, requirePasswordChange: true };
        }
        return { success: true };
      } else {
        return { success: false, mensaje: response.data.mensaje };
      }
    } catch (error) {
      console.error('Error en login:', error);
      return { success: false, mensaje: 'Error de conexión' };
    }
  };

  const registro = async (email, nombre, password, verified) => {
    try {
      const response = await axios.post('/api/auth/registro', {
        email,
        nombre,
        password,
        verified
      });

      if (response.data.estado === 'success') {
        return { success: true };
      } else {
        return { success: false, mensaje: response.data.mensaje };
      }
    } catch (error) {
      console.error('Error en registro:', error);
      return { success: false, mensaje: 'Error de conexión' };
    }
  };

  const logout = async () => {
    try {
      await axios.post('/api/auth/logout', {}, {
        withCredentials: true
      });
      setUser(null);
    } catch (error) {
      console.error('Error al cerrar sesión:', error);
    }
  };

  const value = {
    user,
    loading,
    login,
    registro,
    logout,
    checkSession,
    requirePasswordChange,
    setRequirePasswordChange
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
