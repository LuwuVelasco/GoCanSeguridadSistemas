<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/actualizar_config_password.php';

final class actualizar_config_passwordTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        // BD solo para pruebas (no toca tu MySQL real)
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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Los valores deben ser mayores a 0.');

        actualizar_config_password($this->pdo, 0, 1, 1, 'Tester');
    }

    public function testNumeroHistoricoMenorOIgualACeroDaError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Los valores deben ser mayores a 0.');

        actualizar_config_password($this->pdo, 1, 0, 1, 'Tester');
    }

    public function testFaltanDatosObligatoriosDaError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Faltan datos obligatorios: tiempo de vida útil y número histórico.');

        actualizar_config_password($this->pdo, null, null, 1, 'Tester');
    }

    public function testDatosValidosSeActualizaConfiguracion(): void
    {
        $resp = actualizar_config_password($this->pdo, 30, 5, 1, 'Tester');

        $this->assertSame('success', $resp['estado']);

        $row = $this->pdo->query("
            SELECT tiempo_vida_util, numero_historico
            FROM configuracion_passwords
            WHERE id_configuracion = 1
        ")->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(30, $row['tiempo_vida_util']);
        $this->assertEquals(5,  $row['numero_historico']);
    }
}