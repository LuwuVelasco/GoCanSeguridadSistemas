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
        // 1. Preparación
        // PASO 1: Ingresar al link de la página en el navegador
        String loginUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(loginUrl);
        esperar(2);
        // 2. Logica de la prueba 
        // PASO 2: En el navbar buscar el ícono de la persona
        // PASO 3: Hacer clic en el ícono
        WebElement botonLogin = driver.findElement(By.xpath("/html/body/header/div/div/a/button"));
        js.executeScript("arguments[0].click();", botonLogin);
        esperar(3);
        // PASO 4: Introducir credenciales y hacer clic en ingresar
        WebElement campoUsuario = driver.findElement(By.id("email"));
        campoUsuario.sendKeys("greysonwilliam119@gmail.com");
        WebElement campoPassword = driver.findElement(By.id("password"));
        campoPassword.sendKeys("krol.SMG12");
        WebElement botonIniciarSesion = driver.findElement(By.id("ingresarBtn"));
        botonIniciarSesion.click();
        esperar(3);
        manejarAlertSiExiste();
        // PASO 5: Buscar la opción Información mascotas
        WebElement botonInfoMascotas = driver.findElement(By.id("bt3"));
        botonInfoMascotas.click();
        esperar(2);
        // PASO 6: Hacer clic en la opción
        // (Se abre el modal con todas las mascotas registradas)
        WebElement botonEditar = driver.findElement(By.xpath("//table[@id='petTable']//tbody//tr[1]//td[7]//button"));
        botonEditar.click();
        esperar(2);
        // PASO 7: Presionar botón editar para abrir el formulario
        WebElement editNombreMascota = driver.findElement(By.id("edit_nombre_mascota"));
        WebElement editFechaNacimiento = driver.findElement(By.id("edit_fecha_nacimiento"));
        WebElement editTipo = driver.findElement(By.id("edit_tipo"));
        WebElement editRaza = driver.findElement(By.id("edit_raza"));
        WebElement editNombrePropietario = driver.findElement(By.id("edit_nombre_propietario"));
        // 3. Verificación
        // Verificación de campos obligatorios
        Assert.assertEquals(editNombreMascota.getAttribute("required"), "true");
        Assert.assertEquals(editFechaNacimiento.getAttribute("required"), "true");
        Assert.assertEquals(editTipo.getAttribute("required"), "true");
        Assert.assertEquals(editRaza.getAttribute("required"), "true");
        Assert.assertEquals(editNombrePropietario.getAttribute("required"), "true");
        // PASO 8: Eliminar información y tratar de guardar (debe dar error)
        editNombreMascota.clear();
        editRaza.clear();
        WebElement botonGuardar = driver.findElement(By.xpath("//form[@id='editForm']//button[@type='submit']"));
        botonGuardar.click();
        esperar(1);
        // Debe seguir abierto el modal
        WebElement modalEdicionAbierto = driver.findElement(By.id("editModal"));
        Assert.assertTrue(modalEdicionAbierto.isDisplayed());
        // Completar de nuevo para guardar correctamente
        editNombreMascota.sendKeys("Max Editado");
        editRaza.sendKeys("Golden Retriever");
        botonGuardar.click();
        esperar(2);
        WebElement botonConfirmarEdicion = driver.findElement(By.id("confirmEdit"));
        js.executeScript("arguments[0].click();", botonConfirmarEdicion);
        esperar(2);
        // Mensaje de éxito
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