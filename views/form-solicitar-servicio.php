<?php
require_once '../php/auth/roles.php';
requerir_rol(ROL_ESTUDIANTE, 'usuario.php');
require_once '../php/solicitudes/solicitudes_servicio.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = (int)($_GET['id'] ?? $_POST['id_publicacion'] ?? 0);
$servicio = obtener_servicio_activo($pdo, $id);
$plantilla = $servicio ? obtener_plantilla_servicio($servicio['tipo_servicio'] ?? null) : null;
$usuario = usuario_actual();
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $servicio && $plantilla) {
    $r = procesar_envio_solicitud_servicio($pdo, (int)$usuario['id_usuario'], $servicio, $_POST, $_FILES, $_SESSION['csrf_token']);
    if ($r['ok']) {
        $mensaje = $r['mensaje'];
    } else {
        $error = $r['mensaje'];
    }
}

$title       = $servicio ? 'Solicitar ' . htmlspecialchars($servicio['titulo']) . ' — Classia' : 'Solicitar Servicio — Classia';
$description = 'Formulario para solicitar un servicio técnico o pedagógico personalizado en Classia.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = 'catalogo';
include '../includes/header.php';
?>

  <main class="service-req-container motion-entry">
    <nav aria-label="Ruta de navegación" class="breadcrumb u-mb-4">
      <ol class="breadcrumb__list">
        <li><a href="../index.php">Inicio</a> /</li>
        <li><a href="catalogo.php">Catálogo</a> /</li>
        <?php if ($servicio): ?>
          <li><a href="servicio-detalle.php?id=<?= $id ?>">Detalle del servicio</a> /</li>
        <?php endif; ?>
        <li aria-current="page" class="breadcrumb__current">Solicitar servicio</li>
      </ol>
    </nav>

    <!-- Encabezado de la página -->
    <header class="service-req-header">
      <h1 class="service-req-title">
        Solicitud de Servicio Personalizado
      </h1>
      <p class="service-req-subtitle">
        Completá los requerimientos para que el docente o proveedor evalúe tu necesidad y te envíe una propuesta o presupuesto a medida.
      </p>
    </header>

    <!-- Tarjeta del Servicio Seleccionado -->
    <?php if ($servicio): ?>
      <section class="service-req-summary">
        <div>
          <span class="service-req-tag">
            <?= $plantilla ? htmlspecialchars($plantilla['nombre']) : 'Servicio Especializado' ?>
          </span>
          <h2 class="service-req-item-title">
            <?= htmlspecialchars($servicio['titulo']) ?>
          </h2>
          <p class="service-req-item-meta">
            Proveedor: <a href="proveedor.php?id=<?= (int)($servicio['id_usuario'] ?? 0) ?>" class="u-font-semibold"><?= htmlspecialchars($servicio['autor_nombre'] . ' ' . $servicio['autor_apellido']) ?></a>
            · Categoría: <?= htmlspecialchars($servicio['nombre_categoria'] ?? 'General') ?>
          </p>
        </div>
        <div class="service-req-price-box">
          <span class="service-req-price-label">Precio de referencia</span>
          <strong class="service-req-price-value">
            $<?= number_format((float)$servicio['precio'], 2, ',', '.') ?> UYU
          </strong>
        </div>
      </section>
    <?php else: ?>
      <div class="alert alert-danger u-mb-4">
        El servicio especificado no existe o no se encuentra activo. <a href="catalogo.php">Volver al catálogo</a>.
      </div>
    <?php endif; ?>

    <?php if ($mensaje): ?>
      <div class="alert alert-success u-mb-4">
        ✓ <?= htmlspecialchars($mensaje) ?> · <a href="mis-solicitudes-servicios.php" class="u-font-bold">Ir a mis solicitudes de servicios</a>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-danger u-mb-4">
        ⚠️ <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($servicio && $plantilla): ?>
      <form action="form-solicitar-servicio.php?id=<?= $id ?>" method="post" enctype="multipart/form-data" data-service-form data-tipo-servicio="<?= htmlspecialchars($servicio['tipo_servicio'] ?? '') ?>" class="service-req-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="id_publicacion" value="<?= $id ?>">

        <!-- PASO 1: DATOS DE CONTACTO -->
        <fieldset class="service-req-fieldset">
          <legend class="service-req-legend">
            1. Tus datos de contacto
          </legend>
          <p class="service-req-hint">
            El proveedor utilizará esta información para coordinar el trabajo y enviarte la cotización.
          </p>

          <div class="service-req-grid">
            <div>
              <label for="nombre" class="service-req-label">Nombre y Apellido *</label>
              <input type="text" id="nombre" name="nombre" class="service-req-input" value="<?= htmlspecialchars(trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''))) ?>" required />
            </div>

            <div>
              <label for="correo" class="service-req-label">Correo Electrónico *</label>
              <input type="email" id="correo" name="correo" class="service-req-input" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required />
            </div>

            <div>
              <label for="telefono" class="service-req-label">Teléfono / WhatsApp</label>
              <input type="tel" id="telefono" name="telefono" class="service-req-input" placeholder="Ej: 099 123 456" />
            </div>

            <div>
              <label for="medio-contacto" class="service-req-label">Medio de contacto preferido *</label>
              <select id="medio-contacto" name="medio-contacto" class="service-req-select" required>
                <option value="mensajeria-classia" selected>Mensajería de Classia (Recomendado)</option>
                <option value="correo">Correo electrónico</option>
                <option value="telefono">Teléfono / WhatsApp</option>
              </select>
            </div>
          </div>
        </fieldset>

        <!-- PASO 2: ESPECIFICACIONES TÉCNICAS (SEGÚN TIPO) -->
        <fieldset class="service-req-fieldset">
          <legend class="service-req-legend">
            2. Requerimientos del Servicio
          </legend>

          <!-- Tipo de servicio selector (oculto o bloque) -->
          <input type="hidden" id="tipo-servicio" name="tipo-servicio" value="<?= htmlspecialchars($servicio['tipo_servicio'] ?? 'impresion_3d') ?>" />

          <?php $tipoActual = $servicio['tipo_servicio'] ?? ''; ?>

          <!-- 3D PRINTING -->
          <?php if ($tipoActual === 'impresion_3d'): ?>
            <div class="service-req-stack">
              <div>
                <label for="titulo-proyecto-3d" class="service-req-label">Nombre o Título de la pieza/proyecto *</label>
                <input type="text" id="titulo-proyecto-3d" name="titulo-proyecto-3d" class="service-req-input" required placeholder="Ej: Soporte articulado para cámara o carcasa Arduino" />
              </div>

              <div class="service-req-grid--2col">
                <div>
                  <label for="tipo-trabajo-3d" class="service-req-label">Tipo de trabajo *</label>
                  <select id="tipo-trabajo-3d" name="tipo-trabajo-3d" class="service-req-select" required>
                    <option value="solo-impresion">Solo impresión (tengo el archivo STL/OBJ)</option>
                    <option value="solo-diseno">Solo diseño 3D / modelado</option>
                    <option value="diseno-impresion" selected>Diseño e Impresión 3D completa</option>
                    <option value="modificacion">Modificación de modelo existente</option>
                  </select>
                </div>

                <div>
                  <label for="cantidad-piezas" class="service-req-label">Cantidad de piezas *</label>
                  <input type="number" id="cantidad-piezas" name="cantidad-piezas" class="service-req-input" value="1" min="1" required />
                </div>
              </div>

              <div class="service-req-grid--2col">
                <div>
                  <label for="material-preferido" class="service-req-label">Material sugerido</label>
                  <select id="material-preferido" name="material-preferido" class="service-req-select">
                    <option value="pla">PLA (Estándar / Ecológico)</option>
                    <option value="petg">PETG (Mayor resistencia mecánica)</option>
                    <option value="resina">Resina fotosensible (Alto detalle)</option>
                    <option value="otro">A convenir con el proveedor</option>
                  </select>
                </div>

                <div>
                  <label for="medidas" class="service-req-label">Dimensiones estimadas (L × An × Al en cm)</label>
                  <input type="text" id="medidas" name="medidas" class="service-req-input" placeholder="Ej: 10 × 5 × 3 cm" />
                </div>
              </div>

              <div>
                <label for="descripcion-3d" class="service-req-label">Descripción y función de la pieza *</label>
                <textarea id="descripcion-3d" name="descripcion-3d" class="service-req-textarea" rows="4" required placeholder="Explicá para qué se utilizará la pieza, si debe encajar con otros elementos o soportar peso..."></textarea>
              </div>

              <div>
                <label for="archivo-3d" class="service-req-label">Archivo 3D o Boceto de referencia (STL, OBJ, STEP, PDF, JPG, PNG, ZIP)</label>
                <input type="file" id="archivo-3d" name="archivo-3d" class="service-req-file" accept=".stl,.obj,.step,.3mf,.pdf,.jpg,.jpeg,.png,.zip" />
              </div>
            </div>

          <!-- MENTORÍA -->
          <?php elseif ($tipoActual === 'mentoria'): ?>
            <div class="service-req-stack">
              <div>
                <label for="tema-mentoria" class="service-req-label">Tema o Asignatura de la Mentoría *</label>
                <input type="text" id="tema-mentoria" name="tema-mentoria" class="service-req-input" required placeholder="Ej: Consultas de PHP y Arquitectura MVC o Análisis de Ciberseguridad" />
              </div>

              <div class="service-req-grid--2col">
                <div>
                  <label for="modalidad-mentoria" class="service-req-label">Modalidad *</label>
                  <select id="modalidad-mentoria" name="modalidad-mentoria" class="service-req-select" required>
                    <option value="virtual-meet" selected>Virtual en vivo (Google Meet / Zoom)</option>
                    <option value="presencial">Presencial (Salto / CeRP)</option>
                    <option value="revision-asincronica">Revisión asincrónica de código/proyecto</option>
                  </select>
                </div>

                <div>
                  <label for="duracion-mentoria" class="service-req-label">Duración estimada</label>
                  <select id="duracion-mentoria" name="duracion-mentoria" class="service-req-select">
                    <option value="1-hora">1 hora (Sesión estándar)</option>
                    <option value="2-horas">2 horas (Profundización)</option>
                    <option value="paquete-mensual">Acompañamiento recurrente / Paquete</option>
                  </select>
                </div>
              </div>

              <div>
                <label for="descripcion-mentoria" class="service-req-label">¿Cuáles son tus dudas o temas a profundizar? *</label>
                <textarea id="descripcion-mentoria" name="descripcion-mentoria" class="service-req-textarea" rows="4" required placeholder="Detallá los objetivos concretos de la mentoría..."></textarea>
              </div>

              <div>
                <label for="archivo-mentoria" class="service-req-label">Material o consigna adjunta (PDF, ZIP, código)</label>
                <input type="file" id="archivo-mentoria" name="archivo-mentoria" class="service-req-file" accept=".pdf,.doc,.docx,.zip,.txt,.png,.jpg" />
              </div>
            </div>

          <!-- FORMACIÓN INSTITUCIONAL / OTROS -->
          <?php else: ?>
            <div class="service-req-stack">
              <div>
                <label for="nombre-organizacion" class="service-req-label">Nombre de la Institución u Organización</label>
                <input type="text" id="nombre-organizacion" name="nombre-organizacion" class="service-req-input" placeholder="Ej: Liceo N.º 1 / Empresa de Desarrollo" />
              </div>

              <div>
                <label for="tema-formacion" class="service-req-label">Objetivo del Proyecto / Temática del Taller *</label>
                <input type="text" id="tema-formacion" name="tema-formacion" class="service-req-input" required placeholder="Ej: Taller de Ciberseguridad o Capacitación en Impresión 3D" />
              </div>

              <div>
                <label for="objetivo-formacion" class="service-req-label">Detalles y alcance requerido *</label>
                <textarea id="objetivo-formacion" name="objetivo-formacion" class="service-req-textarea" rows="4" required placeholder="Explicá destinatarios, cantidad estimada de personas, horarios de preferencia..."></textarea>
              </div>
            </div>
          <?php endif; ?>
        </fieldset>

        <!-- PASO 3: PLAZOS Y CONDICIONES -->
        <fieldset class="service-req-fieldset">
          <legend class="service-req-legend">
            3. Plazos y confirmación
          </legend>

          <div class="service-req-stack">
            <div>
              <label for="comentarios-generales" class="service-req-label">Comentarios adicionales u observaciones (Opcional)</label>
              <textarea id="comentarios-generales" name="comentarios-generales" class="service-req-textarea" rows="3" placeholder="Cualquier información adicional para el proveedor..."></textarea>
            </div>

            <div class="service-req-checkboxes">
              <label class="service-req-check-label">
                <input type="checkbox" id="acepta-contacto" name="acepta-contacto" required />
                <span>Autorizo al proveedor a contactarme a través de Classia para coordinar detalles de la cotización.</span>
              </label>

              <label class="service-req-check-label">
                <input type="checkbox" id="acepta-terminos" name="acepta-terminos" required />
                <span>Acepto los términos de servicio y políticas de privacidad de Classia.</span>
              </label>
            </div>
          </div>
        </fieldset>

        <!-- Botones de Acción -->
        <div class="service-req-actions">
          <a href="servicio-detalle.php?id=<?= $id ?>" class="btn btn-secondary">← Cancelar y volver</a>
          <button type="submit" class="btn btn-primary btn-lg u-font-bold">
            Enviar Solicitud de Servicio
          </button>
        </div>
      </form>
    <?php endif; ?>
  </main>

<?php include '../includes/footer.php'; ?>
