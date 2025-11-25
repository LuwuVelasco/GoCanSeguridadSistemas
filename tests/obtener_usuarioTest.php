<?php

use PHPUnit\Framework\TestCase;

define('TESTING_MODE', true);
require_once __DIR__ . '/../src/modules/php/obtener_usuario.php';

class obtener_usuarioTest extends TestCase
{
    /**
     * TEST 1: Verificar que falla cuando no se envía id_usuario
     * 
     * Escenario: Se hace hace una petición sin incluir el campo id_usuario
     * Resultado esperado: Error indicando que falta el id_usuario
     */
    public function testIdUsuarioFaltante()
    {
        // 1. PREPARACIÓN DEL TEST
        // Crear un mock de PDO (objeto falso de base de datos)
        // No necesitamos configurar nada porque la función debe fallar antes de intentar usar la base de datos
        $pdo = $this->createMock(PDO::class);
        
        // Array vacío simula que no llegó ningún parámetro
        $inputVacio = [];
        
        // 2. LÓGICA DEL TEST
        // La función debe detectar que falta id_usuario y retornar error
        $result = procesarSolicitudUsuario($pdo, $inputVacio);
        
        // 3. ASSERT
        // Verificar que el estado sea 'error'
        $this->assertEquals('error', $result['estado']);
        
        // Verificar que el mensaje sea el correcto
        $this->assertEquals('No se encontró un id_usuario válido', $result['mensaje']);
    }

    /**
     * TEST 2: Verificar que falla cuando id_usuario no es numérico
     * 
     * Escenario: Se envía un texto en lugar de un número en el campo de id_usuario
     * Resultado esperado: Error indicando que el id_usuario no es válido
     */
    public function testIdUsuarioInvalido()
    {
        // 1. PREPARACIÓN DEL TEST
        // Mock de PDO (tampoco se usará porque la validación falla antes)
        $pdo = $this->createMock(PDO::class);
        
        // Simular que el usuario envió 'abc' como id_usuario
        $inputInvalido = ['id_usuario' => 'abc'];
        
        // 2. LÓGICA DEL TEST
        // Ejecutar la función con un valor no numérico
        $result = procesarSolicitudUsuario($pdo, $inputInvalido);
        
        // 3. ASSERT
        // Verificar que rechazó el valor no numérico
        $this->assertEquals('error', $result['estado']);
        $this->assertEquals('No se encontró un id_usuario válido', $result['mensaje']);
    }

    /**
     * TEST 3: Verificar que retorna datos cuando encuentra un usuario
     * 
     * Escenario: Buscar un usuario que existe en la base de datos
     * Resultado esperado: Success con el nombre del usuario
     */
    public function testUsuarioEncontrado()
    {
        // 1. PREPARACIÓN DEL TEST
        // PASO 1: Crear mock del PDOStatement
        // Este objeto simula el resultado de $pdo->prepare()
        $stmtMock = $this->createMock(PDOStatement::class);
        
        // Configurar que execute() retorne true (la query se ejecutó bien)
        $stmtMock->method('execute')->willReturn(true);
        
        // Configurar que fetch() retorne un usuario encontrado
        // Simula que la BD devolvió una fila con este nombre
        $stmtMock->method('fetch')->willReturn(['nombre' => 'Dominic']);
        
        // PASO 2: Crear mock del PDO
        $pdo = $this->createMock(PDO::class);
        
        // Configurar que prepare() retorne nuestro statement mock
        $pdo->method('prepare')->willReturn($stmtMock);
        
        // 2. LÓGICA DEL TEST
        // Ejecutar la función buscando el usuario con ID 10
        $result = obtenerUsuarioPorId($pdo, 10);
        
        // 3. ASSERT
        // Verificar que encontró el usuario exitosamente
        $this->assertEquals('success', $result['estado']);
        
        // Verificar que el nombre es el que configuramos en el mock
        $this->assertEquals('Dominic', $result['nombre']);
    }

    /**
     * TEST 4: Verificar que retorna error cuando no encuentra usuario
     * 
     * Escenario: Buscar un ID de usuario que no existe
     * Resultado esperado: Error "Usuario no encontrado"
     */
    public function testUsuarioNoEncontrado()
    {
        // 1. PREPARACIÓN DEL TEST
        
        // Mock del statement
        $stmtMock = $this->createMock(PDOStatement::class);
        
        // execute() retorna true (la query se ejecutó sin errores)
        $stmtMock->method('execute')->willReturn(true);
        
        // fetch() retorna false (no encontró ninguna fila)
        $stmtMock->method('fetch')->willReturn(false);
        
        // Mock del PDO
        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmtMock);
        
        // 2. LÓGICA DEL TEST
        // Buscar un usuario con ID 20 (que no existe según nuestro mock)
        $result = obtenerUsuarioPorId($pdo, 20);
        
        // 3. ASSERT
        // Verificar que retornó error
        $this->assertEquals('error', $result['estado']);
        
        // Verificar que el mensaje indica que no encontró el usuario
        $this->assertEquals('Usuario no encontrado', $result['mensaje']);
    }

    /**
     * TEST 5: Verificar que maneja errores de base de datos
     * 
     * Escenario: La base de datos falla (conexión perdida, tabla no existe, etc.)
     * Resultado esperado: Error genérico "Error del servidor"
     */
    public function testErrorServidor()
    {
        // 1. PREPARACIÓN DEL TEST
        
        // Mock del PDO configurado para lanzar una excepción
        $pdo = $this->createMock(PDO::class);
        
        // Cuando se llame a prepare(), lanzar una excepción, esto simula que algo salió mal en la BD
        $pdo->method('prepare')
            ->willThrowException(new Exception("DB error"));

        // 2. LÓGICA DEL TEST
        // Intentar buscar un usuario, pero el prepare() lanzará excepción
        $result = obtenerUsuarioPorId($pdo, 99);

        // 3. ASSERT

        // Verificar que atrapó el error correctamente
        $this->assertEquals('error', $result['estado']);
        
        // Verificar que NO expone detalles técnicos al usuario, solo un mensaje genérico por seguridad
        $this->assertEquals('Error del servidor', $result['mensaje']);
    }
}