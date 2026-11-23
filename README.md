# Cursolandia

Aplicación web desarrollada como proyecto final de la materia **Programación III**, de la Tecnicatura Universitaria en Desarrollo Web de la Universidad Nacional de San Luis.

Cursolandia es una plataforma de gestión de cursos en la que los usuarios pueden crear cursos, inscribirse, participar en foros y gestionar sus perfiles.

## Tecnologías utilizadas

- PHP 8.0 o superior.
- MySQL 8.0 o superior.
- MySQLi para la conexión con la base de datos.
- HTML5.
- CSS3.
- Bootstrap 5.3.3.


## Funcionalidades

- Registro e inicio de sesión de usuarios.
- Gestión de perfiles e intereses.
- Creación y administración de cursos.
- Cursos públicos y privados.
- Inscripción a cursos gratuitos y pagos.
- Gestión de inscripciones y cupos.
- Sistema de etiquetas y recomendaciones.
- Foro de preguntas y respuestas por curso.
- Solicitud y gestión del plan Pro.
- Panel de administración.

## Estructura del proyecto

```text
Cursolandia/
├── Archivos/
│   ├── Comprobantes/
│   ├── Cursos/
│   └── Perfiles/
├── Bdd/
│   ├── cursolandia.sql
│   └── cursolandia_hosting.sql
├── Clases/
├── Conexiones/
│   └── conexion.php
├── Manejadores/
├── Paginas/
├── .env
├── .gitignore
├── .htaccess
├── index.php
└── README.md
```

## Requisitos

- Servidor web Apache.
- PHP 8.0 o superior.
- MySQL 8.0 o superior.
- Extensión `mysqli` habilitada en PHP.


## Instalación local

### 1. Obtener el proyecto

Clonar el repositorio:

```bash
git clone https://github.com/Jerombagnis/ProyectoProgIII-Cursolandia.git
```

Ingresar al directorio:

```bash
cd ProyectoProgIII-Cursolandia
```

### 2. Crear la base de datos

Importar el archivo SQL mediante el cliente de MySQL:

```bash
mysql -u usuario -p < Bdd/cursolandia.sql
```

Este archivo crea la base de datos `cursolandia`, sus tablas y los datos iniciales de prueba.

El usuario de MySQL debe tener permisos para crear bases de datos y tablas.

### 3. Configurar las credenciales

Crear un archivo `.env` en la raíz del proyecto con los siguientes datos:

```env
DB_HOST=localhost
DB_USER=tu_usuario
DB_PASSWORD=tu_contraseña
DB_NAME=cursolandia
```

Reemplazar los valores de ejemplo por las credenciales correspondientes al entorno local.

### 4. Configurar el servidor web

Configurar Apache para que el directorio del proyecto sea accesible desde el navegador.

Una vez iniciado Apache y MySQL, ingresar a la dirección local correspondiente.

El archivo `index.php` de la raíz redirige a `Paginas/index.php`.

## Cuentas de prueba

Todas las cuentas de prueba utilizan la contraseña:

`1234`

| Perfil | Correo electrónico | Tipo de cuenta |
|---|---|---|
| Administrador | admin@cursolandia.com | Administrador |
| Docente | pro@cursolandia.com | Usuario Pro |
| Usuario gratuito | user@cursolandia.com | Usuario gratuito |
| Docente adicional | docente2@cursolandia.com | Usuario Pro |
| Alumno 1 | alumno1@cursolandia.com | Usuario gratuito |
| Alumno 2 | alumno2@cursolandia.com | Usuario gratuito |

Estas cuentas y sus datos asociados se cargan al importar el archivo SQL.

## Base de datos para hosting

Para entornos de hosting que proporcionan una base de datos previamente creada y no permiten ejecutar `CREATE DATABASE` o `USE`, se incluye:

`Bdd/cursolandia_hosting.sql`

Este archivo contiene la misma estructura y los mismos datos que `Bdd/cursolandia.sql`, pero omite las instrucciones de creación y selección de la base de datos.

## Despliegue

La aplicación se encuentra desplegada en InfinityFree.

Para configurar otro entorno de hosting:

1. Crear una base de datos desde el panel del proveedor.
2. Importar `Bdd/cursolandia_hosting.sql`.
3. Configurar las variables de conexión en `.env`.
4. Subir los archivos de la aplicación al directorio público del hosting.
5. Verificar los permisos y la disponibilidad de los archivos necesarios.


## Consideraciones

- Los datos incluidos en la base de datos son datos de prueba.
- Las imágenes utilizadas son ilustrativas.
- Las cuentas de prueba comparten la misma contraseña.
- El sistema de contraseñas utiliza MD5 por compatibilidad con la implementación actual del proyecto.

## Autoría

Proyecto académico desarrollado para la materia Programación III.

**Universidad Nacional de San Luis**
Tecnicatura Universitaria en Desarrollo Web.