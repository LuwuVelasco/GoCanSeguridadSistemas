import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.edge.EdgeDriver;
import org.openqa.selenium.edge.EdgeOptions;
import org.testng.Assert;
import org.testng.annotations.AfterTest;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Test;

import java.util.concurrent.TimeUnit;

public class EditarInformacionMascotaTest {
    private WebDriver driver;
    private JavascriptExecutor js;

    @BeforeTest
    public void setDriver() {
        System.setProperty("webdriver.edge.driver", "C:\\drivers\\edgedriver\\msedgedriver.exe");
        EdgeOptions options = new EdgeOptions();
        driver = new EdgeDriver(options);
        driver.manage().window().maximize();
        driver.manage().timeouts().implicitlyWait(10, TimeUnit.SECONDS);
        js = (JavascriptExecutor) driver;
    }

    @AfterTest
    public void closeDriver() {
        if (driver != null) {
            driver.quit();
        }
    }
    @Test
    public void verificarCamposObligatoriosEditarMascota() {
        //1. Preparación
        String loginUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(loginUrl);
        esperar(2);
        //2. Lógica de la prueba
        WebElement botonLogin = driver.findElement(By.xpath("/html/body/header/div/div/a/button"));
        js.executeScript("arguments[0].click();", botonLogin);
        esperar(3);
        WebElement campoUsuario = driver.findElement(By.id("email"));
        campoUsuario.sendKeys("greysonwilliam119@gmail.com");
        WebElement campoPassword = driver.findElement(By.id("password"));
        campoPassword.sendKeys("krol.SMG12");
        WebElement botonIniciarSesion = driver.findElement(By.id("ingresarBtn"));
        botonIniciarSesion.click();
        esperar(3);
        manejarAlertSiExiste();
        WebElement botonInfoMascotas = driver.findElement(By.id("bt3"));
        botonInfoMascotas.click();
        esperar(2);
        WebElement botonEditar = driver.findElement(By.xpath("//table[@id='petTable']//tbody//tr[1]//td[7]//button"));
        botonEditar.click();
        esperar(2);
        //3. Verificación - Campos obligatorios
        WebElement editNombreMascota = driver.findElement(By.id("edit_nombre_mascota"));
        WebElement editFechaNacimiento = driver.findElement(By.id("edit_fecha_nacimiento"));
        WebElement editTipo = driver.findElement(By.id("edit_tipo"));
        WebElement editRaza = driver.findElement(By.id("edit_raza"));
        WebElement editNombrePropietario = driver.findElement(By.id("edit_nombre_propietario"));
        Assert.assertEquals(editNombreMascota.getAttribute("required"), "true");
        Assert.assertEquals(editFechaNacimiento.getAttribute("required"), "true");
        Assert.assertEquals(editTipo.getAttribute("required"), "true");
        Assert.assertEquals(editRaza.getAttribute("required"), "true");
        Assert.assertEquals(editNombrePropietario.getAttribute("required"), "true");
        editNombreMascota.clear();
        editRaza.clear();
        WebElement botonGuardar = driver.findElement(By.xpath("//form[@id='editForm']//button[@type='submit']"));
        botonGuardar.click();
        esperar(1);
        WebElement modalEdicionAbierto = driver.findElement(By.id("editModal"));
        Assert.assertTrue(modalEdicionAbierto.isDisplayed());
        editNombreMascota.sendKeys("Max Editado");
        editRaza.sendKeys("Golden Retriever");
        botonGuardar.click();
        esperar(2);
        WebElement botonConfirmarEdicion = driver.findElement(By.id("confirmEdit"));
        js.executeScript("arguments[0].click();", botonConfirmarEdicion);
        esperar(2);
        WebElement mensajeExito = driver.findElement(By.xpath("//*[@id='swal2-title']"));
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