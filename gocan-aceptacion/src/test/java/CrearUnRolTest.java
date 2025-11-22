import io.github.bonigarcia.wdm.WebDriverManager;
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

public class CrearUnRolTest {
    private WebDriver driver;

    @BeforeTest
    public void setDriver() {
        // Configurar EdgeDriver automáticamente
        WebDriverManager.edgedriver().setup();
        EdgeOptions options = new EdgeOptions();
        driver = new EdgeDriver(options);

        driver.manage().window().maximize();
        // Espera implícita pequeña
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
        
    }
}
