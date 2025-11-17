import express from 'express';
import pool from '../config/database.js';

const router = express.Router();

// Obtener todos los reportes
router.get('/', async (req, res) => {
  try {
    const result = await pool.query(
      `SELECT r.*, c.fecha as fecha_cita, c.horario
       FROM reporte r
       LEFT JOIN cita c ON r.id_cita = c.id_cita
       ORDER BY r.fecha DESC`
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener reportes:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener reporte por ID
router.get('/:id_reporte', async (req, res) => {
  try {
    const { id_reporte } = req.params;
    const result = await pool.query(
      `SELECT r.*, c.fecha as fecha_cita, c.horario
       FROM reporte r
       LEFT JOIN cita c ON r.id_cita = c.id_cita
       WHERE r.id_reporte = $1`,
      [id_reporte]
    );

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Reporte no encontrado' });
    }

    res.json(result.rows[0]);
  } catch (error) {
    console.error('Error al obtener reporte:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Crear reporte
router.post('/', async (req, res) => {
  try {
    const { propietario, fecha, nombre_mascota, id_cita, sintomas, diagnostico, receta } = req.body;

    if (!propietario || !fecha || !nombre_mascota) {
      return res.json({ error: true, mensaje: 'Faltan campos requeridos' });
    }

    const result = await pool.query(
      `INSERT INTO reporte (propietario, fecha, nombre_mascota, id_cita, sintomas, diagnostico, receta)
       VALUES ($1, $2, $3, $4, $5, $6, $7)
       RETURNING id_reporte`,
      [propietario, fecha, nombre_mascota, id_cita || null, sintomas || null, diagnostico || null, receta || null]
    );

    res.json({
      id_reporte: result.rows[0].id_reporte,
      mensaje: 'Reporte creado con éxito'
    });

  } catch (error) {
    console.error('Error al crear reporte:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Actualizar reporte
router.put('/:id_reporte', async (req, res) => {
  try {
    const { id_reporte } = req.params;
    const { sintomas, diagnostico, receta } = req.body;

    const result = await pool.query(
      `UPDATE reporte 
       SET sintomas = $1, diagnostico = $2, receta = $3
       WHERE id_reporte = $4
       RETURNING id_reporte`,
      [sintomas || null, diagnostico || null, receta || null, id_reporte]
    );

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Reporte no encontrado' });
    }

    res.json({ mensaje: 'Reporte actualizado correctamente' });
  } catch (error) {
    console.error('Error al actualizar reporte:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Eliminar reporte
router.delete('/:id_reporte', async (req, res) => {
  try {
    const { id_reporte } = req.params;
    
    const result = await pool.query(
      'DELETE FROM reporte WHERE id_reporte = $1 RETURNING id_reporte',
      [id_reporte]
    );

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Reporte no encontrado' });
    }

    res.json({ mensaje: 'Reporte eliminado correctamente' });
  } catch (error) {
    console.error('Error al eliminar reporte:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

export default router;
