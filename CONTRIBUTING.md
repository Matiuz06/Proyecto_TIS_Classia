# Guía de contribución y gestión de proyecto — Classia (AniTech)

Bienvenido al repositorio de **Classia**, el Entorno Virtual de Aprendizaje (EVA) desarrollado por **AniTech**.
Este documento detalla la gestión operativa, flujo de trabajo en GitHub, vinculación con Trello y convención de sprints/requerimientos.

---

## 🔗 Enlaces operativos principales

- 📋 **Tablero Kanban (Trello):** [https://trello.com/b/GxSUgvHG/proyecto-taller-anitech](https://trello.com/b/GxSUgvHG/proyecto-taller-anitech)
- 🐙 **Repositorio GitHub:** [https://github.com/Matiuz06/Proyecto_TIS_Classia](https://github.com/Matiuz06/Proyecto_TIS_Classia)

---

## 1. Flujo de ramas

```
main (protegida)
 └── Dev-<NombreDesarrollador> (ej: Dev-Ezequel, Dev-Mateus)
       └── Testing (integración previa para lógica JS o BD — cuando aplica)
             └── main
```

### Reglas de ramas

| Rama | Descripción |
|---|---|
| `main` | Código estable y entregable. Rama protegida; solo acepta cambios mediante Pull Request revisado y aprobado. |
| `Testing` | Rama de integración para validar funcionalidades complejas (especialmente cambios de JavaScript, base de datos o lógica cliente/servidor) antes de mergear a `main`. |
| `Dev-<Nombre>` / `feature/*` / `hotfix/*` | Ramas de desarrollo individual por desarrollador o funcionalidad. |

> ⚠️ **Sin commits directos a `main`:** Todos los cambios deben ingresar por Pull Request con al menos 1 revisión aprobada de un compañero de equipo.

---

## 2. Definición de roles Scrum

Cada tarea e Issue debe asociarse al rol correspondiente utilizando los labels de GitHub:

- 👤 **Product Owner (`rol: Product Owner`):** Definición de requerimientos, criterios de aceptación y priorización del backlog.
- 💻 **Desarrollador/a (`rol: Desarrollador`):** Implementación técnica, maquetación HTML/CSS, lógica JS y solución de bugs.
- 🧪 **Tester (`rol: Tester`):** Verificación de criterios de aceptación, pruebas de usabilidad y control de calidad.
- ⚡ **Scrum Master (`rol: Scrum Master`):** Gestión de proceso, remoción de bloqueos y facilitación de ceremonias.
- 👔 **Stakeholder (`rol: Stakeholder`):** Feedback del cliente y validación funcional.

---

## 3. Tablero Kanban (Trello) y estimación

El flujo de trabajo visual se administra en el tablero Trello con las siguientes columnas obligatorias:

$$\text{Backlog} \longrightarrow \text{Listo / To Do} \longrightarrow \text{En Desarrollo} \longrightarrow \text{En Review} \longrightarrow \text{En Testing} \longrightarrow \text{Finalizado}$$

### Reglas del tablero

1. **Tamaño máximo de tarea:** Cada issue/tarjeta debe representar máximo **1 a 2 días de trabajo** (`estimación: ½ día`, `estimación: 1 día`, `estimación: 2 días`).
2. **Vinculación bidireccional:**
   - La tarjeta de Trello debe contener el enlace al Issue o PR de GitHub.
   - El Issue de GitHub debe incluir la URL de la tarjeta de Trello en la sección **Vinculación**.
3. **Cierre automático:** Al abrir un PR, incluir `Closes #<número-issue>` para que el issue de GitHub se cierre automáticamente al hacer merge a `main`.

---

## 4. Sprints y Milestones (GitHub)

Los sprints tienen una duración de **2 semanas** y se gestionan mediante **Milestones** en GitHub:

- **Ejemplos:** `Sprint 1`, `Sprint 2`, `Sprint 3`…
- Cada Milestone se configura con su **fecha de inicio** y **fecha de fin**.
- Al iniciar un Sprint:
  1. Se asignan los Issues comprometidos al Milestone actual.
  2. Las tarjetas en Trello se mueven a **Listo / To Do**.
- Al finalizar el Sprint:
  1. Se realiza la integración de los PRs revisados a `main`.
  2. Se realiza la Review y Retrospectiva de Sprint.

---

## 5. Convención de Commits y PRs

### Mensajes de commit (Conventional Commits)

```
<tipo>(<código-requerimiento>): <descripción corta>
```

**Ejemplos:**
- `feat(REQ-AUT-01): agregar formulario de login con correo y contraseña`
- `fix(REQ-CAL-05): corregir cálculo de entregas fuera de plazo`
- `style(RNF-18): ajustar contraste en botones principales según WCAG`
- `docs: actualizar guía de contribución con flujo de Trello y Sprints`

### Plantilla de Pull Request

Todo PR debe completar la plantilla predefinida en `.github/pull_request_template.md`:
- **Descripción:** Qué cambia y por qué.
- **Requerimientos:** Código del SRS (`REQ-XXX-NN`, `RNF-NN` o `RN-NN`).
- **Cómo probar:** Pasos detallados para verificación manual.
- **Vinculaciones:** `Closes #ID` y URL de Trello.
- **Capturas / Evidencia:** Imágenes o GIFs demostrativos.

---

## 6. Nomenclatura del SRS de Classia

### Funcionales (`REQ-XXX-NN`)
`REQ-AUT` (Autenticación) · `REQ-USR` (Usuarios) · `REQ-CUR` (Cursos) · `REQ-CON` (Contenidos) · `REQ-BUS` (Búsqueda) · `REQ-INS` (Inscripción) · `REQ-ACT` (Actividades) · `REQ-EVA` (Evaluaciones) · `REQ-CAL` (Calificaciones) · `REQ-COM` (Comunicación) · `REQ-PAN` (Panel/Calendario) · `REQ-SER` (Servicios) · `REQ-PAG` (Pagos) · `REQ-VAL` (Valoraciones) · `REQ-ADM` (Administración).

### No funcionales (`RNF-NN`)
`RNF-01` a `RNF-09` (Seguridad) · `RNF-10` a `RNF-14` (Privacidad/Ley 18.331) · `RNF-15` a `RNF-16` (Rendimiento) · `RNF-17` a `RNF-18` (Usabilidad/WCAG) · `RNF-19` a `RNF-20` (Compatibilidad) · `RNF-21` a `RNF-24` (Mantenibilidad).

### Dominio / Reglas de negocio (`RN-NN`)
`RN-01` a `RN-14`.

---

*Documento configurado para la Etapa 2 del Proyecto Taller — AniTech / Classia.*
