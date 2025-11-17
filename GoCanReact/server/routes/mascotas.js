import express from 'express';
import pool from '../config/database.js';

const router = express.Router();

// Obtener todas las mascotas
router.get('/', async (req, res) => {
  try {
    const result = await pool.query(
      `SELECT m.*, u.nombre as nombre_propietario, u.email
       FROM mascota m
       LEFT JOIN usuario u ON m.id_usuario = u.id_usuario
       ORDER BY m.nombre_mascota`
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener mascotas:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener mascotas de un usuario
router.get('/usuario/:id_usuario', async (req, res) => {
  try {
    const { id_usuario } = req.params;
    const result = await pool.query(
      'SELECT * FROM mascota WHERE id_usuario = $1 ORDER BY nombre_mascota',
      [id_usuario]
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener mascotas del usuario:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener una mascota por ID
router.get('/:id_mascota', async (req, res) => {
  try {
    const { id_mascota } = req.params;
    const result = await pool.query(
      `SELECT m.*, u.nombre as nombre_propietario, u.email
       FROM mascota m
       LEFT JOIN usuario u ON m.id_usuario = u.id_usuario
       WHERE m.id_mascota = $1`,
      [id_mascota]
    );

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Mascota no encontrada' });
    }

    res.json(result.rows[0]);
  } catch (error) {
    console.error('Error al obtener mascota:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Crear mascota
router.post('/', async (req, res) => {
  try {
    const { nombre_mascota, id_usuario, tipo, raza, fecha_nacimiento } = req.body;

    if (!nombre_mascota || !id_usuario || !tipo) {
      return res.json({ error: true, mensaje: 'Faltan campos requeridos' });
    }

    const result = await pool.query(
      `INSERT INTO mascota (nombre_mascota, id_usuario, tipo, raza, fecha_nacimiento)
       VALUES ($1, $2, $3, $4, $5)
       RETURNING id_mascota`,
      [nombre_mascota, id_usuario, tipo, raza || null, fecha_nacimiento || null]
    );

    res.json({
      id_mascota: result.rows[0].id_mascota,
      mensaje: 'Mascota registrada con éxito'
    });

  } catch (error) {
    console.error('Error al crear mascota:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Actualizar mascota
router.put('/:id_mascota', async (req, res) => {
  try {
    const { id_mascota } = req.params;
    const { nombre_mascota, tipo, raza, fecha_nacimiento } = req.body;

    if (!nombre_mascota || !tipo) {
      return res.json({ error: true, mensaje: 'Faltan campos requeridos' });
    }

    const result = await pool.query(
      `UPDATE mascota 
       SET nombre_mascota = $1, tipo = $2, raza = $3, fecha_nacimiento = $4
       WHERE id_mascota = $5
       RETURNING id_mascota`,
      [nombre_mascota, tipo, raza || null, fecha_nacimiento || null, id_mascota]
    );

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Mascota no encontrada' });
    }

    res.json({ mensaje: 'Mascota actualizada correctamente' });
  } catch (error) {
    console.error('Error al actualizar mascota:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Eliminar mascota
router.delete('/:id_mascota', async (req, res) => {
  try {
    const { id_mascota } = req.params;
    
    const result = await pool.query(
      'DELETE FROM mascota WHERE id_mascota = $1 RETURNING id_mascota',
      [id_mascota]
    );

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Mascota no encontrada' });
    }

    res.json({ mensaje: 'Mascota eliminada correctamente' });
  } catch (error) {
    console.error('Error al eliminar mascota:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

export default router;
