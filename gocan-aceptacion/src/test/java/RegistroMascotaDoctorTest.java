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

// "Verificar el registro de una nueva mascota siendo un doctor."
public class RegistroMascotaDoctorTest {

    // Driver de edge
    private WebDriver driver;

    // ==============================
    // CONFIGURACIÓN GLOBAL DEL TEST
    // ==============================

    @BeforeTest
    public void setUp() {
        // ruta driver de Edge
        System.setProperty("webdriver.edge.driver", "C:\\drivers\\edgedriver\\msedgedriver.exe");

        EdgeOptions options = new EdgeOptions();
        driver = new EdgeDriver(options);

        // Maximiza la ventana
        driver.manage().window().maximize();
        // Configura una espera implícita de hasta 5 segundos
        driver.manage().timeouts().implicitlyWait(5, TimeUnit.SECONDS);
    }

    @AfterTest
    public void tearDown() {
        // driver inicializado, cierra el navegador al finalizar las pruebas
        if (driver != null) {
            driver.quit();
        }
    }

    // ayuda para pausas explícitas
    private void esperar(int segundos) {
        try {
            // Detiene el hilo N segundos
            TimeUnit.SECONDS.sleep(segundos);
        } catch (InterruptedException e) {
            // Si algo interrumpe el sleep, muestra el stack trace
            e.printStackTrace();
        }
    }

    // =====================================================
    // TEST PRINCIPAL: Registrar nueva mascota siendo doctor
    // =====================================================
    @Test
    public void registrarNuevaMascota_exitoso() {

        // ============================================================
        // 1) PREPARACIÓN DE LA PRUEBA
        // ============================================================

        // URL de goCan en localhost
        String loginUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/login/sesionindex.html";
        // Abre la URL de login en el navegador
        driver.get(loginUrl);

        // Credenciales de usuario con rol de doctor
        String correoDoctor = "jaredpitiu1709@gmail.com";
        String passwordDoctor = "Jared.170209";

        // Localiza el campo de correo por id="email"
        WebElement campoCorreo = driver.findElement(By.id("email"));
        // Localiza el campo de contraseña por id="password"
        WebElement campoPassword = driver.findElement(By.id("password"));
        // Localiza el botón de ingresar por id="ingresarBtn"
        WebElement botonIngresar = driver.findElement(By.id("ingresarBtn"));

        // Escribe el correo del doctor en el campo correspondiente
        campoCorreo.sendKeys(correoDoctor);
        // Escribe la contraseña del doctor
        campoPassword.sendKeys(passwordDoctor);

        // ============================================================
        // 2) LÓGICA DE LA PRUEBA
        //    - Iniciar sesión
        //    - Manejar alerta "Error al cargar las citas" si aparece
        //    - Ir a "Registro mascotas"
        //    - Llenar formulario y registrar
        // ============================================================

        // Hace clic en "Ingresar" para iniciar sesión como doctor
        botonIngresar.click();

        // Crea un WebDriverWait general con timeout de 15 segundos
        WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(15));

        // ----------------------------------------------------------
        // 2.1 Manejar alerta inesperada ("Error al cargar las citas")
        //     si aparece después del login
        // ----------------------------------------------------------
        try {
            // Crea un WebDriverWait más corto (5 segundos)
            WebDriverWait waitAlert = new WebDriverWait(driver, Duration.ofSeconds(5));
            // Espera hasta que haya un alert en la página
            Alert alerta = waitAlert.until(ExpectedConditions.alertIsPresent());
            // Imprime el texto de la alerta en consola
            System.out.println("Alerta después de login: " + alerta.getText());
            // Acepta la alerta (equivalente a pulsar "OK" en el pop-up nativo del navegador)
            alerta.accept();
        } catch (org.openqa.selenium.TimeoutException e) {
            // Si no aparece ninguna alerta en 5 segundos, se captura el TimeoutException
        }

        // ----------------------------------------------------------
        // Paso 1 del caso de prueba:
        // "Ya iniciada la sesión en el menú principal ingresar a 'Registro mascotas'.
        //  Se muestra un formulario con campos para la mascota como nombre,
        //  fecha de nacimiento, tipo de animal, raza y nombre de propietario."
        // ----------------------------------------------------------

        // 2.2 Esperar a que el botón "Registro mascotas" sea clickeable (bt0).
        WebElement btnRegistroMascotas = wait.until(
                ExpectedConditions.elementToBeClickable(By.id("bt0"))
        );
        // Hace clic en el botón y debería abrir el modal/formulario de registro de mascotas
        btnRegistroMascotas.click();

        // 2.3 Esperar a que se abra el modal de registro de mascotas con id="petModal"
        WebElement modalMascota = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("petModal"))
        );

        // Dentro del modal, localiza el formulario con id="petForm"
        WebElement formMascota = modalMascota.findElement(By.id("petForm"));

        // ----------------------------------------------------------
        // Aquí se cumple la parte del paso 1:
        // “Se muestra un formulario con campos para la mascota como nombre,
        //  fecha de nacimiento, tipo de animal, raza y nombre de propietario.”
        // Localizamos cada uno de esos campos.
        // ----------------------------------------------------------

        // Campo "nombre_mascota" dentro del formulario
        WebElement campoNombreMascota = formMascota.findElement(By.id("nombre_mascota"));
        // Campo "fecha_nacimiento" (input type="date" o similar)
        WebElement campoFechaNac = formMascota.findElement(By.id("fecha_nacimiento"));
        // Select para "tipo" de animal
        WebElement selectTipo = formMascota.findElement(By.id("tipo"));
        // Campo de texto para "raza"
        WebElement campoRaza = formMascota.findElement(By.id("raza"));
        // Campo de texto para "nombre_propietario"
        WebElement campoNombreProp = formMascota.findElement(By.id("nombre_propietario"));

        // ----------------------------------------------------------
        // Paso 2 del caso de prueba:
        // "Llenar el formulario y verificar que los campos del formulario
        //  estén correctos y presionar el botón de 'Registrar'.
        //  Se espera que se registre la nueva mascota."
        // ----------------------------------------------------------

        // Datos de prueba para la mascota
        String nombreMascota = "MascotaPrueba_" + System.currentTimeMillis();
        // Fecha de nacimiento
        String fechaNacimiento = "2022-01-01";

        // Debe usar un propietario existente.
        String nombrePropietarioExistente = "dpcito";

        // nombre de la mascota
        campoNombreMascota.sendKeys(nombreMascota);

        // Para la fecha, usamos JavaScript para asignar directamente el valor al input.
        ((JavascriptExecutor) driver)
                .executeScript(
                        "arguments[0].value = arguments[1];",
                        campoFechaNac,
                        fechaNacimiento
                );

        // Creamos un objeto Select para manejar el dropdown de tipo de animal
        Select tipoSelect = new Select(selectTipo);
        // Seleccionamos la opción visible "Perro"
        tipoSelect.selectByVisibleText("Perro");

        // Escribimos la raza de la mascota
        campoRaza.sendKeys("Mestizo");
        // Escribimos el nombre del propietario
        campoNombreProp.sendKeys(nombrePropietarioExistente);

        // Localizamos el botón "Registrar" dentro del formulario con el css selector
        WebElement botonRegistrar = formMascota.findElement(By.cssSelector("button[type='submit']"));
        // Clic en "Registrar" para enviar el formulario de registro de mascota
        botonRegistrar.click();

        // ============================================================
        // 3) VERIFICACIÓN DEL RESULTADO ESPERADO (ASSERT)
        //    Se espera un SweetAlert de éxito:
        //      título: "Registrado"
        //      texto:  "Mascota registrada exitosamente."
        // ============================================================

        // Esperamos a que aparezca el título del SweetAlert2 (id="swal2-title")
        WebElement popupTitulo = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("swal2-title"))
        );
        // Localizamos el cuerpo del mensaje en el contenedor HTML del SweetAlert2
        WebElement popupTexto = driver.findElement(By.id("swal2-html-container"));

        // Leemos el texto del título
        String titulo = popupTitulo.getText();
        // Leemos el texto del cuerpo del mensaje
        String texto = popupTexto.getText();

        // Imprimimos en consola
        System.out.println("Pop-up título: " + titulo);
        System.out.println("Pop-up texto: " + texto);

        // Primer assert:
        // Verificamos que el título del pop-up sea exactamente "Registrado"
        Assert.assertEquals(
                titulo,
                "Registrado",
                "El título del pop-up no es el esperado al registrar mascota."
        );

        // Segundo assert:
        // Verificamos que el texto del pop-up contenga la frase
        // "mascota registrada exitosamente"
        Assert.assertTrue(
                texto.toLowerCase().contains("mascota registrada exitosamente"),
                "El mensaje del pop-up no indica que la mascota fue registrada exitosamente."
        );
    }
}
