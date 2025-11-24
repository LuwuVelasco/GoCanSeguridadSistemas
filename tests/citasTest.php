<?php
// Fer - 5 tests
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/citas.php';

final class citasTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE doctores (
                id_doctores INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL,
                id_especialidad INTEGER NOT NULL
            );
        ");

        $this->pdo->exec("
            CREATE TABLE cita (
                id_cita INTEGER PRIMARY KEY AUTOINCREMENT,
                propietario TEXT,
                servicio TEXT,
                doctor TEXT,
                id_usuario INTEGER,
                id_doctor INTEGER,
                fecha TEXT,
                horario TEXT
            );
        ");
    }


    public function testObtenerDoctoresPorEspecialidad(): void
    {
        // Preparación
        $this->pdo->exec("
            INSERT INTO doctores (nombre, id_especialidad)
            VALUES ('Dr. A', 1), ('Dr. B', 1), ('Dr. C', 2)
        ");

        // Lógica
        $result = obtener_doctores_por_especialidad($this->pdo, 1);

        // Verificación
        $this->assertCount(2, $result);
        $this->assertSame('Dr. A', $result[0]['nombre']);
    }

    public function testRegistrarCitaFallaPorCamposFaltantes(): void
    {
        // Preparación vacía

        // Lógica + verificación
        $this->expectException(InvalidArgumentException::class);

        registrar_cita($this->pdo, [
            'propietario' => '',
            'id_usuario' => 1
        ]);
    }

    /** 3) Error cuando el doctor no existe */
    public function testRegistrarCitaDoctorNoExiste(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Doctor no encontrado');

        registrar_cita($this->pdo, [
            'propietario' => 'Juan',
            'especialidadNombre' => 'Gatos',
            'doctor' => 'Dr. Fantasma',
            'id_usuario' => 1,
            'fecha' => '2024-01-01',
            'horario' => '10:00'
        ]);
    }

    public function testRegistrarCitaHorarioOcupado(): void
    {
        // Preparación
        $this->pdo->exec("
            INSERT INTO doctores (nombre, id_especialidad)
            VALUES ('Dr. Real', 1)
        ");

        $this->pdo->exec("
            INSERT INTO cita (propietario, servicio, doctor, id_usuario, id_doctor, fecha, horario)
            VALUES ('Ana', 'Perros', 'Dr. Real', 1, 1, '2024-01-01', '10:00')
        ");

        // Lógica + verificación
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("doctor ya tiene una cita");

        registrar_cita($this->pdo, [
            'propietario' => 'Luis',
            'especialidadNombre' => 'Perros',
            'doctor' => 'Dr. Real',
            'id_usuario' => 2,
            'fecha' => '2024-01-01',
            'horario' => '10:00'
        ]);
    }

    public function testRegistrarCitaExito(): void
    {
        // Preparación
        $this->pdo->exec("
            INSERT INTO doctores (nombre, id_especialidad)
            VALUES ('Dr. Bueno', 1)
        ");

        // Lógica
        $resp = registrar_cita($this->pdo, [
            'propietario' => 'Maria',
            'especialidadNombre' => 'Aves',
            'doctor' => 'Dr. Bueno',
            'id_usuario' => 1,
            'fecha' => '2024-01-02',
            'horario' => '09:00'
        ]);

        // Verificación
        $this->assertSame('success', $resp['estado']);
        $this->assertGreaterThan(0, $resp['id_cita']);
    }
}
