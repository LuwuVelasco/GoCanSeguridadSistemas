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

public class LoginPasswordIncorrectaTest {

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
    public void inicioSesion_contrasenaIncorrecta_muestraError() {
        // 1) PREPARACIÓN
        String baseUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(baseUrl);

        // Usar el MISMO botón de usuario que en tus otros tests
        WebElement botonIrLogin = driver.findElement(By.xpath("/html/body/header/div/div/a/button"));
        botonIrLogin.click();
        esperar(2);

        // (En sesionindex.html ya se define window.RECAPTCHA_BYPASS_LOCAL = true)

        // 2) LÓGICA: correo válido + contraseña incorrecta
        WebElement campoCorreo = driver.findElement(By.id("email"));
        WebElement campoPassword = driver.findElement(By.id("password"));
        WebElement botonIngresar = driver.findElement(By.id("ingresarBtn"));

        campoCorreo.sendKeys("jaredpitiu1709@gmail.com");          // usuario existente
        campoPassword.sendKeys("ClaveTotalmenteIncorrecta123");    // contraseña mala
        botonIngresar.click();

        // 3) VERIFICACIÓN: SweetAlert de error
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(10));

        WebElement popupTitulo = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("swal2-title"))
        );
        WebElement popupTexto = driver.findElement(By.id("swal2-html-container"));

        Assert.assertEquals(
                popupTitulo.getText(),
                "Error de inicio de sesión",
                "El título del pop-up no coincide con el mensaje de error esperado."
        );

        String texto = popupTexto.getText().toLowerCase();
        Assert.assertTrue(
                texto.contains("credenciales") ||
                texto.contains("incorrect") ||
                texto.contains("correo") ||
                texto.contains("contraseña"),
                "El mensaje del pop-up no indica que las credenciales son incorrectas."
        );
    }
}
