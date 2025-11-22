// Lu - 1
//import io.github.bonigarcia.wdm.WebDriverManager; no funcionó
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

    @Test
    public void iniciarSesion() {
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

        //3. Verificación
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

/*@BeforeTest
public void setDriver() {

    // 1. DRIVER LOCAL — fallback
    System.setProperty("webdriver.edge.driver", "C:\\drivers\\edgedriver\\msedgedriver.exe");

    // 2. WebDriverManager — si hay internet, se actualiza solo
    io.github.bonigarcia.wdm.WebDriverManager.edgedriver().setup();

    // 3. Crear driver
    EdgeOptions options = new EdgeOptions();
    driver = new EdgeDriver(options);

    driver.manage().window().maximize();
    driver.manage().timeouts().implicitlyWait(5, TimeUnit.SECONDS);
}
*/