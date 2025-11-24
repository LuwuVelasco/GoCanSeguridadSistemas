<?php
// Fer - 5 tests
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/registrar_veterinario.php';

final class registrar_veterinarioTest extends TestCase
{
    private PDO $pdo;

    /**
     * 1. PREPARACIÓN
     */
    protected function setUp(): void
    {
        // 1. Preparación
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec("
            CREATE TABLE doctores (
                id_doctores     INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre          TEXT NOT NULL,
                id_especialidad INTEGER NOT NULL
            );
        ");
        $this->pdo->exec("
            CREATE TABLE usuario (
                id_usuario     INTEGER PRIMARY KEY AUTOINCREMENT,
                email          TEXT NOT NULL,
                nombre         TEXT NOT NULL,
                password       TEXT NOT NULL,
                rol_id         INTEGER NOT NULL,
                id_doctores    INTEGER NOT NULL,
                fecha_registro TEXT NOT NULL
            );
        ");
        $this->pdo->exec("
            CREATE TABLE configuracion_passwords (
                id_configuracion   INTEGER PRIMARY KEY,
                tiempo_vida_util   INTEGER,
                numero_historico   INTEGER,
                fecha_configuracion TEXT
            );
        ");
        $this->pdo->exec("
            CREATE TABLE historial_passwords (
                id_historial      INTEGER PRIMARY KEY AUTOINCREMENT,
                id_usuario        INTEGER NOT NULL,
                password          TEXT NOT NULL,
                fecha_creacion    TEXT NOT NULL,
                id_configuracion  INTEGER NOT NULL,
                estado            INTEGER NOT NULL
            );
        ");
    }

    public function testCamposVaciosLanzanError(): void
    {
        // 2. Lógica + 3. Verificación
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Todos los campos son obligatorios');

        registrar_veterinario(
            $this->pdo,
            '',
            '',
            '',
            0,
            0
        );
    }


    public function testSinConfiguracionPasswordLanzaError(): void
    {
        // 2. Lógica + 3. Verificación
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No se encontró configuración de contraseña');

        registrar_veterinario(
            $this->pdo,
            'Dr. House',
            'house@example.com',
            'Secreto123',
            1,
            2
        );
    }


    public function testRegistroExitosoInsertaDoctorUsuarioEHistorial(): void
    {
        // 1. Preparación
        $this->pdo->exec("
        INSERT INTO configuracion_passwords
        (id_configuracion, tiempo_vida_util, numero_historico, fecha_configuracion)
        VALUES (1, 30, 5, '2024-01-01 00:00:00');
    ");

        // 2. Lógica
        $resp = registrar_veterinario(
            $this->pdo,
            'Dr. Strange',
            'strange@example.com',
            'ClaveSegura1',
            3,
            1
        );

        // 3. Verificación
        $this->assertSame('success', $resp['estado']);
        $this->assertSame(1, $resp['id_configuracion']);
        $doctor = $this->pdo->query("SELECT * FROM doctores")->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($doctor);

        $usuario = $this->pdo->query("SELECT * FROM usuario")->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($usuario);

        $historial = $this->pdo->query("SELECT * FROM historial_passwords")->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($historial);
    }



    public function testUsaLaUltimaConfiguracionDePasswords(): void
    {
        // 1. Preparación
        $this->pdo->exec("
        INSERT INTO configuracion_passwords
        (id_configuracion, tiempo_vida_util, numero_historico, fecha_configuracion)
        VALUES
            (1, 30, 5, '2024-01-01 00:00:00'),
            (2, 60, 6, '2024-02-01 00:00:00'),
            (3, 90, 7, '2024-03-01 00:00:00');
    ");

        // 2. Lógica
        $resp = registrar_veterinario(
            $this->pdo,
            'Dr. Who',
            'who@example.com',
            'Tardis123',
            4,
            2
        );

        // 3. Verificación
        $this->assertSame(3, $resp['id_configuracion']);

        $usuario = $this->pdo->query("
        SELECT id_usuario FROM usuario ORDER BY id_usuario DESC LIMIT 1
    ")->fetchColumn();

        $histConfig = $this->pdo->query("
        SELECT id_configuracion
        FROM historial_passwords
        WHERE id_usuario = {$usuario}
    ")->fetchColumn();

        $this->assertSame(3, (int)$histConfig);
    }



    public function testPasswordSeGuardaHasheadoEnUsuarioEHistorial(): void
    {
        // 1. Preparación
        $this->pdo->exec("
        INSERT INTO configuracion_passwords
        (id_configuracion, tiempo_vida_util, numero_historico, fecha_configuracion)
        VALUES (5, 45, 3, '2024-05-01 00:00:00');
    ");

        $passwordPlano = 'SuperClave!123';

        // 2. Lógica
        registrar_veterinario(
            $this->pdo,
            'Dr. Watson',
            'watson@example.com',
            $passwordPlano,
            2,
            1
        );

        $idUsuario = (int)$this->pdo->query("
        SELECT id_usuario FROM usuario ORDER BY id_usuario DESC LIMIT 1
    ")->fetchColumn();

        // 3. Verificación
        $hashUsuario = $this->pdo->query("
        SELECT password FROM usuario WHERE id_usuario = {$idUsuario}
    ")->fetchColumn();

        $this->assertTrue(password_verify($passwordPlano, $hashUsuario));

        $hashHistorial = $this->pdo->query("
        SELECT password FROM historial_passwords WHERE id_usuario = {$idUsuario}
    ")->fetchColumn();

        $this->assertTrue(password_verify($passwordPlano, $hashHistorial));
    }
}
