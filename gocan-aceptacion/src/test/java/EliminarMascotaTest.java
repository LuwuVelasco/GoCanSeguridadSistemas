// Fer 3 - Eliminar mascota como doctor

import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
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
import java.util.List;
import java.util.concurrent.TimeUnit;

public class EliminarMascotaTest {

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
        if (driver != null) {
            driver.quit();
        }
    }

    /*
     * CASO DE PRUEBA:
     * Verificar la eliminación de una mascota siendo un doctor.
     *
     * PRECONDICIONES:
     * - Buena conexión a Internet .
     * - Contar con un navegador web (Edge).
     * - Ingreso a la página GoCan (coreUrl).
     * - Conexión con la base de datos 
     * - Tener una cuenta como doctor
     * - Haber iniciado sesión como doctor.
     * - Tener al menos una mascota registrada en la lista.
     */
    @Test
    public void eliminarMascotaComoDoctor() {

        // ===============================================
        // FASE 1: PREPARACIÓN
        // ===============================================

        // Prepa 1: Ingresar a la página principal del sistema GoCan.
        String coreUrl = "http://localhost/GoCanSeguridadSistemas/src/modules/core/";
        driver.get(coreUrl);

        // Prepa 2: Desde el home, hacer clic en el botón de login para ir a la pantalla de inicio de sesión.
        WebElement loginBtn = wait.until(
                ExpectedConditions.visibilityOfElementLocated(
                        By.xpath("/html/body/header/div/div/a/button")));
        js.executeScript("arguments[0].click();", loginBtn);
        esperar(2);

        // Prepa 3: Iniciar sesión con una cuenta de doctor 
        WebElement user = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("email")));
        user.sendKeys("lolo@gmail.com");

        WebElement pass = driver.findElement(By.id("password"));
        pass.sendKeys("12345");

        driver.findElement(By.id("ingresarBtn")).click();
        esperar(3);

        // ===============================================
        // FASE 2: LÓGICA DE PRUEBA
        // ===============================================

        // PASO 1: Hacer clic en el botón de “Información mascotas”.
        // Resultado esperado:
        //   Se muestra una lista de todas las mascotas registradas con todos sus datos
        //   con dos botones a la derecha: “editar” y “eliminar”.
        WebElement botonInfoMascotas = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("bt3")));
        botonInfoMascotas.click();
        esperar(2);

        // Verificar que se abrió el modal/lista de mascotas.
        WebElement tablaModal = wait.until(
                ExpectedConditions.visibilityOfElementLocated(By.id("tablaModal")));
        Assert.assertTrue(tablaModal.isDisplayed(),
                "No se abrió el modal de Información Mascotas");

        // Obtener las filas de la tabla de mascotas.
        List<WebElement> filasAntes = driver.findElements(By.cssSelector("#petTable tbody tr"));
        int totalAntes = filasAntes.size();
        Assert.assertTrue(totalAntes > 0,
                "No hay mascotas registradas para eliminar (precondición incumplida)");

        // Tomar la primera mascota de la lista para eliminarla.
        WebElement primeraFila = filasAntes.get(0);

        // Verificación básica de que en la última columna existan botones (editar / eliminar).
        WebElement ultimaCelda = primeraFila.findElement(By.xpath(".//td[last()]"));
        List<WebElement> botonesAccion = ultimaCelda.findElements(By.tagName("button"));
        Assert.assertTrue(
                botonesAccion.size() >= 1,
                "La última columna no contiene botones de acción para la mascota");

        // PASO 2:
        // Descripción: Hacer clic en el botón de “eliminar” junto a la mascota seleccionada.
        // Resultado esperado: Mensaje de confirmación de eliminación de mascota.
        // En este caso, suponemos que el botón de eliminar es el último botón de la celda.
        WebElement btnEliminar = botonesAccion.get(botonesAccion.size() - 1);
        js.executeScript("arguments[0].click();", btnEliminar);
        esperar(2);

        // Verificar que se muestre el mensaje de confirmación (SweetAlert2 de confirmación).
        WebElement tituloConfirmacion;
        WebElement cuerpoConfirmacion;
        try {
            tituloConfirmacion = wait.until(
                    ExpectedConditions.visibilityOfElementLocated(By.id("swal2-title")));
            cuerpoConfirmacion = driver.findElement(By.id("swal2-html-container"));

            Assert.assertTrue(tituloConfirmacion.isDisplayed(),
                    "No se mostró el título del mensaje de confirmación de eliminación");
            // Aquí podrías validar texto si lo conoces, por ejemplo:
            // Assert.assertTrue(tituloConfirmacion.getText().toLowerCase().contains("eliminar"));

        } catch (Exception e) {
            Assert.fail("No se mostró el mensaje de confirmación de eliminación de mascota");
            return;
        }

        // PASO 3:
        // Descripción: En el mensaje de confirmación hacer clic en el botón izquierdo "Sí, eliminar".
        // Resultado esperado:
        //   Mensaje de confirmación exitoso que dice: "Eliminado"
        //   y "La mascota fue eliminada correctamente."
        try {
            WebElement botonSweetConfirm = wait.until(
                    ExpectedConditions.elementToBeClickable(
                            By.cssSelector("button.swal2-confirm")));
            js.executeScript("arguments[0].click();", botonSweetConfirm);
            esperar(2);
        } catch (Exception e) {
            Assert.fail("NO SE ENCONTRÓ EL BOTÓN 'Sí, eliminar' de SweetAlert2");
            return;
        }

        // Verificar que aparezca el mensaje de confirmación exitosa de eliminación.
        try {
            WebElement tituloExito = wait.until(
                    ExpectedConditions.visibilityOfElementLocated(By.id("swal2-title")));
            WebElement cuerpoExito = driver.findElement(By.id("swal2-html-container"));

            Assert.assertTrue(tituloExito.isDisplayed(),
                    "No se mostró el popup de éxito después de eliminar la mascota");

            Assert.assertEquals(
                    tituloExito.getText(),
                    "Eliminado",
                    "El título del popup de éxito no es 'Eliminado'");

            Assert.assertEquals(
                    cuerpoExito.getText(),
                    "La mascota fue eliminada correctamente.",
                    "El mensaje del popup de éxito no coincide con lo esperado");

            // cerrar el popup de éxito.
            WebElement botonCerrarExito = wait.until(
                    ExpectedConditions.elementToBeClickable(
                            By.cssSelector("button.swal2-confirm")));
            js.executeScript("arguments[0].click();", botonCerrarExito);
            esperar(2);

        } catch (Exception e) {
            Assert.fail("No se encontró o no se pudo validar el mensaje de éxito de eliminación");
            return;
        }

        // PASO 4:
        // Descripción: Observar la lista de las mascotas.
        // Resultado esperado: Se verá que la mascota ya no está en los registros.
        List<WebElement> filasDespues = driver.findElements(By.cssSelector("#petTable tbody tr"));
        int totalDespues = filasDespues.size();

        Assert.assertEquals(
                totalDespues,
                totalAntes - 1,
                "La mascota no fue eliminada correctamente (la cantidad de registros no disminuyó)");
    }

    private void esperar(int segundos) {
        try {
            TimeUnit.SECONDS.sleep(segundos);
        } catch (InterruptedException e) {
            e.printStackTrace();
        }
    }
}
