-- Script para crear las tablas de favoritos y calificaciones si no existen
-- Ejecutar este script en pgAdmin si las tablas no están en la base de datos

-- Tabla de favoritos
CREATE TABLE IF NOT EXISTS favoritos (
    id_favorito SERIAL PRIMARY KEY,
    id_usuario INTEGER NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    id_producto INTEGER NOT NULL REFERENCES producto(id_producto) ON DELETE CASCADE,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(id_usuario, id_producto)
);

-- Índices para mejorar el rendimiento
CREATE INDEX IF NOT EXISTS idx_favoritos_usuario ON favoritos(id_usuario);
CREATE INDEX IF NOT EXISTS idx_favoritos_producto ON favoritos(id_producto);

-- Tabla de calificaciones (si no existe)
CREATE TABLE IF NOT EXISTS calificacion (
    id_calificacion SERIAL PRIMARY KEY,
    id_usuario INTEGER NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    id_producto INTEGER NOT NULL REFERENCES producto(id_producto) ON DELETE CASCADE,
    puntuacion INTEGER NOT NULL CHECK (puntuacion >= 1 AND puntuacion <= 5),
    fecha_calificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(id_usuario, id_producto)
);

-- Índices para calificaciones
CREATE INDEX IF NOT EXISTS idx_calificacion_usuario ON calificacion(id_usuario);
CREATE INDEX IF NOT EXISTS idx_calificacion_producto ON calificacion(id_producto);

-- Comentarios para documentación
COMMENT ON TABLE favoritos IS 'Almacena los productos favoritos de cada usuario';
COMMENT ON TABLE calificacion IS 'Almacena las calificaciones de productos por usuario';

-- Verificar que las tablas se crearon correctamente
SELECT 'Tabla favoritos creada/verificada' as status
WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'favoritos');

SELECT 'Tabla calificacion creada/verificada' as status
WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'calificacion');
