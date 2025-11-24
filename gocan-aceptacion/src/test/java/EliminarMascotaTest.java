// Fer 3 - Eliminar mascota como doctor

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
import java.util.List;
import java.util.concurrent.TimeUnit;

public class EliminarMascotaTest {

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
    public void eliminarMascotaComoDoctor() {
        String coreUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(coreUrl);

        WebElement loginBtn = wait.until(
                ExpectedConditions.visibilityOfElementLocated(
                        By.xpath("/html/body/header/div/div/a/button")));
        js.executeScript("arguments[0].click();", loginBtn);
        esperar(2);

        WebElement user = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("email")));
        user.sendKeys("lolo@gmail.com");

        WebElement pass = driver.findElement(By.id("password"));
        pass.sendKeys("12345"); 

        driver.findElement(By.id("ingresarBtn")).click();
        esperar(3);
        WebElement botonInfoMascotas = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("bt3")));
        botonInfoMascotas.click();
        esperar(2);
        WebElement tablaModal = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("tablaModal")));
        Assert.assertTrue(tablaModal.isDisplayed(), "No se abrió el modal de Información Mascotas");
        List<WebElement> filasAntes = driver.findElements(By.cssSelector("#petTable tbody tr"));
        int totalAntes = filasAntes.size();
        Assert.assertTrue(totalAntes > 0, "No hay mascotas registradas para eliminar");
        WebElement primeraFila = filasAntes.get(0);
        WebElement btnEliminar = primeraFila.findElement(By.xpath(".//td[last()]//button"));

        js.executeScript("arguments[0].click();", btnEliminar);
        esperar(2);
        try {
            WebElement botonSweetConfirm = wait.until(
                    ExpectedConditions.elementToBeClickable(
                            By.cssSelector("button.swal2-confirm")));
            js.executeScript("arguments[0].click();", botonSweetConfirm);
            esperar(2);

        } catch (Exception e) {
            Assert.fail("NO SE ENCONTRÓ EL BOTÓN 'Sí, eliminar' de SweetAlert2");
        }
        esperar(2);
        List<WebElement> filasDespues = driver.findElements(By.cssSelector("#petTable tbody tr"));
        int totalDespues = filasDespues.size();

        Assert.assertEquals(
                totalDespues,
                totalAntes - 1,
                "La mascota no fue eliminada correctamente");
    }

    private void esperar(int segundos) {
        try {
            TimeUnit.SECONDS.sleep(segundos);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }
}
