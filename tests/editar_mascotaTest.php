<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/editar_mascota.php';

final class editar_mascotaTest extends TestCase
{
    private PDO $pdo;

    // 1. Preparación
    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE usuario (
                id_usuario INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL
            );
        ");

        $this->pdo->exec("
            CREATE TABLE mascota (
                id_mascota INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre_mascota TEXT NOT NULL,
                fecha_nacimiento TEXT NOT NULL,
                tipo TEXT NOT NULL,
                raza TEXT NOT NULL,
                id_usuario INTEGER NOT NULL,
                FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
            );
        ");

        $this->pdo->exec("
            CREATE TABLE log_aplicacion (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                id_usuario INTEGER,
                nombre_usuario TEXT,
                accion TEXT,
                descripcion TEXT,
                funcion_afectada TEXT,
                dato_modificado TEXT,
                valor_original TEXT,
                fecha_hora TEXT
            );
        ");

        $this->pdo->exec("
            INSERT INTO usuario (id_usuario, nombre) VALUES (1, 'Juan Perez');
        ");

        $this->pdo->exec("
            INSERT INTO mascota (id_mascota, nombre_mascota, fecha_nacimiento, tipo, raza, id_usuario)
            VALUES (1, 'Firulais', '2020-01-15', 'Perro', 'Labrador', 1);
        ");
    }

    public function testCamposObligatoriosFaltantesDaError(): void
    {
        // 2. Lógica y 3. Verificación
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Todos los campos son obligatorios');

        editar_mascota($this->pdo, 1, '', '2020-01-15', 'Perro', 'Labrador', 'Juan Perez');
    }

    public function testFechaNacimientoInvalidaDaError(): void
    {
        // 2. Lógica y 3. Verificación
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('La fecha de nacimiento no tiene un formato válido (YYYY-MM-DD)');

        editar_mascota($this->pdo, 1, 'Max', '15-01-2020', 'Perro', 'Labrador', 'Juan Perez');
    }

    public function testPropietarioNoExisteDaError(): void
    {
        // 2. Lógica y 3. Verificación
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('El propietario no existe');

        editar_mascota($this->pdo, 1, 'Max', '2020-01-15', 'Perro', 'Labrador', 'Pedro Inexistente');
    }

    public function testSinCambiosDevuelveMensajeExito(): void
    {
        // 2. Lógica - Enviar los mismos datos que ya existen
        $resp = editar_mascota($this->pdo, 1, 'Firulais', '2020-01-15', 'Perro', 'Labrador', 'Juan Perez');

        // 3. Verificación
        $this->assertSame('success', $resp['estado']);
        $this->assertSame('No hubo cambios', $resp['mensaje']);
    }

    public function testActualizacionExitosaDeMascota(): void
    {
        // 2. Lógica
        $resp = editar_mascota($this->pdo, 1, 'Max', '2021-05-10', 'Gato', 'Persa', 'Juan Perez', 1, 'Tester');

        // 3. Verificación
        $this->assertSame('success', $resp['estado']);
        $this->assertSame('Mascota actualizada', $resp['mensaje']);

        // Verificar que se actualizó en la BD
        $mascota = $this->pdo->query("SELECT * FROM mascota WHERE id_mascota = 1")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('Max', $mascota['nombre_mascota']);
        $this->assertEquals('2021-05-10', $mascota['fecha_nacimiento']);
        $this->assertEquals('Gato', $mascota['tipo']);
        $this->assertEquals('Persa', $mascota['raza']);
    }
}