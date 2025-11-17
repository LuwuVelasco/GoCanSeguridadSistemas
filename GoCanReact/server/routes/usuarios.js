import express from 'express';
import bcrypt from 'bcrypt';
import pool from '../config/database.js';

const router = express.Router();

// Obtener todos los usuarios
router.get('/', async (req, res) => {
  try {
    const result = await pool.query(
      `SELECT u.id_usuario, u.email, u.nombre, u.rol_id, u.id_doctores, u.fecha_registro,
              r.nombre_rol, d.nombre as nombre_doctor
       FROM usuario u
       LEFT JOIN roles_y_permisos r ON u.rol_id = r.id_rol
       LEFT JOIN doctores d ON u.id_doctores = d.id_doctores
       ORDER BY u.nombre`
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener usuarios:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener clientes
router.get('/clientes', async (req, res) => {
  try {
    const result = await pool.query(
      `SELECT u.id_usuario, u.email, u.nombre, u.fecha_registro
       FROM usuario u
       WHERE u.rol_id = 3
       ORDER BY u.nombre`
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener clientes:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener funcionarios (doctores y admins)
router.get('/funcionarios', async (req, res) => {
  try {
    const result = await pool.query(
      `SELECT u.id_usuario, u.email, u.nombre, u.rol_id, u.id_doctores, u.fecha_registro,
              r.nombre_rol, d.nombre as nombre_doctor
       FROM usuario u
       LEFT JOIN roles_y_permisos r ON u.rol_id = r.id_rol
       LEFT JOIN doctores d ON u.id_doctores = d.id_doctores
       WHERE u.rol_id IN (1, 2)
       ORDER BY u.nombre`
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener funcionarios:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener usuario por ID
router.get('/:id_usuario', async (req, res) => {
  try {
    const { id_usuario } = req.params;
    const result = await pool.query(
      `SELECT u.id_usuario, u.email, u.nombre, u.rol_id, u.id_doctores, u.fecha_registro,
              r.nombre_rol, d.nombre as nombre_doctor
       FROM usuario u
       LEFT JOIN roles_y_permisos r ON u.rol_id = r.id_rol
       LEFT JOIN doctores d ON u.id_doctores = d.id_doctores
       WHERE u.id_usuario = $1`,
      [id_usuario]
    );

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Usuario no encontrado' });
    }

    res.json(result.rows[0]);
  } catch (error) {
    console.error('Error al obtener usuario:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Registrar funcionario
router.post('/funcionario', async (req, res) => {
  const client = await pool.connect();
  
  try {
    const { nombre, correo, password, rol, especialidad, nombreDoctor } = req.body;

    if (!nombre || !correo || !password || !rol) {
      return res.json({ estado: 'error', mensaje: 'Faltan campos requeridos' });
    }

    await client.query('BEGIN');

    // Si es doctor (rol 2), primero crear el registro en doctores
    let id_doctores = null;
    if (rol === 2 || rol === '2') {
      if (!especialidad || !nombreDoctor) {
        await client.query('ROLLBACK');
        return res.json({ estado: 'error', mensaje: 'Los doctores requieren especialidad y nombre' });
      }

      const doctorResult = await client.query(
        'INSERT INTO doctores (nombre, id_especialidad) VALUES ($1, $2) RETURNING id_doctores',
        [nombreDoctor, especialidad]
      );
      id_doctores = doctorResult.rows[0].id_doctores;
    }

    // Hash de contraseña
    const hashedPassword = await bcrypt.hash(password, 10);

    // Insertar usuario
    const userResult = await client.query(
      `INSERT INTO usuario (email, nombre, password, rol_id, id_doctores, fecha_registro)
       VALUES ($1, $2, $3, $4, $5, NOW())
       RETURNING id_usuario`,
      [correo, nombre, hashedPassword, rol, id_doctores]
    );

    const id_usuario = userResult.rows[0].id_usuario;

    // Obtener configuración de passwords
    const configResult = await client.query(
      'SELECT id_configuracion FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1'
    );

    if (configResult.rows.length > 0) {
      const id_configuracion = configResult.rows[0].id_configuracion;

      // Insertar en historial de passwords
      await client.query(
        `INSERT INTO historial_passwords (id_usuario, password, fecha_creacion, id_configuracion, estado)
         VALUES ($1, $2, NOW(), $3, TRUE)`,
        [id_usuario, hashedPassword, id_configuracion]
      );
    }

    await client.query('COMMIT');

    res.json({
      estado: 'success',
      mensaje: 'Funcionario registrado exitosamente',
      id_usuario,
      id_doctores
    });

  } catch (error) {
    await client.query('ROLLBACK');
    console.error('Error al registrar funcionario:', error);
    res.status(500).json({ 
      estado: 'error', 
      mensaje: 'Error del servidor al registrar funcionario' 
    });
  } finally {
    client.release();
  }
});

// Eliminar usuario
router.delete('/:id_usuario', async (req, res) => {
  try {
    const { id_usuario } = req.params;
    
    const result = await pool.query(
      'DELETE FROM usuario WHERE id_usuario = $1 RETURNING id_usuario',
      [id_usuario]
    );

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Usuario no encontrado' });
    }

    res.json({ mensaje: 'Usuario eliminado correctamente' });
  } catch (error) {
    console.error('Error al eliminar usuario:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener usuario por email
router.get('/email/:email', async (req, res) => {
  try {
    const { email } = req.params;
    const result = await pool.query(
      `SELECT u.id_usuario, u.email, u.nombre, u.rol_id, r.nombre_rol
       FROM usuario u
       LEFT JOIN roles_y_permisos r ON u.rol_id = r.id_rol
       WHERE LOWER(u.email) = LOWER($1)`,
      [email]
    );

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Usuario no encontrado' });
    }

    res.json(result.rows[0]);
  } catch (error) {
    console.error('Error al obtener usuario por email:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

export default router;
