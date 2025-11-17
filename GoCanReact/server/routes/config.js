import express from 'express';
import pool from '../config/database.js';

const router = express.Router();

// Obtener configuración de passwords
router.get('/passwords', async (req, res) => {
  try {
    const result = await pool.query(
      'SELECT * FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1'
    );

    if (result.rows.length === 0) {
      return res.json({ 
        error: true, 
        mensaje: 'No se encontró configuración de passwords' 
      });
    }

    res.json(result.rows[0]);
  } catch (error) {
    console.error('Error al obtener configuración de passwords:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Actualizar configuración de passwords
router.put('/passwords', async (req, res) => {
  try {
    const { tiempo_vida_util, numero_historico } = req.body;

    if (!tiempo_vida_util || !numero_historico) {
      return res.json({ error: true, mensaje: 'Faltan campos requeridos' });
    }

    const result = await pool.query(
      `INSERT INTO configuracion_passwords (tiempo_vida_util, numero_historico, fecha_configuracion)
       VALUES ($1, $2, NOW())
       RETURNING id_configuracion`,
      [tiempo_vida_util, numero_historico]
    );

    res.json({
      id_configuracion: result.rows[0].id_configuracion,
      mensaje: 'Configuración actualizada correctamente'
    });

  } catch (error) {
    console.error('Error al actualizar configuración:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener historial de passwords de un usuario
router.get('/passwords/historial/:id_usuario', async (req, res) => {
  try {
    const { id_usuario } = req.params;
    const result = await pool.query(
      `SELECT * FROM historial_passwords 
       WHERE id_usuario = $1 
       ORDER BY fecha_creacion DESC`,
      [id_usuario]
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener historial de passwords:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

export default router;
