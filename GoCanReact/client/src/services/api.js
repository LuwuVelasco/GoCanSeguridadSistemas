import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json'
  }
});

// Interceptor para manejar errores
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// ========== CITAS ==========
export const citasAPI = {
  getEspecialidades: () => api.get('/citas/especialidades'),
  getDoctoresPorEspecialidad: (id) => api.get(`/citas/doctores/${id}`),
  getTodosDoctores: () => api.get('/citas/doctores'),
  crearCita: (data) => api.post('/citas', data),
  getCitasUsuario: (id) => api.get(`/citas/usuario/${id}`),
  getCitasDoctor: (id) => api.get(`/citas/doctor/${id}`),
  getTodasCitas: (filters = {}) => {
    const params = new URLSearchParams();
    
    // Agregar filtros a los parámetros de consulta
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value);
      }
    });
    
    return api.get(`/citas?${params.toString()}`);
  },
  eliminarCita: (id) => api.delete(`/citas/${id}`),
  actualizarCita: (id, data) => api.put(`/citas/${id}`, data),
  buscarCitas: (filters) => {
    const params = new URLSearchParams();
    
    // Agregar filtros a los parámetros de consulta
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value);
      }
    });
    
    return api.get(`/citas/buscar?${params.toString()}`);
  }
};

// ========== MASCOTAS ==========
export const mascotasAPI = {
  getTodasMascotas: () => api.get('/mascotas'),
  getMascotasUsuario: (id) => api.get(`/mascotas/usuario/${id}`),
  getMascota: (id) => api.get(`/mascotas/${id}`),
  crearMascota: (data) => api.post('/mascotas', data),
  actualizarMascota: (id, data) => api.put(`/mascotas/${id}`, data),
  eliminarMascota: (id) => api.delete(`/mascotas/${id}`)
};

// ========== REPORTES ==========
export const reportesAPI = {
  getTodosReportes: (filters = {}) => {
    const params = new URLSearchParams();
    
    // Agregar filtros a los parámetros de consulta
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value);
      }
    });
    
    return api.get(`/reportes?${params.toString()}`);
  },
  getReporte: (id) => api.get(`/reportes/${id}`),
  crearReporte: (data) => api.post('/reportes', data),
  actualizarReporte: (id, data) => api.put(`/reportes/${id}`, data),
  eliminarReporte: (id) => api.delete(`/reportes/${id}`)
};

// ========== CATÁLOGO ==========
export const catalogoAPI = {
  getTodosProductos: () => api.get('/catalogo'),
  getProducto: (id) => api.get(`/catalogo/${id}`),
  crearProducto: (data) => api.post('/catalogo', data),
  actualizarProducto: (id, data) => api.put(`/catalogo/${id}`, data),
  eliminarProducto: (id) => api.delete(`/catalogo/${id}`)
};

// ========== USUARIOS ==========
export const usuariosAPI = {
  getTodosUsuarios: (filters = {}) => {
    const params = new URLSearchParams();
    
    // Agregar filtros a los parámetros de consulta
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value);
      }
    });
    
    return api.get(`/usuarios?${params.toString()}`);
  },
  getClientes: (nombre) => {
    const params = new URLSearchParams();
    if (nombre) params.append('nombre', nombre);
    return api.get(`/usuarios/clientes?${params.toString()}`);
  },
  getFuncionarios: () => api.get('/usuarios/funcionarios'),
  getUsuario: (id) => api.get(`/usuarios/${id}`),
  getUsuarioPorEmail: (email) => api.get(`/usuarios/email/${email}`),
  buscarUsuarios: (termino) => api.get(`/usuarios/buscar?q=${encodeURIComponent(termino)}`),
  registrarFuncionario: (data) => api.post('/usuarios/funcionarios', data),
  eliminarUsuario: (id) => api.delete(`/usuarios/${id}`)
};

// ========== ROLES ==========
export const rolesAPI = {
  getTodosRoles: () => api.get('/roles'),
  getRol: (id) => api.get(`/roles/${id}`),
  getPermisosUsuario: (id) => api.get(`/roles/usuario/${id}`),
  crearRol: (data) => api.post('/roles', data),
  actualizarPermisos: (id, data) => api.put(`/roles/${id}`, data),
  eliminarRol: (id) => api.delete(`/roles/${id}`)
};

// ========== LOGS ==========
export const logsAPI = {
  getLogsUsuarios: () => api.get('/logs/usuarios'),
  getLogsAplicacion: () => api.get('/logs/aplicacion'),
  registrarLogUsuario: (data) => api.post('/logs/usuarios', data),
  registrarLogAplicacion: (data) => api.post('/logs/aplicacion', data)
};

// ========== CONFIGURACIÓN ==========
export const configAPI = {
  getConfigPasswords: () => api.get('/config/passwords'),
  actualizarConfigPasswords: (data) => api.put('/config/passwords', data),
  getHistorialPasswords: (id) => api.get(`/config/passwords/historial/${id}`)
};

// ========== FAVORITOS ==========
export const favoritosAPI = {
  getFavoritosUsuario: (id) => api.get(`/favoritos/usuario/${id}`),
  agregarFavorito: (data) => api.post('/favoritos', data),
  eliminarFavorito: (idUsuario, idProducto) => api.delete(`/favoritos/${idUsuario}/${idProducto}`),
  verificarFavorito: (idUsuario, idProducto) => api.get(`/favoritos/verificar/${idUsuario}/${idProducto}`)
};

// ========== CALIFICACIONES ==========
export const calificacionesAPI = {
  getCalificacionProducto: (id) => api.get(`/calificaciones/producto/${id}`),
  calificarProducto: (data) => api.post('/calificaciones', data),
  getCalificacionUsuario: (idUsuario, idProducto) => api.get(`/calificaciones/usuario/${idUsuario}/producto/${idProducto}`)
};

export default api;
