export async function registrarCita(url, data) {
  try {
      const response = await fetch(url, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
      });

      const result = await response.json();
      if (result.error) {
          throw new Error(result.mensaje);
      }
      alert(`Cita registrada correctamente. ID: ${result.id_cita}`);
  } catch (error) {
      console.error("Error al registrar cita:", error);
      alert("Error al registrar cita: " + error.message);
  }
}
