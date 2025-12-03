// Dom - 2

import java.util.concurrent.TimeUnit;

import org.openqa.selenium.*;
import org.openqa.selenium.edge.EdgeDriver;
import org.openqa.selenium.edge.EdgeOptions;
import org.testng.Assert;
import org.testng.annotations.AfterTest;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Test;

public class CambiarUsuarioTest {
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

    /*
     * CASO DE PRUEBA: GC-64
     * Verificar que podamos acceder al inicio de sesión desde otra cuenta de usuario
     *
     * PRECONDICIONES:
     * - Tener buena conexión a Internet.
     * - Contar con un navegador web.
     * - Ingresar a la página GoCan.
     * - Conexión con la base de datos.
     * - Tener dos cuentas de usuario.
     */

    @Test
    public void cambiarUsuario_exitosa() {
        // ===========================================
        // PASO 1: Entrar al home mediante la URL
        // RESULTADO ESPERADO: Visualizar la pantalla de home.
        // ===========================================
        String baseUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(baseUrl);

        // Paso 2.- Lógica
        //Ir a la pantalla de inicio de sesión
        WebElement botonLogin = driver.findElement(By.xpath("/html/body/header/div/div/a/button"));
        botonLogin.click();
        espera(2);

        // Iniciar sesión con un usuario existente (Cuenta de Administrador)

        WebElement campoUsuario = driver.findElement(By.id("email"));
        campoUsuario.sendKeys("pruebota@gmail.com");

        WebElement campoPassword = driver.findElement(By.id("password"));
        campoPassword.sendKeys("choche123");

        WebElement botonIniciarSesion = driver.findElement(By.id("ingresarBtn"));
        botonIniciarSesion.click();

        espera(3);

        // Buscar y hacer clic en la opción de cambiar usuario en el submenú
        WebElement botonSubMenu = driver.findElement(By.xpath("/html/body/header/div/div"));
        botonSubMenu.click();
        espera(2);

        WebElement opcionCambiarUsuario = driver.findElement(By.xpath("//*[@id=\"profileDropdown\"]/ul/li[1]/a"));
        opcionCambiarUsuario.click();
        espera(3);

        //Iniciar sesion con otro usuario (Cuenta doctor)
        WebElement campoNuevoUsuario = driver.findElement(By.id("email"));
        campoNuevoUsuario.sendKeys("lolo@gmail.com");

        WebElement campoNuevaPassword = driver.findElement(By.id("password"));
        campoNuevaPassword.sendKeys("choche123");

        WebElement botonIniciarSesion2 = driver.findElement(By.id("ingresarBtn"));
        botonIniciarSesion2.click();

        espera(3);

        // Paso 3.- Verificación
        //Verificar que el primer boton sea registrar mascota
        //El doctor es el unico rol que tiene acceso a esta funcionalidad
        WebElement textoBotonRegistroMascota = driver.findElement(By.xpath("//*[@id=\"bt0\"]/h5"));
        Assert.assertTrue(textoBotonRegistroMascota.isDisplayed());
        Assert.assertEquals("Registro mascotas", textoBotonRegistroMascota.getText());
    }
}
