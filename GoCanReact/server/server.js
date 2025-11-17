import express from 'express';
import cors from 'cors';
import session from 'express-session';
import helmet from 'helmet';
import dotenv from 'dotenv';
import authRoutes from './routes/auth.js';
import citasRoutes from './routes/citas.js';
import mascotasRoutes from './routes/mascotas.js';
import reportesRoutes from './routes/reportes.js';
import catalogoRoutes from './routes/catalogo.js';
import usuariosRoutes from './routes/usuarios.js';
import rolesRoutes from './routes/roles.js';
import logsRoutes from './routes/logs.js';
import configRoutes from './routes/config.js';
import favoritosRoutes from './routes/favoritos.js';
import calificacionesRoutes from './routes/calificaciones.js';

dotenv.config();

const app = express();
const PORT = process.env.PORT || 5000;

// Middleware de seguridad
app.use(helmet({
  contentSecurityPolicy: false,
  crossOriginEmbedderPolicy: false
}));

// CORS configuración
const corsOptions = {
  origin: ['http://localhost:5173', 'http://127.0.0.1:5173', 'http://localhost:3000'],
  credentials: true,
  optionsSuccessStatus: 200
};
app.use(cors(corsOptions));

// Body parsers
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Sesiones
app.use(session({
  secret: process.env.SESSION_SECRET || 'gocan_secret_key_2025',
  resave: false,
  saveUninitialized: false,
  cookie: {
    secure: process.env.NODE_ENV === 'production',
    httpOnly: true,
    maxAge: 1000 * 60 * 60 * 24 // 24 horas
  }
}));

// Rutas
app.use('/api/auth', authRoutes);
app.use('/api/citas', citasRoutes);
app.use('/api/mascotas', mascotasRoutes);
app.use('/api/reportes', reportesRoutes);
app.use('/api/catalogo', catalogoRoutes);
app.use('/api/usuarios', usuariosRoutes);
app.use('/api/roles', rolesRoutes);
app.use('/api/logs', logsRoutes);
app.use('/api/config', configRoutes);
app.use('/api/favoritos', favoritosRoutes);
app.use('/api/calificaciones', calificacionesRoutes);

// Ruta de prueba
app.get('/api/health', (req, res) => {
  res.json({ status: 'OK', message: 'GoCan API funcionando correctamente' });
});

// Manejo de errores
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).json({ 
    estado: 'error', 
    mensaje: 'Error interno del servidor' 
  });
});

// Iniciar servidor
app.listen(PORT, () => {
  console.log(`🚀 Servidor corriendo en http://localhost:${PORT}`);
  console.log(`📊 Base de datos: ${process.env.DB_NAME}`);
});

export default app;
