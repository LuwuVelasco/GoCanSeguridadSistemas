# GoCan Centro Veterinario - Aplicación React

Sistema completo de gestión veterinaria desarrollado con React (frontend) y Node.js/Express (backend), conectado a PostgreSQL.

## 🚀 Características

- **Autenticación completa** con roles (Cliente, Doctor, Administrador)
- **Gestión de citas** médicas
- **Registro de mascotas** y sus datos
- **Reportes médicos** y recetas
- **Catálogo de productos**
- **Sistema de logs** de usuarios y aplicación
- **Gestión de roles y permisos**
- **Configuración de políticas de contraseñas**

## 📋 Requisitos Previos

- Node.js (v16 o superior)
- PostgreSQL (v12 o superior)
- npm o yarn

## 🔧 Instalación

### 1. Clonar o descargar el proyecto

```bash
cd GoCanReact
```

### 2. Instalar dependencias

```bash
# Instalar todas las dependencias (root, server y client)
npm run install-all
```

O manualmente:

```bash
# Instalar dependencias del root
npm install

# Instalar dependencias del servidor
cd server
npm install

# Instalar dependencias del cliente
cd ../client
npm install
```

### 3. Configurar la base de datos

1. Crear la base de datos en PostgreSQL:

```sql
CREATE DATABASE gocan;
```

2. Restaurar el backup proporcionado:

```bash
psql -U postgres -d gocan < backup.sql
```

O ejecutar el script SQL del backup directamente en pgAdmin.

### 4. Configurar variables de entorno

El archivo `.env` ya está configurado en `server/.env` con los siguientes valores:

```env
PORT=5000
DB_HOST=localhost
DB_PORT=5432
DB_NAME=gocan
DB_USER=postgres
DB_PASSWORD=admin

SESSION_SECRET=gocan_secret_key_2025
RECAPTCHA_SECRET=6Ldn970qAAAAANB2ogY4Ml1jVCvjt203gjG0jamr
NODE_ENV=development
```

**Importante:** Si tu configuración de PostgreSQL es diferente, modifica estos valores.

## 🎯 Ejecución

### Opción 1: Ejecutar todo junto (Recomendado)

Desde la carpeta raíz del proyecto:

```bash
npm run dev
```

Esto iniciará:
- Backend en `http://localhost:5000`
- Frontend en `http://localhost:5173`

### Opción 2: Ejecutar por separado

**Terminal 1 - Backend:**
```bash
cd server
npm run dev
```

**Terminal 2 - Frontend:**
```bash
cd client
npm run dev
```

## 👥 Usuarios de Prueba

Según el backup de la base de datos, puedes usar estos usuarios:

### Administrador
- **Email:** elmer.gonzales@gmail.com
- **Contraseña:** admin123 (o la que esté en el backup)

### Doctor
- **Email:** nadia.ormachea@gmail.com
- **Contraseña:** admin123

### Cliente
- **Email:** imajesus08@gmail.com
- **Contraseña:** admin123

**Nota:** Las contraseñas están hasheadas con bcrypt. La contraseña por defecto es `admin123`.

## 📁 Estructura del Proyecto

```
GoCanReact/
├── client/                 # Frontend React
│   ├── public/            # Archivos estáticos
│   ├── src/
│   │   ├── components/    # Componentes reutilizables
│   │   ├── context/       # Context API (AuthContext)
│   │   ├── pages/         # Páginas de la aplicación
│   │   │   ├── cliente/   # Dashboard del cliente
│   │   │   ├── doctor/    # Dashboard del doctor
│   │   │   └── admin/     # Dashboard del administrador
│   │   ├── services/      # API services
│   │   ├── App.jsx        # Componente principal
│   │   └── main.jsx       # Punto de entrada
│   └── package.json
│
├── server/                # Backend Node.js/Express
│   ├── config/           # Configuración (database)
│   ├── routes/           # Rutas de la API
│   │   ├── auth.js       # Autenticación
│   │   ├── citas.js      # Gestión de citas
│   │   ├── mascotas.js   # Gestión de mascotas
│   │   ├── reportes.js   # Reportes médicos
│   │   ├── catalogo.js   # Catálogo de productos
│   │   ├── usuarios.js   # Gestión de usuarios
│   │   ├── roles.js      # Roles y permisos
│   │   ├── logs.js       # Sistema de logs
│   │   └── config.js     # Configuración del sistema
│   ├── server.js         # Servidor principal
│   ├── .env              # Variables de entorno
│   └── package.json
│
├── package.json          # Scripts principales
└── README.md            # Este archivo
```

## 🔐 Funcionalidades por Rol

### Cliente
- ✅ Agendar citas médicas
- ✅ Registrar y gestionar mascotas
- ✅ Ver catálogo de productos
- ✅ Ver historial de citas
- ✅ Gestionar perfil

### Doctor
- ✅ Ver citas asignadas
- ✅ Crear y editar reportes médicos
- ✅ Ver mascotas registradas
- ✅ Gestionar recetas

### Administrador
- ✅ Gestionar usuarios (crear, eliminar)
- ✅ Gestionar roles y permisos
- ✅ Ver todas las citas
- ✅ Ver logs de usuarios
- ✅ Ver logs de aplicación
- ✅ Configurar políticas de contraseñas

## 🛠️ Tecnologías Utilizadas

### Frontend
- React 18
- React Router DOM
- Axios
- SweetAlert2
- Lucide React (iconos)
- CSS3

### Backend
- Node.js
- Express
- PostgreSQL (pg)
- bcrypt
- express-session
- helmet
- cors

## 📝 Scripts Disponibles

```bash
# Desarrollo
npm run dev              # Ejecutar frontend y backend
npm run client           # Solo frontend
npm run server           # Solo backend

# Instalación
npm run install-all      # Instalar todas las dependencias

# Producción
cd client && npm run build    # Build del frontend
cd server && npm start        # Iniciar servidor en producción
```

## 🔄 Migración desde PHP

Este proyecto es una migración completa del sistema PHP original a React + Node.js:

- ✅ Todas las funcionalidades PHP migradas a Node.js/Express
- ✅ Todas las vistas HTML migradas a componentes React
- ✅ Misma base de datos PostgreSQL (sin cambios)
- ✅ Mismos estilos visuales
- ✅ Todas las imágenes preservadas
- ✅ Sistema de autenticación mejorado
- ✅ API REST completa

## 🐛 Solución de Problemas

### Error de conexión a la base de datos
- Verifica que PostgreSQL esté corriendo
- Confirma las credenciales en `server/.env`
- Asegúrate de que la base de datos `gocan` exista

### Error de CORS
- Verifica que el frontend esté corriendo en `http://localhost:5173`
- El backend está configurado para aceptar peticiones de este origen

### Error de módulos no encontrados
- Ejecuta `npm run install-all` nuevamente
- Verifica que Node.js esté actualizado

## 📧 Contacto

Para soporte o consultas sobre el sistema, contacta al equipo de desarrollo.

## 📄 Licencia

Este proyecto es privado y confidencial.

---

**Desarrollado con ❤️ para GoCan Centro Veterinario**
