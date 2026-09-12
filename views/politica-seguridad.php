<?php
$title       = 'Política de seguridad — Classia';
$description = 'Política de seguridad de Classia para proteger la información, los accesos y la confianza de la comunidad.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = '';
include '../includes/header.php';
?>

  <main class="privacy-page motion-entry">
    <header>
      <p>Políticas</p>
      <h1>Política de seguridad</h1>
      <p>
        Classia prioriza la seguridad de la información, los accesos y las operaciones dentro de la plataforma.
      </p>
    </header>

    <section class="privacy-section" aria-labelledby="seguridad-objetivo">
      <h2 id="seguridad-objetivo"><span class="section-num" aria-hidden="true">1</span> Objetivo</h2>
      <p>
        Proteger la confidencialidad, integridad y disponibilidad de los datos de usuarios, proveedores y
        administradores, además de asegurar el funcionamiento adecuado de los servicios ofrecidos por la plataforma.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="seguridad-principios">
      <h2 id="seguridad-principios"><span class="section-num" aria-hidden="true">2</span> Principios</h2>
      <ul>
        <li>Uso de contraseñas cifradas con hash seguro.</li>
        <li>Control de acceso por roles y permisos.</li>
        <li>Protección de sesiones y cookies.</li>
        <li>Validación de entradas y prevención de vulnerabilidades comunes.</li>
        <li>Monitoreo y respuesta ante incidentes.</li>
      </ul>
    </section>

    <section class="privacy-section" aria-labelledby="seguridad-estandares">
      <h2 id="seguridad-estandares"><span class="section-num" aria-hidden="true">3</span> Estándares y controles técnicos</h2>
      <p>
        El desarrollo toma como referencia OWASP Top 10 y OWASP ASVS para autenticación, sesiones, validación de entradas,
        control de acceso y protección de datos. También se aplican prácticas de CIS Controls v8, NIST CSF e ISO/IEC 27001:2022
        como marcos de gestión y mejora, sin afirmar una certificación formal.
      </p>
      <ul>
        <li>Consultas PDO preparadas, escape contextual en HTML y validaciones de servidor.</li>
        <li>Tokens CSRF generados con <code>random_bytes</code> y comparados con <code>hash_equals</code>.</li>
        <li>Contraseñas almacenadas con <code>password_hash</code>, regeneración de ID al autenticar y cookies HttpOnly/SameSite.</li>
        <li>RBAC para separar estudiantes, docentes y administradores, con comprobaciones en backend.</li>
        <li>Revisión automatizada mediante Gitleaks, Semgrep, npm audit y Lighthouse CI en el pipeline.</li>
      </ul>
    </section>

    <section class="privacy-section" aria-labelledby="seguridad-marcos">
      <h2 id="seguridad-marcos"><span class="section-num" aria-hidden="true">4</span> Marcos de referencia</h2>
      <div class="privacy-table-wrap">
        <table>
          <thead>
            <tr>
              <th>Marco</th>
              <th>Aplicación en Classia</th>
              <th>Alcance</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <th scope="row">CIS Controls v8</th>
              <td>Inventario de activos, protección de datos, gestión de cuentas, configuración segura y seguridad del software.</td>
              <td>Buenas prácticas operativas y técnicas.</td>
            </tr>
            <tr>
              <th scope="row">NIST CSF</th>
              <td>Identificar riesgos, proteger accesos y datos, detectar eventos, responder incidentes y recuperar el servicio.</td>
              <td>Organización del ciclo de gestión de riesgos.</td>
            </tr>
            <tr>
              <th scope="row">OWASP Top 10 / ASVS</th>
              <td>Validación de entrada, consultas preparadas, sesiones, CSRF, control de acceso, autenticación y manejo de errores.</td>
              <td>Seguridad de la aplicación web.</td>
            </tr>
            <tr>
              <th scope="row">ISO/IEC 27001:2022</th>
              <td>Referencia para políticas, responsabilidades, gestión de riesgos, control de accesos, proveedores y mejora continua.</td>
              <td>Gobernanza y sistema de gestión de seguridad.</td>
            </tr>
          </tbody>
        </table>
      </div>
      <p>
        Estos marcos orientan el diseño y la revisión del proyecto; su mención no significa que Classia o AniTech posean
        una certificación formal, auditoría de conformidad o acreditación externa.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="seguridad-pci">
      <h2 id="seguridad-pci"><span class="section-num" aria-hidden="true">5</span> Pagos y PCI DSS</h2>
      <p>
        El flujo actual de pagos es una simulación educativa. Classia no debe recibir ni almacenar números completos de
        tarjetas, códigos CVV, claves privadas ni credenciales de proveedores. Para una puesta en producción, el pago debe
        delegarse a una pasarela certificada, usar tokens del proveedor, verificar webhooks firmados y limitar el alcance
        de cumplimiento a las responsabilidades reales de cada parte según PCI DSS.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="seguridad-devsecops">
      <h2 id="seguridad-devsecops"><span class="section-num" aria-hidden="true">6</span> DevSecOps y CI/CD seguro</h2>
      <p>
        La seguridad se integra en el ciclo de desarrollo: revisión por pull request, ramas protegidas, mínimo privilegio
        en workflows y validaciones automáticas antes de integrar cambios.
      </p>
      <ul>
        <li><strong>Gitleaks:</strong> detección de secretos en commits y pull requests.</li>
        <li><strong>npm audit:</strong> revisión de dependencias con vulnerabilidades conocidas.</li>
        <li><strong>Semgrep:</strong> análisis SAST con reglas OWASP, JavaScript y secretos.</li>
        <li><strong>Lighthouse CI y scripts de seguridad:</strong> controles de accesibilidad, rendimiento y cabeceras.</li>
        <li><strong>Trazabilidad:</strong> cambios revisables, resultados de CI y separación entre configuración local y código versionado.</li>
      </ul>
      <p>
        Los workflows deben usar acciones fijadas a versiones confiables, permisos mínimos, secretos almacenados en GitHub
        Secrets y revisión de logs para evitar exposición de información sensible.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="seguridad-2fa">
      <h2 id="seguridad-2fa"><span class="section-num" aria-hidden="true">7</span> Autenticación reforzada</h2>
      <p>
        Classia prevé incorporar autenticación en dos pasos mediante códigos de un solo uso enviados al correo o celular
        verificado. El acceso con Google es autenticación federada y no se considera, por sí solo, un segundo factor.
        Hasta habilitar el desafío adicional, no se presenta como una protección ya disponible.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="seguridad-usuarios">
      <h2 id="seguridad-usuarios"><span class="section-num" aria-hidden="true">8</span> Responsabilidad del usuario</h2>
      <p>
        Cada usuario es responsable de mantener la confidencialidad de sus credenciales, no compartir su contraseña y
        utilizar la plataforma con un criterio de cuidado, especialmente en entornos compartidos o públicos.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="seguridad-contacto">
      <h2 id="seguridad-contacto"><span class="section-num" aria-hidden="true">9</span> Reportes</h2>
      <p>
        Si detectás un problema de seguridad, comunicalo por <a href="contacto.php">Contacto</a> o mediante el canal de
        reporte responsable especificado en la documentación del proyecto.
      </p>
    </section>
  </main>

<?php include '../includes/footer.php'; ?>
