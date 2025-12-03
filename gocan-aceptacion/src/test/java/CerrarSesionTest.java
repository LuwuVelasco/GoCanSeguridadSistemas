// Fer 2 - Cerrar sesión 
import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
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

public class CerrarSesionTest {

    private WebDriver driver;
    private WebDriverWait wait;
    private JavascriptExecutor js;

    @BeforeTest
    public void setDriver() {
        System.setProperty("webdriver.edge.driver", "C:\\drivers\\edgedriver\\msedgedriver.exe");

        EdgeOptions options = new EdgeOptions();
        driver = new EdgeDriver(options);

        driver.manage().window().maximize();
        driver.manage().timeouts().implicitlyWait(5, TimeUnit.SECONDS);

        wait = new WebDriverWait(driver, Duration.ofSeconds(10));
        js = (JavascriptExecutor) driver;
    }

    @AfterTest
    public void closeDriver() {
        if (driver != null) {
            driver.quit();
        }
    }

    /*
     * CASO DE PRUEBA:
     * Verificar el cierre de sesión de una cuenta dentro del sistema.
     *
     * PRECONDICIONES:
     * - Tener una sesión iniciada dentro del sistema.
     * - Conexión con la base de datos (implícita al poder iniciar sesión).
     */
    @Test
    public void cerrarSesionComoCliente() {

        // ===============================================
        // FASE 1: PREPARACIÓN
        // ===============================================

        // Paso de preparación 1:
        // Ir a la pantalla principal (home) del sistema.
        String homeUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(homeUrl);

        // Paso de preparación 2:
        // Hacer clic en el botón "Iniciar sesión" del header para ir a la pantalla de login.
        By loginButtonLocator = By.xpath("/html/body/header/div/div/a/button");
        WebElement botonLogin = wait.until(
                ExpectedConditions.visibilityOfElementLocated(loginButtonLocator));
        js.executeScript("arguments[0].scrollIntoView(true);", botonLogin);
        esperar(1);
        js.executeScript("arguments[0].click();", botonLogin);
        esperar(2);

        // Paso de preparación 3:
        // Iniciar sesión como cliente.
        WebElement campoUsuario = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("email")));
        campoUsuario.sendKeys("imajesus08@gmail.com");

        WebElement campoPassword = driver.findElement(By.id("password"));
        campoPassword.sendKeys("12345");

        WebElement botonIniciarSesion = driver.findElement(By.id("ingresarBtn"));
        botonIniciarSesion.click();
        esperar(3);

        // Verificación rápida de preparación:
        // Comprobar que se cargó correctamente la pantalla de cliente.
        WebElement botonAgendarCita = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("bt0")));
        Assert.assertTrue(botonAgendarCita.isDisplayed(),
                "No se cargó la pantalla de cliente correctamente tras iniciar sesión");


        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================

        // PASO 1 DEL CASO DE PRUEBA:
        // En la parte superior derecha de la pantalla, hacer click en el menú de usuario.
        // RESULTADO ESPERADO:
        // Se despliega un menú con opciones como 'cambiar de usuario' y 'cerrar sesión'.

        // Localizar el menú de usuario 
        WebElement menuUsuario = wait.until(
                ExpectedConditions.visibilityOfElementLocated(
                        By.cssSelector("div.profile")));

        // Hacer scroll hasta el menú de usuario por si no es visible en pantalla.
        js.executeScript("arguments[0].scrollIntoView(true);", menuUsuario);
        esperar(1);

        // Hacer clic sobre el menú de usuario para desplegar las opciones.
        js.executeScript("arguments[0].click();", menuUsuario);
        esperar(1);

        // Verificar que el menú desplegable de usuario se haya mostrado.
        WebElement dropdown = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("profileDropdown")));
        Assert.assertTrue(dropdown.isDisplayed(),
                "El menú desplegable de usuario no se mostró");

        // Verificar específicamente que exista la opción "Cerrar sesión" en el menú desplegable.
        WebElement linkCerrarSesion = dropdown.findElement(
                By.xpath(".//a[contains(text(),'Cerrar sesión')]"));
        Assert.assertTrue(linkCerrarSesion.isDisplayed(),
                "No se encontró la opción 'Cerrar sesión' en el menú");

        // PASO 2 DEL CASO DE PRUEBA:
        // Seleccionar la opción de 'Cerrar sesión'.
        // RESULTADO ESPERADO:
        // Se espera que nos redirija a la pantalla de inicio con ninguna cuenta o sesión iniciada.

        // Hacer clic en la opción "Cerrar sesión".
        linkCerrarSesion.click();

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================

        // Verificar que se haya redirigido nuevamente al home del sistema.
        // Para esto, comprobamos que reaparece el botón de "Iniciar sesión" en el header,
        // lo que indica que no hay sesión activa visible.
        WebElement botonLoginHome = wait.until(
                ExpectedConditions.visibilityOfElementLocated(
                        By.xpath("/html/body/header/div/div/a/button")));

        Assert.assertTrue(botonLoginHome.isDisplayed(),
                "No regresó al home tras cerrar sesión (no se muestra el botón de login)");

    }

    private void esperar(int segundos) {
        try {
            TimeUnit.SECONDS.sleep(segundos);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }
}
