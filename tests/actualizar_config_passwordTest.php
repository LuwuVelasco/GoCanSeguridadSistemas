<?php
// Lu - 5 tests
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/actualizar_config_password.php'; // Incluye la función a probar

final class actualizar_config_passwordTest extends TestCase
{
    private PDO $pdo; // Conexión PDO a la base de datos real

    // ===============================================
    // PASO 1: PREPARACIÓN
    // ===============================================
    protected function setUp(): void
    {
        // Conecta a la base de datos real PostgreSQL (no temporal)
        require __DIR__ . '/../src/modules/php/conexion.php'; // Carga la conexión a PostgreSQL
        $this->pdo = $pdo; // Asigna la conexión PDO al atributo de la clase
    }

    /**
     * Prueba que verifica que el sistema rechaza un tiempo de vida útil menor o igual a cero
     */
    public function testTiempoDeVidaUtilMenorOIgualACeroDaError(): void
    {
        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        // Se espera que lance una excepción de tipo InvalidArgumentException
        $this->expectException(InvalidArgumentException::class); 
        // Se espera que el mensaje de la excepción sea el especificado
        $this->expectExceptionMessage('Los valores deben ser mayores a 0.');

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Llama a la función con tiempo_vida_util = 0 (inválido)
        actualizar_config_password($this->pdo, 0, 1, 1, 'Tester');
    }

    /**
     * Prueba que verifica que el sistema rechaza un número histórico menor o igual a cero
     */
    public function testNumeroHistoricoMenorOIgualACeroDaError(): void
    {
        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        // Se espera que lance una excepción de tipo InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        // Se espera que el mensaje de la excepción sea el especificado
        $this->expectExceptionMessage('Los valores deben ser mayores a 0.');

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Llama a la función con numero_historico = 0 (inválido)
        actualizar_config_password($this->pdo, 1, 0, 1, 'Tester');
    }

    /**
     * Prueba que verifica que el sistema rechaza datos nulos en campos obligatorios
     */
    public function testFaltanDatosObligatoriosDaError(): void
    {
        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        // Se espera que lance una excepción de tipo InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        // Se espera que el mensaje indique que faltan datos obligatorios
        $this->expectExceptionMessage('Faltan datos obligatorios: tiempo de vida útil y número histórico.');

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Llama a la función con valores null (inválidos) en campos obligatorios
        actualizar_config_password($this->pdo, null, null, 1, 'Tester');
    }

    /**
     * Prueba que verifica que con datos válidos se actualiza correctamente la configuración
     */
    public function testDatosValidosSaleMensajeDeExito(): void
    {
        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        // Llama a la función con datos válidos: tiempo_vida_util=30, numero_historico=5
        $resp = actualizar_config_password($this->pdo, 30, 5, 1, 'Tester');

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Verifica que la respuesta tenga estado 'success'
        $this->assertSame('success', $resp['estado']);
        
        // Limpia el registro insertado en la base de datos real
        $this->pdo->exec("DELETE FROM log_aplicacion WHERE nombre_usuario = 'Tester'");
    }

    /**
     * Prueba que verifica que se actualiza correctamente cuando ya existe una configuración previa
     */
    public function testActualizacionCuandoYaExisteConfiguracionPrevia(): void
    {
        // ===============================================
        // PASO 1: PREPARACIÓN
        // ===============================================
        // Guarda los valores originales de la configuración para restaurarlos después
        $original = $this->pdo->query("
            SELECT tiempo_vida_util, numero_historico
            FROM configuracion_passwords
            WHERE id_configuracion = 1
        ")->fetch(PDO::FETCH_ASSOC);

        // Inserta valores de prueba en la base de datos real
        $this->pdo->exec("
            UPDATE configuracion_passwords 
            SET tiempo_vida_util = 10, 
                numero_historico = 2, 
                fecha_configuracion = '2024-01-01 00:00:00'
            WHERE id_configuracion = 1
        ");

        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        // Llama a la función con nuevos valores: tiempo_vida_util=50, numero_historico=8
        actualizar_config_password($this->pdo, 50, 8, 1, 'Tester');

        // Consulta los valores actualizados desde la base de datos
        $row = $this->pdo->query("
            SELECT tiempo_vida_util, numero_historico
            FROM configuracion_passwords
            WHERE id_configuracion = 1
        ")->fetch(PDO::FETCH_ASSOC);

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Verifica que el tiempo_vida_util se haya actualizado a 50
        $this->assertEquals(50, $row['tiempo_vida_util']);
        // Verifica que el numero_historico se haya actualizado a 8
        $this->assertEquals(8, $row['numero_historico']);

        // Restaura los valores originales de la configuración
        if ($original) {
            $this->pdo->exec("
                UPDATE configuracion_passwords 
                SET tiempo_vida_util = {$original['tiempo_vida_util']}, 
                    numero_historico = {$original['numero_historico']}
                WHERE id_configuracion = 1
            ");
        }
        
        // Limpia el log creado durante la prueba
        $this->pdo->exec("DELETE FROM log_aplicacion WHERE nombre_usuario = 'Tester'");
    }
}