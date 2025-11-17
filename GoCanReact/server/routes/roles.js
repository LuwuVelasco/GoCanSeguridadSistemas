import express from 'express';
import pool from '../config/database.js';

const router = express.Router();

// Obtener todos los roles
router.get('/', async (req, res) => {
  try {
    const result = await pool.query(
      'SELECT * FROM roles_y_permisos ORDER BY id_rol'
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener roles:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener rol por ID
router.get('/:id_rol', async (req, res) => {
  try {
    const { id_rol } = req.params;
    const result = await pool.query(
      'SELECT * FROM roles_y_permisos WHERE id_rol = $1',
      [id_rol]
    );

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Rol no encontrado' });
    }

    res.json(result.rows[0]);
  } catch (error) {
    console.error('Error al obtener rol:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener permisos de un usuario
router.get('/usuario/:id_usuario', async (req, res) => {
  try {
    const { id_usuario } = req.params;
    const result = await pool.query(
      `SELECT r.*
       FROM roles_y_permisos r
       INNER JOIN usuario u ON r.id_rol = u.rol_id
       WHERE u.id_usuario = $1`,
      [id_usuario]
    );

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Usuario no encontrado' });
    }

    res.json(result.rows[0]);
  } catch (error) {
    console.error('Error al obtener permisos del usuario:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Crear rol (JSONB permisos) con log
router.post('/', async (req, res) => {
  try {
    const { nombre_rol, permisos } = req.body;
    if (!nombre_rol) return res.json({ error: true, mensaje: 'El nombre del rol es requerido' });

    const result = await pool.query(
      `INSERT INTO roles_y_permisos (nombre_rol, permisos)
       VALUES ($1, COALESCE($2::jsonb, '{}'::jsonb))
       RETURNING id_rol, nombre_rol`,
      [nombre_rol, JSON.stringify(permisos || {})]
    );

    // Log de aplicación
    await pool.query(
      `INSERT INTO log_aplicacion (accion, funcion_afectada, dato_modificado, descripcion)
       VALUES ($1, $2, $3, $4)`,
      ['crear_rol', 'roles_y_permisos', String(result.rows[0].id_rol), `Rol creado: ${result.rows[0].nombre_rol}`]
    );

    res.json({ id_rol: result.rows[0].id_rol, mensaje: 'Rol creado con éxito.' });
  } catch (error) {
    console.error('Error al crear rol:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Actualizar permisos (merge JSONB) con log
router.put('/:id_rol', async (req, res) => {
  try {
    const { id_rol } = req.params;
    const { permisos } = req.body;
    if (!permisos) return res.json({ error: true, mensaje: 'Los permisos son requeridos' });

    // Obtener valor original para log
    const before = await pool.query('SELECT permisos, nombre_rol FROM roles_y_permisos WHERE id_rol = $1', [id_rol]);
    if (before.rows.length === 0) return res.json({ error: true, mensaje: 'Rol no encontrado' });

    const result = await pool.query(
      `UPDATE roles_y_permisos 
          SET permisos = COALESCE(permisos, '{}'::jsonb) || $2::jsonb
        WHERE id_rol = $1
        RETURNING id_rol, nombre_rol, permisos`,
      [id_rol, JSON.stringify(permisos)]
    );

    await pool.query(
      `INSERT INTO log_aplicacion (accion, funcion_afectada, dato_modificado, descripcion, valor_original)
       VALUES ($1, $2, $3, $4, $5)`,
      ['actualizar_permisos_rol', 'roles_y_permisos', String(id_rol), `Actualización de permisos del rol ${result.rows[0].nombre_rol}`, JSON.stringify(before.rows[0].permisos || {})]
    );

    res.json({ mensaje: 'Permisos actualizados con éxito.' });
  } catch (error) {
    console.error('Error al actualizar permisos:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Eliminar rol con log
router.delete('/:id_rol', async (req, res) => {
  try {
    const { id_rol } = req.params;
    
    // Verificar que no haya usuarios con este rol
    const usersCheck = await pool.query(
      'SELECT COUNT(*) as count FROM usuario WHERE rol_id = $1',
      [id_rol]
    );

    if (parseInt(usersCheck.rows[0].count) > 0) {
      return res.json({ 
        error: true, 
        mensaje: 'No se puede eliminar el rol porque hay usuarios asignados' 
      });
    }

    const infoRol = await pool.query('SELECT nombre_rol FROM roles_y_permisos WHERE id_rol = $1', [id_rol]);
    const result = await pool.query('DELETE FROM roles_y_permisos WHERE id_rol = $1 RETURNING id_rol', [id_rol]);

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Rol no encontrado' });
    }

    await pool.query(
      `INSERT INTO log_aplicacion (accion, funcion_afectada, dato_modificado, descripcion)
       VALUES ($1, $2, $3, $4)`,
      ['eliminar_rol', 'roles_y_permisos', String(id_rol), `Rol eliminado: ${infoRol.rows[0]?.nombre_rol || ''} (ID ${id_rol})`]
    );

    res.json({ mensaje: 'Rol eliminado correctamente' });
  } catch (error) {
    console.error('Error al eliminar rol:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

export default router;
