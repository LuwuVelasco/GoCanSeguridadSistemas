<?php
// Lu - 5 tests
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/registrar_log_aplicacion.php';

final class registrar_log_aplicacionTest extends TestCase
{
    private PDO $pdo;

    //1. Preparación
    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

    public function testRegistroCorrectoDeLogDeAplicacion(): void
    {
        //2. Lógica
        $resp = registrar_log_aplicacion(
            $this->pdo, 1, "Tester", "update", "desc", "func", "dato", "valor"
        );

        //3. Verificación
        $this->assertSame("success", $resp["estado"]);

        $row = $this->pdo->query("SELECT * FROM log_aplicacion")->fetch();
        $this->assertEquals(1, $row['id_usuario']);
        $this->assertEquals("Tester", $row['nombre_usuario']);
        $this->assertEquals("update", $row['accion']);
    }

    public function testIdUsuarioNoNumericoSeVuelveNull(): void
    {
        //2. Lógica
        registrar_log_aplicacion(
            $this->pdo, "abc", "Tester", "update", "desc", "func", "dato", "valor"
        );

        $row = $this->pdo->query("SELECT * FROM log_aplicacion")->fetch();

        //3. Verificación
        $this->assertNull($row['id_usuario']);
    }

    public function testAccionVaciaLanzaExcepcion(): void
    {
        //2. Lógica
        //3. Verificación
        $this->expectException(InvalidArgumentException::class);
        registrar_log_aplicacion(
            $this->pdo, 1, "Tester", "", "desc", "func", "dato", "valor"
        );
    }

    public function testFechaYHoraEnTiempoRealEsGuardada(): void
    {
        //2. Lógica
        registrar_log_aplicacion(
            $this->pdo, 1, "Tester", "update", "desc", "func", "dato", "valor"
        );

        $row = $this->pdo->query("SELECT fecha_hora FROM log_aplicacion")->fetch();

        //3. Verificación
        $this->assertNotEmpty($row['fecha_hora']);
    }

    public function testDatoModificadoVacioLanzaExcepcion(): void
    {
        //2. Lógica
        //3. Verificación
        $this->expectException(InvalidArgumentException::class);

        registrar_log_aplicacion(
            $this->pdo, 1, "Tester", "update", "Mi descripcion", "func", "", "valor"
        );
    }
}
