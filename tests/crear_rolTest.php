<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/crear_rol.php';

final class crear_rolTest extends TestCase
{
    private PDO $pdo;

    // 1. Preparación
    protected function setUp(): void
    {
        // BD solo para pruebas
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE roles_y_permisos (
                id_rol INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre_rol TEXT NOT NULL,
                ver_usuarios INTEGER DEFAULT 0,
                crear_usuarios INTEGER DEFAULT 0,
                editar_usuarios INTEGER DEFAULT 0,
                eliminar_usuarios INTEGER DEFAULT 0
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
    }

    public function testNombreRolVacioDaError(): void
    {
        // 2. Lógica y 3. Verificación
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nombre de rol requerido');

        crear_rol($this->pdo, '', [], 1, 'Tester');
    }

    public function testNombreRolNullDaError(): void
    {
        // 2. Lógica y 3. Verificación
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nombre de rol requerido');

        crear_rol($this->pdo, null, [], 1, 'Tester');
    }

    public function testCrearRolSinPermisos(): void
    {
        // 2. Lógica
        $resultado = crear_rol($this->pdo, 'Visitante', [], 1, 'Tester');

        // 3. Verificación
        $this->assertTrue($resultado['success']);
        $this->assertArrayHasKey('id_rol', $resultado);

        // Verificar que se creó en la BD
        $rol = $this->pdo->query("
            SELECT * FROM roles_y_permisos WHERE nombre_rol = 'Visitante'
        ")->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($rol);
        $this->assertEquals('Visitante', $rol['nombre_rol']);
        $this->assertEquals(0, $rol['ver_usuarios']);
        $this->assertEquals(0, $rol['crear_usuarios']);
    }

    public function testCrearRolConPermisos(): void
    {
        // 2. Lógica
        $permisos = [
            ['id_permiso' => 'ver_usuarios', 'habilitado' => true],
            ['id_permiso' => 'crear_usuarios', 'habilitado' => true],
            ['id_permiso' => 'editar_usuarios', 'habilitado' => false]
        ];

        $resultado = crear_rol($this->pdo, 'Editor', $permisos, 1, 'Tester');

        // 3. Verificación
        $this->assertTrue($resultado['success']);

        $rol = $this->pdo->query("
            SELECT * FROM roles_y_permisos WHERE nombre_rol = 'Editor'
        ")->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(1, $rol['ver_usuarios']);
        $this->assertEquals(1, $rol['crear_usuarios']);
        $this->assertEquals(0, $rol['editar_usuarios']);
        $this->assertEquals(0, $rol['eliminar_usuarios']);
    }

    public function testRegistroEnLogAplicacion(): void
    {
        // 2. Lógica
        $permisos = [
            ['id_permiso' => 'ver_usuarios', 'habilitado' => true]
        ];

        crear_rol($this->pdo, 'Moderador', $permisos, 1, 'Tester');

        // 3. Verificación
        $log = $this->pdo->query("
            SELECT * FROM log_aplicacion WHERE accion = 'crear_rol'
        ")->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($log);
        $this->assertEquals(1, $log['id_usuario']);
        $this->assertEquals('Tester', $log['nombre_usuario']);
        $this->assertEquals('crear_rol', $log['accion']);
        $this->assertStringContainsString('Moderador', $log['descripcion']);
    }
}