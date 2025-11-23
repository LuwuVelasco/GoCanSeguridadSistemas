import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
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

public class PasswordGenericaTest {
    private WebDriver driver;
    private WebDriverWait wait;

    @BeforeTest
    public void setDriver() {
        System.setProperty("webdriver.edge.driver", "C:\\drivers\\edgedriver\\msedgedriver.exe");
        EdgeOptions options = new EdgeOptions();
        driver = new EdgeDriver(options);
        driver.manage().window().maximize();
        driver.manage().timeouts().implicitlyWait(5, TimeUnit.SECONDS);
        wait = new WebDriverWait(driver, Duration.ofSeconds(10));
    }

    @AfterTest
    public void closeDriver() {
        if (driver != null) {
            driver.quit();
        }
    }

    @Test
    public void verificarRechazoPasswordConSecuenciaNumerica() {
        // 1. Preparación
        String coreUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(coreUrl);
        esperar(3);
        WebElement botonLogin = driver.findElement(By.xpath("/html/body/header/div/div/a/button"));
        botonLogin.click();
        esperar(3);
        WebElement enlaceRegistro = driver.findElement(By.linkText("Regístrese"));
        enlaceRegistro.click();
        esperar(3);
        // 2. Lógica de la prueba
        WebElement campoEmail = driver.findElement(By.id("email"));
        campoEmail.clear();
        campoEmail.sendKeys("usuario.prueba@gmail.com");
        esperar(2);
        WebElement campoNombre = driver.findElement(By.id("nombre"));
        campoNombre.clear();
        campoNombre.sendKeys("Usuario Prueba");
        esperar(2);
        WebElement campoPassword = driver.findElement(By.id("password"));
        campoPassword.clear();
        campoPassword.sendKeys("Password123!");
        esperar(2);
        WebElement botonCrearCuenta = driver.findElement(By.id("crearCuentaBtn"));
        botonCrearCuenta.click();
        esperar(3);
        // 3. Verificación
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.className("swal2-popup")));
        WebElement tituloMensaje = driver.findElement(By.id("swal2-title"));
        Assert.assertTrue(tituloMensaje.isDisplayed());
        Assert.assertEquals("Contraseña no válida", tituloMensaje.getText());
        WebElement contenidoMensaje = driver.findElement(By.id("swal2-html-container"));
        String textoContenido = contenidoMensaje.getText();
        Assert.assertTrue(textoContenido.contains("secuencias comunes") || textoContenido.contains("123"));

        WebElement botonOk = driver.findElement(By.cssSelector(".swal2-confirm"));
        botonOk.click();
        esperar(2);
    }

    private void esperar(int segundos) {
        try {
            TimeUnit.SECONDS.sleep(segundos);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }
}