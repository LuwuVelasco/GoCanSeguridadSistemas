import express from 'express';
import pool from '../config/database.js';

const router = express.Router();

// Obtener especialidades
router.get('/especialidades', async (req, res) => {
  try {
    const result = await pool.query(
      'SELECT id_especialidad, nombre_especialidad FROM especialidad ORDER BY nombre_especialidad'
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener especialidades:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener doctores por especialidad
router.get('/doctores/:especialidadId', async (req, res) => {
  try {
    const { especialidadId } = req.params;
    const result = await pool.query(
      'SELECT id_doctores, nombre FROM doctores WHERE id_especialidad = $1 ORDER BY nombre',
      [especialidadId]
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener doctores:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener todos los doctores
router.get('/doctores', async (req, res) => {
  try {
    const result = await pool.query(
      `SELECT d.id_doctores, d.nombre, d.id_especialidad, e.nombre_especialidad
       FROM doctores d
       INNER JOIN especialidad e ON d.id_especialidad = e.id_especialidad
       ORDER BY d.nombre`
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener doctores:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Crear cita
router.post('/', async (req, res) => {
  try {
    const { propietario, especialidadNombre, doctor, id_usuario, fecha, horario } = req.body;

    if (!propietario || !especialidadNombre || !doctor || !id_usuario || !fecha || !horario) {
      return res.json({ error: true, mensaje: 'Faltan campos requeridos' });
    }

    // Obtener ID del doctor
    const doctorResult = await pool.query(
      'SELECT id_doctores FROM doctores WHERE nombre = $1 LIMIT 1',
      [doctor]
    );

    if (doctorResult.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Doctor no encontrado' });
    }

    const id_doctor = doctorResult.rows[0].id_doctores;

    // Verificar choque de horario
    const conflictResult = await pool.query(
      `SELECT COUNT(*)::int AS count
       FROM cita
       WHERE id_doctor = $1 AND fecha = $2 AND horario = $3`,
      [id_doctor, fecha, horario]
    );

    if (conflictResult.rows[0].count > 0) {
      return res.json({ 
        error: true, 
        mensaje: 'El doctor ya tiene una cita en ese horario' 
      });
    }

    // Insertar cita
    const result = await pool.query(
      `INSERT INTO cita (propietario, servicio, doctor, id_usuario, id_doctor, fecha, horario)
       VALUES ($1, $2, $3, $4, $5, $6, $7)
       RETURNING id_cita`,
      [propietario, especialidadNombre, doctor, id_usuario, id_doctor, fecha, horario]
    );

    res.json({
      id_cita: result.rows[0].id_cita,
      mensaje: 'Cita registrada con éxito'
    });

  } catch (error) {
    console.error('Error al crear cita:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener citas de un usuario
router.get('/usuario/:id_usuario', async (req, res) => {
  try {
    const { id_usuario } = req.params;
    const result = await pool.query(
      `SELECT c.*, d.nombre as nombre_doctor, e.nombre_especialidad
       FROM cita c
       LEFT JOIN doctores d ON c.id_doctor = d.id_doctores
       LEFT JOIN especialidad e ON d.id_especialidad = e.id_especialidad
       WHERE c.id_usuario = $1
       ORDER BY c.fecha DESC, c.horario DESC`,
      [id_usuario]
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener citas:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener citas de un doctor
router.get('/doctor/:id_doctor', async (req, res) => {
  try {
    const { id_doctor } = req.params;
    const result = await pool.query(
      `SELECT c.*, u.nombre as nombre_usuario, u.email
       FROM cita c
       LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
       WHERE c.id_doctor = $1
       ORDER BY c.fecha DESC, c.horario DESC`,
      [id_doctor]
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener citas del doctor:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Obtener citas con filtros
router.get('/', async (req, res) => {
  const { 
    fecha_inicio, 
    fecha_fin, 
    estado, 
    id_doctor,
    id_usuario,
    ordenar_por = 'fecha_cita',
    orden = 'DESC'
  } = req.query;

  try {
    let query = `
      SELECT 
        c.*, 
        u.nombre as nombre_usuario, 
        u.email, 
        d.nombre as nombre_doctor,
        e.nombre_especialidad
      FROM cita c
      JOIN usuario u ON c.id_usuario = u.id_usuario
      JOIN doctores d ON c.id_doctor = d.id_doctores
      JOIN especialidad e ON d.id_especialidad = e.id_especialidad
    `;

    const queryParams = [];
    const whereClauses = [];

    // Filtros
    if (fecha_inicio && fecha_fin) {
      queryParams.push(fecha_inicio, fecha_fin);
      whereClauses.push(`c.fecha_cita BETWEEN $${queryParams.length - 1} AND $${queryParams.length}`);
    } else if (fecha_inicio) {
      queryParams.push(fecha_inicio);
      whereClauses.push(`c.fecha_cita >= $${queryParams.length}`);
    } else if (fecha_fin) {
      queryParams.push(fecha_fin);
      whereClauses.push(`c.fecha_cita <= $${queryParams.length}`);
    }

    if (estado) {
      queryParams.push(estado);
      whereClauses.push(`c.estado = $${queryParams.length}`);
    }

    if (id_doctor) {
      queryParams.push(id_doctor);
      whereClauses.push(`c.id_doctor = $${queryParams.length}`);
    }

    if (id_usuario) {
      queryParams.push(id_usuario);
      whereClauses.push(`c.id_usuario = $${queryParams.length}`);
    }

    // Construir consulta final
    if (whereClauses.length > 0) {
      query += ' WHERE ' + whereClauses.join(' AND ');
    }

    // Ordenar
    const ordenValido = ['fecha_cita', 'fecha_creacion', 'estado'].includes(ordenar_por.toLowerCase());
    const direccionValida = ['asc', 'desc'].includes(orden.toLowerCase());
    
    query += ` ORDER BY 
      ${ordenValido ? `c.${ordenar_por}` : 'c.fecha_cita'} 
      ${direccionValida ? orden.toUpperCase() : 'DESC'}, 
      c.hora_cita`;

    const result = await pool.query(query, queryParams);
    res.json(result.rows);
  } catch (error) {
    console.error('Error al obtener todas las citas:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Eliminar cita
router.delete('/:id_cita', async (req, res) => {
  try {
    const { id_cita } = req.params;
    
    const result = await pool.query(
      'DELETE FROM cita WHERE id_cita = $1 RETURNING id_cita',
      [id_cita]
    );

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Cita no encontrada' });
    }

    res.json({ mensaje: 'Cita eliminada correctamente' });
  } catch (error) {
    console.error('Error al eliminar cita:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

// Actualizar cita
router.put('/:id_cita', async (req, res) => {
  try {
    const { id_cita } = req.params;
    const { fecha, horario, doctor } = req.body;

    if (!fecha || !horario) {
      return res.json({ error: true, mensaje: 'Faltan campos requeridos' });
    }

    let id_doctor = null;
    if (doctor) {
      const doctorResult = await pool.query(
        'SELECT id_doctores FROM doctores WHERE nombre = $1 LIMIT 1',
        [doctor]
      );
      if (doctorResult.rows.length > 0) {
        id_doctor = doctorResult.rows[0].id_doctores;
      }
    }

    const result = await pool.query(
      `UPDATE cita 
       SET fecha = $1, horario = $2, doctor = $3, id_doctor = $4
       WHERE id_cita = $5
       RETURNING id_cita`,
      [fecha, horario, doctor, id_doctor, id_cita]
    );

    if (result.rows.length === 0) {
      return res.json({ error: true, mensaje: 'Cita no encontrada' });
    }

    res.json({ mensaje: 'Cita actualizada correctamente' });
  } catch (error) {
    console.error('Error al actualizar cita:', error);
    res.status(500).json({ error: true, mensaje: 'Error del servidor' });
  }
});

export default router;
