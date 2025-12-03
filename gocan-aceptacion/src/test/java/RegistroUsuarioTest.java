// Dp - 1
import org.openqa.selenium.*;
import org.openqa.selenium.edge.EdgeDriver;
import org.openqa.selenium.edge.EdgeOptions;
import org.testng.Assert;
import org.testng.annotations.AfterTest;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Test;

import java.util.concurrent.TimeUnit;

// "Verificar el registro exitoso de un nuevo usuario en GoCan"
public class RegistroUsuarioTest {

    // driver que controla el navegador Edge
    private WebDriver driver;

    // ==============================
    // CONFIGURACIÓN GLOBAL DEL TEST
    // ==============================

    @BeforeTest
    public void setUp() {
        // ubi del driver de Edge
        System.setProperty("webdriver.edge.driver", "C:\\drivers\\edgedriver\\msedgedriver.exe");
        //configuracion
        EdgeOptions options = new EdgeOptions();
        driver = new EdgeDriver(options);

        // Maximiza la ventana del navegador
        driver.manage().window().maximize();
        // Configura una espera implícita de 5 segundos
        driver.manage().timeouts().implicitlyWait(5, TimeUnit.SECONDS);
    }

    @AfterTest
    public void tearDown() {
        // Si el driver fue inicializado, cierra el navegador al terminar todas las pruebas
        if (driver != null) {
            driver.quit();
        }
    }

    // pausa para esperas explícitas
    private void esperar(int segundos) {
        try {
            // Detiene el hilo actual durante N segundos
            TimeUnit.SECONDS.sleep(segundos);
        } catch (InterruptedException e) {
            // Si algo interrumpe el sleep, muestra el stack trace
            e.printStackTrace();
        }
    }

    // ====================================================
    // TEST PRINCIPAL: Registro de un nuevo usuario exitoso
    // ====================================================
    @Test
    public void registrarNuevoUsuario_exitoso() {
        // =============================
        // 1) PREPARACIÓN DEL ESCENARIO
        // =============================

        // URL base de goCan en localhost
        String baseUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(baseUrl);

        // -------------------------------------------------------------
        // Paso 1 del caso de prueba:
        // "En la pantalla de 'login', haz clic en el botón que dice
        //  'Registrarse'. Se abre el formulario para crear una nueva cuenta."
        // -------------------------------------------------------------

        // haces click en el botón de usuario para ir a la pantalla de login.
        // Localiza el botón de usuario/login usando un XPATH absoluto
        WebElement botonLogin = driver.findElement(By.xpath("/html/body/header/div/div/a/button"));
        // Clic sobre el botón y debe abrir la pantalla de inicio de sesión (sesionindex.html)
        botonLogin.click();
        // Pausa breve para que cargue el login
        esperar(2);

        // =============================
        // 2) LÓGICA DEL REGISTRO
        // =============================

        // Ahora estamos en sesionindex.html, donde existe el link de "Registrarse" con un css.
        WebElement linkRegistrarse = driver.findElement(By.cssSelector(".register-link a"));
        // Esto implementa el "haz clic en el botón que dice Registrarse".
        linkRegistrarse.click();
        // Pausa breve para que cargue el formulario de registro (registroindex.html)
        esperar(2);

        // -------------------------------------------------------------
        // Paso 2 del caso de prueba:
        // "Llenar los campos del formulario para el registro del nuevo usuario
        //  y cuando se termine de llenar presionar el botón de 'Crear cuenta'.
        //  Saltará una pop-up o ventana en la pantalla que será la verificación
        //  la cual nos dirá que se envió un código al correo introducido."
        // -------------------------------------------------------------

        // Localiza el campo de correo por su id="email" en el formulario de registro
        WebElement campoCorreo = driver.findElement(By.id("email"));
        // Localiza el campo de nombre por su id="nombre"
        WebElement campoNombre = driver.findElement(By.id("nombre"));
        // Localiza el campo de contraseña por su id="password"
        WebElement campoPassword = driver.findElement(By.id("password"));
        // Localiza el botón de "Crear cuenta" por su id="crearCuentaBtn"
        WebElement botonCrearCuenta = driver.findElement(By.id("crearCuentaBtn"));

        //IMPORTANTE: usa un correo que no haya sido registrado antes en el sistema.
        // Definimos un correo de prueba
        String correoPrueba = "prueba@gmail.com";
        // Escribe el correo en el campo correspondiente
        campoCorreo.sendKeys(correoPrueba);
        // Escribe un nombre de usuario de prueba
        campoNombre.sendKeys("Usuario Prueba");
        // Escribe una contraseña que cumpla las reglas del backend
        campoPassword.sendKeys("ClavePrueba170209*");
        // Clic en "Crear cuenta" para enviar el formulario de registro
        botonCrearCuenta.click();
        // Espera breve para que se procese el registro y aparezca el pop-up de verificación
        esperar(2);

        // =============================
        // 3) VERIFICACIÓN DEL RESULTADO
        // =============================

        // se espera que salte un pop-up/ventana indicando que se envió un código al correo.
        // Aquí hacemos una verificación simple: que el título del pop-up esté visible.

        // Localiza el título del SweetAlert2 por id="swal2-title"
        WebElement popupTitulo = driver.findElement(By.id("swal2-title"));
        // Verifica que el pop-up se haya mostrado efectivamente.
        Assert.assertTrue(
                popupTitulo.isDisplayed(),
                "No se mostró el pop-up de verificación."
        );
    }
}
