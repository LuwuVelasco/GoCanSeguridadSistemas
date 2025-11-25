<?php
// DOM - 5 tests
use PHPUnit\Framework\TestCase;

define('TESTING_MODE', true);
require_once __DIR__ . '/../src/modules/php/obtener_usuario.php';

class obtener_usuarioTest extends TestCase
{
    /** 1) id_usuario faltante */
    public function testIdUsuarioFaltante()
    {
        $pdo = $this->createMock(PDO::class);
        
        $result = procesarSolicitudUsuario($pdo, []);

        $this->assertEquals('error', $result['estado']);
        $this->assertEquals('No se encontró un id_usuario válido', $result['mensaje']);
    }

    /** 2) id_usuario no numérico */
    public function testIdUsuarioInvalido()
    {
        $pdo = $this->createMock(PDO::class);
        
        $result = procesarSolicitudUsuario($pdo, ['id_usuario' => 'abc']);

        $this->assertEquals('error', $result['estado']);
        $this->assertEquals('No se encontró un id_usuario válido', $result['mensaje']);
    }

    /** 3) Usuario encontrado */
    public function testUsuarioEncontrado()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('fetch')->willReturn(['nombre' => 'Dominic']);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmtMock);

        $result = obtenerUsuarioPorId($pdo, 10);

        $this->assertEquals('success', $result['estado']);
        $this->assertEquals('Dominic', $result['nombre']);
    }

    /** 4) Usuario no encontrado */
    public function testUsuarioNoEncontrado()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('fetch')->willReturn(false);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmtMock);

        $result = obtenerUsuarioPorId($pdo, 20);

        $this->assertEquals('error', $result['estado']);
        $this->assertEquals('Usuario no encontrado', $result['mensaje']);
    }

    /** 5) Error interno del servidor */
    public function testErrorServidor()
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willThrowException(new Exception("DB error"));

        $result = obtenerUsuarioPorId($pdo, 99);

        $this->assertEquals('error', $result['estado']);
        $this->assertEquals('Error del servidor', $result['mensaje']);
    }
}