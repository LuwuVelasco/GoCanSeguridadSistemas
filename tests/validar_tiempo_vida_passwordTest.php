<?php
// Dp - 5 tests para validar_tiempo_vida_password
declare(strict_types=1);

use PHPUnit\Framework\TestCase; // Importa la clase base TestCase de PHPUnit para crear pruebas unitarias.

// función a probar
require_once __DIR__ . '/../src/modules/php/validar_tiempo_vida_password.php';

// Definimos la clase de pruebas para validar_tiempo_vida_password.
// Extiende TestCase, lo que permite a PHPUnit ejecutarla como suite de tests.
final class validar_tiempo_vida_passwordTest extends TestCase
{
    // Atributo para contener la conexión PDO a la base de datos.
    private PDO $pdo;

    // ================================
    // 1. PREPARACIÓN: conexión a la BD
    // ================================
    // ejecuta antes de cada test
    protected function setUp(): void
    {
        // Usamos la misma conexión que el sistema en producción.
        require __DIR__ . '/../src/modules/php/conexion.php';

        // Asignamos ese PDO
        $this->pdo = $pdo;

        //PDO lanza excepciones cuando ocurre un error SQL.
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // ===========================
    // TEST 1: id_usuario inválido
    // ===========================
    // caso en que se pasa un id_usuario <= 0 y debe lanzar InvalidArgumentException.
    public function testIdUsuarioInvalidoLanzaExcepcion(): void
    {
        // 2. Lógica: definimos lo que esperamos que pase.
        // Esperamos que se lance una excepción InvalidArgumentException.
        $this->expectException(InvalidArgumentException::class);
        // mensaje sea exactamente 'id_usuario inválido'.
        $this->expectExceptionMessage('id_usuario inválido');

        // 3. Verificación:
        // Llamamos a la función con id_usuario = 0 (inválido).
        validar_tiempo_vida_password($this->pdo, 0);
    }

    // ===============================================================
    // TEST 2: sin configuración (tabla configuracion_passwords vacía)
    // ===============================================================
    // no hay configuración de contraseñas en la BD.
    public function testSinConfiguracionLanzaExcepcion(): void
    {
        // Iniciamos una transacción para NO dejar cambios permanentes en la BD.
        $this->pdo->beginTransaction();

        try {
            // 1. Preparación:
            // Primero vaciamos historial_passwords.
            $this->pdo->exec('DELETE FROM historial_passwords');
            // Luego vaciamos configuracion_passwords para simular que no hay configuración.
            $this->pdo->exec('DELETE FROM configuracion_passwords');

            // 2. Lógica esperada:
            // Esperamos que al no encontrar configuración se lance una RuntimeException…
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('No se encontró configuración de contraseñas');

            // 3. Verificación:
            // Llamamos a la función con un usuario válido (1), pero sin configuración en la BD.
            validar_tiempo_vida_password($this->pdo, 1);

        } finally {
            // nos aseguramos de revertir la transacción
            // para que la base de datos no quede modificada por el test.
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
        }
    }

    // ============================================
    // TEST 3: sin historial activo para el usuario
    // ============================================
    // Prueba el caso en que SÍ hay configuración de contraseñas
    // pero el usuario no tiene ninguna contraseña activa en historial_passwords.
    public function testSinHistorialActivoLanzaExcepcion(): void
    {
        // Iniciamos transacción para aislar los cambios de este test.
        $this->pdo->beginTransaction();

        try {
            // 1. Preparación:

            // Limpiamos ambas tablas relevantes dentro de la transacción.
            $this->pdo->exec('DELETE FROM historial_passwords');
            $this->pdo->exec('DELETE FROM configuracion_passwords');

            // Insertamos UNA configuración de contraseñas válida.
            // Esta configuración será la que validará la función.
            $this->pdo->exec("
                INSERT INTO configuracion_passwords (
                    id_configuracion,
                    tiempo_vida_util,
                    numero_historico,
                    fecha_configuracion
                ) VALUES (
                    1,          -- id_configuracion
                    30,         -- tiempo_vida_util en días
                    5,          -- numero_historico (cantidad de contraseñas previas que se guardan)
                    NOW()       -- fecha_configuracion: fecha/hora actual del servidor
                )
            ");

            // no insertamos ninguna fila en historial_passwords.
            // Esto simula el caso donde el usuario no tiene una contraseña activa.

            // 2. Lógica esperada:
            // En estas condiciones, la función debe lanzar RuntimeException…
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('No se encontró una contraseña activa para el usuario');

            // 3. Verificación:
            // Llamamos a la función para el usuario id=1.
            // Al no encontrar contraseña activa, se disparará la excepción de arriba.
            validar_tiempo_vida_password($this->pdo, 1);

        } finally {
            // Revertimos los cambios de la transacción.
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
        }
    }

    // ===========================================================
    // TEST 4: contraseña vigente (dentro del tiempo de vida útil)
    // ===========================================================
    // Prueba el escenario en que la contraseña todavía está dentro del rango de validez.
    public function testPasswordVigenteDevuelveSuccess(): void
    {
        // Transacción para no ensuciar la BD.
        $this->pdo->beginTransaction();

        try {
            // 1. Preparación:

            // Limpiamos las tablas afectadas para tener un escenario controlado.
            $this->pdo->exec('DELETE FROM historial_passwords');
            $this->pdo->exec('DELETE FROM configuracion_passwords');

            // Insertamos configuración con tiempo_vida_util = 30 días.
            $this->pdo->exec("
                INSERT INTO configuracion_passwords (
                    id_configuracion,
                    tiempo_vida_util,
                    numero_historico,
                    fecha_configuracion
                ) VALUES (
                    1,                 -- id_configuracion
                    30,                -- tiempo de vida en días
                    5,                 -- numero_historico
                    '2024-01-01 00:00:00' -- fecha_configuracion fija para el test
                )
            ");

            // Insertamos una contraseña ACTIVA para el usuario 1.
            //  estado (TRUE = activa).
            $this->pdo->exec("
                INSERT INTO historial_passwords (
                    id_usuario,
                    password,
                    fecha_creacion,
                    id_configuracion,
                    estado
                ) VALUES (
                    1,                      -- id_usuario
                    'ClaveTemporal123!',    -- password
                    '2024-01-01 00:00:00',  -- fecha_creacion de la contraseña
                    1,                      -- id_configuracion
                    TRUE                    -- estado = activa *******
                )
            ");

            // Definimos un "ahora" dentro de la ventana de 30 días:
            // del 2024-01-01 al 2024-01-31. Elegimos 2024-01-15 (aún vigente).
            $ahora = new DateTimeImmutable(
                '2024-01-15 00:00:00',
                new DateTimeZone('America/La_Paz')
            );

            // 2. Lógica:
            // Llamamos a la función con ese "ahora".
            $resp = validar_tiempo_vida_password($this->pdo, 1, $ahora);

            // 3. Verificación:
            // Esperamos que el estado sea 'success'.
            $this->assertSame('success', $resp['estado']);
            $this->assertSame('La contraseña sigue siendo válida', $resp['mensaje']);

        } finally {
            // Revertimos la transacción para que no queden datos de prueba.
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
        }
    }

    // ============================================================
    // TEST 5: contraseña expirada
    // ============================================================
    // Prueba el escenario contrario: la contraseña ya superó el tiempo de vida útil
    // y por tanto debería marcarse como expirada.
    public function testPasswordExpiradaDevuelveError(): void
    {
        // usamos una transacción para aislar el test.
        $this->pdo->beginTransaction();

        try {
            // 1. Preparación:

            // Limpiamos tablas relevantes dentro de la transacción.
            $this->pdo->exec('DELETE FROM historial_passwords');
            $this->pdo->exec('DELETE FROM configuracion_passwords');

            // Insertamos la misma configuración que en el test anterior:
            // vida útil = 30 días.
            $this->pdo->exec("
                INSERT INTO configuracion_passwords (
                    id_configuracion,
                    tiempo_vida_util,
                    numero_historico,
                    fecha_configuracion
                ) VALUES (
                    1,
                    30,
                    5,
                    '2024-01-01 00:00:00'
                )
            ");

            // Insertamos una contraseña activa creada el 2024-01-01.
            $this->pdo->exec("
                INSERT INTO historial_passwords (
                    id_usuario,
                    password,
                    fecha_creacion,
                    id_configuracion,
                    estado
                ) VALUES (
                    1,
                    'ClaveTemporal123!',
                    '2024-01-01 00:00:00',
                    1,
                    TRUE
                )
            ");

            // Definimos un "ahora" que esté mucho más allá de los 30 días.
            // Elegimos 2024-02-15 → 45 días después de la creación.
            $ahora = new DateTimeImmutable(
                '2024-02-15 00:00:00',
                new DateTimeZone('America/La_Paz')
            );

            // 2. Lógica:
            // Llamamos a la función con ese "ahora".
            $resp = validar_tiempo_vida_password($this->pdo, 1, $ahora);

            // 3. Verificación:
            // En este caso, esperamos un estado 'error' porque la contraseña ya caducó.
            $this->assertSame('error', $resp['estado']);
            $this->assertSame('La contraseña ha expirado', $resp['mensaje']);

        } finally {
            // Rollback para no dejar registros de prueba.
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
        }
    }
}
