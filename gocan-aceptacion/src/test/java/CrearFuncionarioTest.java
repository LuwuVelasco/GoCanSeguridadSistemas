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

    /*
     * CASO DE PRUEBA: Verificar el registro correcto de funcionarios
     */
    @Test
    public void crearUnFuncionario() {
        // ===============================================
        // PASO 1: PREPARACIÓN
        // ===============================================
        String loginUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(loginUrl);
        esperar(2);

        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================
        
        // PASO 1: En la pantalla del home, dirigirse al navbar y hacer clic en el ícono de la derecha.
        // RESULTADO ESPERADO: Visualizar la pantalla de inicio de sesión.
        WebElement botonLogin = driver.findElement(By.xpath("/html/body/header/div/div/a/button"));
        botonLogin.click();

        esperar(3);

        // PASO 2: Iniciar sesión con un usuario y contraseña de administrador correcto
        // RESULTADO ESPERADO: Visualizar la pantalla de administrador, donde se ve las opciones de
        // configuración de contraseñas, registro de funcionarios, administración de roles, la lista
        // de funcionarios, registro de usuarios en la página
        WebElement campoUsuario = driver.findElement(By.id("email"));
        campoUsuario.sendKeys("luwu@gmail.com");

        WebElement campoPassword = driver.findElement(By.id("password"));
        campoPassword.sendKeys("Aqmdla.1");

        WebElement botonIniciarSesion = driver.findElement(By.id("ingresarBtn"));
        botonIniciarSesion.click();

        esperar(3);

        // PASO 3: Entrar a la sección de registro funcionarios
        // RESULTADO ESPERADO: Se muestra una ventana emergente donde se puede visualizar campos para
        // rellenar como el nombre completo, correo, escoger el rol, etc
        WebElement botonRegistroFuncionarios = driver.findElement(By.xpath("//*[@id=\"bt1\"]"));
        botonRegistroFuncionarios.click();

        esperar(3);

        // PASO 4: Rellenar los campos de nombre, Correo, seleccionar la de veterinario y seleccionar
        // el rol del funcionario y registrar el funcionario
        // RESULTADO ESPERADO: Se registra correctamente al funcionario y se muestra en la lista de funcionarios
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

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Verificar que el funcionario fue registrado correctamente comprobando el mensaje de éxito
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
