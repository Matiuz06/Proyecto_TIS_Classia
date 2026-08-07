# Guía de contribución — Classia

Bienvenido al repositorio de **Classia**, el EVA desarrollado por **AniTech**.
Este documento describe cómo trabajar en el proyecto de forma ordenada y trazable respecto a los requerimientos del SRS.

---

## 1. Flujo de ramas

```
main
 └── dev/<nombre-desarrollador>
       └── (testing — solo si la funcionalidad involucra JS o base de datos)
             └── main
```

### Reglas

| Rama | Propósito |
|---|---|
| `main` | Código en producción. Solo se integra via PR aprobado. |
| `dev/<nombre>` | Rama de trabajo del desarrollador. Se abre desde `main`. |
| `testing` | Rama de integración para validar cambios que involucren lógica JS o BD antes de ir a `main`. Solo se usa cuando aplica. |

> **No hacer push directo a `main`.** Todo cambio entra mediante Pull Request con al menos una revisión de código aprobada.

---

## 2. Nomenclatura de ramas

```
dev/<nombre-desarrollador>/<tipo>/<descripcion-corta>
```

**Ejemplos:**

```
dev/ana/feat/req-aut-01-login
dev/bruno/fix/rnf-15-tiempo-respuesta
dev/ana/refactor/modulo-cursos
```

**Tipos válidos:**

| Tipo | Cuándo usarlo |
|---|---|
| `feat` | Nueva funcionalidad (implementa un REQ del SRS) |
| `fix` | Corrección de bug |
| `refactor` | Cambio sin impacto funcional |
| `docs` | Solo documentación |
| `ci` | Cambios en workflows o scripts de CI/CD |

---

## 3. Convención de commits

Seguimos **Conventional Commits** con referencia al código del SRS:

```
<tipo>(<código-requerimiento>): <descripción corta en imperativo>
```

**Ejemplos:**

```
feat(REQ-AUT-01): implementar formulario de inicio de sesión
fix(REQ-CAL-05): distinguir correctamente actividades vencidas
style(RNF-18): mejorar contraste de botones según WCAG AA
refactor(REQ-CUR-03): extraer lógica de edición de cursos a módulo separado
docs: actualizar CONTRIBUTING con flujo de ramas
ci: agregar workflow de sincronización de labels
```

### Tipos de commit válidos

| Tipo | Cuándo usarlo |
|---|---|
| `feat` | Nueva funcionalidad |
| `fix` | Corrección de bug |
| `refactor` | Cambio interno sin impacto funcional |
| `style` | Cambios de estilo/formato sin lógica |
| `docs` | Solo documentación |
| `test` | Añadir o modificar pruebas |
| `ci` | Pipelines y automatización |
| `chore` | Tareas de mantenimiento menores |

> Si el commit no corresponde a ningún requerimiento del SRS (p. ej., es un cambio de CI puro), omitir el paréntesis: `ci: agregar workflow sync-labels`.

---

## 4. Nomenclatura de requerimientos (SRS)

Classia tiene tres tipos de códigos según el *Documento de Requerimientos de Software*:

### Requerimientos Funcionales — `REQ-XXX-NN`

| Prefijo | Módulo |
|---|---|
| `REQ-AUT` | Autenticación y acceso |
| `REQ-USR` | Gestión de usuarios y roles |
| `REQ-CUR` | Gestión de cursos |
| `REQ-CON` | Organización de contenidos |
| `REQ-BUS` | Búsqueda y navegación |
| `REQ-INS` | Inscripción y matriculación |
| `REQ-ACT` | Actividades y entregas |
| `REQ-EVA` | Evaluaciones |
| `REQ-CAL` | Calificaciones y progreso |
| `REQ-COM` | Comunicación y notificaciones |
| `REQ-PAN` | Calendario y panel principal |
| `REQ-SER` | Catálogo de servicios institucionales |
| `REQ-PAG` | Contratación de servicios y pagos |
| `REQ-VAL` | Valoraciones y comentarios |
| `REQ-ADM` | Panel administrativo y analíticas |

### Requerimientos No Funcionales — `RNF-NN`

| Código | Categoría |
|---|---|
| `RNF-01` a `RNF-09` | Seguridad |
| `RNF-10` a `RNF-14` | Privacidad y cumplimiento normativo |
| `RNF-15` a `RNF-16` | Rendimiento y disponibilidad |
| `RNF-17` a `RNF-18` | Usabilidad y accesibilidad |
| `RNF-19` a `RNF-20` | Compatibilidad y escalabilidad |
| `RNF-21` a `RNF-24` | Mantenibilidad e integridad |

### Reglas de Negocio / Dominio — `RN-NN`

`RN-01` a `RN-14` — Reglas que definen el comportamiento del dominio educativo.

---

## 5. Issues

Usá siempre un **template de issue** al abrir uno nuevo. Los templates disponibles son:

| Template | Cuándo usarlo |
|---|---|
| 📋 Requerimiento funcional | Implementar un `REQ-XXX-NN` |
| ⚙️ Requerimiento no funcional | Implementar o verificar un `RNF-NN` |
| 📐 Regla de negocio / Dominio | Implementar una `RN-NN` |
| 🐛 Reporte de bug | Reportar un comportamiento incorrecto |
| ✨ Solicitud de funcionalidad | Proponer una mejora no contemplada en el SRS |

---

## 6. Pull Requests

Antes de abrir un PR:

1. ✅ Tu rama está actualizada respecto a `main` (o `testing` si aplica).
2. ✅ El código pasa el lint: `npm run lint` (HTMLHint + stylelint + ESLint).
3. ✅ Probaste los cambios en el navegador.
4. ✅ No hay secretos ni credenciales en el código.
5. ✅ Completaste la tabla **"Requerimientos implementados / afectados"** en el PR. _(El workflow `check-req-reference` verifica esto automáticamente.)_

### Labels de PR

Asigná manualmente los labels que correspondan al tipo de cambio y al módulo del SRS.
El `labeler.yml` asigna automáticamente los labels de tipo de archivo (`frontend: html`, `frontend: js`, etc.) y el módulo RF cuando el nombre del archivo coincide con la convención.

---

## 7. Revisión de código

Toda revisión debe incluir:

- ✅ Correctitud funcional respecto al requerimiento del SRS
- ✅ Aspectos de seguridad (ver `RNF-08`)
- ✅ Calidad y legibilidad del código
- ✅ Documentación actualizada si aplica

---

## 8. Política de seguridad

Ver [`SECURITY.md`](./SECURITY.md) para el proceso de reporte de vulnerabilidades.

---

*Documento generado en base al SRS v1.0 de Classia — AniTech.*
