# 🛡️ Política de Seguridad y Cumplimiento — Classia (AniTech)

Este documento establece las políticas, controles automatizados, marcos de referencia normativos y procesos de reporte de seguridad aplicados en **Classia**, la plataforma Entorno Virtual de Aprendizaje (EVA) desarrollada por **AniTech**.

AniTech integra la seguridad como una responsabilidad transversal a todo el ciclo de vida del desarrollo de software mediante un enfoque **DevSecOps**.

---

## 1. Requerimientos No Funcionales de Seguridad (SRS)

La arquitectura y desarrollo de Classia cumplen con los siguientes requerimientos no funcionales definidos en el *Documento de Requerimientos de Software (SRS)*:

### 🔐 Seguridad Técnica y de Aplicación
- **`RNF-01` (Almacenamiento seguro de credenciales):** Las contraseñas se almacenan únicamente utilizando mecanismos de hash seguros con salt (bcrypt / Argon2), nunca en texto plano.
- **`RNF-02` (Control de acceso basado en roles - RBAC):** Implementación del principio de mínimo privilegio diferenciando permisos para Administradores, Docentes y Alumnos.
- **`RNF-03` (Mitigación OWASP):** Aplicación de recomendaciones del **OWASP Top 10** para prevenir inyecciones, fallas criptográficas y control de acceso roto. Verificación referenciada en **OWASP ASVS** (*Application Security Verification Standard*).
- **`RNF-07` (Pagos y alcance PCI-DSS):** Delegación del procesamiento de pagos en una pasarela externa certificada (ej. MercadoPago / Stripe). El sistema no almacena ni procesa datos completos de tarjetas de crédito o débito, reduciendo el alcance normativo PCI-DSS.
- **`RNF-08` (Revisión obligatoria de código):** Todo cambio en el código fuente debe someterse a una revisión de seguridad y pares antes de integrarse a las ramas principales (`main` / `Testing`).
- **`RNF-09` (Seguridad ofensiva prudente):** Pruebas de simulación de ataques (pentesting) restringidas únicamente a entornos de prueba propios o autorizados expresamente.
- **`RNF-24` (Auditoría de eventos):** Registro de eventos críticos (accesos, entregas, calificaciones y cambios administrativos) para trazabilidad ante incidentes.

---

## 2. Privacidad y Cumplimiento Normativo (Uruguay e Internacional)

Classia se diseña respetando el marco legal aplicable al tratamiento de datos personales y académicos:

- **`RNF-10` (Ley N.º 18.331 y URCDP):** Cumplimiento de la *Ley de Protección de Datos Personales y Acción de Habeas Data* de Uruguay (Ley 18.331 y Decreto 414/009), contemplando la inscripción de bases de datos ante la *Unidad Reguladora y de Control de Datos Personales (URCDP)*.
- **`RNF-11` (Derechos ARCO):** Garantía para que estudiantes, docentes y administradores ejerzan sus derechos de **A**cceso, **R**ectificación, **C**ancelación/Supresión y **O**posición sobre sus datos personales.
- **`RNF-12` (Protección de menores de edad):** Aplicación de criterios reforzados de minimización de datos recolectados para usuarios menores de edad en entornos educativos.
- **`RNF-13` (Buenas prácticas internacionales - GDPR):** Adopción complementaria de los principios del *Reglamento General de Protección de Datos (GDPR/RGPD)* de la Unión Europea como referencia de privacidad por diseño (*Privacy by Design*).
- **`RNF-14` (Confidencialidad):** Acceso restringido a datos personales y legajos académicos solo para usuarios explícitamente autorizados.

---

## 3. Controles Automatizados en CI/CD (GitHub Actions)

El repositorio ejecuta escaneos de seguridad automatizados en cada Pull Request y push a ramas principales:

| Control / Dominio | Herramienta | Workflow | Descripción |
|---|---|---|---|
| **Detección de Secretos** | Gitleaks | `.github/workflows/security.yml` | Escaneo automático para evitar credenciales, tokens API o llaves en el código. |
| **Gestión de Vulnerabilidades** | `npm audit` + Dependabot | `.github/workflows/security.yml` / `dependabot.yml` | Identificación y actualización automática de dependencias con vulnerabilidades (CVE). |
| **Análisis Estático SAST** | Semgrep (`p/owasp-top-ten`) | `.github/workflows/security.yml` | Detección de patrones de código inseguro basados en OWASP Top 10. |
| **Seguridad Frontend & CSP** | Script `check-security-headers.sh` | `.github/workflows/security.yml` | Verificación de Content-Security-Policy (CSP) y prevención de `eval()`, `innerHTML` inseguro y `tabnabbing`. |
| **Accesibilidad & Calidad** | Lighthouse CI | `.github/workflows/lighthouse.yml` | Verificación de contraste, accesibilidad WCAG AA (`RNF-18`) y rendimiento (`RNF-15`). |
| **Trazabilidad de Requerimientos** | Check SRS Reference | `.github/workflows/check-req-reference.yml` | Validación obligatoria de referencias SRS (`REQ-XXX-NN`, `RNF-NN`, `RN-NN`) en PRs. |

---

## 4. Matriz de Mapeo de Marcos de Ciberseguridad

Para garantizar una postura de seguridad robusta, AniTech mapea sus prácticas de desarrollo contra marcos reconocidos internacionalmente:

| Framework | Control / Función | Implementación en Classia |
|---|---|---|
| **CIS Controls v8** | Control 3: Protección de datos sensibles | Clasificación de información, minimización de datos y política de no secretos en repo. |
| **CIS Controls v8** | Control 4: Configuración segura de activos | Cabeceras HTTP de seguridad (CSP, HSTS, X-Content-Type-Options) y deshabilitación de scripts inseguros. |
| **CIS Controls v8** | Control 5: Gestión de cuentas y accesos | Principio de mínimo privilegio (RBAC) y control de acceso al repo mediante GitHub Teams. |
| **CIS Controls v8** | Control 16: Seguridad del software | Pipeline DevSecOps (SAST + SCA + Linting + CODEOWNERS + PR review). |
| **NIST CSF** | **Identify (Identificar)** | Inventario de dependencias en `package.json`, activos estáticos e interfaces externas. |
| **NIST CSF** | **Protect (Proteger)** | Protección de ramas (`main`, `Testing`), HTTPS obligatorio y hash de contraseñas (`RNF-01`). |
| **NIST CSF** | **Detect (Detectar)** | Monitoreo continuo vía Dependabot, alertas de seguridad de GitHub y Semgrep programado. |
| **NIST CSF** | **Respond & Recover** | Plan de rollback de código vía Git, backups de configuración y gestión de incidentes AniTech. |
| **ISO/IEC 27001:2022** | A.5.15 / A.8.28 (Control de acceso y desarrollo seguro) | Revisiones obligatorias por asignados de CODEOWNERS y trazabilidad de commits. |
| **OWASP ASVS v4.0** | Nivel 1 / Nivel 2 de verificación | Cobertura de controles de autenticación, gestión de sesiones y sanitización de entradas. |

---

## 5. Reglas de Protección de Ramas y CODEOWNERS

Para cumplir con **`RNF-08`**, el repositorio aplica políticas estrictas en GitHub:

1. **Ramas protegidas:** `main` y `Testing`.
2. **Pull Requests obligatorios:** Ningún desarrollador puede realizar push directo a `main` o `Testing`.
3. **Revisiones requeridas:** Todo PR debe ser revisado y aprobado por al menos 1 integrante asignado.
4. **Passing Status Checks:** Los PRs solo pueden mergearse si todos los workflows de CI/CD (`lint`, `security`, `check-req-reference`) finalizan exitosamente.

---

## 6. Reporte Responsable de Vulnerabilidades

Si descubrís alguna vulnerabilidad de seguridad en Classia, te pedimos que la reportes de forma responsable siguiendo este procedimiento:

1. **Contacto confidencial:** Envía un correo electrónico a la casilla del equipo de seguridad: `seguridad@anitech.com` (o crea un issue privado de seguridad en GitHub).
2. **Información requerida:**
   - Descripción detallada de la vulnerabilidad.
   - Pasos para reproducirla o PoC (*Proof of Concept*).
   - Componentes o rutas afectadas.
3. **Compromiso de respuesta:**
   - Acuse de recibo en un plazo máximo de **48 horas**.
   - Evaluación y plan de remediación en un plazo de **5 días hábiles**.
4. **Divulgación responsable:** Solicitamos no divulgar públicamente la vulnerabilidad hasta que el equipo haya desplegado el parche de corrección.

---

*Última actualización: Septiembre 2026 — AniTech DevSecOps Team.*
