<?php

/**
 * Responsabilidad: Declaración y compromiso de accesibilidad web (WCAG 2.1 AA).
 * Actualizado Sprint 4 — RNF-17 y RNF-18.
 */

$title       = 'Política de accesibilidad — Classia';
$description = 'Declaración de accesibilidad de Classia: compromisos WCAG 2.1 AA, navegación por teclado, contraste y diseño responsivo.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = '';
include '../includes/header.php';
?>

  <main id="main-content" class="privacy-page motion-entry">
    <header>
      <p>Políticas</p>
      <h1>Política de accesibilidad</h1>
      <p>
        Classia está comprometida con brindar una experiencia digital accesible e inclusiva para personas
        con diferentes capacidades. Esta declaración describe las medidas adoptadas para cumplir con
        <strong>WCAG 2.1 nivel AA</strong> y el diseño adaptable a múltiples dispositivos.
      </p>
    </header>

    <section class="privacy-section" aria-labelledby="accesibilidad-objetivo">
      <h2 id="accesibilidad-objetivo"><span class="section-num" aria-hidden="true">1</span> Objetivo y alcance</h2>
      <p>
        Reducir las barreras de acceso a la información y funcionalidades de la plataforma,
        considerando la accesibilidad como una prioridad transversal de diseño y desarrollo,
        no como una mejora opcional.
      </p>
      <p>
        Esta política aplica a todas las vistas y componentes de la plataforma Classia:
        catálogo de cursos, panel de usuario, formularios de inscripción, páginas institucionales
        y contenidos educativos.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="accesibilidad-contraste">
      <h2 id="accesibilidad-contraste"><span class="section-num" aria-hidden="true">2</span> Contraste de color (WCAG 1.4.3 / 1.4.6)</h2>
      <p>
        Todos los tokens de color del sistema de diseño (<code>--color-text</code>,
        <code>--color-heading</code>, <code>--color-text-muted</code>, etc.) están calibrados para
        garantizar un ratio de contraste mínimo de <strong>4.5:1</strong> para texto normal y
        <strong>3:1</strong> para texto grande, tanto en modo claro como en modo oscuro.
      </p>
      <ul>
        <li>Los colores de texto, encabezados y elementos interactivos cumplen WCAG AA.</li>
        <li>
          El modo oscuro cuenta con su propio conjunto de tokens redefinidos para mantener los
          mismos ratios de contraste sobre fondos oscuros.
        </li>
        <li>Los íconos y gráficos informativos cumplen con el criterio 1.4.11 (Non-text Contrast).</li>
      </ul>
    </section>

    <section class="privacy-section" aria-labelledby="accesibilidad-teclado">
      <h2 id="accesibilidad-teclado"><span class="section-num" aria-hidden="true">3</span> Navegación por teclado y foco visible (WCAG 2.4.3 / 2.4.7 / 2.4.11)</h2>
      <ul>
        <li>
          Todos los elementos interactivos (enlaces, botones, campos de formulario, selectores) muestran
          un indicador de foco visible mediante <code>:focus-visible</code>, implementado con el token
          <code>--color-focus</code> (contorno de 3 px).
        </li>
        <li>
          Se incluye un <strong>skip-link</strong> («Saltar al contenido principal») visible al recibir
          foco, que permite a usuarios de teclado y lectores de pantalla omitir la navegación repetida.
        </li>
        <li>El orden de tabulación sigue el flujo visual lógico del documento.</li>
        <li>
          Los diálogos nativos (<code>&lt;dialog&gt;</code>) devuelven el foco al elemento disparador al cerrarse,
          respetando el ciclo de foco dentro del modal mientras está abierto.
        </li>
      </ul>
    </section>

    <section class="privacy-section" aria-labelledby="accesibilidad-semantica">
      <h2 id="accesibilidad-semantica"><span class="section-num" aria-hidden="true">4</span> HTML semántico y atributos ARIA (WCAG 1.3.1 / 4.1.2)</h2>
      <ul>
        <li>
          Se utiliza HTML5 semántico: <code>&lt;main&gt;</code>, <code>&lt;nav&gt;</code>,
          <code>&lt;header&gt;</code>, <code>&lt;footer&gt;</code>, <code>&lt;section&gt;</code>,
          <code>&lt;article&gt;</code>, <code>&lt;aside&gt;</code>.
        </li>
        <li>
          Los formularios usan etiquetas <code>&lt;label&gt;</code> asociadas mediante <code>for</code>/
          <code>id</code> o agrupación con <code>&lt;fieldset&gt;</code> y <code>&lt;legend&gt;</code>.
        </li>
        <li>
          Las imágenes decorativas llevan <code>alt=""</code> y las informativas llevan texto alternativo
          descriptivo. Los íconos SVG decorativos tienen <code>aria-hidden="true"</code>.
        </li>
        <li>
          Los menús desplegables usan <code>role="menu"</code>, <code>role="menuitem"</code> y
          <code>aria-label</code>. El menú de usuario usa <code>aria-expanded</code> para comunicar
          su estado.
        </li>
        <li>
          Las secciones con contenido se asocian a sus encabezados mediante <code>aria-labelledby</code>.
        </li>
        <li>
          El botón de modo oscuro actualiza su <code>aria-label</code> dinámicamente para reflejar
          la acción disponible («Cambiar a modo claro» o «Cambiar a modo oscuro»).
        </li>
      </ul>
    </section>

    <section class="privacy-section" aria-labelledby="accesibilidad-darkmode">
      <h2 id="accesibilidad-darkmode"><span class="section-num" aria-hidden="true">5</span> Modo oscuro y preferencias del sistema</h2>
      <p>
        Classia implementa un modo oscuro completo que puede activarse mediante el botón de la barra
        de navegación o de forma automática cuando el sistema operativo del usuario tiene configurada
        la preferencia <code>prefers-color-scheme: dark</code>.
      </p>
      <ul>
        <li>La preferencia se guarda en <code>localStorage</code> para persistir entre sesiones.</li>
        <li>
          Se aplica antes del primer pintado de la página (técnica anti-FOUC) para evitar parpadeos
          visuales molestos.
        </li>
        <li>
          Ambos temas cumplen con los ratios de contraste mínimos WCAG AA. Ningún contenido informativo
          se pierde al alternar entre modos.
        </li>
      </ul>
    </section>

    <section class="privacy-section" aria-labelledby="accesibilidad-responsive">
      <h2 id="accesibilidad-responsive"><span class="section-num" aria-hidden="true">6</span> Diseño adaptable (RNF-18)</h2>
      <ul>
        <li>
          El diseño es fluido mediante CSS Grid y Flexbox con <em>breakpoints</em> definidos en
          320 px, 360 px, 420 px, 720 px, 980 px y 1280 px.
        </li>
        <li>
          No existe desborde horizontal (<em>horizontal overflow</em>) en ninguna resolución probada.
          Las tablas de datos extensas se envuelven en contenedores con scroll horizontal controlado.
        </li>
        <li>Los tamaños de texto utilizan <code>clamp()</code> para escalar fluidamente.</li>
        <li>
          El sitio ha sido verificado en Chrome, Firefox, Safari y Edge en resoluciones desde 320 px
          hasta pantallas ultraanchas (1920 px+).
        </li>
        <li>Los objetivos táctiles tienen un tamaño mínimo de 44 × 44 px (WCAG 2.5.5).</li>
      </ul>
    </section>

    <section class="privacy-section" aria-labelledby="accesibilidad-animaciones">
      <h2 id="accesibilidad-animaciones"><span class="section-num" aria-hidden="true">7</span> Movimiento y animaciones (WCAG 2.3.3)</h2>
      <p>
        Todas las animaciones y transiciones respetan la media query
        <code>prefers-reduced-motion: reduce</code>. Cuando el usuario tiene configurada esta
        preferencia en su sistema operativo, las duraciones de animación se reducen a 1 ms,
        eliminando efectivamente el movimiento sin perder funcionalidad.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="accesibilidad-revision">
      <h2 id="accesibilidad-revision"><span class="section-num" aria-hidden="true">8</span> Revisión y herramientas</h2>
      <p>
        La accesibilidad se evalúa con las siguientes herramientas y metodologías:
      </p>
      <ul>
        <li>Auditoría automática con <strong>Lighthouse</strong> (Google Chrome DevTools).</li>
        <li>Inspección manual con navegación exclusiva por teclado (Tab, Shift+Tab, Enter, Escape, flechas).</li>
        <li>Verificación de ratios de contraste con <strong>WCAG Color Contrast Checker</strong>.</li>
        <li>Validación de HTML semántico con <strong>HTMLHint</strong> y W3C Validator.</li>
      </ul>
      <p>
        La revisión no sustituye las pruebas con personas usuarias reales. El equipo de Classia
        está comprometido a iterar sobre los resultados de accesibilidad en cada sprint.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="accesibilidad-contacto">
      <h2 id="accesibilidad-contacto"><span class="section-num" aria-hidden="true">9</span> Reportar barreras de acceso</h2>
      <p>
        Si encontrás barreras de acceso, dificultades de uso o querés sugerir mejoras de accesibilidad,
        podés comunicarlo a través de la página de <a href="contacto.php">Contacto</a>.
        Nos comprometemos a responder en un plazo razonable y a incorporar las mejoras necesarias.
      </p>
    </section>
  </main>

<?php include '../includes/footer.php'; ?>
