// Dp - 1
import org.openqa.selenium.*;
import org.openqa.selenium.edge.EdgeDriver;
import org.openqa.selenium.edge.EdgeOptions;
import org.testng.Assert;
import org.testng.annotations.AfterTest;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Test;

import java.util.concurrent.TimeUnit;

public class RegistroUsuarioTest {

    private WebDriver driver;

    @BeforeTest
    public void setUp() {
        System.setProperty("webdriver.edge.driver", "C:\\drivers\\edgedriver\\msedgedriver.exe");

        EdgeOptions options = new EdgeOptions();
        driver = new EdgeDriver(options);

        driver.manage().window().maximize();
        driver.manage().timeouts().implicitlyWait(5, TimeUnit.SECONDS);
    }

    @AfterTest
    public void tearDown() {
        if (driver != null) {
            driver.quit();
        }
    }

    private void esperar(int segundos) {
        try {
            TimeUnit.SECONDS.sleep(segundos);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }

    @Test
    public void registrarNuevoUsuario_exitoso() {
        // 1) PREPARACIÓN
        String baseUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(baseUrl);

        // Ir a la pantalla de inicio de sesión (igual que en tus otros tests)
        WebElement botonLogin = driver.findElement(By.xpath("/html/body/header/div/div/a/button"));
        botonLogin.click();
        esperar(2);

        // 2) LÓGICA
        // Ahora sí estamos en sesionindex.html, donde existe el link de registro
        // Usamos el CSS de la clase .register-link para evitar problemas con acentos
        WebElement linkRegistrarse = driver.findElement(By.cssSelector(".register-link a"));
        linkRegistrarse.click();
        esperar(2);

        // Aquí ya deberías estar en registroindex.html, puedes seguir con:
        WebElement campoCorreo = driver.findElement(By.id("email"));
        WebElement campoNombre = driver.findElement(By.id("nombre"));
        WebElement campoPassword = driver.findElement(By.id("password"));
        WebElement botonCrearCuenta = driver.findElement(By.id("crearCuentaBtn"));

        String correoPrueba = "usuario1.prueba@gmail.com";
        campoCorreo.sendKeys(correoPrueba);
        campoNombre.sendKeys("Usuario Prueba Aceptacion 1");
        campoPassword.sendKeys("ClavePrueba170209*");
        botonCrearCuenta.click();
        esperar(2);

        // 3) VERIFICACIÓN (ejemplo simple, puedes afinar el mensaje)
        WebElement popupTitulo = driver.findElement(By.id("swal2-title"));
        Assert.assertTrue(popupTitulo.isDisplayed(), "No se mostró el pop-up de verificación.");
    }
}
