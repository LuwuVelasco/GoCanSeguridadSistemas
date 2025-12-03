// Dp - 2
import org.openqa.selenium.*;
import org.openqa.selenium.edge.EdgeDriver;
import org.openqa.selenium.edge.EdgeOptions;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;
import org.testng.annotations.AfterTest;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Test;

import java.time.Duration;
import java.util.concurrent.TimeUnit;

// "Verificar mensaje de error al ingresar contraseña incorrecta"
public class LoginPasswordIncorrectaTest {

    // Driver navegador Edge
    private WebDriver driver;

    // =============================
    // CONFIGURACIÓN GLOBAL DEL TEST
    // =============================

    @BeforeTest
    public void setUp() {
        // ubicacion del ejecutable del driver de Edge en tu máquina
        System.setProperty("webdriver.edge.driver", "C:\\drivers\\edgedriver\\msedgedriver.exe");

        // Crea opciones específicas para Edge (aquí no se setea nada extra, pero queda preparado)
        EdgeOptions options = new EdgeOptions();
        // Instancia el navegador Edge con esas opciones
        driver = new EdgeDriver(options);

        // Maximiza la ventana del navegador
        driver.manage().window().maximize();
        // Configura una espera implícita de 5 segundos para encontrar elementos
        driver.manage().timeouts().implicitlyWait(5, TimeUnit.SECONDS);
    }

    @AfterTest
    public void tearDown() {
        // Si el driver no es nulo, cierra el navegador al finalizar las pruebas
        if (driver != null) {
            driver.quit();
        }
    }

    // Pequeño helper para hacer "pausas" explícitas
    private void esperar(int segundos) {
        try {
            // Duerme el hilo de ejecución N segundos
            TimeUnit.SECONDS.sleep(segundos);
        } catch (InterruptedException e) {
            // Si algo interrumpe el sleep, imprime el stacktrace
            e.printStackTrace();
        }
    }

    // =====================================
    // TEST PRINCIPAL: contraseña incorrecta
    // =====================================
    @Test
    public void inicioSesion_contrasenaIncorrecta_muestraError() {
        // ================================
        // 1) PREPARACIÓN DEL ESCENARIO
        // ================================

        // URL del sistema GoCan en localhost
        String baseUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(baseUrl);

        // -----------------------------------------
        // Paso 1 del caso de prueba:
        // "Ingresar al formulario de inicio de sesión mediante
        //  el símbolo de perfil que está en la parte superior
        //  derecha de la página de inicio."
        // -----------------------------------------

        // Localiza el botón de usuario (icono de perfil) usando un XPATH absoluto
        WebElement botonIrLogin = driver.findElement(By.xpath("/html/body/header/div/div/a/button"));
        // Hace clic en el botón → debería abrir el formulario de inicio de sesión
        botonIrLogin.click();
        // Pausa breve para permitir que la nueva página termine de cargar
        esperar(2);

        // window.RECAPTCHA_BYPASS_LOCAL = true para no depender del captcha real.

        // ====================================
        // 2) LÓGICA DEL CASO DE PRUEBA
        // (Paso 2 y 3: enviar credenciales)
        // ====================================

        // -----------------------------------------
        // Paso 2:
        // "Escribir un correo válido y una contraseña incorrecta.
        //  Los campos aceptan los datos."
        // -----------------------------------------

        // Localiza el campo de correo por su atributo id="email"
        WebElement campoCorreo = driver.findElement(By.id("email"));
        // Localiza el campo de contraseña por su atributo id="password"
        WebElement campoPassword = driver.findElement(By.id("password"));
        // Localiza el botón de "Ingresar" por su atributo id="ingresarBtn"
        WebElement botonIngresar = driver.findElement(By.id("ingresarBtn"));

        // Escribe en el campo de correo un email VÁLIDO de un usuario existente
        campoCorreo.sendKeys("jaredpitiu1709@gmail.com");
        // Escribe en el campo de contraseña un valor INCORRECTO a propósito
        campoPassword.sendKeys("ClaveTotalmenteIncorrecta123");
        // -----------------------------------------
        // Paso 3:
        // "Pulsa el botón 'Ingresar'."
        // -----------------------------------------
        // Hace clic en el botón de ingresar para enviar el formulario de login
        botonIngresar.click();

        // ======================================
        // 3) VERIFICACIÓN (ASSERT / RESULTADO)
        // ======================================

        // Creamos una espera explícita de hasta 10 segundos
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(10));

        // -----------------------------------------
        // Resultado esperado:
        // "Salta un pop-up que nos indica que hubo un error en el inicio de sesión,
        //  donde el correo o contraseñas son incorrectos."
        // Aquí validamos el SweetAlert2 que aparece.
        // -----------------------------------------

        // SweetAlert2 usa id="swal2-title" para el título del modal.
        WebElement popupTitulo = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("swal2-title"))
        );
        // SweetAlert2 usa id="swal2-html-container" para el contenido principal del mensaje.
        WebElement popupTexto = driver.findElement(By.id("swal2-html-container"));

        // Primera verificación:
        // se compara el texto del título del pop-up contra el título esperado.
        // Si no coincide, el test falla con el mensaje personalizado.
        Assert.assertEquals(
                popupTitulo.getText(),
                "Error de inicio de sesión",
                "El título del pop-up no coincide con el mensaje de error esperado."
        );

        // Normalizamos el texto del cuerpo del pop-up a minúsculas
        String texto = popupTexto.getText().toLowerCase();

        // Segunda verificación:
        // Comprobamos que el mensaje mencione que las credenciales son incorrectas.
        // Permitimos varias variantes por si el texto exacto cambia ligeramente:
        // - contiene "credenciales"
        // - o contiene "incorrect"
        // - o menciona "correo"
        // - o menciona "contraseña"
        Assert.assertTrue(
                texto.contains("credenciales") ||
                texto.contains("incorrect") ||
                texto.contains("correo") ||
                texto.contains("contraseña"),
                "El mensaje del pop-up no indica que las credenciales son incorrectas."
        );
    }
}
