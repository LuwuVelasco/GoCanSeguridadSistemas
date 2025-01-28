export function initConfigPasswordForm() {
  // Obtener el formulario del modal
  const configForm = document.getElementById('passwordConfigForm');
  if (!configForm) {
      console.error("No se encontró el formulario de configuración de contraseñas (#passwordConfigForm).");
      return;
  }

  // Manejar el submit del formulario
  configForm.addEventListener('submit', (e) => {
      e.preventDefault();

      // Desactivar el botón de submit para evitar duplicados
      const submitButton = configForm.querySelector("button[type='submit']");
      if (submitButton) submitButton.disabled = true;

      // Obtener valores del formulario
      const tiempoVidaUtil = document.getElementById('tiempoVidaUtil').value;
      const numeroHistorico = document.getElementById('numeroHistorico').value;

      console.log("Datos a enviar:", { tiempoVidaUtil, numeroHistorico });

      // Enviar datos al PHP
      fetch("http://localhost/GoCanSeguridadSistemas/src/modules/php/actualizar_config_password.php", {
          method: "POST",
          headers: {
              "Content-Type": "application/x-www-form-urlencoded"
          },
          body: `tiempo_vida_util=${encodeURIComponent(tiempoVidaUtil)}&numero_historico=${encodeURIComponent(numeroHistorico)}`
      })
      .then(response => response.json())
      .then(data => {
          console.log("Respuesta del servidor:", data);
          if (data.estado === "success") {
              Swal.fire({
                  icon: "success",
                  title: "Configuración actualizada",
                  text: data.mensaje
              });
              // Cerrar el modal
              const modalElement = document.getElementById("passwordConfigModal");
              if (modalElement) modalElement.style.display = "none";

              // Reiniciar el formulario
              configForm.reset();
          } else {
              Swal.fire({
                  icon: "error",
                  title: "Error",
                  text: data.mensaje
              });
          }
      })
      .catch(error => {
          console.error("Error al actualizar la configuración:", error);
          Swal.fire({
              icon: "error",
              title: "Error de red",
              text: "No se pudo actualizar la configuración de contraseñas."
          });
      })
      .finally(() => {
          if (submitButton) submitButton.disabled = false;
      });
  });
}
