# Classia

**Classia** es una plataforma web orientada a la **gestión educativa y contratación de servicios vinculados a la educación**, desarrollada como proyecto académico integrador.

La propuesta busca reunir en un mismo entorno digital diferentes actores, recursos y servicios educativos, proporcionando una experiencia organizada, accesible y sencilla tanto para quienes desean aprender como para quienes ofrecen cursos o servicios especializados.

---

## 🎯 Objetivo del proyecto

El objetivo principal de Classia es desarrollar una plataforma capaz de centralizar distintas actividades relacionadas con la educación y los servicios educativos.

La plataforma busca facilitar procesos como:

- Acceso y registro de usuarios.
- Exploración de cursos y servicios.
- Inscripción y contratación.
- Gestión de perfiles.
- Seguimiento de cursos.
- Publicación y administración de servicios.
- Gestión de solicitudes.
- Valoraciones.
- Procesos de compra y pago.
- Interacción entre estudiantes, docentes/proveedores y administradores.

La finalidad es construir una solución que permita integrar estas funcionalidades dentro de una interfaz coherente, clara y fácil de utilizar.

---

## 👥 Roles de la plataforma

Classia contempla diferentes perfiles de usuario, cada uno con funcionalidades específicas.

### Estudiantes

Los estudiantes pueden explorar contenidos educativos, acceder a cursos, consultar actividades y gestionar recursos relacionados con su aprendizaje.

### Docentes / Proveedores

Este perfil puede crear, publicar y gestionar tanto **cursos** como **servicios educativos** dentro de la plataforma.

Entre sus posibilidades se encuentran:

- Crear y administrar cursos.
- Publicar y gestionar servicios.
- Recibir solicitudes o contrataciones.
- Gestionar el contenido asociado a sus publicaciones.
- Interactuar con los usuarios que acceden a sus cursos o servicios.
- Consultar y administrar su actividad dentro de la plataforma.

### Administradores

Los administradores se encargan de gestionar aspectos generales del sistema y supervisar su correcto funcionamiento.

---

## 🧩 Funcionalidades contempladas

El proyecto incluye interfaces y flujos relacionados con:

- Página principal.
- Inicio de sesión.
- Registro de usuarios.
- Recuperación y cambio de contraseña.
- Configuración inicial del usuario.
- Gestión de perfiles.
- Catálogo de cursos y servicios.
- Visualización detallada de servicios.
- Visualización de cursos.
- Solicitud de servicios personalizados.
- Carrito de compras.
- Pasarela de pago.
- Confirmación de compra.
- Sistema de valoraciones.
- Panel de docente/proveedor.

Estas funcionalidades forman parte de una implementación progresiva del sistema.

---

## 🛍️ Cursos y servicios educativos

Classia no está pensada únicamente como una plataforma de cursos.

También contempla la posibilidad de ofrecer y contratar diferentes **servicios vinculados al ámbito educativo**, permitiendo la interacción entre usuarios y docentes/proveedores.

Algunos ejemplos de servicios que puede soportar la plataforma son:

- Formación y capacitación.
- Mentorías.
- Proyectos educativos.
- Diseño de recursos didácticos.
- Diseño e impresión 3D.
- Robótica y automatización.
- Otros servicios especializados.

---

## 🖥️ Estructura general del proyecto

La estructura del proyecto se organiza principalmente de la siguiente manera:

```text
Proyecto/
│
├── index.html
├── html/
│   ├── login.html
│   ├── registro.html
│   ├── usuario.html
│   ├── catalogo.html
│   ├── curso.html
│   ├── servicioDetalle.html
│   ├── carrito.html
│   ├── pasarela_pago.html
│   ├── confirmacion.html
│   ├── valoracion.html
│   ├── panelProveedor.html
│   └── ...
├── css/
│   └── style.css
├── js/
│   └── script.js
├── assets/
├── scripts/
└── package.json
```

---

## 🛠️ Tecnologías utilizadas

El proyecto utiliza principalmente:

- **HTML5** para la estructura de las páginas.
- **CSS3** para estilos y diseño visual.
- **JavaScript** para interacción y comportamiento del lado del cliente.
- **Node.js** y **npm** como soporte para herramientas de desarrollo y validación.
- **Git** y **GitHub** para control de versiones y gestión del código fuente.

---

## ✅ Calidad y validación del código

El proyecto contempla el uso de herramientas de análisis y validación para mantener una estructura de código consistente.

Entre ellas pueden encontrarse:

- HTMLHint.
- HTML Validate.
- Stylelint.
- ESLint.
- Herramientas de análisis relacionadas con buenas prácticas y seguridad.

Para instalar las dependencias del proyecto:

```bash
npm install
```

Para ejecutar las validaciones disponibles:

```bash
npm run lint
```

---

## 🚀 Ejecución local

El proyecto puede ejecutarse mediante un servidor web local.

Por ejemplo:

```bash
npx http-server
```

Luego se debe acceder desde el navegador a la dirección indicada por el servidor.

También puede utilizarse cualquier otro servidor local compatible con HTML, CSS y JavaScript.

---

## 🎨 Principios de diseño

La interfaz de Classia busca mantener:

- Navegación clara.
- Consistencia visual.
- Buena jerarquía de información.
- Componentes reutilizables.
- Diseño adaptable a diferentes dispositivos.
- Accesibilidad.
- Retroalimentación visual.
- Organización clara de cursos y servicios.
- Experiencia de usuario sencilla e intuitiva.

---

## 🔐 Seguridad

El proyecto contempla progresivamente buenas prácticas relacionadas con:

- Autenticación.
- Gestión de contraseñas.
- Validación de datos.
- Protección de información.
- Control de acceso según roles.
- Gestión segura de sesiones.
- Revisión de dependencias.
- Aplicación de buenas prácticas de desarrollo.

La implementación completa de estos mecanismos dependerá de la integración con el backend y la base de datos.

---

## 📌 Estado del proyecto

Classia se encuentra actualmente **en desarrollo**.

La versión actual está enfocada principalmente en la construcción de:

- Interfaces.
- Navegación.
- Flujos de usuario.
- Componentes visuales.
- Diseño responsive.
- Organización de módulos.
- Validación del frontend.

Algunas funcionalidades pueden utilizar datos simulados mientras se desarrollan las siguientes etapas.

---

## 🔮 Desarrollo futuro

Entre las futuras incorporaciones se contempla:

- Backend.
- Base de datos.
- Autenticación real.
- Sistema completo de roles y permisos.
- Persistencia de usuarios.
- Persistencia de cursos y servicios.
- Gestión de contrataciones.
- Historial de operaciones.
- Sistema de notificaciones.
- Mejoras de accesibilidad.
- Mejoras de experiencia de usuario.
- Integración completa entre usuarios y docentes/proveedores.

---

## 📚 Contexto académico

Classia surge como un proyecto integrador cuyo propósito es aplicar conocimientos de diferentes áreas del desarrollo de software dentro de una solución común.

El proyecto permite trabajar de manera progresiva conceptos relacionados con:

- Análisis de requerimientos.
- Ingeniería de software.
- Diseño de interfaces.
- Desarrollo frontend.
- Programación.
- Bases de datos.
- Seguridad informática.
- Control de versiones.
- Documentación.
- Pruebas de software.
- Accesibilidad.
- Experiencia de usuario.

---

## 🌱 Visión del proyecto

La visión de Classia es evolucionar hacia un **ecosistema digital educativo** en el que aprendizaje, enseñanza y prestación de servicios puedan gestionarse desde una misma plataforma.

Más que funcionar únicamente como un catálogo de cursos, Classia busca explorar un modelo donde distintos actores del ámbito educativo puedan conectarse, compartir recursos, crear cursos, ofrecer servicios y gestionar sus actividades desde un entorno común.

---

## ⚠️ Aviso

Este proyecto se encuentra en desarrollo académico.

Determinadas funcionalidades, usuarios, cursos, servicios, valoraciones, precios y operaciones presentes en la plataforma pueden utilizar información ficticia o simulada exclusivamente con fines de desarrollo, prueba y demostración.
