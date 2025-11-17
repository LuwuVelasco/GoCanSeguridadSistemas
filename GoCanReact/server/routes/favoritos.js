import express from 'express';
import pool from '../config/database.js';

const router = express.Router();

// Obtener favoritos de un usuario
router.get('/usuario/:id_usuario', async (req, res) => {
  try {
    const { id_usuario } = req.params;
    
    const result = await pool.query(
      `SELECT f.id_favorito, f.id_usuario, f.id_producto, f.fecha_agregado,
              p.nombre, p.descripcion, p.precio, p.categoria, p.imagen,
              COALESCE(AVG(c.puntuacion), 0) as calificacion_promedio
       FROM favoritos f
       INNER JOIN producto p ON f.id_producto = p.id_producto
       LEFT JOIN calificacion c ON p.id_producto = c.id_producto
       WHERE f.id_usuario = $1
       GROUP BY f.id_favorito, f.id_usuario, f.id_producto, f.fecha_agregado,
                p.nombre, p.descripcion, p.precio, p.categoria, p.imagen
       ORDER BY f.fecha_agregado DESC`,
      [id_usuario]
    );

    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener favoritos:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Agregar producto a favoritos
router.post('/', async (req, res) => {
  try {
    const { id_usuario, id_producto } = req.body;

    // Verificar si ya existe
    const existe = await pool.query(
      'SELECT 1 FROM favoritos WHERE id_usuario = $1 AND id_producto = $2',
      [id_usuario, id_producto]
    );

    if (existe.rows.length > 0) {
      return res.status(400).json({ 
        error: true, 
        mensaje: 'El producto ya está en favoritos' 
      });
    }

    const result = await pool.query(
      `INSERT INTO favoritos (id_usuario, id_producto, fecha_agregado)
       VALUES ($1, $2, NOW())
       RETURNING *`,
      [id_usuario, id_producto]
    );

    res.json({ 
      success: true, 
      mensaje: 'Producto agregado a favoritos',
      favorito: result.rows[0]
    });
  } catch (error) {
    console.error('Error al agregar favorito:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Eliminar producto de favoritos
router.delete('/:id_usuario/:id_producto', async (req, res) => {
  try {
    const { id_usuario, id_producto } = req.params;

    await pool.query(
      'DELETE FROM favoritos WHERE id_usuario = $1 AND id_producto = $2',
      [id_usuario, id_producto]
    );

    res.json({ 
      success: true, 
      mensaje: 'Producto eliminado de favoritos' 
    });
  } catch (error) {
    console.error('Error al eliminar favorito:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Verificar si un producto está en favoritos
router.get('/verificar/:id_usuario/:id_producto', async (req, res) => {
  try {
    const { id_usuario, id_producto } = req.params;

    const result = await pool.query(
      'SELECT 1 FROM favoritos WHERE id_usuario = $1 AND id_producto = $2',
      [id_usuario, id_producto]
    );

    res.json({ esFavorito: result.rows.length > 0 });
  } catch (error) {
    console.error('Error al verificar favorito:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

export default router;
