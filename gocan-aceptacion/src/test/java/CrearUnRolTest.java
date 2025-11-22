// Lu - 3
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
import org.openqa.selenium.Alert;
import java.time.Duration;

import java.util.concurrent.TimeUnit;

public class CrearUnRolTest {
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
    public void crearUnRol() {
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

        WebElement botonAdminRoles = driver.findElement(By.xpath("//*[@id=\"bt2\"]"));
        botonAdminRoles.click();

        esperar(3);

        WebElement botonAnadirRol = driver.findElement(By.id("addRoleButton"));   
        botonAnadirRol.click();

        esperar(3);

        WebElement campoNombreRol = driver.findElement(By.id("roleName"));
        campoNombreRol.sendKeys("Role Test");

        WebElement permisoHabilitadoEjemplo = driver.findElement(By.xpath("/html/body/div[3]/div/div[2]/form/div[2]/table/tbody/tr[1]/td[2]/input"));
        permisoHabilitadoEjemplo.click();

        WebElement botonCrearRol = driver.findElement(By.xpath("/html/body/div[3]/div/div[2]/form/div[3]/button[2]"));
        botonCrearRol.click();

        //3. Verificación
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(5));
        Alert alert = wait.until(ExpectedConditions.alertIsPresent());

        String alertText = alert.getText();
        System.out.println("Texto del alert: " + alertText);

        // En tu pantalla se ve "Rol creado con éxito."
        Assert.assertEquals(alertText, "Rol creado con éxito.");

        alert.accept();
    }

    private void esperar(int segundos) {
        try {
            TimeUnit.SECONDS.sleep(segundos);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }
}