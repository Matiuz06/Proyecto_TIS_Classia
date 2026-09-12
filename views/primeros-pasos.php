<?php
$paso_actual = 1;
$errores_onboarding = [];
$datos_onboarding = [];
$usuario_onboarding = [];
require_once '../php/usuarios/onboarding.php';

$title       = 'Primeros pasos — Classia';
$description = 'Configuración inicial del perfil y las preferencias del usuario en Classia.';
$cssPrefix   = '..';
$jsPrefix    = '..';

$activePage  = 'cuenta';
include '../includes/header.php';
?>

  <main class="onboarding-page motion-entry">
    <header class="onboarding-header">
      <p class="onboarding-subtitle">Configuración inicial</p>
      <h1>Contanos qué buscás en Classia</h1>
      <p class="onboarding-description">
        Esta información permitirá organizar tu espacio y recomendarte cursos,
        proyectos, mentorías y servicios relacionados con tus intereses.
        Podrás modificar estas preferencias más adelante desde tu perfil.
      </p>
      <p class="step-desc">Paso <?= $paso_actual ?> de 9. Completá cada sección para habilitar la siguiente.</p>
    </header>

    <form action="../php/usuarios/onboarding.php" method="post">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <input type="hidden" name="paso" value="<?= $paso_actual ?>">
      <?php if ($errores_onboarding): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errores_onboarding as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>

      <fieldset class="onboarding-step<?= clase_paso(1, $paso_actual) ?>"<?= atributo_paso(1, $paso_actual) ?><?= $paso_actual === 1 ? '' : ' hidden style="display:none!important"' ?>>
        <legend class="onboarding-legend">
          <span class="step-num">1</span> Datos personales y profesionales
        </legend>
        <p class="step-desc">
          Completa algunos datos básicos para personalizar tu experiencia.
        </p>

        <div class="form-grid form-grid--2col">
          <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" autocomplete="given-name" value="<?= htmlspecialchars($usuario_onboarding['nombre'] ?? '') ?>" required />
          </div>

          <div class="form-group">
            <label for="apellido">Apellido</label>
            <input type="text" id="apellido" name="apellido" autocomplete="family-name" value="<?= htmlspecialchars($usuario_onboarding['apellido'] ?? '') ?>" required />
          </div>
        </div>

        <div class="form-grid form-grid--2col" style="margin-top: var(--space-4);">
          <div class="form-group">
            <label for="profesion">Profesión u ocupación</label>
            <select id="profesion" name="profesion" required>
              <option value="">Seleccioná una opción</option>
              <option value="estudiante">Estudiante</option>
              <option value="docente">Docente</option>
              <option value="educador-no-formal">Educador o tallerista</option>
              <option value="profesional-tecnologia">Profesional de tecnología</option>
              <option value="desarrollador">Desarrollador de software</option>
              <option value="disenador">Diseñador</option>
              <option value="especialista-robotica">Especialista en robótica o automatización</option>
              <option value="emprendedor">Emprendedor</option>
              <option value="representante-institucion">Representante de una institución</option>
              <option value="empresa">Representante de una empresa</option>
              <option value="otro">Otra ocupación</option>
            </select>
          </div>

          <div class="form-group">
            <label for="otra-profesion">Otra profesión u ocupación</label>
            <input type="text" id="otra-profesion" name="otra_profesion"
              placeholder="Completa este campo si seleccionaste otra ocupación" />
          </div>
        </div>

        <div class="form-grid form-grid--3col" style="margin-top: var(--space-4);">
          <div class="form-group">
            <label for="institucion">Institución, empresa o emprendimiento</label>
            <input type="text" id="institucion" name="institucion" autocomplete="organization" placeholder="Opcional" />
          </div>

          <div class="form-group">
            <label for="pais">País</label>
            <input type="text" id="pais" name="pais" autocomplete="country-name" value="Uruguay" />
          </div>

          <div class="form-group">
            <label for="departamento">Departamento o localidad</label>
            <input type="text" id="departamento" name="departamento" autocomplete="address-level1"
              placeholder="Ejemplo: Salto" />
          </div>
        </div>
      </fieldset>

      <fieldset class="onboarding-step<?= clase_paso(2, $paso_actual) ?>"<?= atributo_paso(2, $paso_actual) ?><?= $paso_actual === 2 ? '' : ' hidden style="display:none!important"' ?>>
        <legend class="onboarding-legend">
          <span class="step-num">2</span> ¿Cómo pensás utilizar la plataforma?
        </legend>
        <p class="step-desc">Podés seleccionar más de una opción.</p>

        <div class="options-grid">
          <label class="option-card" for="uso-explorar">
            <input type="checkbox" id="uso-explorar" name="uso_plataforma[]" value="explorar-servicios" />
            <span class="option-text">Explorar cursos y servicios educativos</span>
          </label>

          <label class="option-card" for="uso-contratar">
            <input type="checkbox" id="uso-contratar" name="uso_plataforma[]" value="contratar-servicios" />
            <span class="option-text">Contratar servicios para mí</span>
          </label>

          <label class="option-card" for="uso-institucion">
            <input type="checkbox" id="uso-institucion" name="uso_plataforma[]" value="contratar-institucion" />
            <span class="option-text">Contratar formación o servicios para una institución</span>
          </label>

          <label class="option-card" for="uso-publicar">
            <input type="checkbox" id="uso-publicar" name="uso_plataforma[]" value="publicar-servicios" />
            <span class="option-text">Publicar y comercializar mis propios servicios</span>
          </label>

          <label class="option-card" for="uso-aprender">
            <input type="checkbox" id="uso-aprender" name="uso_plataforma[]" value="formacion-personal" />
            <span class="option-text">Ampliar mi formación profesional</span>
          </label>

          <label class="option-card" for="uso-asesoramiento">
            <input type="checkbox" id="uso-asesoramiento" name="uso_plataforma[]" value="buscar-asesoramiento" />
            <span class="option-text">Buscar mentoría o acompañamiento especializado</span>
          </label>

          <label class="option-card" for="uso-proyectos">
            <input type="checkbox" id="uso-proyectos" name="uso_plataforma[]" value="desarrollar-proyectos" />
            <span class="option-text">Encontrar apoyo para desarrollar un proyecto</span>
          </label>
        </div>
      </fieldset>

      <fieldset class="onboarding-step<?= clase_paso(3, $paso_actual) ?>"<?= atributo_paso(3, $paso_actual) ?><?= $paso_actual === 3 ? '' : ' hidden style="display:none!important"' ?>>
        <legend class="onboarding-legend">
          <span class="step-num">3</span> Categorías de interés
        </legend>
        <p class="step-desc">
          Seleccioná las categorías que te gustaría encontrar en tus recomendaciones.
        </p>

        <div class="options-grid">
          <label class="option-card" for="categoria-cursos">
            <input type="checkbox" id="categoria-cursos" name="categorias[]" value="cursos" />
            <span class="option-text">Cursos presenciales, virtuales o híbridos</span>
          </label>

          <label class="option-card" for="categoria-proyectos">
            <input type="checkbox" id="categoria-proyectos" name="categorias[]" value="proyectos-educativos" />
            <span class="option-text">Proyectos educativos</span>
          </label>

          <label class="option-card" for="categoria-formacion">
            <input type="checkbox" id="categoria-formacion" name="categorias[]" value="formacion-institucional" />
            <span class="option-text">Formación para empresas e instituciones</span>
          </label>

          <label class="option-card" for="categoria-3d">
            <input type="checkbox" id="categoria-3d" name="categorias[]" value="diseno-impresion-3d" />
            <span class="option-text">Diseño e impresión 3D</span>
          </label>

          <label class="option-card" for="categoria-robotica">
            <input type="checkbox" id="categoria-robotica" name="categorias[]" value="robotica-automatizacion" />
            <span class="option-text">Robótica y automatización</span>
          </label>

          <label class="option-card" for="categoria-mentorias">
            <input type="checkbox" id="categoria-mentorias" name="categorias[]" value="mentorias" />
            <span class="option-text">Mentorías y acompañamiento especializado</span>
          </label>

          <label class="option-card" for="categoria-programacion">
            <input type="checkbox" id="categoria-programacion" name="categorias[]" value="programacion" />
            <span class="option-text">Programación y desarrollo web</span>
          </label>

          <label class="option-card" for="categoria-diseno">
            <input type="checkbox" id="categoria-diseno" name="categorias[]" value="diseno" />
            <span class="option-text">Diseño gráfico, multimedia y experiencia de usuario</span>
          </label>

          <label class="option-card" for="categoria-educacion">
            <input type="checkbox" id="categoria-educacion" name="categorias[]" value="educacion" />
            <span class="option-text">Educación, didáctica y evaluación</span>
          </label>

          <label class="option-card" for="categoria-gestion">
            <input type="checkbox" id="categoria-gestion" name="categorias[]" value="gestion" />
            <span class="option-text">Gestión institucional y de proyectos</span>
          </label>
        </div>
      </fieldset>

      <fieldset class="onboarding-step<?= clase_paso(4, $paso_actual) ?>"<?= atributo_paso(4, $paso_actual) ?><?= $paso_actual === 4 ? '' : ' hidden style="display:none!important"' ?>>
        <legend class="onboarding-legend">
          <span class="step-num">4</span> Modalidades preferidas
        </legend>
        <p class="step-desc">Seleccioná las modalidades de tu preferencia.</p>

        <div class="options-grid">
          <label class="option-card" for="modalidad-virtual">
            <input type="checkbox" id="modalidad-virtual" name="modalidades[]" value="virtual" />
            <span class="option-text">Virtual</span>
          </label>

          <label class="option-card" for="modalidad-presencial">
            <input type="checkbox" id="modalidad-presencial" name="modalidades[]" value="presencial" />
            <span class="option-text">Presencial</span>
          </label>

          <label class="option-card" for="modalidad-hibrida">
            <input type="checkbox" id="modalidad-hibrida" name="modalidades[]" value="hibrida" />
            <span class="option-text">Híbrida</span>
          </label>

          <label class="option-card" for="modalidad-entregable">
            <input type="checkbox" id="modalidad-entregable" name="modalidades[]" value="producto-entregable" />
            <span class="option-text">Producto o trabajo entregable</span>
          </label>

          <label class="option-card" for="modalidad-indiferente">
            <input type="checkbox" id="modalidad-indiferente" name="modalidades[]" value="sin-preferencia" />
            <span class="option-text">No tengo una modalidad preferida</span>
          </label>
        </div>
      </fieldset>

      <fieldset class="onboarding-step<?= clase_paso(5, $paso_actual) ?>"<?= atributo_paso(5, $paso_actual) ?><?= $paso_actual === 5 ? '' : ' hidden style="display:none!important"' ?>>
        <legend class="onboarding-legend">
          <span class="step-num">5</span> Nivel de experiencia
        </legend>
        <p class="step-desc">Seleccioná el nivel que mejor describe tu situación.</p>

        <div class="options-grid">
          <label class="option-card" for="nivel-inicial">
            <input type="radio" id="nivel-inicial" name="nivel_experiencia" value="inicial" required />
            <span class="option-text"><strong>Inicial:</strong> estoy comenzando o quiero aprender desde cero</span>
          </label>

          <label class="option-card" for="nivel-intermedio">
            <input type="radio" id="nivel-intermedio" name="nivel_experiencia" value="intermedio" />
            <span class="option-text"><strong>Intermedio:</strong> ya tengo conocimientos o experiencia previa</span>
          </label>

          <label class="option-card" for="nivel-avanzado">
            <input type="radio" id="nivel-avanzado" name="nivel_experiencia" value="avanzado" />
            <span class="option-text"><strong>Avanzado:</strong> busco especialización o servicios profesionales</span>
          </label>

          <label class="option-card" for="nivel-variable">
            <input type="radio" id="nivel-variable" name="nivel_experiencia" value="depende-categoria" />
            <span class="option-text">Mi nivel depende de la categoría</span>
          </label>
        </div>
      </fieldset>

      <fieldset class="onboarding-step<?= clase_paso(6, $paso_actual) ?>"<?= atributo_paso(6, $paso_actual) ?><?= $paso_actual === 6 ? '' : ' hidden style="display:none!important"' ?>>
        <legend class="onboarding-legend">
          <span class="step-num">6</span> Preferencias de contratación
        </legend>
        <p class="step-desc">Indicá tus preferencias de presupuesto y formato.</p>

        <div class="form-grid form-grid--2col" style="margin-bottom: var(--space-4);">
          <div class="form-group">
            <label for="presupuesto">Presupuesto habitual</label>
            <select id="presupuesto" name="presupuesto">
              <option value="">Prefiero no indicarlo</option>
              <option value="gratuito">Busco principalmente opciones gratuitas</option>
              <option value="menos-de-1000">Menos de $1.000</option>
              <option value="entre-1000-y-3000">Entre $1.000 y $3.000</option>
              <option value="entre-3000-y-10000">Entre $3.000 y $10.000</option>
              <option value="mas-de-10000">Más de $10.000</option>
              <option value="a-consultar">Depende del servicio</option>
            </select>
          </div>

          <div class="form-group">
            <label for="duracion-preferida">Duración preferida de cursos o formaciones</label>
            <select id="duracion-preferida" name="duracion_preferida">
              <option value="">Sin preferencia</option>
              <option value="menos-de-10-horas">Menos de 10 horas</option>
              <option value="entre-10-y-20-horas">Entre 10 y 20 horas</option>
              <option value="mas-de-20-horas">Más de 20 horas</option>
              <option value="personalizada">Duración personalizada</option>
            </select>
          </div>
        </div>

        <div class="options-grid">
          <label class="option-card" for="servicios-gratuitos">
            <input type="checkbox" id="servicios-gratuitos" name="preferencias_contratacion[]"
              value="mostrar-gratuitos" />
            <span class="option-text">Mostrar primero los servicios gratuitos</span>
          </label>

          <label class="option-card" for="mejor-valorados">
            <input type="checkbox" id="mejor-valorados" name="preferencias_contratacion[]"
              value="mostrar-mejor-valorados" />
            <span class="option-text">Priorizar servicios mejor valorados</span>
          </label>

          <label class="option-card" for="servicios-locales">
            <input type="checkbox" id="servicios-locales" name="preferencias_contratacion[]"
              value="mostrar-servicios-locales" />
            <span class="option-text">Mostrar servicios presenciales cercanos a mi ubicación</span>
          </label>
        </div>
      </fieldset>

      <fieldset class="onboarding-step<?= clase_paso(7, $paso_actual) ?>"<?= atributo_paso(7, $paso_actual) ?><?= $paso_actual === 7 ? '' : ' hidden style="display:none!important"' ?>>
        <legend class="onboarding-legend">
          <span class="step-num">7</span> Comunicación y notificaciones
        </legend>
        <p class="step-desc">Elegí qué información querés recibir en tu cuenta.</p>

        <div class="options-grid">
          <label class="option-card" for="notificacion-recomendaciones">
            <input type="checkbox" id="notificacion-recomendaciones" name="notificaciones[]" value="recomendaciones"
              checked />
            <span class="option-text">Recomendaciones relacionadas con mis intereses</span>
          </label>

          <label class="option-card" for="notificacion-promociones">
            <input type="checkbox" id="notificacion-promociones" name="notificaciones[]" value="promociones" />
            <span class="option-text">Promociones, descuentos y cupones</span>
          </label>

          <label class="option-card" for="notificacion-mensajes">
            <input type="checkbox" id="notificacion-mensajes" name="notificaciones[]" value="mensajes" checked />
            <span class="option-text">Mensajes de clientes o proveedores</span>
          </label>

          <label class="option-card" for="notificacion-contrataciones">
            <input type="checkbox" id="notificacion-contrataciones" name="notificaciones[]" value="contrataciones"
              checked />
            <span class="option-text">Confirmaciones y novedades sobre contrataciones</span>
          </label>

          <label class="option-card" for="notificacion-nuevos-servicios">
            <input type="checkbox" id="notificacion-nuevos-servicios" name="notificaciones[]" value="nuevos-servicios" />
            <span class="option-text">Nuevos servicios de las categorías seleccionadas</span>
          </label>
        </div>
      </fieldset>

      <fieldset class="onboarding-step<?= clase_paso(8, $paso_actual) ?>"<?= atributo_paso(8, $paso_actual) ?><?= $paso_actual === 8 ? '' : ' hidden style="display:none!important"' ?>>
        <legend class="onboarding-legend">
          <span class="step-num">8</span> Perfil como proveedor
        </legend>
        <p class="step-desc">
          Completa esta sección si también pensás publicar y comercializar servicios.
        </p>

        <div class="options-grid" style="margin-bottom: var(--space-4);">
          <label class="option-card" for="quiere-publicar">
            <input type="checkbox" id="quiere-publicar" name="quiere_publicar" value="si" />
            <span class="option-text">Quiero publicar servicios en Classia</span>
          </label>
        </div>

        <div class="form-grid form-grid--2col" style="margin-bottom: var(--space-4);">
          <div class="form-group">
            <label for="tipo-proveedor">Tipo de proveedor</label>
            <select id="tipo-proveedor" name="tipo_proveedor">
              <option value="">Seleccioná una opción</option>
              <option value="docente">Docente</option>
              <option value="profesional-independiente">Profesional independiente</option>
              <option value="institucion-educativa">Institución educativa</option>
              <option value="empresa">Empresa</option>
              <option value="emprendimiento">Emprendimiento</option>
              <option value="equipo-profesional">Equipo de profesionales</option>
            </select>
          </div>

          <div class="form-group">
            <label for="sitio-web">Sitio web, portafolio o perfil profesional</label>
            <input type="url" id="sitio-web" name="sitio_web" placeholder="https://ejemplo.com" />
          </div>
        </div>

        <div class="form-group">
          <label for="descripcion-profesional">Presentación profesional</label>
          <textarea id="descripcion-profesional" name="descripcion_profesional" rows="4" maxlength="700"
            placeholder="Contá brevemente tu experiencia, formación y los servicios que podrías ofrecer"></textarea>
        </div>
      </fieldset>

      <fieldset class="onboarding-step<?= clase_paso(9, $paso_actual) ?>"<?= atributo_paso(9, $paso_actual) ?><?= $paso_actual === 9 ? '' : ' hidden style="display:none!important"' ?>>
        <legend class="onboarding-legend">
          <span class="step-num">9</span> Privacidad y personalización
        </legend>
        <p class="step-desc">
          Classia utilizará las preferencias seleccionadas para organizar el catálogo y mostrar recomendaciones
          relacionadas.
        </p>

        <div class="form-grid" style="gap: var(--space-3);">
          <label class="option-card" for="acepta-personalizacion">
            <input type="checkbox" id="acepta-personalizacion" name="acepta_personalizacion" value="si" required />
            <span class="option-text">
              Acepto que mis intereses y mi actividad se utilicen para personalizar las recomendaciones dentro de la
              plataforma.
            </span>
          </label>

          <label class="option-card" for="acepta-privacidad">
            <input type="checkbox" id="acepta-privacidad" name="acepta_privacidad" value="si" required />
            <span class="option-text">
              Confirmo que leí y acepto la <a href="politica_privacidad.php"
                style="color: var(--color-brand-primary); font-weight: 600;">política de privacidad</a> y el tratamiento
              de mis datos personales.
            </span>
          </label>
        </div>
      </fieldset>

      <div class="onboarding-actions">
        <button type="submit" class="btn btn-primary"><?= $paso_actual === 9 ? 'Finalizar configuración' : 'Guardar y continuar' ?></button>
      </div>
    </form>
  </main>

<script>
  const datosOnboarding = <?= json_encode($datos_onboarding, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  Object.entries(datosOnboarding).forEach(([clave, valor]) => {
    const controles = document.querySelectorAll(`[name="${clave}"], [name="${clave}[]"]`);
    controles.forEach((control) => {
      if (control.type === 'checkbox' || control.type === 'radio') {
        control.checked = Array.isArray(valor) ? valor.includes(control.value) : valor === control.value;
      } else {
        control.value = valor;
      }
    });
  });
</script>

<?php include '../includes/footer.php'; ?>