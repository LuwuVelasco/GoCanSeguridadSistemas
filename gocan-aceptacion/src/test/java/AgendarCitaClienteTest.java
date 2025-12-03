// Fer 1 - Agendar cita como cliente
import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.TimeoutException;
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

import java.time.Duration;
import java.time.LocalDate;
import java.util.List;
import java.util.concurrent.TimeUnit;

public class AgendarCitaClienteTest {

    private WebDriver driver;
    private WebDriverWait wait;
    private JavascriptExecutor js;

    @BeforeTest
    public void setDriver() {
        System.setProperty("webdriver.edge.driver", "C:\\drivers\\edgedriver\\msedgedriver.exe");

        EdgeOptions options = new EdgeOptions();
        driver = new EdgeDriver(options);

        driver.manage().window().maximize();
        driver.manage().timeouts().implicitlyWait(5, TimeUnit.SECONDS);

        wait = new WebDriverWait(driver, Duration.ofSeconds(10));
        js = (JavascriptExecutor) driver;
    }

    @AfterTest
    public void closeDriver() {
        if (driver != null) driver.quit();
    }

    /*
     * CASO DE PRUEBA: GC-1
     * Verificar que es posible agendar una cita como cliente.
     *
     * PRECONDICIONES:
     * - Tener buena conexión a Internet.
     * - Contar con un navegador web.
     * - Ingresar a la página GoCan.
     * - Conexión con la base de datos.
     * - Tener una cuenta como cliente.
     */

        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================

    @Test
    public void agendarCitaComoCliente() {

        // ===========================================
        // PASO 1: Entrar al home y hacer clic en el ícono del login
        // RESULTADO ESPERADO: Visualizar la pantalla de inicio de sesión.
        // ===========================================

        driver.get("http://localhost/GoCanSeguridadSistemas/src/modules/core/");

        WebElement botonLogin = wait.until(ExpectedConditions.visibilityOfElementLocated(
                By.xpath("/html/body/header/div/div/a/button")));
        js.executeScript("arguments[0].click();", botonLogin);
        esperar(2);

        // Validar que aparece el campo de email → pantalla login cargada
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("email")));


        // ===========================================
        // PASO 2: Iniciar sesión con usuario cliente válido
        // RESULTADO ESPERADO: Visualizar pantalla de cliente con opción Agendar Cita
        // ===========================================

        WebElement campoUsuario = driver.findElement(By.id("email"));
        campoUsuario.sendKeys("imajesus08@gmail.com");

        WebElement campoPassword = driver.findElement(By.id("password"));
        campoPassword.sendKeys("12345");

        driver.findElement(By.id("ingresarBtn")).click();
        esperar(3);

        WebElement botonAgendarCita = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("bt0")));

        Assert.assertTrue(botonAgendarCita.isDisplayed(),
                "No se encontró botón Agendar Cita tras iniciar sesión.");


        // ===========================================
        // PASO 3: Hacer clic en “Agendar Cita”
        // RESULTADO ESPERADO: Se muestra el pop-up con campos de reserva
        // ===========================================

        botonAgendarCita.click();
        esperar(2);

        WebElement modalReserva = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("reserveModal")));

        Assert.assertTrue(modalReserva.isDisplayed(),
                "No se mostró el modal de agendamiento.");


        // ===========================================
        // PASO 4: Llenar los datos del pop-up y reservar
        // RESULTADO ESPERADO: Visualizar mensaje de reserva exitosa
        // ===========================================

        WebElement campoPropietario = driver.findElement(By.id("propietario"));
        if (campoPropietario.getAttribute("value").isEmpty()) {
            campoPropietario.sendKeys("Jesus");
        }
        esperar(1);

        driver.findElement(By.xpath("//*[@id='especialidad']/option[2]")).click();
        esperar(1);

        driver.findElement(By.xpath("//*[@id='doctor']/option[4]")).click();
        esperar(1);

        LocalDate fechaManana = LocalDate.now().plusDays(1);
        String fechaTexto = fechaManana.format(java.time.format.DateTimeFormatter.ofPattern("dd/MM/yyyy"));
        driver.findElement(By.id("fecha")).sendKeys(fechaTexto);
        esperar(1);

        driver.findElement(By.id("hora")).sendKeys("10:57");
        esperar(1);

        driver.findElement(By.id("reservar")).click();


        // Validación del mensaje de éxito 
        try {
            Alert alert = new WebDriverWait(driver, Duration.ofSeconds(5))
                    .until(ExpectedConditions.alertIsPresent());

            String text = alert.getText().toLowerCase();
            alert.accept();

            Assert.assertTrue(
                text.contains("cita") || text.contains("reserva") || text.contains("correctamente"),
                "El mensaje no indica éxito."
            );

        } catch (TimeoutException e) {

            WebElement titulo = wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("swal2-title")));
            Assert.assertEquals(titulo.getText(), "Éxito");

            WebElement btnCerrar = wait.until(ExpectedConditions.elementToBeClickable(
                    By.cssSelector("button.swal2-confirm")));
            btnCerrar.click();
        }

        // ===============================================
        // FASE 3: ASSERT O VERIFICACIÓN
        // ===============================================


        // ===========================================
        // PASO 5: Ir al botón “Reservas”
        // RESULTADO ESPERADO: Se visualiza la cita recién agendada
        // ===========================================

        WebElement botonReservas = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("bt1")));
        botonReservas.click();
        esperar(2);

        List<WebElement> filas = driver.findElements(By.cssSelector("#tablaReservas tbody tr"));

        Assert.assertTrue(filas.size() > 0, "No hay reservas registradas.");

        boolean encontrada = filas.stream()
                .anyMatch(fila -> fila.getText().toLowerCase().contains("jesus")
                        && fila.getText().contains(fechaTexto));

        Assert.assertTrue(encontrada, "La reserva no aparece en la lista.");
    }

    private void esperar(int segundos) {
        try { TimeUnit.SECONDS.sleep(segundos); }
        catch (InterruptedException e) { e.printStackTrace(); }
    }
}
