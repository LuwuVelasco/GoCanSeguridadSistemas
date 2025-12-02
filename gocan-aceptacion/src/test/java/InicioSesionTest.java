// Lu - 1
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.edge.EdgeDriver;
import org.openqa.selenium.edge.EdgeOptions;
import org.testng.Assert;
import org.testng.annotations.AfterTest;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Test;

import java.util.concurrent.TimeUnit;

public class InicioSesionTest {
    private WebDriver driver;

    @BeforeTest
    public void setDriver() {
        System.setProperty("webdriver.edge.driver", "C:\\drivers\\edgedriver\\msedgedriver.exe");

        EdgeOptions options = new EdgeOptions();
        driver = new EdgeDriver(options);

        driver.manage().window().maximize();
        driver.manage().timeouts().implicitlyWait(5, TimeUnit.SECONDS);
    }

    @AfterTest
    public void closeDriver() {
        if (driver != null) {
            driver.quit();
        }
    }

    /*
     * CASO DE PRUEBA: Verificar inicio de sesión exitoso de un usuario registrado
     */
    @Test
    public void iniciarSesion() {
        // ===============================================
        // PASO 1: PREPARACIÓN
        // ===============================================
        String loginUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(loginUrl);

        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        
        // PASO 1: En la página principal selecciona el símbolo de perfil que está en la parte 
        // superior derecha para poder iniciar sesión.
        // RESULTADO ESPERADO: Se abre la pantalla de inicio de sesión con los campos de correo y contraseña.
        WebElement botonLogin = driver.findElement(By.xpath("/html/body/header/div/div/a/button"));
        botonLogin.click();

        esperar(3);

        // PASO 2: Escribe tu correo y contraseña, también realiza el captcha.
        // RESULTADO ESPERADO: Los campos aceptan la información.
        WebElement campoUsuario = driver.findElement(By.id("email"));
        campoUsuario.sendKeys("luwu@gmail.com");

        WebElement campoPassword = driver.findElement(By.id("password"));
        campoPassword.sendKeys("Aqmdla.1");

        // PASO 3: Haz clic en el botón "Ingresar".
        // RESULTADO ESPERADO: Se espera que el sistema lo redirija a la página que está 
        // designada según su rol.
        WebElement botonIniciarSesion = driver.findElement(By.id("ingresarBtn"));
        botonIniciarSesion.click();

        esperar(3);

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Verificar que el usuario fue redirigido correctamente a la página de administrador
        // comprobando que el primer botón de la página admin esté visible
        WebElement primerBotonPaginaAdmin = driver.findElement(By.xpath("//*[@id=\"bt0\"]"));
        Assert.assertTrue(primerBotonPaginaAdmin.isDisplayed());
    }

    private void esperar(int segundos) {
        try {
            TimeUnit.SECONDS.sleep(segundos);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }
}