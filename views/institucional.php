<?php

/**
 * Responsabilidad: Presentación institucional sobre la plataforma Classia y el equipo AniTech.
 */

$title       = 'Sobre Nosotros — Classia & AniTech';
$description = 'Conoce la historia, el equipo de desarrollo, la filosofía y el compromiso institucional detrás de Classia y AniTech.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = 'institucional';
include '../includes/header.php';
?>

  <main class="privacy-page institutional-page motion-entry">
    <header>
      <h1>Sobre Nosotros</h1>
      <p>
        Classia es una plataforma educativa integral nacida de la colaboración, el rigor técnico y la convicción
        de que la tecnología debe estar al servicio de las personas y de los procesos formativos reales.
      </p>
      <div class="institutional-slogan-box">
        <p>«Tecnología con propósito, estabilidad en cada línea y adaptabilidad frente a cada desafío.»</p>
      </div>
    </header>

    <!-- 1. ORIGEN E HISTORIA -->
    <section class="privacy-section" aria-labelledby="institucional-origen">
      <h2 id="institucional-origen"><span class="section-num" aria-hidden="true">1</span> El Origen de Classia</h2>
      <p>
        Classia nace del trabajo conjunto de tres amigos y compañeros en el marco de la asignatura <strong>Taller Integrador de Sistemas</strong>
        del Profesorado de Informática (CeRP del Litoral Norte). Lo que comenzó como un desafío académico evolucionó rápidamente en
        la creación de <strong>AniTech</strong>, una startup de desarrollo orientada a construir software sólido, accesible y con
        impacto positivo en el entorno educativo y profesional.
      </p>
      <p>
        Observando las dificultades comunes en la gestión de cursos, la dispersión de recursos y la falta de un canal directo y transparente
        para solicitar servicios formativos y tecnológicos (como laboratorios prácticos, impresión 3D o asesorías personalizadas), decidimos
        diseñar un ecosistema unificado que conecte a estudiantes, docentes y proveedores con claridad y seguridad.
      </p>

      <!-- Simbología AniTech -->
      <h3 style="margin-top: var(--space-5);">Nuestra Identidad y Simbología</h3>
      <p>
        La identidad visual y el isotipo de AniTech condensan los tres rasgos que definen nuestra forma de encarar la ingeniería de software:
      </p>
      <div class="symbols-grid">
        <div class="symbol-card">
          <span class="symbol-card__icon" aria-hidden="true">🦅</span>
          <span class="symbol-card__trait">Inteligencia Estratégica</span>
          <h4 class="symbol-card__title">El Cuervo</h4>
          <p class="symbol-card__desc">
            Representa la visión afilada, el pensamiento crítico y la capacidad de anticipar problemas de arquitectura antes de escribir la primera línea de código.
          </p>
        </div>
        <div class="symbol-card">
          <span class="symbol-card__icon" aria-hidden="true">🐢</span>
          <span class="symbol-card__trait">Estabilidad y Resiliencia</span>
          <h4 class="symbol-card__title">La Tortuga</h4>
          <p class="symbol-card__desc">
            Simboliza los cimientos firmes, la longevidad del sistema, la disciplina y la persistencia indispensable para construir soluciones duraderas y seguras.
          </p>
        </div>
        <div class="symbol-card">
          <span class="symbol-card__icon" aria-hidden="true">🐬</span>
          <span class="symbol-card__trait">Adaptabilidad y Empatía</span>
          <h4 class="symbol-card__title">El Delfín</h4>
          <p class="symbol-card__desc">
            Encarna la comunicación fluida en equipo, la agilidad para responder a los cambios y la cercanía constante con la experiencia real de los usuarios.
          </p>
        </div>
      </div>
    </section>

    <!-- 2. CONOCE AL EQUIPO -->
    <section class="privacy-section" aria-labelledby="institucional-equipo">
      <h2 id="institucional-equipo"><span class="section-num" aria-hidden="true">2</span> Conoce al Equipo</h2>
      <p>
        Detrás de Classia no hay soluciones genéricas ni automatizaciones impersonales: hay tres perfiles complementarios
        que combinan desarrollo, testing, gestión, seguridad y una pasión genuina por resolver problemas reales.
      </p>

      <div class="team-grid">
        <!-- Ezequiel Costa -->
        <article class="team-card">
          <div class="team-card__img-wrap">
            <img src="<?= $cssPrefix ?>/assets/images/team/EzequielPhotoPic.png" alt="Foto de Ezequiel Costa" class="team-card__img" loading="lazy" />
          </div>
          <div class="team-card__body">
            <h3 class="team-card__name">Ezequiel Costa</h3>
            <p class="team-card__role">Director Técnico de Operaciones · Lead Full Stack &amp; UX</p>
            <blockquote class="team-card__quote">
              «Si no lo hacés, tenés que pagar para que lo hagan.»
              <cite>— Ezequiel Costa</cite>
            </blockquote>
            
            <div class="team-card__interest-quote">
              <span class="interest-label">☕ Inspiración · The Mentalist</span>
              <em>«No hay trucos mágicos: si prestás suficiente atención, los detalles y la lógica siempre te dicen exactamente dónde mirar.»</em>
              <cite>— Patrick Jane (The Mentalist)</cite>
            </div>

            <p class="team-card__bio">
              El «manitas» por excelencia de AniTech. Su premisa operativa es sencilla: si algo no se sabe, se desarma, se investiga
              y se aprende hasta dominarlo. Con un recorrido laboral tan variado que parece conocer a medio mundo, aporta una mirada pragmática,
              despierta y dialéctica (con un inconfundible estilo sofista). Gran observador y apasionado de <em>The Mentalist</em>, traslada ese
              olfato deductivo tanto a la arquitectura de sistemas como a la optimización de procesos y la experiencia de usuario.
            </p>
            <div class="team-card__tags">
              <span class="team-card__tag">Operaciones</span>
              <span class="team-card__tag">Full Stack</span>
              <span class="team-card__tag">UX/UI</span>
              <span class="team-card__tag">Arquitectura</span>
            </div>
          </div>
        </article>

        <!-- Mateus Moraes -->
        <article class="team-card">
          <div class="team-card__img-wrap">
            <img src="<?= $cssPrefix ?>/assets/images/team/MateusPhotoPic.png" alt="Foto de Mateus Moraes" class="team-card__img" loading="lazy" />
          </div>
          <div class="team-card__body">
            <h3 class="team-card__name">Mateus Moraes</h3>
            <p class="team-card__role">Director Técnico de Testing · QA Lead &amp; Full Stack</p>
            <div class="team-card__interest-quote">
              <span class="interest-label">👻 Inspiración · Pokémon &amp; Gengar</span>
              <em>«En la oscuridad, hasta tu propia sombra puede adelantarte. Para ganar no basta con fuerza: hay que anticipar cada movimiento desde las sombras.»</em>
              <cite>— Pokédex &amp; Lore de Gengar (Pokémon)</cite>
            </div>

            <p class="team-card__bio">
              El bastión de la calidad y la robustez del sistema. Jugador y estratega experto del universo Pokémon, con una fijación
              especial por Gengar, aplica esa misma precisión táctica a la hora de buscar casos de borde y validar cada flujo del software.
              Deportista de alma que desafía toda recomendación saliendo a correr sin precalentar, equilibra el rigor técnico con su faceta
              artística componiendo música y rimando como rapero under.
            </p>
            <div class="team-card__tags">
              <span class="team-card__tag">QA &amp; Testing</span>
              <span class="team-card__tag">Seguridad</span>
              <span class="team-card__tag">Backend PHP</span>
              <span class="team-card__tag">Validación</span>
            </div>
          </div>
        </article>

        <!-- Thiago Sosa -->
        <article class="team-card">
          <div class="team-card__img-wrap">
            <img src="<?= $cssPrefix ?>/assets/images/team/ThiagoPhotoPic.png" alt="Foto de Thiago Sosa" class="team-card__img" loading="lazy" />
          </div>
          <div class="team-card__body">
            <h3 class="team-card__name">Thiago Sosa</h3>
            <p class="team-card__role">Director de Gestión Operativa · Scrum Master &amp; Ciberseguridad</p>
            <blockquote class="team-card__quote">
              «Aprender de todo para conectar lo que otros ven fragmentado.»
              <cite>— Thiago Sosa</cite>
            </blockquote>
            
            <div class="team-card__interest-quote">
              <span class="interest-label">🌐 Inspiración · Watch Dogs</span>
              <em>«Todo está conectado. La ilusión del control termina donde empieza el conocimiento: la verdadera seguridad es comprender cómo fluyen los sistemas.»</em>
              <cite>— Aiden Pearce / DedSec (Watch Dogs)</cite>
            </div>

            <p class="team-card__bio">
              Dizque filósofo, poeta y escritor. Con un perfil de aspirante a polímata y una curiosidad generalista insaciable, se mueve
              entre el altruismo, el pensamiento reflexivo y el espíritu libre. Su fascinación por la franquicia <em>Watch Dogs</em> encendió
              tempranamente su vocación por la ciberseguridad, el cifrado y la defensa de la privacidad, liderando la articulación ágil del equipo,
              el diseño de interfaces y la integridad normativa de la plataforma.
            </p>
            <div class="team-card__tags">
              <span class="team-card__tag">Scrum Master</span>
              <span class="team-card__tag">Ciberseguridad</span>
              <span class="team-card__tag">UX/UI</span>
              <span class="team-card__tag">Privacidad</span>
            </div>
          </div>
        </article>
      </div>
    </section>

    <!-- 3. QUIENES NOS APOYAN (MASCOTAS / SOPORTE INCONDICIONAL) -->
    <section class="privacy-section" aria-labelledby="institucional-soporte-mascotas">
      <h2 id="institucional-soporte-mascotas"><span class="section-num" aria-hidden="true">3</span> Quienes nos apoyan: Soporte Incondicional</h2>
      <p>
        Ningún sprint exitoso ni ninguna larga noche de debugging hubiera sido posible sin nuestro departamento honorario de soporte emocional,
        supervisión de código y control estricto de descansos: nuestras mascotas.
      </p>

      <div class="supporters-grid">
        <!-- Ezequiel Dogs -->
        <article class="supporter-card">
          <div class="supporter-card__img-wrap">
            <img src="<?= $cssPrefix ?>/assets/images/team/Perro1Ezequiel.jpeg" alt="Compañero canino de Ezequiel" class="supporter-card__img" loading="lazy" />
          </div>
          <div class="supporter-card__body">
            <h4 class="supporter-card__title">Guardián Canino I</h4>
            <p class="supporter-card__subtitle">Equipo Ezequiel</p>
            <p class="supporter-card__desc">Auditor principal de pausas activas y soporte en horas pico de deploy.</p>
          </div>
        </article>

        <article class="supporter-card">
          <div class="supporter-card__img-wrap">
            <img src="<?= $cssPrefix ?>/assets/images/team/Perro2Ezequiel.jpeg" alt="Compañero canino de Ezequiel" class="supporter-card__img" loading="lazy" />
          </div>
          <div class="supporter-card__body">
            <h4 class="supporter-card__title">Guardián Canino II</h4>
            <p class="supporter-card__subtitle">Equipo Ezequiel</p>
            <p class="supporter-card__desc">Especialista en vigilancia perimetral y supervisión de cableado.</p>
          </div>
        </article>

        <article class="supporter-card">
          <div class="supporter-card__img-wrap">
            <img src="<?= $cssPrefix ?>/assets/images/team/Perro3Ezequiel.jpeg" alt="Compañero canino de Ezequiel" class="supporter-card__img" loading="lazy" />
          </div>
          <div class="supporter-card__body">
            <h4 class="supporter-card__title">Guardián Canino III</h4>
            <p class="supporter-card__subtitle">Equipo Ezequiel</p>
            <p class="supporter-card__desc">Compañero incondicional de escritorio y testeador de ergonomía.</p>
          </div>
        </article>

        <article class="supporter-card">
          <div class="supporter-card__img-wrap">
            <img src="<?= $cssPrefix ?>/assets/images/team/Perro4Ezequiel.jpeg" alt="Compañero canino de Ezequiel" class="supporter-card__img" loading="lazy" />
          </div>
          <div class="supporter-card__body">
            <h4 class="supporter-card__title">Guardián Canino IV</h4>
            <p class="supporter-card__subtitle">Equipo Ezequiel</p>
            <p class="supporter-card__desc">Experto en levantamiento de ánimos y rondas de inspección técnica.</p>
          </div>
        </article>

        <!-- Mateus Cat & Parrot -->
        <article class="supporter-card">
          <div class="supporter-card__img-wrap">
            <img src="<?= $cssPrefix ?>/assets/images/team/Gato1mateus.jpeg" alt="Gato de Mateus" class="supporter-card__img" loading="lazy" />
          </div>
          <div class="supporter-card__body">
            <h4 class="supporter-card__title">Supervisor Felino</h4>
            <p class="supporter-card__subtitle">Equipo Mateus</p>
            <p class="supporter-card__desc">Encargado oficial de sentarse sobre el teclado durante el code review.</p>
          </div>
        </article>

        <article class="supporter-card">
          <div class="supporter-card__img-wrap">
            <img src="<?= $cssPrefix ?>/assets/images/team/Loro1Mateus.jpeg" alt="Loro de Mateus" class="supporter-card__img" loading="lazy" />
          </div>
          <div class="supporter-card__body">
            <h4 class="supporter-card__title">Loro Animador</h4>
            <p class="supporter-card__subtitle">Equipo Mateus</p>
            <p class="supporter-card__desc">Voz de aliento acústica y corista en sesiones de composición y rap.</p>
          </div>
        </article>

        <!-- Thiago Dogs, Parrots & Pigeon -->
        <article class="supporter-card">
          <div class="supporter-card__img-wrap">
            <img src="<?= $cssPrefix ?>/assets/images/team/Perros1Thiago.jpeg" alt="Perros de Thiago" class="supporter-card__img" loading="lazy" />
          </div>
          <div class="supporter-card__body">
            <h4 class="supporter-card__title">Dúo Canino</h4>
            <p class="supporter-card__subtitle">Equipo Thiago</p>
            <p class="supporter-card__desc">Socios de debate filosófico y fieles acompañantes de madrugadas.</p>
          </div>
        </article>

        <article class="supporter-card">
          <div class="supporter-card__img-wrap">
            <img src="<?= $cssPrefix ?>/assets/images/team/Loros1Thiago.jpeg" alt="Loros de Thiago" class="supporter-card__img" loading="lazy" />
          </div>
          <div class="supporter-card__body">
            <h4 class="supporter-card__title">Loros Centinelas</h4>
            <p class="supporter-card__subtitle">Equipo Thiago</p>
            <p class="supporter-card__desc">Vigilantes aéreos de sprints y promotores de creatividad libre.</p>
          </div>
        </article>

        <article class="supporter-card">
          <div class="supporter-card__img-wrap">
            <img src="<?= $cssPrefix ?>/assets/images/team/Paloma1Thiago.jpeg" alt="Paloma de Thiago" class="supporter-card__img" loading="lazy" />
          </div>
          <div class="supporter-card__body">
            <h4 class="supporter-card__title">Paloma Mensajera</h4>
            <p class="supporter-card__subtitle">Equipo Thiago</p>
            <p class="supporter-card__desc">Símbolo de paz, resiliencia y serenidad en momentos de refactorización.</p>
          </div>
        </article>
      </div>
    </section>

    <!-- 4. IDENTIDAD EMPRESARIAL (MISIÓN, VISIÓN, VALORES) -->
    <section class="privacy-section" aria-labelledby="institucional-filosofia">
      <h2 id="institucional-filosofia"><span class="section-num" aria-hidden="true">4</span> Filosofía Empresarial</h2>
      
      <div class="pillars-grid">
        <div class="pillar-card">
          <h3 class="pillar-card__title">Misión</h3>
          <p class="pillar-card__desc">
            Desarrollar soluciones digitales a medida que resuelvan problemas reales de negocio y educación,
            combinando solidez técnica, seguridad por diseño, interfaces claras y acompañamiento cercano durante cada etapa del ciclo de vida del software.
          </p>
        </div>

        <div class="pillar-card">
          <h3 class="pillar-card__title">Visión</h3>
          <p class="pillar-card__desc">
            Consolidarnos como un equipo y empresa de referencia en el desarrollo de plataformas tecnológicas y educativas,
            destacándonos por la adaptabilidad a entornos cambiantes, la ética profesional y la creación de valor comunitario sustentable.
          </p>
        </div>

        <div class="pillar-card">
          <h3 class="pillar-card__title">Valores</h3>
          <p class="pillar-card__desc">
            Rigor técnico sin atajos, honestidad en los alcances, respeto por la privacidad de los usuarios,
            mejora continua mediante feedback sincero y trabajo colaborativo sin jerarquías rígidas.
          </p>
        </div>
      </div>
    </section>

    <!-- 5. METODOLOGÍA Y AMBIENTE INSTITUCIONAL -->
    <section class="privacy-section" aria-labelledby="institucional-metodologia">
      <h2 id="institucional-metodologia"><span class="section-num" aria-hidden="true">5</span> Metodología y Ambiente de Trabajo</h2>
      <p>
        En AniTech entendemos que la calidad del código es un reflejo directo de la salud del equipo. Trabajamos con una estructura
        horizontal y transparente basada en buenas prácticas de la industria:
      </p>
      <ul>
        <li>
          <strong>Marco Ágil (Scrum adaptado):</strong> Sprints iterativos con metas claras, reuniones diarias de sincronización y
          retrospectivas orientadas a detectar mejoras reales en los procesos.
        </li>
        <li>
          <strong>Enfoque DevSecOps:</strong> La seguridad no es una etapa final; se integra desde el diseño de tablas y contratos de API hasta
          el manejo de sesiones, consultas preparadas PDO, tokens CSRF y hash de contraseñas.
        </li>
        <li>
          <strong>Control de Versiones y Code Review:</strong> Uso estricto de Git y GitHub mediante pull requests revisados por pares antes de
          cualquier integración a ramas principales.
        </li>
        <li>
          <strong>Ambiente Institucional Saludable:</strong> Flexibilidad orientada a objetivos, comunicación abierta por canales organizados y
          prioridad al diálogo constructivo para la resolución de cualquier desafío.
        </li>
      </ul>
    </section>

    <!-- 6. CAUSAS SOCIALES Y OBJETIVOS HUMANITARIOS -->
    <section class="privacy-section" aria-labelledby="institucional-social">
      <h2 id="institucional-social"><span class="section-num" aria-hidden="true">6</span> Compromiso Social y Objetivos Humanitarios</h2>
      <p>
        Classia no fue concebida únicamente como una herramienta comercial, sino como un puente para reducir brechas educativas y tecnológicas:
      </p>
      <div class="pillars-grid">
        <div class="pillar-card">
          <h3 class="pillar-card__title">Democratización Educativa</h3>
          <p class="pillar-card__desc">
            Facilitar el acceso a cursos prácticos y mentorías técnicas de nivel profesional para estudiantes de instituciones públicas y comunidades del interior.
          </p>
        </div>

        <div class="pillar-card">
          <h3 class="pillar-card__title">Accesibilidad e Inclusión</h3>
          <p class="pillar-card__desc">
            Diseño adaptado a estándares WCAG, asegurando navegación por teclado, alto contraste, compatibilidad con lectores de pantalla y semántica HTML5 estricta.
          </p>
        </div>

        <div class="pillar-card">
          <h3 class="pillar-card__title">Fomento a Emprendedores y PyMEs</h3>
          <p class="pillar-card__desc">
            Brindar servicios especializados de digitalización, prototipado 3D y consultoría tecnológica a costos accesibles para proyectos emergentes locales.
          </p>
        </div>
      </div>
    </section>

    <!-- 7. ESTADÍSTICAS DEL PROYECTO -->
    <section class="privacy-section" aria-labelledby="institucional-metricas">
      <h2 id="institucional-metricas"><span class="section-num" aria-hidden="true">7</span> Métricas y Estadísticas Clave</h2>
      <p>
        Nuestra evolución técnica se sustenta en datos concretos de desarrollo y validación continua:
      </p>

      <div class="stats-grid">
        <div class="stat-card">
          <span class="stat-card__number">4+</span>
          <span class="stat-card__label">Sprints Completados</span>
          <span class="stat-card__desc">Entregas iterativas con valor funcional tangible</span>
        </div>

        <div class="stat-card">
          <span class="stat-card__number">100%</span>
          <span class="stat-card__label">Código Propio Modular</span>
          <span class="stat-card__desc">Arquitectura limpia en PHP, JS y CSS estructurado</span>
        </div>

        <div class="stat-card">
          <span class="stat-card__number">3</span>
          <span class="stat-card__label">Áreas en Sinergia</span>
          <span class="stat-card__desc">Desarrollo, Testing QA y Ciberseguridad integrados</span>
        </div>

        <div class="stat-card">
          <span class="stat-card__number">0</span>
          <span class="stat-card__label">Atajos en Seguridad</span>
          <span class="stat-card__desc">Cifrado, 2FA, CSRF y PDO en toda la plataforma</span>
        </div>
      </div>
    </section>

    <!-- 8. CONTACTO Y ENLACE INSTITUCIONAL -->
    <section class="privacy-section" aria-labelledby="institucional-contacto">
      <h2 id="institucional-contacto"><span class="section-num" aria-hidden="true">8</span> Contacto e Información Corporativa</h2>
      <p>
        Si representás a una institución educativa, empresa o colectivo interesado en colaborar, contratar servicios
        o conocer más sobre nuestros desarrollos, estamos a disposición.
      </p>
      <div class="privacy-contact-box">
        <h3>¿Querés ponerte en contacto con el equipo de AniTech?</h3>
        <p style="margin: var(--space-2) 0 var(--space-4); color: var(--color-text-muted);">
          Podés escribirnos directamente a través de nuestro formulario oficial o consultar nuestras políticas de servicio.
        </p>
        <div style="display: flex; justify-content: center; gap: var(--space-3); flex-wrap: wrap;">
          <a href="contacto.php" class="btn btn-primary" style="font-weight: 700;">Formulario de Contacto</a>
          <a href="politica-privacidad.php" class="btn btn-secondary">Política de Privacidad</a>
        </div>
      </div>
    </section>
  </main>

<?php include '../includes/footer.php'; ?>
