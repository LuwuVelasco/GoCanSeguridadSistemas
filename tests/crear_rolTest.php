<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/crear_rol.php';
require_once __DIR__ . '/../src/modules/php/conexion.php';

final class crear_rolTest extends TestCase
{
    private PDO $pdo;

    // 1. PREPARACIÓN
    protected function setUp(): void
    {
        $this->pdo = require __DIR__ . '/../src/modules/php/conexion.php';
        $this->pdo->exec("
            DELETE FROM roles_y_permisos 
            WHERE nombre_rol IN ('Visitante', 'Editor', 'Moderador', 'TEST_ROL')
        ");

        $this->pdo->exec("
            DELETE FROM log_aplicacion
            WHERE accion = 'crear_rol'
        ");
    }

    public function testNombreRolVacioDaError(): void
    {
        // 2. LÓGICA y 3. VERIFICACIÓN
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nombre de rol requerido');

        crear_rol($this->pdo, '', [], 1, 'Tester');
    }

    public function testNombreRolNullDaError(): void
    {
        // 2. LÓGICA y 3. VERIFICACIÓN
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nombre de rol requerido');

        crear_rol($this->pdo, null, [], 1, 'Tester');
    }

    public function testCrearRolSinPermisos(): void
    {
        // 2. LÓGICA
        $resultado = crear_rol($this->pdo, 'Visitante', [], 1, 'Tester');

        // 3. VERIFICACIÓN
        $this->assertTrue($resultado['success']);
        $this->assertArrayHasKey('id_rol', $resultado);

        $rol = $this->pdo->query("
            SELECT * FROM roles_y_permisos 
            WHERE nombre_rol = 'Visitante'
        ")->fetch();

        $this->assertNotFalse($rol);
        $this->assertEquals('Visitante', $rol['nombre_rol']);
    }

    public function testCrearRolConPermisos(): void
    {
        // 2. LÓGICA
        $permisos = [
            ['id_permiso' => 'ver_roles_creados', 'habilitado' => true],
            ['id_permiso' => 'registro_roles', 'habilitado' => true]
        ];

        crear_rol($this->pdo, 'Editor', $permisos, 1, 'Tester');

        // 3. VERIFICACIÓN
        $rol = $this->pdo->query("
            SELECT * 
            FROM roles_y_permisos 
            WHERE nombre_rol = 'Editor'
        ")->fetch();

        $this->assertNotFalse($rol);
        $this->assertEquals(true, $rol['ver_roles_creados']);
        $this->assertEquals(true, $rol['registro_roles']);
    }

    public function testRegistroEnLogAplicacion(): void
    {
        // 2. LÓGICA
        $permisos = [
            ['id_permiso' => 'ver_roles_creados', 'habilitado' => true]
        ];

        crear_rol($this->pdo, 'Moderador', $permisos, 1, 'Tester');

        // 3. VERIFICACIÓN
        $log = $this->pdo->query("
            SELECT * 
            FROM log_aplicacion 
            WHERE accion = 'crear_rol' 
            ORDER BY id_log DESC
            LIMIT 1
        ")->fetch();

        $this->assertNotFalse($log);
        $this->assertEquals('Tester', $log['nombre_usuario']);
        $this->assertEquals('crear_rol', $log['accion']);
        $this->assertStringContainsString('Moderador', $log['descripcion']);
    }
}
