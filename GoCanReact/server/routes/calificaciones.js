import express from 'express';
import pool from '../config/database.js';

const router = express.Router();

// Obtener calificación promedio de un producto
router.get('/producto/:id_producto', async (req, res) => {
  try {
    const { id_producto } = req.params;
    
    const result = await pool.query(
      `SELECT 
        COALESCE(AVG(puntuacion), 0) as promedio,
        COUNT(*) as total_calificaciones
       FROM calificacion
       WHERE id_producto = $1`,
      [id_producto]
    );

    res.json(result.rows[0]);
  } catch (error) {
    console.error('Error al obtener calificación:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Calificar un producto
router.post('/', async (req, res) => {
  try {
    const { id_usuario, id_producto, puntuacion } = req.body;

    if (puntuacion < 1 || puntuacion > 5) {
      return res.status(400).json({ 
        error: true, 
        mensaje: 'La puntuación debe estar entre 1 y 5' 
      });
    }

    // Verificar si ya calificó
    const existe = await pool.query(
      'SELECT id_calificacion FROM calificacion WHERE id_usuario = $1 AND id_producto = $2',
      [id_usuario, id_producto]
    );

    if (existe.rows.length > 0) {
      // Actualizar calificación existente
      await pool.query(
        'UPDATE calificacion SET puntuacion = $1, fecha_calificacion = NOW() WHERE id_calificacion = $2',
        [puntuacion, existe.rows[0].id_calificacion]
      );

      res.json({ 
        success: true, 
        mensaje: 'Calificación actualizada' 
      });
    } else {
      // Crear nueva calificación
      await pool.query(
        `INSERT INTO calificacion (id_usuario, id_producto, puntuacion, fecha_calificacion)
         VALUES ($1, $2, $3, NOW())`,
        [id_usuario, id_producto, puntuacion]
      );

      res.json({ 
        success: true, 
        mensaje: 'Producto calificado exitosamente' 
      });
    }
  } catch (error) {
    console.error('Error al calificar producto:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener calificación de un usuario para un producto
router.get('/usuario/:id_usuario/producto/:id_producto', async (req, res) => {
  try {
    const { id_usuario, id_producto } = req.params;

    const result = await pool.query(
      'SELECT puntuacion FROM calificacion WHERE id_usuario = $1 AND id_producto = $2',
      [id_usuario, id_producto]
    );

    if (result.rows.length > 0) {
      res.json({ puntuacion: result.rows[0].puntuacion });
    } else {
      res.json({ puntuacion: 0 });
    }
  } catch (error) {
    console.error('Error al obtener calificación del usuario:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

export default router;
