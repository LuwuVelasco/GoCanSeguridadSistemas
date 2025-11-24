// Dp - 3
import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.edge.EdgeDriver;
import org.openqa.selenium.edge.EdgeOptions;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.Assert;
import org.testng.annotations.AfterTest;
import org.testng.annotations.BeforeTest;
import org.testng.annotations.Test;

import java.time.Duration;
import java.util.concurrent.TimeUnit;

public class RegistroMascotaDoctorTest {

    private WebDriver driver;

    @BeforeTest
    public void setUp() {
        System.setProperty("webdriver.edge.driver", "C:\\drivers\\edgedriver\\msedgedriver.exe");

        EdgeOptions options = new EdgeOptions();
        driver = new EdgeDriver(options);

        driver.manage().window().maximize();
        driver.manage().timeouts().implicitlyWait(5, TimeUnit.SECONDS);
    }

    @AfterTest
    public void tearDown() {
        if (driver != null) {
            driver.quit();
        }
    }

    private void esperar(int segundos) {
        try {
            TimeUnit.SECONDS.sleep(segundos);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }

    @Test
    public void registrarNuevaMascota_exitoso() {

        // ============================================================
        // 1) PREPARACIÓN DE LA PRUEBA
        // ============================================================

        // Ir a la página de inicio de sesión
        String loginUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/login/sesionindex.html";
        driver.get(loginUrl);

        // Credenciales (las que me diste)
        String correoDoctor = "jaredpitiu1709@gmail.com";
        String passwordDoctor = "Jared.170209";

        WebElement campoCorreo = driver.findElement(By.id("email"));
        WebElement campoPassword = driver.findElement(By.id("password"));
        WebElement botonIngresar = driver.findElement(By.id("ingresarBtn"));

        campoCorreo.sendKeys(correoDoctor);
        campoPassword.sendKeys(passwordDoctor);

        // ============================================================
        // 2) LÓGICA DE LA PRUEBA
        //    - Iniciar sesión
        //    - Manejar alerta "Error al cargar las citas" si aparece
        //    - Ir a "Registro mascotas"
        //    - Llenar formulario y registrar
        // ============================================================

        botonIngresar.click();

        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(15));

        // 2.1 Manejar alerta inesperada (Error al cargar las citas) si aparece
        try {
            WebDriverWait waitAlert = new WebDriverWait(driver, Duration.ofSeconds(5));
            Alert alerta = waitAlert.until(ExpectedConditions.alertIsPresent());
            System.out.println("Alerta después de login: " + alerta.getText());
            alerta.accept();
        } catch (org.openqa.selenium.TimeoutException e) {
            // No apareció ninguna alerta, seguimos normal
        }

        // 2.2 Esperar a que se muestre el botón "Registro mascotas" (id="bt0")
        WebElement btnRegistroMascotas = wait.until(
                ExpectedConditions.elementToBeClickable(By.id("bt0"))
        );
        btnRegistroMascotas.click();

        // 2.3 Esperar que se abra el modal de registro de mascotas (id="petModal")
        WebElement modalMascota = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("petModal"))
        );

        // Formulario dentro del modal
        WebElement formMascota = modalMascota.findElement(By.id("petForm"));

        // Campos del formulario
        WebElement campoNombreMascota = formMascota.findElement(By.id("nombre_mascota"));
        WebElement campoFechaNac = formMascota.findElement(By.id("fecha_nacimiento"));
        WebElement selectTipo = formMascota.findElement(By.id("tipo"));
        WebElement campoRaza = formMascota.findElement(By.id("raza"));
        WebElement campoNombreProp = formMascota.findElement(By.id("nombre_propietario"));

        // Datos de prueba
        String nombreMascota = "MascotaPrueba_" + System.currentTimeMillis();
        String fechaNacimiento = "2022-01-01";  // formato YYYY-MM-DD

        // ⚠ IMPORTANTE:
        // Debe existir un usuario en la tabla `usuario` cuyo `nombre` sea EXACTAMENTE este.
        // Cambia esta cadena por el nombre real de un cliente que ya exista en tu BD.
        String nombrePropietarioExistente = "dpcito";

        campoNombreMascota.sendKeys(nombreMascota);

        // Seteamos la fecha directamente con JS para evitar problemas de formato
        ((JavascriptExecutor) driver)
                .executeScript("arguments[0].value = arguments[1];",
                        campoFechaNac, fechaNacimiento);

        Select tipoSelect = new Select(selectTipo);
        tipoSelect.selectByVisibleText("Perro"); // u otro valor válido

        campoRaza.sendKeys("Mestizo");
        campoNombreProp.sendKeys(nombrePropietarioExistente);

        // Botón "Registrar" (type="submit" dentro de #petForm)
        WebElement botonRegistrar = formMascota.findElement(By.cssSelector("button[type='submit']"));
        botonRegistrar.click();

        // ============================================================
        // 3) VERIFICACIÓN DEL RESULTADO ESPERADO (ASSERT)
        //    - Se muestra un SweetAlert de éxito:
        //        título: "Registrado"
        //        texto:  "Mascota registrada exitosamente."
        // ============================================================

        WebElement popupTitulo = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("swal2-title"))
        );
        WebElement popupTexto = driver.findElement(By.id("swal2-html-container"));

        String titulo = popupTitulo.getText();
        String texto = popupTexto.getText();

        System.out.println("Pop-up título: " + titulo);
        System.out.println("Pop-up texto: " + texto);

        Assert.assertEquals(
                titulo,
                "Registrado",
                "El título del pop-up no es el esperado al registrar mascota."
        );

        Assert.assertTrue(
                texto.toLowerCase().contains("mascota registrada exitosamente"),
                "El mensaje del pop-up no indica que la mascota fue registrada exitosamente."
        );
    }
}
