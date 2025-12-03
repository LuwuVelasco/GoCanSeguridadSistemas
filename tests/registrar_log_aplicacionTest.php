<?php
// Lu - 5 tests
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/registrar_log_aplicacion.php'; // Incluye la función a probar

final class registrar_log_aplicacionTest extends TestCase
{
    private PDO $pdo; // Conexión PDO a la base de datos real

    // ===============================================
    // PASO 1: PREPARACIÓN
    // ===============================================
    protected function setUp(): void
    {
        // Conecta a la base de datos real
        require __DIR__ . '/../src/modules/php/conexion.php'; // Carga la conexión a PostgreSQL
        $this->pdo = $pdo; // Asigna la conexión PDO al atributo de la clase
    }

    /**
     * Prueba que verifica que se registra correctamente un log en la aplicación
     */
    public function testRegistroCorrectoDeLogDeAplicacion(): void
    {
        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        // Llama a la función para registrar un log con datos válidos
        $resp = registrar_log_aplicacion(
            $this->pdo, // Conexión a la base de datos
            1,          // ID del usuario
            "Tester",   // Nombre del usuario
            "update",   // Acción realizada
            "desc",     // Descripción de la acción
            "func",     // Función afectada
            "dato",     // Dato modificado
            "valor"     // Valor original
        );

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Verifica que la respuesta tenga estado 'success'
        $this->assertSame("success", $resp["estado"]);

        // Consulta el último registro insertado en la base de datos
        $row = $this->pdo->query("SELECT * FROM log_aplicacion WHERE nombre_usuario = 'Tester' ORDER BY fecha_hora DESC LIMIT 1")->fetch();
        // Verifica que el id_usuario sea 1
        $this->assertEquals(1, $row['id_usuario']);
        // Verifica que el nombre_usuario sea 'Tester'
        $this->assertEquals("Tester", $row['nombre_usuario']);
        // Verifica que la acción sea 'update'
        $this->assertEquals("update", $row['accion']);

        // Limpia el registro insertado en la base de datos real
        $this->pdo->exec("DELETE FROM log_aplicacion WHERE nombre_usuario = 'Tester'");
    }

    /**
     * Prueba que verifica que un ID de usuario no numérico se convierte a NULL
     */
    public function testIdUsuarioNoNumericoSeVuelveNull(): void
    {
        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        // Llama a la función con un id_usuario no numérico ("abc")
        registrar_log_aplicacion(
            $this->pdo,  // Conexión a la base de datos
            "abc",       // ID de usuario inválido (no numérico)
            "Tester",    // Nombre del usuario
            "update",    // Acción realizada
            "desc",      // Descripción
            "func",      // Función afectada
            "dato",      // Dato modificado
            "valor"      // Valor original
        );

        // Consulta el último registro insertado
        $row = $this->pdo->query("SELECT * FROM log_aplicacion WHERE nombre_usuario = 'Tester' ORDER BY fecha_hora DESC LIMIT 1")->fetch();

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Verifica que el id_usuario sea NULL cuando el valor no es numérico
        $this->assertNull($row['id_usuario']);

        // Limpia el registro insertado en la base de datos real
        $this->pdo->exec("DELETE FROM log_aplicacion WHERE nombre_usuario = 'Tester'");
    }

    /**
     * Prueba que verifica que una acción vacía lanza una excepción
     */
    public function testAccionVaciaLanzaExcepcion(): void
    {
        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        // Se espera que lance una excepción de tipo InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Llama a la función con una acción vacía (inválida)
        registrar_log_aplicacion(
            $this->pdo, // Conexión a la base de datos
            1,          // ID del usuario
            "Tester",   // Nombre del usuario
            "",         // Acción vacía (inválida)
            "desc",     // Descripción
            "func",     // Función afectada
            "dato",     // Dato modificado
            "valor"     // Valor original
        );
    }

    /**
     * Prueba que verifica que la fecha y hora se guarda correctamente en tiempo real
     */
    public function testFechaYHoraEnTiempoRealEsGuardada(): void
    {
        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        // Llama a la función para registrar un log
        registrar_log_aplicacion(
            $this->pdo, // Conexión a la base de datos
            1,          // ID del usuario
            "Tester",   // Nombre del usuario
            "update",   // Acción realizada
            "desc",     // Descripción
            "func",     // Función afectada
            "dato",     // Dato modificado
            "valor"     // Valor original
        );

        // Consulta la fecha_hora del último registro insertado
        $row = $this->pdo->query("SELECT fecha_hora FROM log_aplicacion WHERE nombre_usuario = 'Tester' ORDER BY fecha_hora DESC LIMIT 1")->fetch();

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Verifica que el campo fecha_hora no esté vacío
        $this->assertNotEmpty($row['fecha_hora']);

        // Limpia el registro insertado en la base de datos real
        $this->pdo->exec("DELETE FROM log_aplicacion WHERE nombre_usuario = 'Tester'");
    }

    /**
     * Prueba que verifica que un dato modificado vacío lanza una excepción
     */
    public function testDatoModificadoVacioLanzaExcepcion(): void
    {
        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        // Se espera que lance una excepción de tipo InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Llama a la función con dato_modificado vacío (inválido)
        registrar_log_aplicacion(
            $this->pdo,             // Conexión a la base de datos
            1,                      // ID del usuario
            "Tester",               // Nombre del usuario
            "update",               // Acción realizada
            "Mi descripcion",       // Descripción
            "func",                 // Función afectada
            "",                     // Dato modificado vacío (inválido)
            "valor"                 // Valor original
        );
    }
}
