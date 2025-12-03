<?php
// Dp - 5 tests para registrar_mascota
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// Incluimos la función del php
require_once __DIR__ . '/../src/modules/php/registrar_mascota.php';

final class registrar_mascotaTest extends TestCase
{
    //conexión PDO a la BD real
    private PDO $pdo;

    // ====================================================
    // 1. PREPARACIÓN GLOBAL: se prepara antes de cada test
    // ====================================================
    protected function setUp(): void
    {
        // archivo de conexión para el sistema
        require __DIR__ . '/../src/modules/php/conexion.php'; // define $pdo

        // Asignamos la conexión a nuestro atributo de clase
        $this->pdo = $pdo;

        // PDO configuración:
        // - Lance excepciones en caso de error
        // - No emule prepared statements
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    
    // 1) Todos los campos vacíos → InvalidArgumentException
    // Verifica que la función valide correctamente los campos requeridos.

    public function testTodosLosCamposVaciosLanzaExcepcion(): void
    {
        // ================================
        // 2. Lógica: definimos expectativas
        // ================================
        // lanza una InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        // mensaje indique que todos los campos son obligatorios
        $this->expectExceptionMessage('Todos los campos son obligatorios');

        // =====================================
        // 3. Verificación: invocamos la función
        // =====================================
        // parámetros string se envían vacíos
        registrar_mascota($this->pdo, '', '', '', '', '');
    }


     // 2) Formato de fecha inválido → InvalidArgumentException
     // Verifica que la fecha debe cumplir el formato YYYY-MM-DD.

    public function testFormatoDeFechaInvalidoLanzaExcepcion(): void
    {
        // ================================
        // 2. Lógica: expectativas
        // ================================
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Formato de fecha inválido (YYYY-MM-DD)');

        // ================================
        // 3. Verificación
        // ================================
        // La función validará primero el formato de fecha y lanzará la excepción,
        registrar_mascota(
            $this->pdo,
            'Firulais',   // nombre_mascota válido
            '01-01-2024', // fecha en formato (incorrecto)
            'Perro',      // tipo
            'Mestizo',    // raza
            'Cualquier Nombre' // propietario
        );
    }


     // 3) Propietario no existente → RuntimeException
     // Verifica que se lance error cuando el propietario no está en la tabla usuario.
    
    public function testPropietarioNoExistenteLanzaExcepcion(): void
    {
        // nombre que no existe en la tabla usuario
        $nombreInexistente = 'USUARIO_INEXISTENTE_TEST_' . uniqid();

        // =======================
        // 2. Lógica: expectativas
        // =======================
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('El propietario no existe');

        // ===============
        // 3. Verificación
        // ===============
        // Mascota valida pero propietario no existe
        registrar_mascota(
            $this->pdo,
            'Firulais',           // nombre_mascota
            '2024-01-01',         // fecha_nacimiento válida
            'Perro',              // tipo
            'Mestizo',            // raza
            $nombreInexistente    // propietario que (NO existe)
        );
    }


     // 4) Registro válido → success + mascota guardada
     // Verifica la respuesta y que la mascota se guarda en la BD.

    public function testRegistroValidoDevuelveSuccessYGuardaMascota(): void
    {
        // Iniciamos una transacción para aislar los cambios del test
        $this->pdo->beginTransaction();

        try {
            // ================================
            // 1. Preparación
            // ================================
            // Obtenemos un usuario real de la tabla usuario.
            $rowUser = $this->pdo->query("
                SELECT id_usuario, nombre
                  FROM usuario
                 ORDER BY id_usuario
                 LIMIT 1
            ")->fetch(PDO::FETCH_ASSOC);

            // Si no existe ningún usuario, el test falla explícitamente
            $this->assertNotFalse(
                $rowUser,
                'No hay ningún usuario en la tabla usuario para ejecutar la prueba.'
            );

            // Guardamos id_usuario y nombre para usar en el registro de mascota
            $idUsuario         = (int)$rowUser['id_usuario'];
            $nombrePropietario = (string)$rowUser['nombre'];

            // ================================
            // 2. Lógica: llamamos a la función
            // ================================
            $resp = registrar_mascota(
                $this->pdo,
                'Firulais',          // nombre_mascota
                '2024-01-01',        // fecha_nacimiento válida
                'Perro',             // tipo
                'Mestizo',           // raza
                $nombrePropietario   // propietario existente
            );

            // ============================
            // 3. Verificación de respuesta
            // ============================
            // Verificamos que el estado sea success
            $this->assertSame('success', $resp['estado']);
            // Verificamos el mensaje de éxito
            $this->assertSame('Mascota registrada exitosamente', $resp['mensaje']);
            // Verificamos que la respuesta incluya el id_mascota
            $this->assertArrayHasKey('id_mascota', $resp);

            // Convertimos id_mascota a entero
            $idMascota = (int)$resp['id_mascota'];

            // ================================
            // 3. Verificación en base de datos
            // ================================
            // Consultamos la mascota recién insertada por su id_mascota
            $stmt = $this->pdo->prepare("
                SELECT nombre_mascota, fecha_nacimiento, tipo, raza, id_usuario
                  FROM mascota
                 WHERE id_mascota = :id
            ");
            $stmt->execute([':id' => $idMascota]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Aseguramos que la fila exista
            $this->assertNotFalse($row, 'No se encontró la mascota recién registrada.');
            // Comprobamos que los campos coinciden con lo enviado
            $this->assertSame('Firulais',    $row['nombre_mascota']);
            $this->assertSame('2024-01-01',  $row['fecha_nacimiento']);
            $this->assertSame('Perro',       $row['tipo']);
            $this->assertSame('Mestizo',     $row['raza']);
            $this->assertSame($idUsuario, (int)$row['id_usuario']);

        } finally {
            // Siempre hacemos rollback para no dejar datos de prueba en la BD real
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
        }
    }


     // 5) Se pueden registrar múltiples mascotas para el mismo propietario
     // Verifica que la función permite más de una mascota asociada al mismo usuario.

    public function testSePuedenRegistrarMultiplesMascotasParaMismoPropietario(): void
    {
        // Transacción para aislar inserts de este test
        $this->pdo->beginTransaction();

        try {
            // ================================
            // 1. Preparación
            // ================================
            // Obtenemos un usuario real existente
            $rowUser = $this->pdo->query("
                SELECT id_usuario, nombre
                  FROM usuario
                 ORDER BY id_usuario
                 LIMIT 1
            ")->fetch(PDO::FETCH_ASSOC);

            $this->assertNotFalse(
                $rowUser,
                'No hay ningún usuario en la tabla usuario para ejecutar la prueba.'
            );

            $idUsuario         = (int)$rowUser['id_usuario'];
            $nombrePropietario = (string)$rowUser['nombre'];

            // ===========================================================
            // 2. Lógica: registramos 2 mascotas para el mismo propietario
            // ===========================================================
            registrar_mascota(
                $this->pdo,
                'Firulais',
                '2024-01-01',
                'Perro',
                'Mestizo',
                $nombrePropietario
            );
            registrar_mascota(
                $this->pdo,
                'Mishi',
                '2023-05-10',
                'Gato',
                'Criollo',
                $nombrePropietario
            );

            // ===============
            // 3. Verificación
            // ===============
            // Contamos cuántas mascotas tiene este usuario en la tabla mascota
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) AS total
                  FROM mascota
                 WHERE id_usuario = :id_usuario
            ");
            $stmt->execute([':id_usuario' => $idUsuario]);
            $total = (int)$stmt->fetchColumn();

            // Verificamos que tenga al menos 2 mascotas (las que se acaban de registrar)
            $this->assertGreaterThanOrEqual(
                2,
                $total,
                'Se esperaban al menos 2 mascotas registradas para el mismo propietario.'
            );

        } finally {
            // Revertimos los cambios para que la BD quede limpia
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
        }
    }
}
