// Dom - 3
import java.util.List;
import java.util.concurrent.TimeUnit;

import org.openqa.selenium.*;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.edge.EdgeDriver;
import org.openqa.selenium.edge.EdgeOptions;
import org.testng.Assert;
import org.testng.annotations.AfterTest;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Test;

public class RegistroLogCrearFuncionarioTest {
    private WebDriver driver;
    
    @BeforeTest
    public void setUp() {
        System.setProperty("webdriver.edge.driver", "C:\\drivers\\edgedriver\\msedgedriver.exe");

        EdgeOptions options = new EdgeOptions();
        driver = new EdgeDriver(options);

        driver.manage().window().maximize();
        driver.manage().timeouts().implicitlyWait(5, java.util.concurrent.TimeUnit.SECONDS);
    }

    @AfterTest
    public void tearDown() {
        if (driver != null) {
            driver.quit();
        }
    }

    public void espera(int segundos) {
        try {
            TimeUnit.SECONDS.sleep(segundos);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    @Test
    public void registrarLogCrearFuncionario_exitosa() {
        // Paso 1.- Preparación
        String baseUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(baseUrl);

        //Ir a la pantalla de inicio de sesión
        WebElement botonLogin = driver.findElement(By.xpath("/html/body/header/div/div/a/button"));
        botonLogin.click();
        espera(2);

        // Paso 2.- Lógica
        // Iniciar sesión con un usuario existente

        WebElement campoUsuario = driver.findElement(By.id("email"));
        campoUsuario.sendKeys("pruebota@gmail.com");

        WebElement campoPassword = driver.findElement(By.id("password"));
        campoPassword.sendKeys("choche123");

        WebElement botonIniciarSesion = driver.findElement(By.id("ingresarBtn"));
        botonIniciarSesion.click();

        espera(3);

        List<WebElement> listaFuncionarioPrevia = driver.findElements(By.xpath("//*[@id='lista-veterinarios-table']/tbody/tr"));
        int tamanioListaPrevia = listaFuncionarioPrevia.size();

        WebElement botonRegistroFuncionarios = driver.findElement(By.xpath("//*[@id=\"bt1\"]"));
        botonRegistroFuncionarios.click();

        espera(3);

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

        WebElement botonCerrarMensaje = driver.findElement(By.xpath("/html/body/div[7]/div/div[6]/button[1]"));
        botonCerrarMensaje.click();

        espera(4);

        try {
            Alert alert = driver.switchTo().alert();
            System.out.println("Texto del alert: " + alert.getText());
            alert.accept();
        } catch (NoAlertPresentException e) {
            // No hay alerta presente, continuar con la ejecución normal
        }

        driver.navigate().refresh();
        espera(3);

        List<WebElement> listaFuncionarioActual = driver.findElements(By.xpath("//*[@id='lista-veterinarios-table']/tbody/tr"));
        int tamanioListaActual = listaFuncionarioActual.size();

        // Verificar que el tamaño de la lista actual sea igual al de la lista previa + 1
        Assert.assertEquals(tamanioListaActual, tamanioListaPrevia + 1);
    }
}
