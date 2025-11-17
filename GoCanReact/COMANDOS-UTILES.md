# 🛠️ COMANDOS ÚTILES PARA GOCAN REACT

## 📦 Instalación

```bash
# Instalar todas las dependencias (recomendado)
npm run install-all

# O instalar manualmente
npm install                    # Dependencias raíz
cd server && npm install       # Dependencias del servidor
cd ../client && npm install    # Dependencias del cliente
```

## 🚀 Ejecución

```bash
# Ejecutar todo (frontend + backend)
npm run dev

# Ejecutar solo el servidor
npm run server
# O
cd server && npm run dev

# Ejecutar solo el cliente
npm run client
# O
cd client && npm run dev
```

## 🏗️ Build para Producción

```bash
# Build del frontend
cd client
npm run build

# El build estará en: client/dist/
```

## 🔍 Verificación

```bash
# Verificar versión de Node.js
node --version

# Verificar versión de npm
npm --version

# Verificar que PostgreSQL esté corriendo
# En Windows: abrir Services y buscar "PostgreSQL"
```

## 🗄️ Base de Datos

```bash
# Conectar a PostgreSQL (desde CMD/PowerShell)
psql -U postgres

# Listar bases de datos
\l

# Conectar a la base de datos gocan
\c gocan

# Listar tablas
\dt

# Ver datos de una tabla
SELECT * FROM usuario;

# Salir de psql
\q
```

## 🧹 Limpieza

```bash
# Eliminar node_modules y reinstalar
rm -rf node_modules
rm -rf server/node_modules
rm -rf client/node_modules
npm run install-all

# Limpiar caché de npm
npm cache clean --force
```

## 🐛 Debugging

```bash
# Ver logs del servidor en tiempo real
cd server
npm run dev
# Los logs aparecerán en la consola

# Ver logs del cliente
cd client
npm run dev
# Los logs aparecerán en la consola del navegador (F12)
```

## 📊 Información del Proyecto

```bash
# Ver estructura de carpetas
tree /F

# Ver tamaño de node_modules
du -sh node_modules

# Listar todos los scripts disponibles
npm run
```

## 🔄 Actualizar Dependencias

```bash
# Ver dependencias desactualizadas
npm outdated

# Actualizar todas las dependencias (con cuidado)
npm update

# Actualizar una dependencia específica
npm install nombre-paquete@latest
```

## 🌐 URLs Importantes

```
Frontend:        http://localhost:5173
Backend API:     http://localhost:5000
API Health:      http://localhost:5000/api/health
```

## 📝 Endpoints API Principales

```
# Autenticación
POST   /api/auth/login
POST   /api/auth/registro
POST   /api/auth/logout
GET    /api/auth/session

# Citas
GET    /api/citas
POST   /api/citas
GET    /api/citas/usuario/:id
DELETE /api/citas/:id

# Mascotas
GET    /api/mascotas
POST   /api/mascotas
GET    /api/mascotas/usuario/:id
PUT    /api/mascotas/:id
DELETE /api/mascotas/:id

# Reportes
GET    /api/reportes
POST   /api/reportes
PUT    /api/reportes/:id
DELETE /api/reportes/:id

# Usuarios
GET    /api/usuarios
POST   /api/usuarios/funcionario
DELETE /api/usuarios/:id

# Roles
GET    /api/roles
GET    /api/roles/usuario/:id

# Logs
GET    /api/logs/usuarios
GET    /api/logs/aplicacion

# Configuración
GET    /api/config/passwords
PUT    /api/config/passwords
```

## 🔧 Solución Rápida de Problemas

```bash
# Problema: Puerto ocupado
# Solución: Cambiar puerto en server/.env
PORT=5001

# Problema: Error de conexión a BD
# Solución: Verificar credenciales en server/.env
DB_HOST=localhost
DB_PORT=5432
DB_NAME=gocan
DB_USER=postgres
DB_PASSWORD=admin

# Problema: Módulos no encontrados
# Solución: Reinstalar dependencias
npm run install-all

# Problema: CORS error
# Solución: Verificar que frontend esté en puerto 5173
```

## 📱 Probar en Diferentes Dispositivos

```bash
# Ver IP local
ipconfig

# Acceder desde otro dispositivo en la misma red
http://TU_IP_LOCAL:5173

# Ejemplo:
http://192.168.1.100:5173
```

## 🎨 Desarrollo Frontend

```bash
# Crear nuevo componente
cd client/src/components
# Crear archivo: NuevoComponente.jsx

# Importar en donde lo necesites
import NuevoComponente from './components/NuevoComponente';
```

## 🔐 Seguridad

```bash
# Generar nueva SECRET_KEY para sesiones
# En Node.js REPL:
node
> require('crypto').randomBytes(32).toString('hex')
# Copiar el resultado a SESSION_SECRET en .env
```

## 📦 Agregar Nuevas Dependencias

```bash
# Backend
cd server
npm install nombre-paquete

# Frontend
cd client
npm install nombre-paquete
```

## 🎯 Testing (para implementar)

```bash
# Instalar Jest (backend)
cd server
npm install --save-dev jest

# Instalar React Testing Library (frontend)
cd client
npm install --save-dev @testing-library/react
```

## 📚 Recursos Útiles

- React Docs: https://react.dev
- Express Docs: https://expressjs.com
- PostgreSQL Docs: https://www.postgresql.org/docs
- Vite Docs: https://vitejs.dev

---

**Tip:** Guarda este archivo para referencia rápida durante el desarrollo! 💡
