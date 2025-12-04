<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/editar_mascota.php';
require_once __DIR__ . '/../src/modules/php/conexion.php';

final class editar_mascotaTest extends TestCase
{
    private PDO $pdo;
    private int $testUserId = 9999;
    private int $testMascotaId = 9999;

    // 1. PREPARACIÓN
    protected function setUp(): void
    {
        $this->pdo = require __DIR__ . '/../src/modules/php/conexion.php';
        
        // Limpiar datos de prueba anteriores
        $this->pdo->exec("DELETE FROM mascota WHERE id_mascota = {$this->testMascotaId}");
        $this->pdo->exec("DELETE FROM usuario WHERE id_usuario = {$this->testUserId}");
        $this->pdo->exec("DELETE FROM log_aplicacion WHERE accion = 'editar_mascota' AND descripcion LIKE '%ID {$this->testMascotaId}%'");
        
        // Insertar usuario de prueba
        $this->pdo->exec("
            INSERT INTO usuario (id_usuario, nombre, email)
            VALUES ({$this->testUserId}, 'Juan Perez', 'juan.perez.test@example.com')
        ");
        
        // Insertar mascota de prueba
        $this->pdo->exec("
            INSERT INTO mascota 
            (id_mascota, nombre_mascota, fecha_nacimiento, tipo, raza, id_usuario)
            VALUES 
            ({$this->testMascotaId}, 'Firulais', '2020-01-15', 'Perro', 'Labrador', {$this->testUserId})
        ");
    }

    protected function tearDown(): void
    {
        // Limpiar después de cada test
        $this->pdo->exec("DELETE FROM mascota WHERE id_mascota = {$this->testMascotaId}");
        $this->pdo->exec("DELETE FROM usuario WHERE id_usuario = {$this->testUserId}");
        $this->pdo->exec("DELETE FROM log_aplicacion WHERE accion = 'editar_mascota' AND descripcion LIKE '%ID {$this->testMascotaId}%'");
    }

    public function testCamposObligatoriosFaltantesDaError(): void
    {
        // 2. LÓGICA y 3. VERIFICACIÓN
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Todos los campos son obligatorios');

        editar_mascota(
            $this->pdo,
            $this->testMascotaId,
            '',  // nombre vacío
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
            $this->testMascotaId,
            'Max',
            '15-01-2020',  // formato inválido
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
            $this->testMascotaId,
            'Max',
            '2020-01-15',
            'Perro',
            'Labrador',
            'Pedro Inexistente'  // propietario que no existe
        );
    }

    public function testSinCambiosDevuelveMensajeExito(): void
    {
        // 2. LÓGICA 
        $resp = editar_mascota(
            $this->pdo,
            $this->testMascotaId,
            'Firulais',
            '2020-01-15',
            'Perro',
            'Labrador',
            'Juan Perez'
        );

        // 3. VERIFICACIÓN
        $this->assertSame('success', $resp['estado']);
        $this->assertSame('No hubo cambios', $resp['mensaje']);
    }

    public function testActualizacionExitosaDeMascota(): void
    {
        // 2. LÓGICA
        $resp = editar_mascota(
            $this->pdo,
            $this->testMascotaId,
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
            ->query("SELECT * FROM mascota WHERE id_mascota = {$this->testMascotaId}")
            ->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($mascota);
        $this->assertEquals('Max', $mascota['nombre_mascota']);
        $this->assertEquals('2021-05-10', $mascota['fecha_nacimiento']);
        $this->assertEquals('Gato', $mascota['tipo']);
        $this->assertEquals('Persa', $mascota['raza']);
        
        // Verificar que se registró en el log
        $stmt = $this->pdo->prepare("
            SELECT * FROM log_aplicacion 
            WHERE accion = 'editar_mascota' 
            AND descripcion LIKE :descripcion
        ");
        $stmt->execute([':descripcion' => "%ID {$this->testMascotaId}%"]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertNotFalse($log, 'Debe existir un registro en log_aplicacion');
        $this->assertEquals(1, $log['id_usuario']);
        $this->assertEquals('Tester', $log['nombre_usuario']);
    }
}
