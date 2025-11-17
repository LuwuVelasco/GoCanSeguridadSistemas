import express from 'express';
import pool from '../config/database.js';

const router = express.Router();

// Obtener logs de usuarios
router.get('/usuarios', async (req, res) => {
  try {
    const result = await pool.query(
      `SELECT l.*, u.nombre as nombre_usuario_completo
       FROM log_usuarios l
       LEFT JOIN usuario u ON l.id_usuario = u.id_usuario
       ORDER BY l.fecha_hora DESC
       LIMIT 1000`
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener logs de usuarios:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener logs de aplicación
router.get('/aplicacion', async (req, res) => {
  try {
    const result = await pool.query(
      `SELECT l.*, u.nombre as nombre_usuario_completo
       FROM log_aplicacion l
       LEFT JOIN usuario u ON l.id_usuario = u.id_usuario
       ORDER BY l.fecha_hora DESC
       LIMIT 1000`
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener logs de aplicación:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Registrar log de usuario
router.post('/usuarios', async (req, res) => {
  try {
    const { id_usuario, nombre_usuario, accion, descripcion } = req.body;

    const result = await pool.query(
      `INSERT INTO log_usuarios (id_usuario, nombre_usuario, accion, descripcion, fecha_hora)
       VALUES ($1, $2, $3, $4, NOW())
       RETURNING id_log`,
      [id_usuario || null, nombre_usuario || null, accion, descripcion || null]
    );

    res.json({
      id_log: result.rows[0].id_log,
      mensaje: 'Log registrado correctamente'
    });

  } catch (error) {
    console.error('Error al registrar log de usuario:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Registrar log de aplicación
router.post('/aplicacion', async (req, res) => {
  try {
    const { 
      id_usuario, 
      nombre_usuario, 
      accion, 
      descripcion, 
      funcion_afectada, 
      dato_modificado, 
      valor_original 
    } = req.body;

    const result = await pool.query(
      `INSERT INTO log_aplicacion (
        id_usuario, nombre_usuario, accion, descripcion, 
        funcion_afectada, dato_modificado, valor_original, fecha_hora
      ) VALUES ($1, $2, $3, $4, $5, $6, $7, NOW())
      RETURNING id_log`,
      [
        id_usuario || null, 
        nombre_usuario || null, 
        accion, 
        descripcion || null,
        funcion_afectada || null,
        dato_modificado || null,
        valor_original || null
      ]
    );

    res.json({
      id_log: result.rows[0].id_log,
      mensaje: 'Log de aplicación registrado correctamente'
    });

  } catch (error) {
    console.error('Error al registrar log de aplicación:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

export default router;
