import org.openqa.selenium.Alert;
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

public class RegistrarMascotaTest {
    private WebDriver driver;
    private JavascriptExecutor js;
    private WebDriverWait wait;

    @BeforeTest
    public void setDriver() {
        System.setProperty("webdriver.edge.driver", "C:\\drivers\\edgedriver\\msedgedriver.exe");
        EdgeOptions options = new EdgeOptions();
        driver = new EdgeDriver(options);
        driver.manage().window().maximize();
        driver.manage().timeouts().implicitlyWait(10, TimeUnit.SECONDS);
        js = (JavascriptExecutor) driver;
        wait = new WebDriverWait(driver, Duration.ofSeconds(10));
    }

    @AfterTest
    public void closeDriver() {
        if (driver != null) {
            driver.quit();
        }
    }

    @Test
    public void verificarCamposObligatoriosRegistrarMascota() {
        // 1. Preparación - Ingresar al link de la página
        String loginUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(loginUrl);
        esperar(3);
        // 2. Logica de la prueba 
        WebElement botonLogin = driver.findElement(By.xpath("/html/body/header/div/div/a/button"));
        js.executeScript("arguments[0].click();", botonLogin);
        esperar(3);
        WebElement campoUsuario = driver.findElement(By.id("email"));
        campoUsuario.sendKeys("greysonwilliam119@gmail.com");
        WebElement campoPassword = driver.findElement(By.id("password"));
        campoPassword.sendKeys("krol.SMG12");
        WebElement botonIniciarSesion = driver.findElement(By.id("ingresarBtn"));
        botonIniciarSesion.click();
        esperar(4);
        manejarAlertSiExiste();
        WebElement botonRegistroMascota = driver.findElement(By.id("bt0"));
        Assert.assertTrue(botonRegistroMascota.isDisplayed());
        esperar(2);
        botonRegistroMascota.click();
        esperar(3);
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("petModal")));
        WebElement modal = driver.findElement(By.id("petModal"));
        Assert.assertTrue(modal.isDisplayed());
        WebElement nombreMascota = wait.until(ExpectedConditions.visibilityOfElementLocated(
            By.xpath("//form[@id='petForm']//input[@id='nombre_mascota']")
        ));
        WebElement fechaNacimiento = driver.findElement(By.id("fecha_nacimiento"));
        WebElement tipo = driver.findElement(By.id("tipo"));
        WebElement raza = driver.findElement(By.id("raza"));
        WebElement nombrePropietario = driver.findElement(By.id("nombre_propietario"));
        Assert.assertEquals(nombreMascota.getAttribute("required"), "true");
        Assert.assertEquals(fechaNacimiento.getAttribute("required"), "true");
        Assert.assertEquals(tipo.getAttribute("required"), "true");
        Assert.assertEquals(raza.getAttribute("required"), "true");
        Assert.assertEquals(nombrePropietario.getAttribute("required"), "true");
        WebElement botonRegistrar = driver.findElement(By.xpath("//form[@id='petForm']//button[@type='submit']"));
        botonRegistrar.click();
        esperar(2);
        WebElement modalAbierto = driver.findElement(By.id("petModal"));
        Assert.assertTrue(modalAbierto.isDisplayed());
        nombreMascota.sendKeys("Firulais");
        botonRegistrar.click();
        esperar(2);
        Assert.assertTrue(modalAbierto.isDisplayed());
        fechaNacimiento.sendKeys("15012020");
        botonRegistrar.click();
        esperar(2);
        Assert.assertTrue(modalAbierto.isDisplayed());
        WebElement selectTipo = driver.findElement(By.id("tipo"));
        js.executeScript("arguments[0].value = 'Perro';", selectTipo);
        botonRegistrar.click();
        esperar(2);
        Assert.assertTrue(modalAbierto.isDisplayed());
        raza.sendKeys("Labrador");
        botonRegistrar.click();
        esperar(2);
        Assert.assertTrue(modalAbierto.isDisplayed());
        nombrePropietario.sendKeys("Mateo");
        botonRegistrar.click();
        esperar(3);
        WebElement mensajeExito = wait.until(ExpectedConditions.visibilityOfElementLocated(
            By.xpath("//*[@id='swal2-title']")
        ));
        Assert.assertTrue(mensajeExito.isDisplayed());
    }

    private void manejarAlertSiExiste() {
        try {
            esperar(2);
            Alert alert = driver.switchTo().alert();
            alert.accept();
        } catch (Exception e) {
        }
    }

    private void esperar(int segundos) {
        try {
            TimeUnit.SECONDS.sleep(segundos);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }
}