<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '..\src\modules\php\actualizar_config_password.php';

final class actualizar_config_passwordTest extends TestCase{
    
    public function testTiempoDeVidaUtilMenorOIgualACeroDaError(): void{
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Los valores deben ser mayores a 0.');
        actualizar_config_password(0, 1);
    }

    public function testNumeroHistoricoMenorOIgualACeroDaError(): void{
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Los valores deben ser mayores a 0.');
        actualizar_config_password(1, 0);
    }

    public function testFaltanDatosObligatoriosDaError(): void{
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Faltan datos obligatorios: tiempo de vida útil y número histórico.');
        actualizar_config_password(null, null); 
    }

    public function testDatosValidosSeActualizaConfiguracion(): void{
        $this->assertTrue(actualizar_config_password(1, 1));
    }
}
?>