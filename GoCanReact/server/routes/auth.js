import express from 'express';
import bcrypt from 'bcrypt';
import axios from 'axios';
import pool from '../config/database.js';

const router = express.Router();

// Función auxiliar para registrar logs
async function registrarLog(idUsuario, nombreUsuario, accion, descripcion) {
  try {
    await pool.query(
      `INSERT INTO log_usuarios (id_usuario, nombre_usuario, accion, descripcion, fecha_hora)
       VALUES ($1, $2, $3, $4, NOW())`,
      [idUsuario, nombreUsuario, accion, descripcion]
    );
  } catch (error) {
    console.error('Error al registrar log:', error);
  }
}

// Login
router.post('/login', async (req, res) => {
  try {
    const { email, password, 'g-recaptcha-response': captcha } = req.body;

    if (!email || !password) {
      return res.json({ 
        estado: 'error', 
        mensaje: 'El email y la contraseña son obligatorios' 
      });
    }

    // Validar reCAPTCHA
    if (!captcha) {
      const userCheck = await pool.query('SELECT id_usuario, nombre FROM usuario WHERE LOWER(email) = LOWER($1)', [email]);
      if (userCheck.rows.length > 0) {
        await registrarLog(userCheck.rows[0].id_usuario, userCheck.rows[0].nombre, 'captcha_fallido', 'Captcha inválido');
      }
      return res.status(400).json({ estado: 'error', mensaje: 'captcha_fallido' });
    }

    // Validar reCAPTCHA (opcional en desarrollo)
    const isLocal = req.ip === '127.0.0.1' || req.ip === '::1';
    if (!isLocal && captcha) {
      try {
        const secretKey = process.env.RECAPTCHA_SECRET;
        const verifyUrl = `https://www.google.com/recaptcha/api/siteverify?secret=${secretKey}&response=${captcha}`;
        const captchaResponse = await axios.post(verifyUrl);
        
        if (!captchaResponse.data.success) {
          await registrarLog(null, null, 'captcha_fallido', 'Captcha inválido');
          return res.json({ estado: 'error', mensaje: 'captcha_fallido' });
        }
      } catch (error) {
        console.error('Error verificando captcha:', error);
      }
    }

    // Buscar usuario
    const result = await pool.query(
      `SELECT u.id_usuario, u.id_doctores, u.password, u.nombre, u.rol_id, r.nombre_rol AS rol,
              COALESCE(u.intentos_fallidos, 0) AS intentos_fallidos,
              u.bloqueado_hasta, u.password_expira_el, COALESCE(u.requiere_cambio_password, FALSE) AS requiere_cambio_password
         FROM usuario u
         INNER JOIN roles_y_permisos r ON u.rol_id = r.id_rol
        WHERE LOWER(u.email) = LOWER($1)
        LIMIT 1`,
      [email]
    );

    if (result.rows.length === 0) {
      await registrarLog(null, null, 'login_fallido', `Usuario no encontrado: ${email}`);
      return res.json({ 
        estado: 'error', 
        mensaje: 'El email o la contraseña son incorrectos' 
      });
    }

    const user = result.rows[0];

    // Bloqueo por intentos fallidos
    if (user.bloqueado_hasta && new Date(user.bloqueado_hasta) > new Date()) {
      const minutos = Math.ceil((new Date(user.bloqueado_hasta) - new Date()) / 60000);
      return res.json({ estado: 'error', mensaje: `Cuenta bloqueada. Intente en ${minutos} min` });
    }
    
    // Verificar contraseña
    let passwordMatch = false;
    if (user.password.startsWith('$2')) {
      // Password hasheada con bcrypt
      passwordMatch = await bcrypt.compare(password, user.password);
    } else {
      // Password en texto plano (legacy)
      passwordMatch = password === user.password;
    }

    if (!passwordMatch) {
      const nuevosIntentos = (user.intentos_fallidos || 0) + 1;
      let bloqueadoHasta = null;
      let intentosToStore = nuevosIntentos;
      if (nuevosIntentos >= 3) {
        bloqueadoHasta = new Date(Date.now() + 15 * 60 * 1000); // 15 minutos
        intentosToStore = 0; // reset tras bloquear
      }
      await pool.query(
        'UPDATE usuario SET intentos_fallidos = $1, bloqueado_hasta = $2 WHERE id_usuario = $3',
        [intentosToStore, bloqueadoHasta, user.id_usuario]
      );
      if (bloqueadoHasta) {
        await registrarLog(user.id_usuario, user.nombre, 'bloqueo_usuario', 'Bloqueo por demasiados intentos fallidos');
      } else {
        await registrarLog(user.id_usuario, user.nombre, 'login_fallido', `Contraseña incorrecta para ${email}`);
      }
      return res.json({ estado: 'error', mensaje: 'El email o la contraseña son incorrectos' });
    }

    // Resetear intentos al éxito
    await pool.query('UPDATE usuario SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id_usuario = $1', [user.id_usuario]);

    // Verificar si la contraseña ha expirado
    const passwordExpired = user.password_expira_el && new Date(user.password_expira_el) < new Date();
    const requireChange = passwordExpired || user.requiere_cambio_password;

    // Crear sesión
    req.session.user = {
      id: user.id_usuario,
      nombre: user.nombre,
      rol: user.rol,
      rol_id: user.rol_id,
      id_doctores: user.id_doctores,
      requiere_cambio_password: requireChange
    };

    // Registrar inicio de sesión exitoso
    await registrarLog(user.id_usuario, user.nombre, 'login_exitoso', 'Inicio de sesión exitoso');

    // Enviar respuesta exitosa
    return res.json({
      estado: 'success',
      id_usuario: user.id_usuario,
      nombre: user.nombre,
      rol: user.rol,
      rol_id: user.rol_id,
      id_doctores: user.id_doctores,
      require_change: requireChange
    });

  } catch (error) {
    console.error('Error en login:', error);
    res.status(500).json({ 
      estado: 'error', 
      mensaje: 'Error en el servidor' 
    });
  }
});

// Registro
router.post('/registro', async (req, res) => {
  const client = await pool.connect();
  
  try {
    const { email, nombre, password, verified } = req.body;

    if (!verified) {
      return res.json({ estado: 'error', mensaje: 'Token no verificado' });
    }

    if (!email || !nombre || !password) {
      return res.json({ estado: 'error', mensaje: 'Faltan campos requeridos' });
    }

    // Validar email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      return res.json({ estado: 'error', mensaje: 'Email inválido' });
    }

    await client.query('BEGIN');

    // Verificar si el email ya existe
    const checkEmail = await client.query(
      'SELECT 1 FROM usuario WHERE LOWER(email) = LOWER($1) LIMIT 1',
      [email]
    );

    if (checkEmail.rows.length > 0) {
      await client.query('ROLLBACK');
      return res.json({ estado: 'error', mensaje: 'El correo ya está registrado' });
    }

    // Hash de contraseña
    const hashedPassword = await bcrypt.hash(password, 10);

    // Insertar usuario (rol 3 = Cliente)
    const userResult = await client.query(
      `INSERT INTO usuario (email, nombre, password, fecha_registro, rol_id)
       VALUES ($1, $2, $3, NOW(), 3)
       RETURNING id_usuario`,
      [email, nombre, hashedPassword]
    );

    const id_usuario = userResult.rows[0].id_usuario;

    // Obtener configuración de passwords
    const configResult = await client.query(
      'SELECT id_configuracion FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1'
    );

    if (configResult.rows.length === 0) {
      await client.query('ROLLBACK');
      return res.json({ estado: 'error', mensaje: 'No se encontró configuración de contraseña' });
    }

    const id_configuracion = configResult.rows[0].id_configuracion;

    // Insertar en historial de passwords
    await client.query(
      `INSERT INTO historial_passwords (id_usuario, password, fecha_creacion, id_configuracion, estado)
       VALUES ($1, $2, NOW(), $3, TRUE)`,
      [id_usuario, hashedPassword, id_configuracion]
    );

    await client.query('COMMIT');

    res.json({
      estado: 'success',
      mensaje: 'Usuario registrado correctamente',
      id_usuario
    });

  } catch (error) {
    await client.query('ROLLBACK');
    console.error('Error en registro:', error);
    res.status(500).json({ 
      estado: 'error', 
      mensaje: 'Error del servidor al registrar' 
    });
  } finally {
    client.release();
  }
});

// Logout
router.post('/logout', (req, res) => {
  req.session.destroy((err) => {
    if (err) {
      return res.status(500).json({ estado: 'error', mensaje: 'Error al cerrar sesión' });
    }
    res.json({ estado: 'success', mensaje: 'Sesión cerrada correctamente' });
  });
});

// Verificar sesión
router.get('/session', (req, res) => {
  if (req.session.user) {
    res.json({ 
      estado: 'success', 
      usuario: req.session.user,
      require_change: req.session.user.requiere_cambio_password || false
    });
  } else {
    res.json({ estado: 'error', mensaje: 'No hay sesión activa' });
  }
});

// Recuperación de contraseña: solicitar código
router.post('/recover/request', async (req, res) => {
  try {
    const { email } = req.body;
    const u = await pool.query('SELECT id_usuario, nombre FROM usuario WHERE LOWER(email)=LOWER($1)', [email]);
    // Siempre devolver success para no filtrar existencia
    if (u.rows.length === 0) return res.json({ estado: 'success', mensaje: 'codigo_enviado' });

    const id = u.rows[0].id_usuario;
    const code = Math.floor(100000 + Math.random() * 900000).toString();
    const expires = new Date(Date.now() + 15 * 60 * 1000);

    await pool.query(
      `INSERT INTO recuperacion_password (id_usuario, codigo, expira_en, usado)
       VALUES ($1, $2, $3, FALSE)`,
      [id, code, expires]
    );
    await registrarLog(id, u.rows[0].nombre, 'recuperacion_solicitada', 'Se solicitó código de recuperación');
    // En producción: enviar email con el código
    res.json({ estado: 'success', mensaje: 'codigo_enviado' });
  } catch (e) {
    console.error('recover/request', e);
    res.status(500).json({ estado: 'error', mensaje: 'Error solicitando recuperación' });
  }
});

// Recuperación de contraseña: verificar código
router.post('/recover/verify', async (req, res) => {
  try {
    const { email, codigo } = req.body;
    const u = await pool.query('SELECT id_usuario FROM usuario WHERE LOWER(email)=LOWER($1)', [email]);
    if (u.rows.length === 0) return res.json({ estado: 'error', mensaje: 'usuario_no_encontrado' });
    const ver = await pool.query(
      `SELECT id_recuperacion FROM recuperacion_password
        WHERE id_usuario = $1 AND codigo = $2 AND usado = FALSE AND expira_en > NOW()
        ORDER BY id_recuperacion DESC LIMIT 1`,
      [u.rows[0].id_usuario, codigo]
    );
    if (ver.rows.length === 0) return res.json({ estado: 'error', mensaje: 'codigo_invalido' });
    res.json({ estado: 'success', mensaje: 'codigo_valido' });
  } catch (e) {
    console.error('recover/verify', e);
    res.status(500).json({ estado: 'error', mensaje: 'Error verificando código' });
  }
});

// Recuperación de contraseña: restablecer
router.post('/recover/reset', async (req, res) => {
  const client = await pool.connect();
  try {
    const { email, codigo, newPassword } = req.body;
    await client.query('BEGIN');
    const u = await client.query('SELECT id_usuario, nombre FROM usuario WHERE LOWER(email)=LOWER($1)', [email]);
    if (u.rows.length === 0) { await client.query('ROLLBACK'); return res.json({ estado: 'error', mensaje: 'usuario_no_encontrado' }); }
    const id = u.rows[0].id_usuario;
    const ver = await client.query(
      `SELECT id_recuperacion FROM recuperacion_password
        WHERE id_usuario = $1 AND codigo = $2 AND usado = FALSE AND expira_en > NOW()
        ORDER BY id_recuperacion DESC LIMIT 1`,
      [id, codigo]
    );
    if (ver.rows.length === 0) { await client.query('ROLLBACK'); return res.json({ estado: 'error', mensaje: 'codigo_invalido' }); }

    const hashedNew = await bcrypt.hash(newPassword, 10);
    await client.query(
      `UPDATE usuario SET password = $1, requiere_cambio_password = FALSE, password_expira_el = NOW() + interval '90 days' WHERE id_usuario = $2`,
      [hashedNew, id]
    );
    await client.query(
      `INSERT INTO historial_passwords (id_usuario, password, fecha_creacion, id_configuracion, estado)
       SELECT $1, $2, NOW(), id_configuracion, TRUE FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1`,
      [id, hashedNew]
    );
    await client.query('UPDATE recuperacion_password SET usado = TRUE WHERE id_recuperacion = $1', [ver.rows[0].id_recuperacion]);
    await registrarLog(id, u.rows[0].nombre, 'password_actualizada', 'Recuperación de contraseña exitosa');
    await client.query('COMMIT');
    res.json({ estado: 'success', mensaje: 'password_actualizada' });
  } catch (e) {
    await client.query('ROLLBACK');
    console.error('recover/reset', e);
    res.status(500).json({ estado: 'error', mensaje: 'Error al restablecer contraseña' });
  } finally {
    client.release();
  }
});

export default router;
