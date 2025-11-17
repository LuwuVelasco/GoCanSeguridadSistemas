-- =====================================================
-- GoCan React - Script SQL COMPLETO (Idempotente)
-- Este script NO rompe la BD existente. Crea/ajusta solo lo necesario
-- Ejecutar en la base de datos existente (gocan)
-- =====================================================

-- ============== TABLAS BASE (si no existieran) ==============
CREATE TABLE IF NOT EXISTS roles_y_permisos (
  id_rol SERIAL PRIMARY KEY,
  nombre_rol VARCHAR(100) UNIQUE NOT NULL,
  permisos JSONB DEFAULT '{}'::jsonb
);

CREATE TABLE IF NOT EXISTS usuario (
  id_usuario SERIAL PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  nombre VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  rol_id INTEGER REFERENCES roles_y_permisos(id_rol),
  fecha_registro TIMESTAMP DEFAULT NOW(),
  -- Campos agregados por nuevas funcionalidades
  intentos_fallidos INTEGER DEFAULT 0,
  bloqueado_hasta TIMESTAMP NULL,
  password_expira_el TIMESTAMP NULL,
  requiere_cambio_password BOOLEAN DEFAULT FALSE,
  id_doctores INTEGER NULL
);

CREATE TABLE IF NOT EXISTS configuracion_passwords (
  id_configuracion SERIAL PRIMARY KEY,
  vida_util_dias INTEGER DEFAULT 90,
  historial_count INTEGER DEFAULT 5,
  fecha_actualizacion TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS historial_passwords (
  id_historial SERIAL PRIMARY KEY,
  id_usuario INTEGER NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  password VARCHAR(255) NOT NULL,
  fecha_creacion TIMESTAMP DEFAULT NOW(),
  id_configuracion INTEGER REFERENCES configuracion_passwords(id_configuracion),
  estado BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS doctores (
  id_doctores SERIAL PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL,
  id_especialidad INTEGER NULL
);

CREATE TABLE IF NOT EXISTS especialidad (
  id_especialidad SERIAL PRIMARY KEY,
  nombre_especialidad VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS cita (
  id_cita SERIAL PRIMARY KEY,
  id_usuario INTEGER NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  id_doctores INTEGER NOT NULL REFERENCES doctores(id_doctores) ON DELETE CASCADE,
  fecha DATE NOT NULL,
  hora TIME NOT NULL,
  estado VARCHAR(50) DEFAULT 'pendiente'
);

CREATE TABLE IF NOT EXISTS mascota (
  id_mascota SERIAL PRIMARY KEY,
  id_usuario INTEGER NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  nombre VARCHAR(255) NOT NULL,
  tipo VARCHAR(50) NOT NULL,
  raza VARCHAR(255),
  fecha_nacimiento DATE
);

CREATE TABLE IF NOT EXISTS reporte (
  id_reporte SERIAL PRIMARY KEY,
  id_usuario INTEGER NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  id_mascota INTEGER NOT NULL REFERENCES mascota(id_mascota) ON DELETE CASCADE,
  sintomas TEXT,
  diagnostico TEXT,
  receta TEXT,
  fecha DATE DEFAULT CURRENT_DATE
);

CREATE TABLE IF NOT EXISTS producto (
  id_producto SERIAL PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL,
  descripcion TEXT,
  categoria VARCHAR(100),
  precio NUMERIC(12,2) DEFAULT 0,
  imagen TEXT
);

-- Logs
CREATE TABLE IF NOT EXISTS log_usuarios (
  id_log SERIAL PRIMARY KEY,
  id_usuario INTEGER NULL REFERENCES usuario(id_usuario) ON DELETE SET NULL,
  nombre_usuario VARCHAR(255),
  accion VARCHAR(100) NOT NULL,
  descripcion TEXT,
  fecha_hora TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS log_aplicacion (
  id_log SERIAL PRIMARY KEY,
  id_usuario INTEGER NULL REFERENCES usuario(id_usuario) ON DELETE SET NULL,
  accion VARCHAR(100) NOT NULL,
  funcion_afectada VARCHAR(100),
  dato_modificado VARCHAR(255),
  descripcion TEXT,
  valor_original TEXT,
  fecha_hora TIMESTAMP DEFAULT NOW()
);

-- ============== TABLAS NUEVAS (Favoritos, Calificaciones, Recuperación) ==============
CREATE TABLE IF NOT EXISTS favoritos (
  id_favorito SERIAL PRIMARY KEY,
  id_usuario INTEGER NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  id_producto INTEGER NOT NULL REFERENCES producto(id_producto) ON DELETE CASCADE,
  fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(id_usuario, id_producto)
);
CREATE INDEX IF NOT EXISTS idx_favoritos_usuario ON favoritos(id_usuario);
CREATE INDEX IF NOT EXISTS idx_favoritos_producto ON favoritos(id_producto);

CREATE TABLE IF NOT EXISTS calificacion (
  id_calificacion SERIAL PRIMARY KEY,
  id_usuario INTEGER NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  id_producto INTEGER NOT NULL REFERENCES producto(id_producto) ON DELETE CASCADE,
  puntuacion INTEGER NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),
  fecha_calificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(id_usuario, id_producto)
);
CREATE INDEX IF NOT EXISTS idx_calificacion_usuario ON calificacion(id_usuario);
CREATE INDEX IF NOT EXISTS idx_calificacion_producto ON calificacion(id_producto);

CREATE TABLE IF NOT EXISTS recuperacion_password (
  id_recuperacion SERIAL PRIMARY KEY,
  id_usuario INTEGER NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  codigo VARCHAR(6) NOT NULL,
  expira_en TIMESTAMP NOT NULL,
  usado BOOLEAN DEFAULT FALSE,
  creado_en TIMESTAMP DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_recuperacion_usuario ON recuperacion_password(id_usuario);

-- ============== ALTERS (solo si faltan) ==============
-- Usuario: campos de seguridad
ALTER TABLE usuario ADD COLUMN IF NOT EXISTS intentos_fallidos INTEGER DEFAULT 0;
ALTER TABLE usuario ADD COLUMN IF NOT EXISTS bloqueado_hasta TIMESTAMP NULL;
ALTER TABLE usuario ADD COLUMN IF NOT EXISTS password_expira_el TIMESTAMP NULL;
ALTER TABLE usuario ADD COLUMN IF NOT EXISTS requiere_cambio_password BOOLEAN DEFAULT FALSE;
ALTER TABLE usuario ADD COLUMN IF NOT EXISTS id_doctores INTEGER NULL;

-- Configuración de contraseñas
ALTER TABLE configuracion_passwords ADD COLUMN IF NOT EXISTS vida_util_dias INTEGER DEFAULT 90;
ALTER TABLE configuracion_passwords ADD COLUMN IF NOT EXISTS historial_count INTEGER DEFAULT 5;
ALTER TABLE configuracion_passwords ADD COLUMN IF NOT EXISTS fecha_actualizacion TIMESTAMP DEFAULT NOW();

-- ============== DATOS SEMILLA (idempotentes) ==============
-- Roles base
INSERT INTO roles_y_permisos (nombre_rol, permisos)
SELECT x.nombre_rol, x.permisos::jsonb
FROM (
  VALUES
    ('Administrador', '{"usuarios":true,"roles":true,"citas":true,"reportes":true,"catalogo":true,"logs":true,"config":true}'),
    ('Doctor', '{"citas":true,"reportes":true,"mascotas":true,"clientes":true}'),
    ('Cliente', '{"citas":true,"mascotas":true,"catalogo":true,"favoritos":true}')
) AS x(nombre_rol, permisos)
WHERE NOT EXISTS (SELECT 1 FROM roles_y_permisos r WHERE r.nombre_rol = x.nombre_rol);

-- Configuración por defecto
INSERT INTO configuracion_passwords (vida_util_dias, historial_count)
SELECT 90, 5
WHERE NOT EXISTS (SELECT 1 FROM configuracion_passwords);

-- Especialidades base
INSERT INTO especialidad (nombre_especialidad)
SELECT x.nombre FROM (VALUES ('Medicina General'),('Dermatología'),('Odontología'),('Cirugía')) AS x(nombre)
WHERE NOT EXISTS (SELECT 1 FROM especialidad);

-- Productos de muestra mínimos (solo si no hay)
INSERT INTO producto (nombre, descripcion, categoria, precio, imagen)
SELECT x.nombre, x.descripcion, x.categoria, x.precio, x.imagen FROM (
  VALUES
    ('Alimento Premium', 'Alimento balanceado para perros', 'Alimentos', 120.00, '/images/productos/alimento-premium.jpg'),
    ('Juguete Pelota', 'Pelota resistente para perro', 'Juguetes', 35.50, '/images/productos/pelota.jpg')
) AS x(nombre, descripcion, categoria, precio, imagen)
WHERE NOT EXISTS (SELECT 1 FROM producto);

-- ============== VISTAS / CHECKS ÚTILES ==============
-- Evitar doble cita mismo doctor/hora
CREATE OR REPLACE VIEW v_conflictos_cita AS
SELECT c1.* FROM cita c1
JOIN cita c2 ON c1.id_doctores = c2.id_doctores AND c1.fecha = c2.fecha AND c1.hora = c2.hora AND c1.id_cita <> c2.id_cita;

-- ============== COMENTARIOS ==============
COMMENT ON TABLE favoritos IS 'Almacena los productos favoritos de cada usuario';
COMMENT ON TABLE calificacion IS 'Calificaciones (1-5) por usuario y producto';
COMMENT ON TABLE recuperacion_password IS 'Códigos temporales para recuperar contraseña';
COMMENT ON COLUMN usuario.intentos_fallidos IS 'Contador de intentos de login fallidos';
COMMENT ON COLUMN usuario.bloqueado_hasta IS 'Fecha/hora hasta la que el usuario está bloqueado';
COMMENT ON COLUMN usuario.password_expira_el IS 'Fecha de expiración de la contraseña';
COMMENT ON COLUMN usuario.requiere_cambio_password IS 'Forzar cambio de contraseña en siguiente login';

-- ============== QUERIES DE VERIFICACIÓN ==============
SELECT 'roles_y_permisos' tabla, COUNT(*) total FROM roles_y_permisos UNION ALL
SELECT 'usuario', COUNT(*) FROM usuario UNION ALL
SELECT 'configuracion_passwords', COUNT(*) FROM configuracion_passwords UNION ALL
SELECT 'historial_passwords', COUNT(*) FROM historial_passwords UNION ALL
SELECT 'doctores', COUNT(*) FROM doctores UNION ALL
SELECT 'especialidad', COUNT(*) FROM especialidad UNION ALL
SELECT 'cita', COUNT(*) FROM cita UNION ALL
SELECT 'mascota', COUNT(*) FROM mascota UNION ALL
SELECT 'reporte', COUNT(*) FROM reporte UNION ALL
SELECT 'producto', COUNT(*) FROM producto UNION ALL
SELECT 'favoritos', COUNT(*) FROM favoritos UNION ALL
SELECT 'calificacion', COUNT(*) FROM calificacion UNION ALL
SELECT 'recuperacion_password', COUNT(*) FROM recuperacion_password UNION ALL
SELECT 'log_usuarios', COUNT(*) FROM log_usuarios UNION ALL
SELECT 'log_aplicacion', COUNT(*) FROM log_aplicacion;
