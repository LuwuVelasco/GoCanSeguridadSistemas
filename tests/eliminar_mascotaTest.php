<?php

use PHPUnit\Framework\TestCase;

// Definimos constante para que el script principal no ejecute headers ni echo
define('TESTING_MODE', true);

// Incluimos las funciones refactorizadas
require_once __DIR__ . '/../src/modules/php/eliminar_mascota.php';

class eliminar_mascotaTest extends TestCase
{
    /**
     * TEST 1: id_mascota faltante
     */
    public function testIdMascotaFaltante()
    {
        // Creamos un mock de PDO (no se usará en esta validación)
        $pdo = $this->createMock(PDO::class);

        // Input vacío simula que no se envió id_mascota
        $input = [];

        // Ejecutamos la función bajo prueba
        $result = procesarSolicitudEliminarMascota($pdo, $input);

        // Verificamos que el estado sea 'error'
        $this->assertEquals('error', $result['estado']);
        // Verificamos que el mensaje sea el esperado
        $this->assertEquals('ID de mascota no válido', $result['mensaje']);
    }

    /**
     * TEST 2: id_mascota inválido (no numérico)
     */
    public function testIdMascotaInvalido()
    {
        $pdo = $this->createMock(PDO::class);

        // Input con id_mascota no numérico
        $input = ['id_mascota' => 'abc'];

        $result = procesarSolicitudEliminarMascota($pdo, $input);

        $this->assertEquals('error', $result['estado']);
        $this->assertEquals('ID de mascota no válido', $result['mensaje']);
    }

    /**
     * TEST 3: Mascota eliminada exitosamente
     */
    public function testMascotaEliminada()
    {
        // Creamos mock del statement
        $stmtMock = $this->createMock(PDOStatement::class);

        // Simulamos DELETE exitoso → rowCount = 1
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('rowCount')->willReturn(1);

        // Mock del PDO que retorna nuestro statement
        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmtMock);

        // Llamamos la función principal
        $result = eliminarMascotaPorId($pdo, 10);

        // Verificamos estado success
        $this->assertEquals('success', $result['estado']);
        // Verificamos mensaje
        $this->assertEquals('Mascota eliminada exitosamente', $result['mensaje']);
    }

    /**
     * TEST 4: Mascota no encontrada (rowCount = 0)
     */
    public function testMascotaNoEncontrada()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('rowCount')->willReturn(0);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmtMock);

        $result = eliminarMascotaPorId($pdo, 999);

        $this->assertEquals('error', $result['estado']);
        $this->assertEquals('No se encontró la mascota (o ya fue eliminada)', $result['mensaje']);
    }

    /**
     * TEST 5: Error del servidor / excepción PDO
     */
    public function testErrorPDO()
    {
        $pdo = $this->createMock(PDO::class);

        // Simula que prepare lanza una excepción
        $pdo->method('prepare')->willThrowException(new PDOException("Error en BD"));

        $result = eliminarMascotaPorId($pdo, 5);

        $this->assertEquals('error', $result['estado']);
        $this->assertEquals('Error al eliminar la mascota', $result['mensaje']);
    }
}