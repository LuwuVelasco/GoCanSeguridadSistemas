<?php
// Dp - 5 tests para validar_tiempo_vida_password
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/validar_tiempo_vida_password.php';

final class validar_tiempo_vida_passwordTest extends TestCase
{
    private PDO $pdo;

    // 1. Preparación
    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE configuracion_passwords (
                id_configuracion  INTEGER PRIMARY KEY,
                tiempo_vida_util  INTEGER NOT NULL
            );
        ");

        $this->pdo->exec("
            CREATE TABLE historial_passwords (
                id_historial    INTEGER PRIMARY KEY AUTOINCREMENT,
                id_usuario      INTEGER NOT NULL,
                password_hash   TEXT,
                fecha_creacion  TEXT NOT NULL,
                estado          INTEGER NOT NULL
            );
        ");
    }

    public function testIdUsuarioInvalidoLanzaExcepcion(): void
    {
        // 2. Lógica
        // 3. Verificación
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('id_usuario inválido');

        validar_tiempo_vida_password($this->pdo, 0);
    }

    public function testSinConfiguracionLanzaExcepcion(): void
    {
        // 2. Lógica
        // 3. Verificación
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No se encontró configuración de contraseñas');

        validar_tiempo_vida_password($this->pdo, 1);
    }

    public function testSinHistorialActivoLanzaExcepcion(): void
    {
        // 1. Preparación
        $this->pdo->exec("
            INSERT INTO configuracion_passwords (id_configuracion, tiempo_vida_util)
            VALUES (1, 30)
        ");

        // 2. Lógica
        // 3. Verificación
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No se encontró una contraseña activa para el usuario');

        validar_tiempo_vida_password($this->pdo, 1);
    }

    public function testPasswordVigenteDevuelveSuccess(): void
    {
        // 1. Preparación
        $this->pdo->exec("
            INSERT INTO configuracion_passwords (id_configuracion, tiempo_vida_util)
            VALUES (1, 30)
        ");

        $this->pdo->exec("
            INSERT INTO historial_passwords (id_usuario, password_hash, fecha_creacion, estado)
            VALUES (1, 'hash', '2024-01-01 00:00:00', 1)
        ");

        $ahora = new DateTimeImmutable('2024-01-15 00:00:00', new DateTimeZone('America/La_Paz'));

        // 2. Lógica
        $resp = validar_tiempo_vida_password($this->pdo, 1, $ahora);

        // 3. Verificación
        $this->assertSame('success', $resp['estado']);
        $this->assertSame('La contraseña sigue siendo válida', $resp['mensaje']);
    }

    public function testPasswordExpiradaDevuelveError(): void
    {
        // 1. Preparación
        $this->pdo->exec("
            INSERT INTO configuracion_passwords (id_configuracion, tiempo_vida_util)
            VALUES (1, 30)
        ");

        $this->pdo->exec("
            INSERT INTO historial_passwords (id_usuario, password_hash, fecha_creacion, estado)
            VALUES (1, 'hash', '2024-01-01 00:00:00', 1)
        ");

        $ahora = new DateTimeImmutable('2024-02-15 00:00:00', new DateTimeZone('America/La_Paz'));

        // 2. Lógica
        $resp = validar_tiempo_vida_password($this->pdo, 1, $ahora);

        // 3. Verificación
        $this->assertSame('error', $resp['estado']);
        $this->assertSame('La contraseña ha expirado', $resp['mensaje']);
    }
}
