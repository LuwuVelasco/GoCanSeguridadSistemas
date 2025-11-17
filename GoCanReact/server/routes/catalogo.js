import express from 'express';
import pool from '../config/database.js';

const router = express.Router();

// Obtener todos los productos
router.get('/', async (req, res) => {
  try {
    const result = await pool.query(
      `SELECT p.*, u.nombre as nombre_usuario
       FROM producto p
       LEFT JOIN usuario u ON p.id_usuario = u.id_usuario
       ORDER BY p.nombre`
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener productos:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener producto por ID
router.get('/:id_producto', async (req, res) => {
  try {
    const { id_producto } = req.params;
    const result = await pool.query(
      `SELECT p.*, u.nombre as nombre_usuario
       FROM producto p
       LEFT JOIN usuario u ON p.id_usuario = u.id_usuario
       WHERE p.id_producto = $1`,
      [id_producto]
    );

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Producto no encontrado' });
    }

    res.json(result.rows[0]);
  } catch (error) {
    console.error('Error al obtener producto:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Crear producto
router.post('/', async (req, res) => {
  try {
    const { nombre, descripcion, precio, categoria, id_usuario, imagen } = req.body;

    if (!nombre || !precio || !categoria) {
      return res.json({ error: true, mensaje: 'Faltan campos requeridos' });
    }

    const result = await pool.query(
      `INSERT INTO producto (nombre, descripcion, precio, categoria, id_usuario, imagen)
       VALUES ($1, $2, $3, $4, $5, $6)
       RETURNING id_producto`,
      [nombre, descripcion || null, precio, categoria, id_usuario || null, imagen || null]
    );

    res.json({
      id_producto: result.rows[0].id_producto,
      mensaje: 'Producto creado con éxito'
    });

  } catch (error) {
    console.error('Error al crear producto:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Actualizar producto
router.put('/:id_producto', async (req, res) => {
  try {
    const { id_producto } = req.params;
    const { nombre, descripcion, precio, categoria, imagen } = req.body;

    if (!nombre || !precio || !categoria) {
      return res.json({ error: true, mensaje: 'Faltan campos requeridos' });
    }

    const result = await pool.query(
      `UPDATE producto 
       SET nombre = $1, descripcion = $2, precio = $3, categoria = $4, imagen = $5
       WHERE id_producto = $6
       RETURNING id_producto`,
      [nombre, descripcion || null, precio, categoria, imagen || null, id_producto]
    );

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Producto no encontrado' });
    }

    res.json({ mensaje: 'Producto actualizado correctamente' });
  } catch (error) {
    console.error('Error al actualizar producto:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Eliminar producto
router.delete('/:id_producto', async (req, res) => {
  try {
    const { id_producto } = req.params;
    
    const result = await pool.query(
      'DELETE FROM producto WHERE id_producto = $1 RETURNING id_producto',
      [id_producto]
    );

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Producto no encontrado' });
    }

    res.json({ mensaje: 'Producto eliminado correctamente' });
  } catch (error) {
    console.error('Error al eliminar producto:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

export default router;
