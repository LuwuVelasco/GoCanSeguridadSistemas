<?php
// Fer - 5 tests 
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/registrar_veterinario.php';  

/**
 * Pruebas unitarias para registrar_veterinario()
 */
final class registrar_veterinarioTest extends TestCase
{
    /** @var PDO Conexión a la base de datos */
    private PDO $pdo;

    // ===============================================
    // PASO 1: PREPARACIÓN GLOBAL POR CADA TEST
    // ===============================================
    protected function setUp(): void
    {
        // Cargamos la conexión desde conexion.php.
        // IMPORTANTE: conexion.php hace "return $pdo;"
        /** @var PDO $pdo */
        $pdo = require __DIR__ . '/../src/modules/php/conexion.php';

        if (!$pdo instanceof PDO) {
            throw new RuntimeException('conexion.php no devolvió un PDO válido');
        }

        $this->pdo = $pdo;

        // Configuramos el PDO para que lance excepciones
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $this->pdo->beginTransaction();
    }

    // ===============================================
    // LIMPIEZA GLOBAL AL FINAL DE CADA TEST
    // ===============================================
    protected function tearDown(): void
    {
        // Si la transacción sigue abierta, la revertimos.
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    /**
     * Helper: obtener un id_especialidad que exista en la tabla especialidad.
     *
     * No borramos ni insertamos nada aquí, solo usamos el primer registro que ya exista.
     * Si no hay ninguno, lanzamos excepción para que al menos se cree una especialidad en la BD antes de correr los tests.
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
                'Crea al menos una especialidad en la BD antes de ejecutar estos tests.'
            );
        }

        return (int)$id;
    }


    // ============================================================
    // TEST 1: CAMPOS VACÍOS → InvalidArgumentException
    // ============================================================
    public function testCamposVaciosLanzanError(): void
    {
        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        // Configuramos lo que esperamos que ocurra:
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Todos los campos son obligatorios');

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Llamamos a la función con todos los campos vacíos / 0.
        registrar_veterinario(
            $this->pdo,
            '',
            '',
            '',
            0,
            0
        );
    }

    // ============================================================
    // TEST 2: SIN CONFIGURACIÓN DE PASSWORDS → RuntimeException
    // ============================================================
    public function testSinConfiguracionPasswordLanzaError(): void
    {
        // ===============================================
        // PASO 1: PREPARACIÓN DEL ESCENARIO
        // ===============================================
        // Queremos simular que NO existe configuración de contraseña.
        // Esto ocurre dentro de la transacción del TEST.
        // Solo limpiamos tablas relacionadas con la configuración:
        //  - historial_passwords 
        //  - configuracion_passwords 
        $this->pdo->exec("DELETE FROM historial_passwords");
        $this->pdo->exec("DELETE FROM configuracion_passwords");

        // Necesitamos un id_especialidad válido porque la función inserta primero en doctores.
        $idEspecialidad = $this->obtenerIdEspecialidadValido();

        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ========================================A=======
        // Esperamos una RuntimeException con el mensaje que maneja la función.
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No se encontró configuración de contraseña');

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Al no existir filas en configuracion_passwords, registrar_veterinario
        // debe lanzar la excepción de "No se encontró configuración de contraseña".
        registrar_veterinario(
            $this->pdo,
            'Dr. House',
            'house@example.com',
            'Secreto123',
            $idEspecialidad,
            2
        );
    }

    // ============================================================
    // TEST 3: REGISTRO EXITOSO INSERTA DOCTOR, USUARIO E HISTORIAL
    // ============================================================
    public function testRegistroExitosoInsertaDoctorUsuarioEHistorial(): void
    {
        // ===============================================
        // PASO 1: PREPARACIÓN DEL ESCENARIO
        // ===============================================
        // Dejamos un entorno controlado SOLO para la configuración de passwords.
        $this->pdo->exec("DELETE FROM historial_passwords");
        $this->pdo->exec("DELETE FROM configuracion_passwords");

        // Insertamos UNA configuración de passwords para este test.
        $this->pdo->exec("
            INSERT INTO configuracion_passwords (tiempo_vida_util, numero_historico, fecha_configuracion)
            VALUES (30, 5, NOW())
        ");

        // Obtenemos el ID que se acaba de generar.
        $idConfigEsperado = (int)$this->pdo->query("
            SELECT id_configuracion
            FROM configuracion_passwords
            ORDER BY id_configuracion DESC
            LIMIT 1
        ")->fetchColumn();

        // Necesitamos una especialidad que exista
        $idEspecialidad = $this->obtenerIdEspecialidadValido();

        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        $resp = registrar_veterinario(
            $this->pdo,
            'Dr. Strange',
            'strange@example.com',
            'ClaveSegura1',
            $idEspecialidad,
            1
        );

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Verificamos que la función respondió con "success"
        $this->assertSame('success', $resp['estado']);
        // Verificamos que usó la configuración que creamos
        $this->assertSame($idConfigEsperado, (int)$resp['id_configuracion']);

        // Usamos el id_usuario que devuelve la función para verificar los datos.
        $idUsuario = (int)$resp['id_usuario'];

        // 1) Verificamos que el usuario exista en la tabla usuario
        $usuario = $this->pdo->query("
            SELECT *
            FROM usuario
            WHERE id_usuario = {$idUsuario}
        ")->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($usuario, 'No se encontró el usuario registrado');

        // 2) Verificamos que el doctor asociado exista
        $idDoctor = (int)$usuario['id_doctores'];

        $doctor = $this->pdo->query("
            SELECT *
            FROM doctores
            WHERE id_doctores = {$idDoctor}
        ")->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($doctor, 'No se encontró el doctor asociado al usuario');

        // 3) Verificamos que exista un historial de password para ese usuario
        //    OJO: en tu BD real no existe la columna id_historial, así que ordenamos por fecha_creacion
        $historial = $this->pdo->query("
            SELECT *
            FROM historial_passwords
            WHERE id_usuario = {$idUsuario}
            ORDER BY fecha_creacion DESC
            LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($historial, 'No se encontró ningún historial de password registrado para ese usuario');
    }

    // ============================================================
    // TEST 4: USA SIEMPRE LA ÚLTIMA CONFIGURACIÓN 
    // ============================================================
    public function testUsaLaUltimaConfiguracionDePasswords(): void
    {
        // ===============================================
        // PASO 1: PREPARACIÓN DEL ESCENARIO
        // ===============================================
        // Limpiamos solo lo referente a configuraciones/historial.
        $this->pdo->exec("DELETE FROM historial_passwords");
        $this->pdo->exec("DELETE FROM configuracion_passwords");

        // Insertamos tres configuraciones con fechas distintas.
        $this->pdo->exec("
            INSERT INTO configuracion_passwords (tiempo_vida_util, numero_historico, fecha_configuracion)
            VALUES 
                (30, 5, '2024-01-01 00:00:00'),
                (60, 6, '2024-02-01 00:00:00'),
                (90, 7, '2024-03-01 00:00:00')
        ");

        // Obtenemos el ID de la ÚLTIMA configuración 
        $idConfigUltima = (int)$this->pdo->query("
            SELECT id_configuracion
            FROM configuracion_passwords
            ORDER BY id_configuracion DESC
            LIMIT 1
        ")->fetchColumn();

        // Especialidad válida
        $idEspecialidad = $this->obtenerIdEspecialidadValido();

        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        $resp = registrar_veterinario(
            $this->pdo,
            'Dr. Who',
            'who@example.com',
            'Tardis123',
            $idEspecialidad,
            2
        );

        // Obtenemos el id_usuario desde la respuesta de la función
        $idUsuario = (int)$resp['id_usuario'];

        // Consultamos qué configuración quedó registrada en historial_passwords.
        $idConfigEnHistorial = (int)$this->pdo->query("
            SELECT id_configuracion
            FROM historial_passwords
            WHERE id_usuario = {$idUsuario}
            ORDER BY fecha_creacion DESC
            LIMIT 1
        ")->fetchColumn();

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // La respuesta de la función debe usar la última configuración
        $this->assertSame($idConfigUltima, (int)$resp['id_configuracion']);
        // El historial también debe referir a esa misma configuración
        $this->assertSame($idConfigUltima, $idConfigEnHistorial);
    }

    // ============================================================
    // TEST 5: PASSWORD SE GUARDA HASHEADO EN USUARIO E HISTORIAL
    // ============================================================
    public function testPasswordSeGuardaHasheadoEnUsuarioEHistorial(): void
    {
        // ===============================================
        // PASO 1: PREPARACIÓN DEL ESCENARIO
        // ===============================================
        // Limpiamos solo la parte de configuraciones/historial para no romper FKs.
        $this->pdo->exec("DELETE FROM historial_passwords");
        $this->pdo->exec("DELETE FROM configuracion_passwords");

        // Creamos una configuración de passwords que se usará en este test.
        $this->pdo->exec("
            INSERT INTO configuracion_passwords (tiempo_vida_util, numero_historico, fecha_configuracion)
            VALUES (45, 3, '2024-05-01 00:00:00')
        ");

        $passwordPlano = 'SuperClave!123';
        $idEspecialidad = $this->obtenerIdEspecialidadValido();

        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        $resp = registrar_veterinario(
            $this->pdo,
            'Dr. Watson',
            'watson@example.com',
            $passwordPlano,
            $idEspecialidad,
            1
        );

        // Obtenemos el último usuario insertado desde la respuesta de la función
        $idUsuario = (int)$resp['id_usuario'];

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // 1) Verificamos el hash almacenado en usuario
        $hashUsuario = $this->pdo->query("
            SELECT password
            FROM usuario
            WHERE id_usuario = {$idUsuario}
        ")->fetchColumn();

        $this->assertIsString($hashUsuario, 'No se encontró el hash de usuario');
        $this->assertTrue(
            password_verify($passwordPlano, $hashUsuario),
            'El password en usuario no está hasheado correctamente'
        );

        // 2) Verificamos el hash almacenado en historial_passwords
        $hashHistorial = $this->pdo->query("
            SELECT password
            FROM historial_passwords
            WHERE id_usuario = {$idUsuario}
            ORDER BY fecha_creacion DESC
            LIMIT 1
        ")->fetchColumn();

        $this->assertIsString($hashHistorial, 'No se encontró el hash en historial_passwords');
        $this->assertTrue(
            password_verify($passwordPlano, $hashHistorial),
            'El password en historial_passwords no está hasheado correctamente'
        );
    }
}
