# Registro de Pruebas Funcionales — Classia · Segunda Entrega

## Resumen del Documento
Este documento registra las **pruebas funcionales realizadas** sobre la plataforma **Classia** durante la segunda entrega. Cubre las funcionalidades implementadas, los resultados obtenidos y las pruebas pendientes para los módulos en desarrollo.

> **Sprint:** Segunda entrega funcional y técnica  
> **Fecha de pruebas:** Septiembre 2026  
> **Ambiente:** Servidor local PHP integrado (`php -S localhost:8000`) + MariaDB/MySQL local

---

## Entorno de Pruebas

| Parámetro | Valor |
|:---|:---|
| Servidor web | PHP built-in server `php -S localhost:8000` |
| Motor de BD | MariaDB / MySQL |
| Base de datos | `classia_db` |
| Usuario BD | `classia_user` |
| Esquema | `sql/schema.sql` (importado completo) |
| Datos de prueba | Seeds incluidos en `schema.sql` (10 usuarios, 10 publicaciones, 10 solicitudes) |

---

## 1. Pruebas de Base de Datos

### 1.1 Importación del esquema DDL

| ID | Descripción | Resultado | Observaciones |
|:---:|:---|:---:|:---|
| P-BD-01 | Importar `sql/schema.sql` en base vacía | ✅ Pasó | Se crean las 9 tablas sin errores |
| P-BD-02 | Verificar integridad referencial (FKs) | ✅ Pasó | ON DELETE CASCADE y RESTRICT funcionan correctamente |
| P-BD-03 | Verificar datos semilla (roles, categorías, usuarios) | ✅ Pasó | 3 roles, 10 categorías, 10 usuarios de prueba |
| P-BD-04 | Verificar constraints UNIQUE (`email`, `nombre_rol`, `nombre_categoria`) | ✅ Pasó | INSERT duplicado retorna error 1062 |
| P-BD-05 | Verificar CHECK en `valoraciones.puntuacion` (1–5) | ✅ Pasó | Valores fuera de rango son rechazados |

### 1.2 Conexión PHP/MySQL (PDO)

| ID | Descripción | Resultado | Observaciones |
|:---:|:---|:---:|:---|
| P-BD-06 | Conexión PDO desde `config/database.php` | ✅ Pasó | Variables `.env` cargadas correctamente |
| P-BD-07 | Sentencias preparadas contra SQL Injection | ✅ Pasó | Parámetros bindeados en todos los controladores |
| P-BD-08 | Manejo de excepción `PDOException` | ✅ Pasó | `RuntimeException` genérica (no expone detalles al usuario) |

---

## 2. Pruebas de Registro de Usuarios

| ID | Descripción | Resultado | Observaciones |
|:---:|:---|:---:|:---|
| P-REG-01 | Registro con todos los campos completos y válidos | ✅ Pasó | INSERT en `usuarios`, redirige a `login.php?registro=exitoso` |
| P-REG-02 | Registro con email ya existente | ✅ Pasó | Muestra "No es posible registrar este correo" (sin confirmar existencia) |
| P-REG-03 | Registro con campo vacío | ✅ Pasó | Muestra "Todos los campos son obligatorios" |
| P-REG-04 | Registro con email inválido (sin @) | ✅ Pasó | Muestra "Ingresá un correo electrónico válido" |
| P-REG-05 | Registro con contraseña < 8 caracteres | ✅ Pasó | Muestra "La contraseña debe tener al menos 8 caracteres" |
| P-REG-06 | Registro con contraseña sin números | ✅ Pasó | Muestra validación de complejidad |
| P-REG-07 | Registro con contraseñas no coincidentes | ✅ Pasó | Muestra "Las contraseñas no coinciden" |
| P-REG-08 | Envío con token CSRF manipulado | ✅ Pasó | Muestra "La sesión del formulario expiró" |
| P-REG-09 | Verificación de hash en BD (`password_hash`, bcrypt) | ✅ Pasó | Hash almacenado con prefijo `$2y$` |

---

## 3. Pruebas de Autenticación (Login)

| ID | Descripción | Resultado | Observaciones |
|:---:|:---|:---:|:---|
| P-AUTH-01 | Vista de login renderiza correctamente | ✅ Pasó | `views/login.php` accesible |
| P-AUTH-02 | Autenticación contra BD | ✅ Implementado | `php/auth/procesar_login.php`, CSRF, contraseña y correo confirmado |
| P-AUTH-03 | Redirección por rol tras login | ✅ Implementado | Redirección backend según rol |
| P-AUTH-04 | Cierre de sesión (`logout.php`) | ✅ Pasó | Destruye `$_SESSION`, invalida cookie, redirige |
| P-AUTH-05 | Sesión expira al cerrar navegador | ✅ Pasó | Cookie de navegador fuera de localhost; excepción de desarrollo documentada |

---

## 4. Pruebas de Gestión de Publicaciones (Docente)

| ID | Descripción | Resultado | Observaciones |
|:---:|:---|:---:|:---|
| P-PUB-01 | Crear publicación con todos los campos válidos | ✅ Pasó | INSERT en `publicaciones`, redirige a panel con "creada" |
| P-PUB-02 | Crear publicación con precio negativo | ✅ Pasó | Muestra "El precio debe ser un número mayor a cero" |
| P-PUB-03 | Crear publicación con tipo inválido | ✅ Pasó | Muestra "El tipo de publicación seleccionado no es válido" |
| P-PUB-04 | Crear publicación con categoría inexistente | ✅ Pasó | Verifica con SELECT antes del INSERT |
| P-PUB-05 | Crear publicación con CSRF manipulado | ✅ Pasó | Error de sesión mostrado |
| P-PUB-06 | Editar publicación propia (datos completos) | ✅ Pasó | UPDATE con verificación de `id_usuario` |
| P-PUB-07 | Editar publicación de otro usuario | ✅ Pasó | "No tenés permisos para modificar esta publicación" |
| P-PUB-08 | Cambiar estado a Pausado | ✅ Pasó | UPDATE `estado='Pausado'`, confirma en panel |
| P-PUB-09 | Cambiar estado a Inactivo | ✅ Pasó | Ídem |
| P-PUB-10 | Cambiar estado a Activo | ✅ Pasó | Ídem |
| P-PUB-11 | Listado dinámico en panel proveedor | ✅ Pasó | SELECT PDO con datos reales de BD |

---

## 5. Pruebas de Vistas (Frontend)

| ID | Descripción | Resultado | Observaciones |
|:---:|:---|:---:|:---|
| P-UI-01 | Catálogo carga y muestra publicaciones | ✅ Pasó | `views/catalogo.php` |
| P-UI-02 | Detalle de servicio renderiza correctamente | ✅ Pasó | `views/servicio-detalle.php` |
| P-UI-03 | Formulario de solicitud de servicio | ✅ Pasó | `views/form-solicitar-servicio.php` — estructura completa |
| P-UI-04 | Formulario de solicitud 3D | ✅ Pasó | `views/solicitud-impresion-3d.php` |
| P-UI-05 | Carrito de compras | ✅ Pasó | `views/carrito.php` — estructura visual |
| P-UI-06 | Pasarela de pago simulada | ✅ Pasó | `views/pasarela-pago.php` |
| P-UI-07 | Panel administrador | ✅ Pasó | `views/panel-administrador.php` — estático |
| P-UI-08 | Política de privacidad | ✅ Pasó | `views/politica-privacidad.php` |
| P-UI-09 | Responsive design (mobile-first) | ✅ Pasó | CSS Grid + Flexbox + @media queries |
| P-UI-10 | Accesibilidad ARIA (atributos semánticos) | ✅ Pasó | Validado con HTMLHint y HTML Validate |

---

## 6. Pruebas de Seguridad

| ID | Descripción | Resultado | Observaciones |
|:---:|:---|:---:|:---|
| P-SEC-01 | Gitleaks — detección de secretos en repositorio | ✅ Pasó | CI/CD no detectó credenciales expuestas |
| P-SEC-02 | Semgrep SAST — análisis estático de código | ✅ Pasó | Sin vulnerabilidades reportadas |
| P-SEC-03 | Cabeceras CSP presentes | ✅ Pasó | Verificado en `panel-administrador.php` |
| P-SEC-04 | SQL Injection (parámetros en nombres de campos) | ✅ Pasó | Todas las consultas usan PDO prepare |
| P-SEC-05 | XSS en campos de formulario | ✅ Pasó | Datos tratados antes de renderizar |

---

## 7. Pruebas Pendientes (Backlog — Próxima Entrega)

| ID | Descripción | Módulo | Dependencia |
|:---:|:---|:---|:---|
| P-PEND-01 | Autenticación en dos pasos por correo/celular | `php/auth/` y `sql/migrations/` | Requiere desafío OTP, expiración y enrolamiento |
| P-PEND-02 | Envío y persistencia de solicitud de servicio | `php/solicitudes/` | Implementación pendiente |
| P-PEND-03 | Persistencia de contratación y pago | `php/contrataciones/`, `php/pagos/` | Implementación pendiente |
| P-PEND-04 | Emisión y listado de valoraciones | `php/valoraciones/` | Implementación pendiente |
| P-PEND-05 | Panel administrador con datos reales | `php/admin/` | No iniciado |
| P-PEND-06 | Redirección por rol tras login | `php/auth/login.php` | Requiere P-PEND-01 |

---

## Resumen de Estado

| Categoría de prueba | Total | ✅ Pasó | 🔄 Pendiente |
|:---|:---:|:---:|:---:|
| Base de datos | 8 | 8 | 0 |
| Registro de usuarios | 9 | 9 | 0 |
| Autenticación | 5 | 3 | 2 |
| Gestión de publicaciones | 11 | 11 | 0 |
| Vistas (frontend) | 10 | 10 | 0 |
| Seguridad | 5 | 5 | 0 |
| **Total** | **48** | **46** | **2** |
