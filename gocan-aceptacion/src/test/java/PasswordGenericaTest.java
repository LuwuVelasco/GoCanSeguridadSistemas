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
        // 1. Preparación - Ingresar a la pantalla principal del sistema
        // PASO 1:
        // "En la pantalla de inicio de sesión se debe presionar en el texto 
        //  'Registrarse' que está debajo del botón de ingresar. Debe cambiar 
        //   a la pantalla de registro con los campos correspondientes."
        String coreUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(coreUrl);
        esperar(3);
        // Abrir login
        WebElement botonLogin = driver.findElement(By.xpath("/html/body/header/div/div/a/button"));
        botonLogin.click();
        esperar(3);
        // Clic en "Regístrese"
        WebElement enlaceRegistro = driver.findElement(By.linkText("Regístrese"));
        enlaceRegistro.click();
        esperar(3);
        // 2. Logica de la prueba
        // PASO 2:
        // "Ingresar el correo y nombre de usuario, pero en contraseña colocar 
        //  una secuencia de números como '123'. Se espera un pop-up con el mensaje 
        //  'Contraseña no válida' y que indique que no puede contener secuencias".
        // Ingresar correo válido
        WebElement campoEmail = driver.findElement(By.id("email"));
        campoEmail.clear();
        campoEmail.sendKeys("usuario.prueba@gmail.com");
        esperar(2);
        // Ingresar nombre de usuario
        WebElement campoNombre = driver.findElement(By.id("nombre"));
        campoNombre.clear();
        campoNombre.sendKeys("Usuario Prueba");
        esperar(2);
        // Ingresar contraseña con secuencia numérica
        WebElement campoPassword = driver.findElement(By.id("password"));
        campoPassword.clear();
        campoPassword.sendKeys("123");   // ← secuencia genérica según el caso
        esperar(2);
        // Intentar registrar
        WebElement botonCrearCuenta = driver.findElement(By.id("crearCuentaBtn"));
        botonCrearCuenta.click();
        esperar(3);
        // 3. Verificación
        // Debe aparecer un pop-up indicando:
        //   "Contraseña no válida"
        //   y el texto indicando que no debe contener secuencias comunes.
        // Esperar pop-up de advertencia
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.className("swal2-popup")));
        // Validar título del mensaje
        WebElement tituloMensaje = driver.findElement(By.id("swal2-title"));
        Assert.assertTrue(tituloMensaje.isDisplayed());
        Assert.assertEquals("Contraseña no válida", tituloMensaje.getText());
        // Validar mensaje relacionado a secuencias numéricas
        WebElement contenidoMensaje = driver.findElement(By.id("swal2-html-container"));
        String textoContenido = contenidoMensaje.getText();
        Assert.assertTrue(
                textoContenido.contains("secuencias comunes") ||
                textoContenido.contains("123")
        );
        // Cerrar mensaje
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
