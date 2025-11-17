# 📋 PASOS PARA EJECUTAR EL PROYECTO GOCAN REACT

## ✅ PASO 1: Verificar Requisitos

Antes de comenzar, asegúrate de tener instalado:

1. **Node.js** (versión 16 o superior)
   - Verifica: Abre PowerShell y escribe `node --version`
   - Si no lo tienes, descárgalo de: https://nodejs.org/

2. **PostgreSQL** (versión 12 o superior)
   - Verifica que esté corriendo
   - Asegúrate de tener acceso a pgAdmin

## ✅ PASO 2: Restaurar la Base de Datos

1. Abre **pgAdmin**

2. Crea la base de datos (si no existe):
   - Click derecho en "Databases" → "Create" → "Database"
   - Nombre: `gocan`
   - Click en "Save"

3. La base de datos ya tiene los datos del backup que proporcionaste, así que no necesitas restaurar nada adicional.

## ✅ PASO 3: Configurar el Proyecto

1. Abre **PowerShell** o **CMD**

2. Navega a la carpeta del proyecto:
   ```powershell
   cd "C:\Users\jared\Downloads\Nueva carpeta\GoCanReact"
   ```

3. Verifica que el archivo `server/.env` tenga la configuración correcta:
   - Si tu usuario de PostgreSQL NO es `postgres` o tu contraseña NO es `admin`, edita el archivo `server/.env`

## ✅ PASO 4: Instalar Dependencias

En la carpeta del proyecto, ejecuta:

```powershell
npm run install-all
```

Este comando instalará todas las dependencias necesarias para el servidor y el cliente.

**Tiempo estimado:** 2-5 minutos dependiendo de tu conexión a internet.

## ✅ PASO 5: Ejecutar el Proyecto

### Opción A: Ejecutar Todo Junto (RECOMENDADO)

En la carpeta raíz del proyecto, ejecuta:

```powershell
npm run dev
```

Esto iniciará automáticamente:
- ✅ Backend (servidor) en: http://localhost:5000
- ✅ Frontend (aplicación React) en: http://localhost:5173

### Opción B: Ejecutar por Separado

Si prefieres tener más control, abre **DOS ventanas de PowerShell**:

**Ventana 1 - Backend:**
```powershell
cd "C:\Users\jared\Downloads\Nueva carpeta\GoCanReact\server"
npm run dev
```

**Ventana 2 - Frontend:**
```powershell
cd "C:\Users\jared\Downloads\Nueva carpeta\GoCanReact\client"
npm run dev
```

## ✅ PASO 6: Abrir la Aplicación

1. Abre tu navegador (Chrome, Edge, Firefox)

2. Ve a: **http://localhost:5173**

3. Deberías ver la página de inicio de GoCan

## 🔐 PASO 7: Iniciar Sesión

Usa uno de estos usuarios de prueba:

### Como Administrador:
- **Email:** elmer.gonzales@gmail.com
- **Contraseña:** admin123

### Como Doctor:
- **Email:** nadia.ormachea@gmail.com
- **Contraseña:** admin123

### Como Cliente:
- **Email:** imajesus08@gmail.com
- **Contraseña:** admin123

## 🎉 ¡LISTO!

Si todo funcionó correctamente, deberías poder:

- ✅ Iniciar sesión
- ✅ Ver el dashboard según tu rol
- ✅ Navegar por las diferentes secciones
- ✅ Crear citas, mascotas, reportes, etc.

## ❌ Problemas Comunes

### Problema 1: "Cannot find module"
**Solución:** Ejecuta nuevamente `npm run install-all`

### Problema 2: "Error connecting to database"
**Soluciones:**
1. Verifica que PostgreSQL esté corriendo
2. Abre pgAdmin y confirma que la base de datos `gocan` existe
3. Verifica las credenciales en `server/.env`

### Problema 3: "Port 5000 already in use"
**Solución:** 
1. Cierra cualquier otra aplicación que use el puerto 5000
2. O cambia el puerto en `server/.env` (cambia `PORT=5000` a `PORT=5001`)

### Problema 4: La página no carga
**Soluciones:**
1. Verifica que ambos servidores (backend y frontend) estén corriendo
2. Revisa la consola de PowerShell para ver si hay errores
3. Intenta refrescar la página (F5)

### Problema 5: "CORS error"
**Solución:** Asegúrate de que el frontend esté corriendo en `http://localhost:5173`

## 🛑 Para Detener el Proyecto

En la ventana de PowerShell donde está corriendo, presiona:

```
Ctrl + C
```

Luego confirma con `S` (Sí) o `Y` (Yes)

## 📞 Ayuda Adicional

Si tienes problemas:

1. Revisa los mensajes de error en la consola de PowerShell
2. Verifica que todos los pasos anteriores se hayan completado correctamente
3. Asegúrate de tener las versiones correctas de Node.js y PostgreSQL

## 🔄 Para Volver a Ejecutar

Cada vez que quieras usar la aplicación:

1. Abre PowerShell
2. Ve a la carpeta del proyecto
3. Ejecuta `npm run dev`
4. Abre http://localhost:5173 en tu navegador

---

**¡Disfruta usando GoCan Centro Veterinario!** 🐕🐈
