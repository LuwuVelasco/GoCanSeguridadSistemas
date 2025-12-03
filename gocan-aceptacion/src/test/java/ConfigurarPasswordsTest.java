// Dom - 1

import java.util.concurrent.TimeUnit;

import org.openqa.selenium.*;
import org.openqa.selenium.edge.EdgeDriver;
import org.openqa.selenium.edge.EdgeOptions;
import org.testng.Assert;
import org.testng.annotations.AfterTest;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Test;


public class ConfigurarPasswordsTest {
    private WebDriver driver;
    
    @BeforeTest
    public void setUp() {
        System.setProperty("webdriver.edge.driver", "C:\\drivers\\edgedriver\\msedgedriver.exe");

        EdgeOptions options = new EdgeOptions();
        driver = new EdgeDriver(options);

        driver.manage().window().maximize();
        driver.manage().timeouts().implicitlyWait(5, java.util.concurrent.TimeUnit.SECONDS);
    }

    @AfterTest
    public void tearDown() {
        if (driver != null) {
            driver.quit();
        }
    }

    public void espera(int segundos) {
        try {
            TimeUnit.SECONDS.sleep(segundos);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    /*
     * CASO DE PRUEBA: GC-14
     * Verificar la configuración de las contraseñas
     *
     * PRECONDICIONES:
     * - Tener buena conexión a Internet.
     * - Contar con un navegador web.
     * - Ingresar a la página GoCan.
     * - Conexión con la base de datos.
     * - Tener dos cuentas de usuario.
     */

    @Test
    public void configurarContrasenia_exitosa() {
        // ===========================================
        // PASO 1: Entrar al home mediante la URL
        // RESULTADO ESPERADO: Visualizar la pantalla de home.
        // ===========================================
        String baseUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(baseUrl);

        //Ir a la pantalla de inicio de sesión
        WebElement botonLogin = driver.findElement(By.xpath("/html/body/header/div/div/a/button"));
        botonLogin.click();
        espera(2);

        // Paso 2.- Lógica
        // Iniciar sesión con un usuario existente (Usuario de administrador)

        WebElement campoUsuario = driver.findElement(By.id("email"));
        campoUsuario.sendKeys("pruebota@gmail.com");

        WebElement campoPassword = driver.findElement(By.id("password"));
        campoPassword.sendKeys("choche123");

        //Pulsar el botón de iniciar sesión
        WebElement botonIniciarSesion = driver.findElement(By.id("ingresarBtn"));
        botonIniciarSesion.click();

        espera(3);

        // Navegar a la sección de configuración de contraseñas
        WebElement botonConfigPass = driver.findElement(By.xpath("//*[@id=\"bt0\"]"));
        botonConfigPass.click();

        espera(2);
        // Rellenar el formulario de configuración de contraseñas
        WebElement campoTiempoVidaUtil = driver.findElement(By.xpath("//*[@id=\"tiempoVidaUtil\"]"));
        campoTiempoVidaUtil.sendKeys("30");

        WebElement campoNumeroPassHistoricas = driver.findElement(By.xpath("//*[@id=\"numeroHistorico\"]"));
        campoNumeroPassHistoricas.sendKeys("5");

        // Guardar la configuración
        WebElement botonGuardarConfiguracion = driver.findElement(By.xpath("//*[@id=\"passwordConfigForm\"]/div[3]/button[2]"));
        botonGuardarConfiguracion.click();

        espera(2);

        // Paso 3.- Verificación
        // Verificar que se muestra un mensaje de éxito
        WebElement mensajeConfirmacion = driver.findElement(By.xpath("//*[@id=\"swal2-html-container\"]"));
        Assert.assertTrue(mensajeConfirmacion.isDisplayed());
        Assert.assertEquals("Configuración de contraseñas actualizada exitosamente.", mensajeConfirmacion.getText());
    }
}
