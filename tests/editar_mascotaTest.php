<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/editar_mascota.php';
require_once __DIR__ . '/../src/modules/php/conexion.php';

final class editar_mascotaTest extends TestCase
{
    private PDO $pdo;

    // 1. PREPARACIÓN
    protected function setUp(): void
    {
        $this->pdo = require __DIR__ . '/../src/modules/php/conexion.php';
        $this->pdo->exec("
            DELETE FROM mascota 
            WHERE id_mascota = 1
        ");
        $stmt = $this->pdo->prepare("
            SELECT id_usuario 
            FROM usuario 
            WHERE id_usuario = 1
        ");
        $stmt->execute();

        if (!$stmt->fetch()) {
            $this->pdo->exec("
                INSERT INTO usuario (id_usuario, nombre)
                VALUES (1, 'Juan Perez')
            ");
        }
        $this->pdo->exec("
            INSERT INTO mascota 
            (id_mascota, nombre_mascota, fecha_nacimiento, tipo, raza, id_usuario)
            VALUES 
            (1, 'Firulais', '2020-01-15', 'Perro', 'Labrador', 1)
        ");
        $this->pdo->exec("
            DELETE FROM log_aplicacion 
            WHERE accion = 'editar_mascota'
        ");
    }

    public function testCamposObligatoriosFaltantesDaError(): void
    {
        // 2. LÓGICA y 3. VERIFICACIÓN
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Todos los campos son obligatorios');

        editar_mascota(
            $this->pdo,
            1,
            '',
            '2020-01-15',
            'Perro',
            'Labrador',
            'Juan Perez'
        );
    }

    public function testFechaNacimientoInvalidaDaError(): void
    {
        // 2. LÓGICA y 3. VERIFICACIÓN
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('La fecha de nacimiento no tiene un formato válido (YYYY-MM-DD)');

        editar_mascota(
            $this->pdo,
            1,
            'Max',
            '15-01-2020',
            'Perro',
            'Labrador',
            'Juan Perez'
        );
    }

    public function testPropietarioNoExisteDaError(): void
    {
        // 2. LÓGICA y 3. VERIFICACIÓN
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('El propietario no existe');

        editar_mascota(
            $this->pdo,
            1,
            'Max',
            '2020-01-15',
            'Perro',
            'Labrador',
            'Pedro Inexistente'
        );
    }

    public function testSinCambiosDevuelveMensajeExito(): void
    {
        // 2. LÓGICA 
        $resp = editar_mascota(
            $this->pdo,
            1,
            'Firulais',
            '2020-01-15',
            'Perro',
            'Labrador',
            'Juan Perez'
        );

        // 3. VERIFICACIÓN
        $this->assertSame('success', $resp['estado']);
        $this->assertSame('Mascota actualizada', $resp['mensaje']);
    }

    public function testActualizacionExitosaDeMascota(): void
    {
        // 2. LÓGICA
        $resp = editar_mascota(
            $this->pdo,
            1,
            'Max',
            '2021-05-10',
            'Gato',
            'Persa',
            'Juan Perez',
            1,
            'Tester'
        );

        // 3. VERIFICACIÓN
        $this->assertSame('success', $resp['estado']);
        $this->assertSame('Mascota actualizada', $resp['mensaje']);

        // Verificar que sí se actualizaron los datos
        $mascota = $this->pdo
            ->query("SELECT * FROM mascota WHERE id_mascota = 1")
            ->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($mascota);
        $this->assertEquals('Max', $mascota['nombre_mascota']);
        $this->assertEquals('2021-05-10', $mascota['fecha_nacimiento']);
        $this->assertEquals('Gato', $mascota['tipo']);
        $this->assertEquals('Persa', $mascota['raza']);
    }
}
