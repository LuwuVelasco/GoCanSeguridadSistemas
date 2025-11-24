<?php
// Dp - 5 tests para registrar_mascota
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/registrar_mascota.php';

final class registrar_mascotaTest extends TestCase
{
    private PDO $pdo;

    // 1. Preparación
    protected function setUp(): void
    {
        // BD solo para pruebas (en memoria)
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Tabla usuario
        $this->pdo->exec("
            CREATE TABLE usuario (
                id_usuario INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL
            );
        ");

        // Tabla mascota
        $this->pdo->exec("
            CREATE TABLE mascota (
                id_mascota INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre_mascota   TEXT NOT NULL,
                fecha_nacimiento TEXT NOT NULL,
                tipo             TEXT NOT NULL,
                raza             TEXT NOT NULL,
                id_usuario       INTEGER NOT NULL
            );
        ");
    }

    public function testTodosLosCamposVaciosLanzaExcepcion(): void
    {
        // 2. Lógica
        // 3. Verificación
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Todos los campos son obligatorios');

        registrar_mascota($this->pdo, '', '', '', '', '');
    }

    public function testFormatoDeFechaInvalidoLanzaExcepcion(): void
    {
        // 1. Preparación
        $this->pdo->exec("INSERT INTO usuario (nombre) VALUES ('Juan Pérez')");

        // 2. Lógica
        // 3. Verificación
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Formato de fecha inválido (YYYY-MM-DD)');

        registrar_mascota(
            $this->pdo,
            'Firulais',
            '01-01-2024',   // formato incorrecto
            'Perro',
            'Mestizo',
            'Juan Pérez'
        );
    }

    public function testPropietarioNoExistenteLanzaExcepcion(): void
    {
        // 2. Lógica
        // 3. Verificación
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('El propietario no existe');

        registrar_mascota(
            $this->pdo,
            'Firulais',
            '2024-01-01',
            'Perro',
            'Mestizo',
            'Propietario Inexistente'
        );
    }

    public function testRegistroValidoDevuelveSuccessYGuardaMascota(): void
    {
        // 1. Preparación
        $this->pdo->exec("INSERT INTO usuario (nombre) VALUES ('Juan Pérez')");
        $idUsuario = (int)$this->pdo->lastInsertId();

        // 2. Lógica
        $resp = registrar_mascota(
            $this->pdo,
            'Firulais',
            '2024-01-01',
            'Perro',
            'Mestizo',
            'Juan Pérez'
        );

        // 3. Verificación
        $this->assertSame('success', $resp['estado']);
        $this->assertSame('Mascota registrada exitosamente', $resp['mensaje']);
        $this->assertArrayHasKey('id_mascota', $resp);

        $idMascota = (int)$resp['id_mascota'];

        $row = $this->pdo->query("
            SELECT * 
            FROM mascota 
            WHERE id_mascota = $idMascota
        ")->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($row, 'No se encontró la mascota recién registrada.');
        $this->assertEquals('Firulais',      $row['nombre_mascota']);
        $this->assertEquals('2024-01-01',    $row['fecha_nacimiento']);
        $this->assertEquals('Perro',         $row['tipo']);
        $this->assertEquals('Mestizo',       $row['raza']);
        $this->assertEquals($idUsuario, (int)$row['id_usuario']);
    }

    public function testSePuedenRegistrarMultiplesMascotasParaMismoPropietario(): void
    {
        // 1. Preparación
        $this->pdo->exec("INSERT INTO usuario (nombre) VALUES ('Juan Pérez')");

        // 2. Lógica
        registrar_mascota(
            $this->pdo,
            'Firulais',
            '2024-01-01',
            'Perro',
            'Mestizo',
            'Juan Pérez'
        );
        registrar_mascota(
            $this->pdo,
            'Mishi',
            '2023-05-10',
            'Gato',
            'Criollo',
            'Juan Pérez'
        );

        // 3. Verificación
        $total = (int)$this->pdo->query("SELECT COUNT(*) FROM mascota")->fetchColumn();
        $this->assertEquals(2, $total, 'No se registraron las 2 mascotas esperadas.');
    }
}
