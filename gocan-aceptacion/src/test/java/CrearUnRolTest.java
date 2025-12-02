// Lu - 2
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

    /*
     * CASO DE PRUEBA: Verificar el añadir un rol de la sección de administración de roles
     */
    @Test
    public void crearUnRol() {
        // ===============================================
        // PASO 1: PREPARACIÓN
        // ===============================================
        String loginUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(loginUrl);

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

        // PASO 3: Entrar a la sección de administración de roles
        // RESULTADO ESPERADO: Se visualiza una tabla donde se muestran todos los roles con las
        // acciones de editar y eliminar
        WebElement botonAdminRoles = driver.findElement(By.xpath("//*[@id=\"bt2\"]"));
        botonAdminRoles.click();

        esperar(3);

        // PASO 4: Presionar el botón de añadir rol
        // RESULTADO ESPERADO: Se muestra una ventana emergente donde se le dará el nombre al nuevo
        // rol y se podrá darle los permisos del sistema
        WebElement botonAnadirRol = driver.findElement(By.id("addRoleButton"));   
        botonAnadirRol.click();

        esperar(3);

        // PASO 5: Poner su nombre del nuevo rol y marcar los permisos y presionar el botón de guardar rol
        // RESULTADO ESPERADO: Se guarda el rol correctamente
        WebElement campoNombreRol = driver.findElement(By.id("roleName"));
        campoNombreRol.sendKeys("Role Test");

        WebElement permisoHabilitadoEjemplo = driver.findElement(By.xpath("/html/body/div[3]/div/div[2]/form/div[2]/table/tbody/tr[1]/td[2]/input"));
        permisoHabilitadoEjemplo.click();

        WebElement botonCrearRol = driver.findElement(By.xpath("/html/body/div[3]/div/div[2]/form/div[3]/button[2]"));
        botonCrearRol.click();

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================
        // Verificar que el rol fue creado correctamente esperando la alerta de confirmación
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