export async function loadLogAplicacion(url, tbodySelector) {
  try {
      const response = await fetch(url);
      const logs = await response.json();

      console.log("Logs de aplicación obtenidos:", logs); // Depuración

      const tbody = document.querySelector(tbodySelector);
      tbody.innerHTML = ''; // Limpiar el contenido de la tabla

      logs.forEach(log => {
          const row = document.createElement('tr');
          row.innerHTML = `
              <td>${log.fecha_hora || 'N/A'}</td>
              <td>${log.nombre_usuario || 'N/A'}</td>
              <td>${log.accion || 'N/A'}</td>
              <td>${log.funcion_afectada || 'N/A'}</td>
              <td>${log.dato_modificado || 'N/A'}</td>
              <td>${log.descripcion || 'N/A'}</td>
              <td>${log.valor_original || 'N/A'}</td>
          `;
          tbody.appendChild(row);
      });
  } catch (error) {
      console.error("Error al cargar logs de aplicación:", error);

      Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Ocurrió un error al cargar el registro de acciones en la aplicación.',
      });
  }
}
