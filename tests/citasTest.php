<?php
// Fer - 5 tests (con BD real PostgreSQL)
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/citas.php'; 

/**
 * Pruebas unitarias/integradas para las funciones de citas:
 *  - obtener_doctores_por_especialidad()
 *  - registrar_cita()
 */
final class citasTest extends TestCase
{
    /** @var PDO Conexión a la base de datos */
    private PDO $pdo;

    // ===============================================
    // PASO 1: PREPARACIÓN GLOBAL POR CADA TEST
    // ===============================================
    protected function setUp(): void
    {
        // Cargamos la conexión REAL desde conexion.php.
        // IMPORTANTE: conexion.php debe hacer "return $pdo;"
        /** @var PDO $pdo */
        $pdo = require __DIR__ . '/../src/modules/php/conexion.php';

        if (!$pdo instanceof PDO) {
            throw new RuntimeException('conexion.php no devolvió un PDO válido');
        }

        $this->pdo = $pdo;

        // Configuramos el PDO para que lance excepciones y no emule prepares
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        // Cada test se ejecuta dentro de una transacción.
        $this->pdo->beginTransaction();
    }

    // ===============================================
    // LIMPIEZA GLOBAL AL FINAL DE CADA TEST
    // ===============================================
   protected function tearDown(): void
    {
        // Si la transacción sigue abierta, la revertimos.
        // Así la BD queda igual que antes del test.
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    /**
     * Devuelve un id_especialidad válido de la tabla real "especialidad".
     * Si no hay registros, lanza excepción para que sepas que debes
     * crear al menos una especialidad en la BD antes de ejecutar los tests.
     */
    private function obtenerIdEspecialidadValido(): int
    {
        $id = $this->pdo->query("
            SELECT id_especialidad
            FROM especialidad
            ORDER BY id_especialidad ASC
            LIMIT 1
        ")->fetchColumn();

        if ($id === false) {
            throw new RuntimeException(
                'No hay registros en la tabla especialidad. ' .
                'Crea al menos una especialidad en la BD antes de ejecutar citasTest.'
            );
        }

        return (int)$id;
    }

    /**
     * Devuelve un id_usuario válido de la tabla real "usuario".
     * Si no hay registros, lanza excepción para configurar datos base.
     */
    private function obtenerIdUsuarioValido(): int
    {
        $id = $this->pdo->query("
            SELECT id_usuario
            FROM usuario
            ORDER BY id_usuario ASC
            LIMIT 1
        ")->fetchColumn();

        if ($id === false) {
            throw new RuntimeException(
                'No hay registros en la tabla usuario. ' .
                'Crea al menos un usuario en la BD antes de ejecutar citasTest.'
            );
        }

        return (int)$id;
    }

    // ===============================================
    // TEST 1: OBTENER DOCTORES POR ESPECIALIDAD
    // ===============================================
    public function testObtenerDoctoresPorEspecialidad(): void
    {
        // ===============================================
        // PASO 1: PREPARACIÓN DEL ESCENARIO
        // ===============================================
        $idEspecialidad = $this->obtenerIdEspecialidadValido();

        // Insertamos doctores de prueba para ESA especialidad.
        // Estos inserts solo viven dentro de la transacción del test.
        $this->pdo->exec("
            INSERT INTO doctores (nombre, id_especialidad)
            VALUES 
                ('Dr. A Test Citas', {$idEspecialidad}),
                ('Dr. B Test Citas', {$idEspecialidad})
        ");

        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        $result = obtener_doctores_por_especialidad($this->pdo, $idEspecialidad);

        // ===============================================
        // FASE 3: VERIFICACIÓN
        // ===============================================
        // Debe devolver al menos los 2 doctores que acabamos de insertar.
        $this->assertGreaterThanOrEqual(2, count($result), 'Se esperaban al menos 2 doctores');

        // Extraemos solo los nombres para facilitar la verificación
        $nombres = array_column($result, 'nombre');

        $this->assertContains('Dr. A Test Citas', $nombres);
        $this->assertContains('Dr. B Test Citas', $nombres);
    }

    // ===============================================
    // TEST 2: CAMPOS FALTANTES AL REGISTRAR CITA
    // ===============================================
    public function testRegistrarCitaFallaPorCamposFaltantes(): void
    {
        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        // Se espera InvalidArgumentException por faltar campos obligatorios
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Faltan campos requeridos.');

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Falta especialidadNombre, doctor, fecha y horario.
        registrar_cita($this->pdo, [
            'propietario' => '',
            'id_usuario' => 1
        ]);
    }

    // ===============================================
    // TEST 3: ERROR CUANDO EL DOCTOR NO EXISTE
    // ===============================================
    public function testRegistrarCitaDoctorNoExiste(): void
    {
        // ===============================================
        // PASO 1: PREPARACIÓN DEL ESCENARIO
        // ===============================================
        $idUsuario = $this->obtenerIdUsuarioValido();

        // Usamos un nombre MUY poco probable en la BD real
        $nombreDoctorInexistente = 'Dr. Fantasma Test Unico 999999';

        $existe = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM doctores 
            WHERE nombre = :nom
        ");
        $existe->execute([':nom' => $nombreDoctorInexistente]);
        if ((int)$existe->fetchColumn() > 0) {
            $this->markTestSkipped("El nombre de doctor de prueba ya existe en la BD real.");
        }

        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Doctor no encontrado.');

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        registrar_cita($this->pdo, [
            'propietario'        => 'Juan',
            'especialidadNombre' => 'Gatos',
            'doctor'             => $nombreDoctorInexistente,
            'id_usuario'         => $idUsuario,
            'fecha'              => '2026-01-01',
            'horario'            => '10:00'
        ]);
    }

    // ===============================================
    // TEST 4: HORARIO OCUPADO PARA EL MISMO DOCTOR
    // ===============================================
    public function testRegistrarCitaHorarioOcupado(): void
    {
        // ===============================================
        // PASO 1: PREPARACIÓN DEL ESCENARIO
        // ===============================================
        $idEspecialidad = $this->obtenerIdEspecialidadValido();
        $idUsuario      = $this->obtenerIdUsuarioValido();

        // Creamos un doctor de prueba
        $nombreDoctor = 'Dr. Horario Test Citas';

        $stmt = $this->pdo->prepare("
            INSERT INTO doctores (nombre, id_especialidad)
            VALUES (:nombre, :id_especialidad)
        ");
        $stmt->execute([
            ':nombre'          => $nombreDoctor,
            ':id_especialidad' => $idEspecialidad
        ]);

        // Obtenemos su id_doctores
        $idDoctor = (int)$this->pdo->query("
            SELECT id_doctores
            FROM doctores
            WHERE nombre = '{$nombreDoctor}'
            ORDER BY id_doctores DESC
            LIMIT 1
        ")->fetchColumn();

        // Insertamos UNA cita previa en la misma fecha y horario
        $stmt = $this->pdo->prepare("
            INSERT INTO cita (propietario, servicio, doctor, id_usuario, id_doctor, fecha, horario)
            VALUES (:propietario, :servicio, :doctor, :id_usuario, :id_doctor, :fecha, :horario)
        ");
        $stmt->execute([
            ':propietario' => 'Ana',
            ':servicio'    => 'Perros Test',
            ':doctor'      => $nombreDoctor,
            ':id_usuario'  => $idUsuario,
            ':id_doctor'   => $idDoctor,
            ':fecha'       => '2024-01-01',
            ':horario'     => '10:00'
        ]);

        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('El doctor ya tiene una cita en ese horario.');

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Intentamos registrar otra cita con el mismo doctor, fecha y horario.
        registrar_cita($this->pdo, [
            'propietario'        => 'Luis',
            'especialidadNombre' => 'Perros Test',
            'doctor'             => $nombreDoctor,
            'id_usuario'         => $idUsuario,
            'fecha'              => '2024-01-01',
            'horario'            => '10:00'
        ]);
    }

    // ===============================================
    // TEST 5: REGISTRO EXITOSO DE CITA
    // ===============================================
    public function testRegistrarCitaExito(): void
    {
        // ===============================================
        // PASO 1: PREPARACIÓN DEL ESCENARIO
        // ===============================================
        $idEspecialidad = $this->obtenerIdEspecialidadValido();
        $idUsuario      = $this->obtenerIdUsuarioValido();

        // Creamos un doctor de prueba nuevo para este test
        $nombreDoctor = 'Dr. Exito Test Citas';

        $stmt = $this->pdo->prepare("
            INSERT INTO doctores (nombre, id_especialidad)
            VALUES (:nombre, :id_especialidad)
        ");
        $stmt->execute([
            ':nombre'          => $nombreDoctor,
            ':id_especialidad' => $idEspecialidad
        ]);

        // No le creamos citas previas, así que su horario está libre.

        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        $resp = registrar_cita($this->pdo, [
            'propietario'        => 'Maria',
            'especialidadNombre' => 'Aves Test',
            'doctor'             => $nombreDoctor,
            'id_usuario'         => $idUsuario,
            'fecha'              => '2028-01-02',
            'horario'            => '09:00'
        ]);

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        $this->assertSame('success', $resp['estado']);
        $this->assertGreaterThan(0, $resp['id_cita'], 'El id_cita debe ser mayor a 0');

        // Opcional: verificar que la cita realmente exista en la tabla cita
        $idCita = (int)$resp['id_cita'];

        $cita = $this->pdo->query("
            SELECT *
            FROM cita
            WHERE id_cita = {$idCita}
        ")->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($cita, 'No se encontró la cita recién registrada');
        $this->assertSame('Maria', $cita['propietario']);
        $this->assertSame($nombreDoctor, $cita['doctor']);
    }
}
