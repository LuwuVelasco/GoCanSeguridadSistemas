// Fer 1 - Agendar cita como cliente
import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
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
import java.util.concurrent.TimeUnit;

public class AgendarCitaClienteTest {

    private WebDriver driver;
    private WebDriverWait wait;

    @BeforeTest
    public void setDriver() {
        System.setProperty("webdriver.edge.driver", "C:\\drivers\\edgedriver\\msedgedriver.exe");

        EdgeOptions options = new EdgeOptions();
        driver = new EdgeDriver(options);

        driver.manage().window().maximize();
        driver.manage().timeouts().implicitlyWait(5, TimeUnit.SECONDS);

        wait = new WebDriverWait(driver, Duration.ofSeconds(10));
    }

    @AfterTest
    public void closeDriver() {
        if (driver != null) {
            driver.quit();
        }
    }

    @Test
    public void agendarCitaComoCliente() {
        String homeUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(homeUrl);

        // 1) Ir a login
        WebElement botonLogin = driver.findElement(By.xpath("/html/body/header/div/div/a/button"));
        botonLogin.click();
        esperar(2);

        // 2) Iniciar sesión como cliente
        WebElement campoUsuario = driver.findElement(By.id("email"));
        campoUsuario.sendKeys("imajesus08@gmail.com");

        WebElement campoPassword = driver.findElement(By.id("password"));
        campoPassword.sendKeys("12345");

        WebElement botonIniciarSesion = driver.findElement(By.id("ingresarBtn"));
        botonIniciarSesion.click();
        esperar(3);

        // 3) Abrir modal de Agendar Cita
        WebElement botonAgendarCita = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("bt0"))
        );
        Assert.assertTrue(botonAgendarCita.isDisplayed(), "No se encontró el botón Agendar Cita");
        botonAgendarCita.click();
        esperar(2);

        WebElement modalReserva = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("reserveModal"))
        );
        Assert.assertTrue(modalReserva.isDisplayed(), "El modal de reserva no se mostró");

        // 4) Llenar formulario
        WebElement campoPropietario   = driver.findElement(By.id("propietario"));
        WebElement selectEspecialidad = driver.findElement(By.id("especialidad"));
        WebElement selectDoctor       = driver.findElement(By.id("doctor"));
        WebElement campoFecha         = driver.findElement(By.id("fecha"));
        WebElement campoHora          = driver.findElement(By.id("hora"));
        WebElement botonReservar      = driver.findElement(By.id("reservar"));

        if (campoPropietario.getAttribute("value") == null ||
            campoPropietario.getAttribute("value").isEmpty()) {
            campoPropietario.sendKeys("Jesus");
        }

        // Esperar carga de especialidades
        esperar(3);
        WebElement opcionEspecialidad = driver.findElement(
                By.xpath("//*[@id='especialidad']/option[2]")
        );
        opcionEspecialidad.click();

        // Esperar y seleccionar doctor
        esperar(3);
        WebElement opcionDoctor = driver.findElement(
                By.xpath("//*[@id='doctor']/option[6]")
        );
        opcionDoctor.click();

        // Fecha futura (mañana)
        LocalDate fechaManana = LocalDate.now().plusDays(1);
        String fechaTexto = fechaManana.toString(); // YYYY-MM-DD
        campoFecha.sendKeys(fechaTexto);

        // Hora (elige una, aquí 17:45)
        campoHora.sendKeys("17:45");

        // 5) Reservar
        botonReservar.click();

        // 5.1) MANEJAR POSIBLE alert() DE ERROR
        try {
            WebDriverWait waitAlert = new WebDriverWait(driver, Duration.ofSeconds(5));
            Alert alert = waitAlert.until(ExpectedConditions.alertIsPresent());
            String alertText = alert.getText();
            alert.accept();

            // Si aparece un alert, consideramos que la prueba debe fallar,
            // pero ya no revienta con UnhandledAlertException.
            Assert.fail("Se mostró un alert de error en lugar de reserva exitosa: " + alertText);

        } catch (TimeoutException e) {
            // No apareció alert() de JS -> seguimos con el SweetAlert2 de éxito
        }

        // 6) Verificar SweetAlert2 de éxito
        WebElement tituloPopup = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("swal2-title"))
        );
        WebElement cuerpoPopup = driver.findElement(By.id("swal2-html-container"));

        Assert.assertTrue(tituloPopup.isDisplayed(), "No se mostró el popup de éxito");
        Assert.assertEquals(tituloPopup.getText(), "Éxito");

        String mensaje = cuerpoPopup.getText();
        System.out.println("Mensaje de la reserva: " + mensaje);

        Assert.assertTrue(
                mensaje.toLowerCase().contains("cita") ||
                mensaje.toLowerCase().contains("reserva"),
                "El mensaje no parece ser de reserva exitosa"
        );
    }

    private void esperar(int segundos) {
        try {
            TimeUnit.SECONDS.sleep(segundos);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }
}
