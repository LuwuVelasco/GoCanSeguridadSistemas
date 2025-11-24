
// Fer 1 - Cerrar sesión 
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

    @Test
    public void cerrarSesionComoCliente() {
        String homeUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(homeUrl);
        By loginButtonLocator = By.xpath("/html/body/header/div/div/a/button");
        WebElement botonLogin = wait.until(
                ExpectedConditions.visibilityOfElementLocated(loginButtonLocator));
        js.executeScript("arguments[0].scrollIntoView(true);", botonLogin);
        esperar(1);
        js.executeScript("arguments[0].click();", botonLogin);
        esperar(2);

        WebElement campoUsuario = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("email")));
        campoUsuario.sendKeys("imajesus08@gmail.com");

        WebElement campoPassword = driver.findElement(By.id("password"));
        campoPassword.sendKeys("12345");

        WebElement botonIniciarSesion = driver.findElement(By.id("ingresarBtn"));
        botonIniciarSesion.click();
        esperar(3);

        WebElement botonAgendarCita = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("bt0")));
        Assert.assertTrue(botonAgendarCita.isDisplayed(), "No se cargó la pantalla de cliente correctamente");
        WebElement menuUsuario = wait.until(
                ExpectedConditions.visibilityOfElementLocated(
                        By.cssSelector("div.profile")));

        js.executeScript("arguments[0].scrollIntoView(true);", menuUsuario);
        esperar(1);
        js.executeScript("arguments[0].click();", menuUsuario);
        esperar(1);

        WebElement dropdown = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("profileDropdown")));
        Assert.assertTrue(dropdown.isDisplayed(), "El menú desplegable de usuario no se mostró");
        WebElement linkCerrarSesion = dropdown.findElement(
                By.xpath(".//a[contains(text(),'Cerrar sesión')]"));
        Assert.assertTrue(linkCerrarSesion.isDisplayed(), "No se encontró la opción 'Cerrar sesión' en el menú");
        linkCerrarSesion.click();
        WebElement botonLoginHome = wait.until(
                ExpectedConditions.visibilityOfElementLocated(
                        By.xpath("/html/body/header/div/div/a/button")));

        Assert.assertTrue(botonLoginHome.isDisplayed(), "No regresó al home tras cerrar sesión");
    }

    private void esperar(int segundos) {
        try {
            TimeUnit.SECONDS.sleep(segundos);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }
}
