// Lu - 3
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

public class CrearFuncionarioTest {
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

    @Test
    public void crearUnFuncionario() {
        //1. Preparación
        String loginUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(loginUrl);

        //2. Lógica de la prueba
        WebElement botonLogin = driver.findElement(By.xpath("/html/body/header/div/div/a/button"));
        botonLogin.click();

        esperar(3);

        WebElement campoUsuario = driver.findElement(By.id("email"));
        campoUsuario.sendKeys("luwu@gmail.com");

        WebElement campoPassword = driver.findElement(By.id("password"));
        campoPassword.sendKeys("Aqmdla.1");

        WebElement botonIniciarSesion = driver.findElement(By.id("ingresarBtn"));
        botonIniciarSesion.click();

        esperar(3);

        WebElement botonRegistroFuncionarios = driver.findElement(By.xpath("//*[@id=\"bt1\"]"));
        botonRegistroFuncionarios.click();

        esperar(3);

        WebElement nombreFuncionario = driver.findElement(By.id("nombre"));
        nombreFuncionario.sendKeys("Funcionario Prueba");

        WebElement correoFuncionario = driver.findElement(By.id("correo"));
        correoFuncionario.sendKeys("funcionarioPrueba@gmail.com");

        WebElement esVeterinario = driver.findElement(By.id("esVeterinario"));
        esVeterinario.click();

        WebElement especialidad = driver.findElement(By.xpath("/html/body/div[4]/div/form/div[6]/select/option[2]"));
        especialidad.click();

        WebElement seleccionRol = driver.findElement(By.xpath("/html/body/div[4]/div/form/div[7]/select/option[3]"));
        seleccionRol.click();

        WebElement botonCrearFuncionario = driver.findElement(By.xpath("/html/body/div[4]/div/form/div[8]/button[2]"));
        botonCrearFuncionario.click();

        //3. Verificación
        WebElement mensajeExito = driver.findElement(By.xpath("//*[@id=\"swal2-title\"]"));
        Assert.assertTrue(mensajeExito.isDisplayed());
        Assert.assertEquals("Éxito", mensajeExito.getText());
    }

    private void esperar(int segundos) {
        try {
            TimeUnit.SECONDS.sleep(segundos);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }
}
