<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/modules/php/registrar_funcionario.php';

/**
 * Pruebas unitarias para registrar_funcionario()
 */
final class registrar_funcionarioTest extends TestCase
{
    /** @var PDO Conexión a la base de datos */
    private PDO $pdo;

    // ===============================================
    // PASO 1: PREPARACIÓN GLOBAL POR CADA TEST
    // ===============================================
    protected function setUp(): void
    {
        // Cargamos la conexión desde conexion.php.
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

    // ============================================================
    // TEST 1: CAMPOS VACÍOS → InvalidArgumentException
    // ============================================================
    public function testCamposVaciosLanzanError(): void
    {
        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Faltan campos requeridos');

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        registrar_funcionario(
            $this->pdo,
            '', // Nombre vacío
            '', // Email vacío
            '', // Password vacío
            0   // Rol inválido
        );
    }

    // ============================================================
    // TEST 2: EMAIL INVÁLIDO → InvalidArgumentException
    // ============================================================
    public function testEmailInvalidoLanzaError(): void
    {
        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Correo electrónico inválido');

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        registrar_funcionario(
            $this->pdo,
            'Juan Perez',
            'correo-no-valido', // Email inválido
            '123456',
            1
        );
    }

    // ============================================================
    // TEST 3: SIN CONFIGURACIÓN DE PASSWORDS → RuntimeException
    // ============================================================
    public function testSinConfiguracionPasswordLanzaError(): void
    {
        // ===============================================
        // PASO 1: PREPARACIÓN DEL ESCENARIO
        // ===============================================
        // Limpiamos tablas relacionadas con la configuración
        $this->pdo->exec("DELETE FROM historial_passwords");
        $this->pdo->exec("DELETE FROM configuracion_passwords");

        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No se encontró configuración de contraseña');

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        registrar_funcionario(
            $this->pdo,
            'Juan Perez',
            'juan@example.com',
            'Secret123',
            1
        );
    }

    // ============================================================
    // TEST 4: REGISTRO EXITOSO
    // ============================================================
    public function testRegistroExitoso(): void
    {
        // ===============================================
        // PASO 1: PREPARACIÓN DEL ESCENARIO
        // ===============================================
        // Limpiamos configuración previa
        $this->pdo->exec("DELETE FROM historial_passwords");
        $this->pdo->exec("DELETE FROM configuracion_passwords");

        // Insertamos una configuración válida
        $this->pdo->exec("
            INSERT INTO configuracion_passwords (tiempo_vida_util, numero_historico, fecha_configuracion)
            VALUES (30, 5, NOW())
        ");

        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        $resp = registrar_funcionario(
            $this->pdo,
            'Maria Lopez',
            'maria@example.com',
            'PasswordSeguro1',
            1
        );

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        $this->assertSame('success', $resp['estado']);
        $this->assertArrayHasKey('id_usuario', $resp);
        $this->assertArrayHasKey('id_configuracion', $resp);

        // Verificamos que el usuario se haya insertado
        $idUsuario = (int)$resp['id_usuario'];
        $usuario = $this->pdo->query("SELECT * FROM usuario WHERE id_usuario = $idUsuario")->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($usuario, 'El usuario no se encontró en la base de datos');
        $this->assertEquals('maria@example.com', $usuario['email']);
    }

    // ============================================================
    // TEST 5: PASSWORD SE GUARDA HASHEADO
    // ============================================================
    public function testPasswordSeGuardaHasheado(): void
    {
        // ===============================================
        // PASO 1: PREPARACIÓN DEL ESCENARIO
        // ===============================================
        // Limpiamos configuración previa
        $this->pdo->exec("DELETE FROM historial_passwords");
        $this->pdo->exec("DELETE FROM configuracion_passwords");

        // Insertamos una configuración válida
        $this->pdo->exec("
            INSERT INTO configuracion_passwords (tiempo_vida_util, numero_historico, fecha_configuracion)
            VALUES (60, 3, NOW())
        ");

        $passwordPlano = 'MiPasswordSecreto';
        
        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        $resp = registrar_funcionario(
            $this->pdo,
            'Carlos Gomez',
            'carlos@example.com',
            $passwordPlano,
            1
        );

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        $idUsuario = (int)$resp['id_usuario'];
        
        // Verificar en tabla usuario
        $hashUsuario = $this->pdo->query("SELECT password FROM usuario WHERE id_usuario = $idUsuario")->fetchColumn();
        $this->assertTrue(password_verify($passwordPlano, $hashUsuario), 'El password en usuario no coincide con el hash');

        // Verificar en historial_passwords
        $hashHistorial = $this->pdo->query("SELECT password FROM historial_passwords WHERE id_usuario = $idUsuario ORDER BY fecha_creacion DESC LIMIT 1")->fetchColumn();
        $this->assertTrue(password_verify($passwordPlano, $hashHistorial), 'El password en historial no coincide con el hash');
    }
}
