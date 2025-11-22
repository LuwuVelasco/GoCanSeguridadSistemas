<?php
// Lu - 5 tests
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/actualizar_config_password.php';

final class actualizar_config_passwordTest extends TestCase
{
    private PDO $pdo;

    // 1. Preparación
    protected function setUp(): void
    {
        // BD solo para pruebas
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE configuracion_passwords (
                id_configuracion INTEGER PRIMARY KEY,
                tiempo_vida_util INTEGER,
                numero_historico INTEGER,
                fecha_configuracion TEXT
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

    public function testTiempoDeVidaUtilMenorOIgualACeroDaError(): void
    {
        //2. Lógica
        //3. Verificación
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Los valores deben ser mayores a 0.');

        actualizar_config_password($this->pdo, 0, 1, 1, 'Tester');
    }

    public function testNumeroHistoricoMenorOIgualACeroDaError(): void
    {
        //2. Lógica
        //3. Verificación
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Los valores deben ser mayores a 0.');

        actualizar_config_password($this->pdo, 1, 0, 1, 'Tester');
    }

    public function testFaltanDatosObligatoriosDaError(): void
    {
        //2. Lógica
        //3. Verificación
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Faltan datos obligatorios: tiempo de vida útil y número histórico.');

        actualizar_config_password($this->pdo, null, null, 1, 'Tester');
    }

    public function testDatosValidosSaleMensajeDeExito(): void
    {
        //2. Lógica
        $resp = actualizar_config_password($this->pdo, 30, 5, 1, 'Tester');

        //3. Verificación
        $this->assertSame('success', $resp['estado']);
    }

    public function testActualizacionCuandoYaExisteConfiguracionPrevia(): void
    {
        // 1. Preparación
        $this->pdo->exec("
            INSERT INTO configuracion_passwords 
                (id_configuracion, tiempo_vida_util, numero_historico, fecha_configuracion)
            VALUES (1, 10, 2, '2024-01-01 00:00:00')
        ");

        // 2. Lógica
        actualizar_config_password($this->pdo, 50, 8, 1, 'Tester');

        $row = $this->pdo->query("
            SELECT tiempo_vida_util, numero_historico
            FROM configuracion_passwords
            WHERE id_configuracion = 1
        ")->fetch(PDO::FETCH_ASSOC);

        // 3. Verificación
        $this->assertEquals(50, $row['tiempo_vida_util']);
        $this->assertEquals(8,  $row['numero_historico']);
    }
}