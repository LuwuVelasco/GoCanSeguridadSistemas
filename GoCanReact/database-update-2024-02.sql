-- =====================================================
-- GoCan React - Actualización de Base de Datos (2024-02)
-- Script IDEMPOTENTE - Se puede ejecutar múltiples veces sin errores
-- =====================================================

-- ============== ACTUALIZACIÓN DE TABLAS EXISTENTES ==============

-- Añadir campos faltantes a la tabla usuario si no existen
DO $$
BEGIN
    -- Verificar y agregar campos faltantes
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                  WHERE table_name = 'usuario' AND column_name = 'intentos_fallidos') THEN
        ALTER TABLE usuario ADD COLUMN intentos_fallidos INTEGER DEFAULT 0;
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                  WHERE table_name = 'usuario' AND column_name = 'bloqueado_hasta') THEN
        ALTER TABLE usuario ADD COLUMN bloqueado_hasta TIMESTAMP NULL;
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                  WHERE table_name = 'usuario' AND column_name = 'password_expira_el') THEN
        ALTER TABLE usuario ADD COLUMN password_expira_el TIMESTAMP NULL;
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                  WHERE table_name = 'usuario' AND column_name = 'requiere_cambio_password') THEN
        ALTER TABLE usuario ADD COLUMN requiere_cambio_password BOOLEAN DEFAULT FALSE;
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                  WHERE table_name = 'usuario' AND column_name = 'fecha_ultimo_cambio_password') THEN
        ALTER TABLE usuario ADD COLUMN fecha_ultimo_cambio_password TIMESTAMP NULL;
    END IF;
    
    -- Asegurarse que el campo id_doctores exista y sea una clave foránea
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                  WHERE table_name = 'usuario' AND column_name = 'id_doctores') THEN
        ALTER TABLE usuario ADD COLUMN id_doctores INTEGER REFERENCES doctores(id_doctores);
    END IF;
    
    RAISE NOTICE 'Campos de usuario actualizados correctamente';
EXCEPTION WHEN OTHERS THEN
    RAISE WARNING 'Error al actualizar campos de usuario: %', SQLERRM;
END $$;

-- Crear tabla de configuración si no existe
CREATE TABLE IF NOT EXISTS configuracion (
    id_configuracion SERIAL PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT NOT NULL,
    descripcion TEXT,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar o actualizar configuraciones por defecto
INSERT INTO configuracion (clave, valor, descripcion)
VALUES 
    ('password_expiry_days', '90', 'Días de validez de la contraseña'),
    ('password_history_count', '5', 'Número de contraseñas anteriores a verificar'),
    ('max_login_attempts', '3', 'Intentos máximos de inicio de sesión')
ON CONFLICT (clave) DO UPDATE 
SET valor = EXCLUDED.valor,
    descripcion = EXCLUDED.descripcion,
    fecha_actualizacion = CURRENT_TIMESTAMP;

-- ============== TABLA DE HISTORIAL DE CONTRASEÑAS ==============
CREATE TABLE IF NOT EXISTS historial_contraseñas (
    id_historial SERIAL PRIMARY KEY,
    id_usuario INTEGER NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    password VARCHAR(255) NOT NULL,
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cambiado_por INTEGER REFERENCES usuario(id_usuario)
);

-- Crear índice para búsquedas más rápidas
CREATE INDEX IF NOT EXISTS idx_historial_usuario ON historial_contraseñas(id_usuario);

-- ============== ACTUALIZACIÓN DE TABLA DE CITAS ==============
CREATE TABLE IF NOT EXISTS cita (
    id_cita SERIAL PRIMARY KEY,
    id_usuario INTEGER NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    id_doctor INTEGER REFERENCES doctores(id_doctores),
    fecha_cita DATE NOT NULL,
    hora_cita TIME NOT NULL,
    motivo TEXT,
    estado VARCHAR(50) DEFAULT 'pendiente' CHECK (estado IN ('pendiente', 'confirmada', 'completada', 'cancelada')),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notas TEXT
);

-- Añadir índices para búsquedas rápidas
CREATE INDEX IF NOT EXISTS idx_cita_usuario ON cita(id_usuario);
CREATE INDEX IF NOT EXISTS idx_cita_doctor ON cita(id_doctor);
CREATE INDEX IF NOT EXISTS idx_cita_fecha ON cita(fecha_cita);
CREATE INDEX IF NOT EXISTS idx_cita_estado ON cita(estado);

-- ============== TABLA DE ESPECIALIDADES ==============
CREATE TABLE IF NOT EXISTS especialidad (
    id_especialidad SERIAL PRIMARY KEY,
    nombre_especialidad VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activa BOOLEAN DEFAULT TRUE
);

-- Insertar especialidades comunes si no existen
INSERT INTO especialidad (nombre_especialidad, descripcion)
VALUES 
    ('Medicina General', 'Atención médica general y chequeos de rutina'),
    ('Cirugía', 'Procedimientos quirúrgicos generales'),
    ('Dermatología', 'Enfermedades de la piel'),
    ('Oftalmología', 'Cuidado de los ojos y visión'),
    ('Odontología', 'Cuidado dental y oral')
ON CONFLICT (nombre_especialidad) DO NOTHING;

-- Actualizar la tabla doctores para referenciar especialidades
ALTER TABLE doctores 
    ADD COLUMN IF NOT EXISTS id_especialidad INTEGER REFERENCES especialidad(id_especialidad);

-- ============== TABLA DE MASCOTAS ==============
CREATE TABLE IF NOT EXISTS mascota (
    id_mascota SERIAL PRIMARY KEY,
    id_usuario INTEGER NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    nombre VARCHAR(100) NOT NULL,
    especie VARCHAR(50) NOT NULL,
    raza VARCHAR(100),
    fecha_nacimiento DATE,
    sexo CHAR(1) CHECK (sexo IN ('M', 'H')), -- M: Macho, H: Hembra
    color VARCHAR(50),
    peso DECIMAL(5,2),
    notas TEXT,
    foto_url TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices para búsquedas rápidas
CREATE INDEX IF NOT EXISTS idx_mascota_usuario ON mascota(id_usuario);
CREATE INDEX IF NOT EXISTS idx_mascota_especie ON mascota(especie);

-- ============== TABLA DE REPORTES ==============
CREATE TABLE IF NOT EXISTS reporte (
    id_reporte SERIAL PRIMARY KEY,
    id_mascota INTEGER REFERENCES mascota(id_mascota) ON DELETE SET NULL,
    id_usuario INTEGER NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    id_doctor INTEGER REFERENCES doctores(id_doctores),
    fecha_reporte DATE NOT NULL,
    diagnostico TEXT,
    tratamiento TEXT,
    observaciones TEXT,
    peso_actual DECIMAL(5,2),
    temperatura DECIMAL(4,1),
    frecuencia_cardiaca INTEGER,
    frecuencia_respiratoria INTEGER,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices para búsquedas rápidas
CREATE INDEX IF NOT EXISTS idx_reporte_mascota ON reporte(id_mascota);
CREATE INDEX IF NOT EXISTS idx_reporte_usuario ON reporte(id_usuario);
CREATE INDEX IF NOT EXISTS idx_reporte_fecha ON reporte(fecha_reporte);

-- ============== TABLA DE LOGS ==============
CREATE TABLE IF NOT EXISTS log_auditoria (
    id_log SERIAL PRIMARY KEY,
    id_usuario INTEGER REFERENCES usuario(id_usuario) ON DELETE SET NULL,
    accion VARCHAR(50) NOT NULL,
    tabla_afectada VARCHAR(50),
    id_registro_afectado INTEGER,
    datos_anteriores JSONB,
    datos_nuevos JSONB,
    direccion_ip VARCHAR(45),
    user_agent TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices para búsquedas rápidas
CREATE INDEX IF NOT EXISTS idx_log_usuario ON log_auditoria(id_usuario);
CREATE INDEX IF NOT EXISTS idx_log_accion ON log_auditoria(accion);
CREATE INDEX IF NOT EXISTS idx_log_tabla ON log_auditoria(tabla_afectada);
CREATE INDEX IF NOT EXISTS idx_log_fecha ON log_auditoria(fecha_creacion);

-- ============== FUNCIONES DE AYUDA ==============

-- Función para actualizar automáticamente los timestamps
CREATE OR REPLACE FUNCTION actualizar_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.fecha_actualizacion = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Crear triggers para actualización automática de timestamps
DO $$
BEGIN
    -- Para la tabla cita
    IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'trigger_actualizar_cita_timestamp') THEN
        CREATE TRIGGER trigger_actualizar_cita_timestamp
        BEFORE UPDATE ON cita
        FOR EACH ROW
        EXECUTE FUNCTION actualizar_timestamp();
    END IF;
    
    -- Para la tabla mascota
    IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'trigger_actualizar_mascota_timestamp') THEN
        CREATE TRIGGER trigger_actualizar_mascota_timestamp
        BEFORE UPDATE ON mascota
        FOR EACH ROW
        EXECUTE FUNCTION actualizar_timestamp();
    END IF;
    
    -- Para la tabla reporte
    IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'trigger_actualizar_reporte_timestamp') THEN
        CREATE TRIGGER trigger_actualizar_reporte_timestamp
        BEFORE UPDATE ON reporte
        FOR EACH ROW
        EXECUTE FUNCTION actualizar_timestamp();
    END IF;
    
    RAISE NOTICE 'Triggers de actualización de timestamps configurados correctamente';
EXCEPTION WHEN OTHERS THEN
    RAISE WARNING 'Error al configurar los triggers: %', SQLERRM;
END $$;

-- ============== FUNCIÓN PARA REGISTRAR CAMBIOS DE CONTRASEÑA ==============
CREATE OR REPLACE FUNCTION registrar_cambio_password()
RETURNS TRIGGER AS $$
DECLARE
    max_historial INTEGER;
    oldest_id INTEGER;
    config_id INTEGER;
BEGIN
    -- Obtener la configuración actual
    SELECT id_configuracion, valor::INTEGER INTO config_id, max_historial
    FROM configuracion 
    WHERE clave = 'password_history_count'
    LIMIT 1;
    
    -- Insertar la contraseña anterior en el historial
    INSERT INTO historial_contraseñas (id_usuario, password, fecha_cambio, cambiado_por)
    VALUES (NEW.id_usuario, OLD.password, CURRENT_TIMESTAMP, NEW.id_usuario);
    
    -- Mantener solo el número máximo de registros de historial
    IF max_historial > 0 THEN
        -- Encontrar el ID del registro más antiguo a borrar
        SELECT id_historial INTO oldest_id
        FROM historial_contraseñas
        WHERE id_usuario = NEW.id_usuario
        ORDER BY fecha_cambio DESC
        OFFSET max_historial
        LIMIT 1;
        
        -- Eliminar registros antiguos
        IF oldest_id IS NOT NULL THEN
            DELETE FROM historial_contraseñas 
            WHERE id_usuario = NEW.id_usuario 
            AND id_historial <= oldest_id;
        END IF;
    END IF;
    
    -- Actualizar la fecha del último cambio de contraseña
    NEW.fecha_ultimo_cambio_password = CURRENT_TIMESTAMP;
    
    -- Actualizar la fecha de expiración de la contraseña
    SELECT valor::INTEGER INTO max_historial
    FROM configuracion 
    WHERE clave = 'password_expiry_days'
    LIMIT 1;
    
    NEW.password_expira_el = CURRENT_TIMESTAMP + (COALESCE(max_historial, 90) || ' days')::INTERVAL;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Crear trigger para el cambio de contraseña
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'trigger_registrar_cambio_password') THEN
        CREATE TRIGGER trigger_registrar_cambio_password
        BEFORE UPDATE OF password ON usuario
        FOR EACH ROW
        WHEN (OLD.password IS DISTINCT FROM NEW.password)
        EXECUTE FUNCTION registrar_cambio_password();
        
        RAISE NOTICE 'Trigger de cambio de contraseña configurado correctamente';
    END IF;
EXCEPTION WHEN OTHERS THEN
    RAISE WARNING 'Error al configurar el trigger de cambio de contraseña: %', SQLERRM;
END $$;

-- ============== VISTAS ÚTILES ==============

-- Vista para ver el historial de contraseñas de un usuario
CREATE OR REPLACE VIEW vista_historial_passwords AS
SELECT 
    h.id_historial,
    u.id_usuario,
    u.nombre,
    u.email,
    h.fecha_cambio,
    h.cambiado_por,
    u2.nombre AS cambiado_por_nombre
FROM historial_contraseñas h
JOIN usuario u ON h.id_usuario = u.id_usuario
LEFT JOIN usuario u2 ON h.cambiado_por = u2.id_usuario
ORDER BY h.fecha_cambio DESC;

-- Vista para ver las citas con información detallada
CREATE OR REPLACE VIEW vista_citas_detalladas AS
SELECT 
    c.id_cita,
    c.fecha_cita,
    c.hora_cita,
    c.estado,
    c.notas,
    u.id_usuario,
    u.nombre AS nombre_usuario,
    u.email,
    d.id_doctores,
    d.nombre AS nombre_doctor,
    e.nombre_especialidad,
    m.id_mascota,
    m.nombre AS nombre_mascota,
    m.especie,
    m.raza
FROM cita c
JOIN usuario u ON c.id_usuario = u.id_usuario
LEFT JOIN doctores d ON c.id_doctor = d.id_doctores
LEFT JOIN especialidad e ON d.id_especialidad = e.id_especialidad
LEFT JOIN mascota m ON c.id_usuario = m.id_usuario;

-- Vista para ver reportes con información detallada
CREATE OR REPLACE VIEW vista_reportes_detallados AS
SELECT 
    r.id_reporte,
    r.fecha_reporte,
    r.diagnostico,
    r.tratamiento,
    r.observaciones,
    r.peso_actual,
    r.temperatura,
    r.frecuencia_cardiaca,
    r.frecuencia_respiratoria,
    u.id_usuario,
    u.nombre AS nombre_usuario,
    u.email,
    m.id_mascota,
    m.nombre AS nombre_mascota,
    m.especie,
    m.raza,
    m.fecha_nacimiento,
    d.id_doctores,
    d.nombre AS nombre_doctor,
    e.nombre_especialidad,
    r.fecha_creacion,
    r.fecha_actualizacion
FROM reporte r
JOIN usuario u ON r.id_usuario = u.id_usuario
LEFT JOIN mascota m ON r.id_mascota = m.id_mascota
LEFT JOIN doctores d ON r.id_doctor = d.id_doctores
LEFT JOIN especialidad e ON d.id_especialidad = e.id_especialidad
ORDER BY r.fecha_reporte DESC, r.fecha_creacion DESC;

-- ============== ÍNDICES DE TEXTO COMPLETO PARA BÚSQUEDAS ==============

-- Índice para búsqueda de usuarios
CREATE INDEX IF NOT EXISTS idx_usuario_busqueda ON usuario 
USING GIN (to_tsvector('spanish', 
    COALESCE(nombre, '') || ' ' || 
    COALESCE(email, '') || ' ' || 
    COALESCE(CAST(id_usuario AS TEXT), '')
));

-- Índice para búsqueda de mascotas
CREATE INDEX IF NOT EXISTS idx_mascota_busqueda ON mascota 
USING GIN (to_tsvector('spanish', 
    COALESCE(nombre, '') || ' ' || 
    COALESCE(especie, '') || ' ' || 
    COALESCE(raza, '') || ' ' || 
    COALESCE(CAST(id_mascota AS TEXT), '')
));

-- Índice para búsqueda en reportes
CREATE INDEX IF NOT EXISTS idx_reporte_busqueda ON reporte 
USING GIN (to_tsvector('spanish', 
    COALESCE(diagnostico, '') || ' ' || 
    COALESCE(tratamiento, '') || ' ' || 
    COALESCE(observaciones, '') || ' ' ||
    COALESCE(CAST(id_reporte AS TEXT), '')
));

-- ============== ACTUALIZACIÓN FINALIZADA ==============
DO $$
BEGIN
    RAISE NOTICE 'Actualización de la base de datos completada exitosamente';
    RAISE NOTICE 'Se han aplicado las siguientes mejoras:';
    RAISE NOTICE '- Campos de seguridad para usuarios (bloqueo, expiración de contraseña, etc.)';
    RAISE NOTICE '- Historial de contraseñas con límite configurable';
    RAISE NOTICE '- Índices para búsquedas rápidas en tablas principales';
    RAISE NOTICE '- Vistas detalladas para consultas comunes';
    RAISE NOTICE '- Búsqueda de texto completo en usuarios, mascotas y reportes';
    RAISE NOTICE '- Triggers para actualización automática de timestamps';
    RAISE NOTICE '- Configuración centralizada en la tabla configuracion';
END $$;
