<?php require_once '../php/publicaciones/obtenerPublicaciones.php'; ?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <meta
      name="description"
      content="Panel de gestión para proveedores de cursos y servicios en Classia." />

    <title>Panel proveedor</title>
    <link rel="icon" type="image/png" href="../assets/favicon.png" />
    <link rel="stylesheet" href="../css/animation.css" />
    <link rel="stylesheet" href="../css/style.css" />
  </head>

  <body>
    <header class="site-header">
      <div class="site-header__inner">
        <a class="site-brand" href="../index.php">
          <img src="../assets/logoC.png" />
        </a>
        <nav class="site-nav" aria-label="Navegación principal">
          <a href="../index.php">Inicio</a>
          <a href="catalogo.php">Catálogo</a>
          <a href="carrito.php">Carrito</a>
          <a href="login.php" aria-current="page">Mi cuenta</a>
        </nav>
      </div>
    </header>

    <main class="provider-dashboard">
      <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 12px 16px; margin: 15px 0; border-radius: 6px; font-weight: bold;">
          <?php 
            if ($_GET['mensaje'] === 'creada') echo "Publicación creada exitosamente.";
            elseif ($_GET['mensaje'] === 'actualizada') echo "Publicación actualizada correctamente.";
            elseif ($_GET['mensaje'] === 'estado_actualizado') echo "El estado de la publicación se actualizó correctamente.";
          ?>
        </div>
      <?php endif; ?>

      <header>

        <h1>Panel del proveedor</h1>

        <p>
          Gestiona tus cursos, servicios, solicitudes, estudiantes y datos
          profesionales desde un único lugar.
        </p>

        <p>
          Sesión iniciada como
          <strong>Ana Fernández</strong>
        </p>

        <nav aria-label="Acciones rápidas del proveedor">
          <ul>
            <li>
              <a href="crearPublicacion.php">
                Publicar un curso o servicio
              </a>
            </li>

            <li>
              <a href="#publicaciones"> Gestionar publicaciones </a>
            </li>

            <li>
              <a href="#solicitudes"> Ver solicitudes </a>
            </li>

            <li>
              <a href="#perfil"> Editar perfil profesional </a>
            </li>
          </ul>
        </nav>
      </header>

      <section aria-labelledby="titulo-resumen">
        <h2 id="titulo-resumen">Resumen de actividad</h2>

        <article>
          <h3>Publicaciones activas</h3>

          <p>
            <strong>5</strong>
          </p>

          <a href="#publicaciones"> Ver publicaciones </a>
        </article>

        <article>
          <h3>Estudiantes inscriptos</h3>

          <p>
            <strong>78</strong>
          </p>

          <a href="#estudiantes"> Ver estudiantes </a>
        </article>

        <article>
          <h3>Solicitudes pendientes</h3>

          <p>
            <strong>4</strong>
          </p>

          <a href="#solicitudes"> Revisar solicitudes </a>
        </article>

        <article>
          <h3>Servicios en proceso</h3>

          <p>
            <strong>3</strong>
          </p>

          <a href="#contrataciones"> Ver contrataciones </a>
        </article>

        <article>
          <h3>Valoración promedio</h3>

          <p>
            <strong>4,8 de 5</strong>
          </p>

          <a href="#valoraciones"> Ver valoraciones </a>
        </article>

        <article>
          <h3>Ingresos simulados</h3>

          <p>
            <strong>$18.750</strong>
          </p>

          <p>
            Total correspondiente a contrataciones ficticias realizadas en la
            plataforma.
          </p>
        </article>
      </section>

      <section aria-labelledby="titulo-notificaciones">
        <header>
          <h2 id="titulo-notificaciones">Notificaciones</h2>

          <p>Tienes 5 notificaciones sin revisar.</p>

          <button type="button">Marcar todas como leídas</button>
        </header>

        <article>
          <header>
            <h3>Nueva solicitud de impresión 3D</h3>

            <time datetime="2026-07-27T15:40"> Hoy, 15:40 </time>
          </header>

          <p>
            El Centro Educativo del Norte solicita un presupuesto para imprimir
            veinte piezas destinadas a un proyecto de geometría.
          </p>

          <a href="#solicitudes"> Revisar solicitud </a>
        </article>

        <article>
          <header>
            <h3>Nueva inscripción en un curso</h3>

            <time datetime="2026-07-27T13:15"> Hoy, 13:15 </time>
          </header>

          <p>
            María González se inscribió en el curso Desarrollo web con HTML, CSS
            y JavaScript.
          </p>

          <a href="#estudiantes"> Ver inscripción </a>
        </article>

        <article>
          <header>
            <h3>Solicitud de mentoría</h3>

            <time datetime="2026-07-26T20:10"> Ayer, 20:10 </time>
          </header>

          <p>
            Andrés Pereira solicita una mentoría para revisar la arquitectura de
            su proyecto de software educativo.
          </p>

          <a href="#solicitudes"> Consultar disponibilidad </a>
        </article>

        <article>
          <header>
            <h3>Nueva valoración recibida</h3>

            <time datetime="2026-07-25T18:30"> 25 de julio, 18:30 </time>
          </header>

          <p>
            Tu servicio Diseño de recursos didácticos 3D recibió una valoración
            de 5 estrellas.
          </p>

          <a href="#valoraciones"> Leer valoración </a>
        </article>

        <article>
          <header>
            <h3>Servicio próximo a vencer</h3>

            <time datetime="2026-07-24T09:00"> 24 de julio, 09:00 </time>
          </header>

          <p>
            La publicación Taller de robótica educativa tiene disponibilidad
            cargada únicamente hasta el próximo mes.
          </p>

          <a href="formsSolicitarServicio.php"> Actualizar disponibilidad </a>
        </article>

        <p>
          <a href="#titulo-notificaciones"> Ver todas las notificaciones </a>
        </p>
      </section>

      <section id="publicaciones" aria-labelledby="titulo-publicaciones">
        <header>
          <h2 id="titulo-publicaciones">Mis cursos y servicios</h2>

          <p>Consulta y administra las publicaciones asociadas a tu perfil (Alta, Consulta, Edición y Baja lógica).</p>

          <a href="crearPublicacion.php" class="btn btn-primary-action">
            + Crear nueva publicación
          </a>
        </header>

        <br>

        <?php if (empty($publicaciones_proveedor)): ?>
          <p>No tenés publicaciones registradas aún. <a href="crearPublicacion.php">Creá tu primera publicación</a>.</p>
        <?php else: ?>
          <div class="publicaciones-lista">
            <?php foreach ($publicaciones_proveedor as $pub): ?>
              <article class="pub-card">
                <header>
                  <h4>
                    <a href="servicioDetalle.php?id=<?php echo $pub['id_publicacion']; ?>">
                      <?php echo htmlspecialchars($pub['titulo']); ?>
                    </a>
                  </h4>
                  <span class="pub-badge">
                    <?php echo htmlspecialchars($pub['tipo']); ?> — <?php echo htmlspecialchars($pub['nombre_categoria']); ?>
                  </span>
                </header>

                <p class="pub-desc">
                  <?php echo htmlspecialchars($pub['descripcion']); ?>
                </p>

                <dl class="pub-details">
                  <div>
                    <dt><strong>Estado:</strong></dt>
                    <dd>
                      <?php 
                        $statusClass = ($pub['estado'] === 'Activo') ? 'status-activo' : (($pub['estado'] === 'Pausado') ? 'status-pausado' : 'status-inactivo');
                      ?>
                      <span class="<?php echo $statusClass; ?>">
                        <?php echo htmlspecialchars($pub['estado']); ?>
                      </span>
                    </dd>
                  </div>

                  <div>
                    <dt><strong>Precio:</strong></dt>
                    <dd>$<?php echo number_format($pub['precio'], 2); ?></dd>
                  </div>

                  <div>
                    <dt><strong>Fecha de creación:</strong></dt>
                    <dd><?php echo date('d/m/Y', strtotime($pub['fecha_creacion'])); ?></dd>
                  </div>
                </dl>

                <nav aria-label="Acciones de la publicación <?php echo htmlspecialchars($pub['titulo']); ?>">
                  <ul class="pub-actions">
                    <li>
                      <a href="editarPublicacion.php?id=<?php echo $pub['id_publicacion']; ?>" class="pub-actions-link">
                        Editar
                      </a>
                    </li>

                    <li>
                      <form action="editarPublicacion.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <input type="hidden" name="id_publicacion" value="<?php echo (int)$pub['id_publicacion']; ?>">
                        
                        <?php if ($pub['estado'] === 'Activo'): ?>
                          <button type="submit" name="cambiar_estado" value="Pausado" class="btn-status btn-status-pause">
                            Pausar
                          </button>
                        <?php else: ?>
                          <button type="submit" name="cambiar_estado" value="Activo" class="btn-status btn-status-activate">
                            Activar
                          </button>
                        <?php endif; ?>
                      </form>
                    </li>

                    <?php if ($pub['estado'] !== 'Inactivo'): ?>
                      <li>
                        <form action="editarPublicacion.php" method="POST">
                          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                          <input type="hidden" name="id_publicacion" value="<?php echo (int)$pub['id_publicacion']; ?>">
                          <button type="submit" name="cambiar_estado" value="Inactivo" onclick="return confirm('¿Confirmás que querés dar de baja esta publicación?');" class="btn-status btn-status-delete">
                            Dar de baja (Inactivar)
                          </button>
                        </form>
                      </li>
                    <?php endif; ?>
                  </ul>
                </nav>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>


        <article>
          <header>
            <h3>Solicitud de mentoría</h3>

            <p>
              Estado:
              <strong>Pendiente de confirmación</strong>
            </p>
          </header>

          <dl>
            <div>
              <dt>Cliente</dt>
              <dd>Andrés Pereira</dd>
            </div>

            <div>
              <dt>Servicio</dt>
              <dd>Mentoría para proyectos de software educativos</dd>
            </div>

            <div>
              <dt>Fecha solicitada</dt>
              <dd>
                <time datetime="2026-08-02T18:00">
                  2 de agosto de 2026, 18:00
                </time>
              </dd>
            </div>

            <div>
              <dt>Duración</dt>
              <dd>1 hora</dd>
            </div>

            <div>
              <dt>Precio</dt>
              <dd>$750</dd>
            </div>
          </dl>

          <p>
            El cliente necesita ayuda para revisar el modelo de base de datos de
            una plataforma educativa.
          </p>

          <nav aria-label="Acciones de la solicitud de mentoría">
            <ul>
              <li>
                <a href="#solicitudes"> Ver detalles </a>
              </li>

              <li>
                <button type="button">Confirmar reserva</button>
              </li>

              <li>
                <a href="#solicitudes"> Proponer otro horario </a>
              </li>

              <li>
                <button type="button">Rechazar</button>
              </li>
            </ul>
          </nav>
        </article>

        <p>
          <a href="#solicitudes"> Ver todas las solicitudes </a>
        </p>
      </section>

      <section id="estudiantes" aria-labelledby="titulo-estudiantes">
        <header>
          <h2 id="titulo-estudiantes">Estudiantes</h2>

          <p>Consulta la cantidad de estudiantes inscriptos en tus cursos.</p>
        </header>

        <article>
          <h3>Desarrollo web con HTML, CSS y JavaScript</h3>

          <dl>
            <div>
              <dt>Estudiantes inscriptos</dt>
              <dd>48</dd>
            </div>

            <div>
              <dt>Estudiantes activos</dt>
              <dd>39</dd>
            </div>

            <div>
              <dt>Finalizaron el curso</dt>
              <dd>9</dd>
            </div>
          </dl>

          <a href="#estudiantes"> Ver lista de estudiantes </a>
        </article>

        <article>
          <h3>Inteligencia artificial aplicada a la enseñanza</h3>

          <dl>
            <div>
              <dt>Estudiantes inscriptos</dt>
              <dd>30</dd>
            </div>

            <div>
              <dt>Estudiantes activos</dt>
              <dd>26</dd>
            </div>

            <div>
              <dt>Finalizaron el curso</dt>
              <dd>4</dd>
            </div>
          </dl>

          <a href="#estudiantes"> Ver lista de estudiantes </a>
        </article>
      </section>

      <section aria-labelledby="titulo-habilidades">
        <header>
          <h2 id="titulo-habilidades">Habilidades y especialidades</h2>

          <p>
            Estas habilidades se muestran en tu perfil público y ayudan a los
            usuarios a conocer tu experiencia.
          </p>
        </header>

        <ul>
          <li>Desarrollo web</li>
          <li>HTML5</li>
          <li>CSS3</li>
          <li>JavaScript</li>
          <li>PHP</li>
          <li>MySQL</li>
          <li>Diseño e impresión 3D</li>
          <li>Robótica educativa</li>
          <li>Micro:bit</li>
          <li>Diseño de proyectos educativos</li>
          <li>Formación docente</li>
          <li>Mentoría tecnológica</li>
        </ul>

        <a href="#perfil"> Editar habilidades </a>
      </section>

      <section aria-labelledby="titulo-perfil-profesional">
        <br>
        <header>
          <h2 id="titulo-perfil-profesional">Perfil profesional</h2>

          <p>Información visible para los posibles clientes y estudiantes.</p>
        </header>

        <h3>Ana Fernández</h3>

        <p>
          Docente de Informática y desarrolladora especializada en tecnología
          educativa, proyectos de software y fabricación digital.
        </p>

        <dl>
          <div>
            <dt>Ubicación</dt>
            <dd>Salto, Uruguay</dd>
          </div>

          <div>
            <dt>Miembro desde</dt>
            <dd>Marzo de 2026</dd>
          </div>

          <div>
            <dt>Tiempo de respuesta</dt>
            <dd>Menos de 4 horas</dd>
          </div>

          <div>
            <dt>Idiomas</dt>
            <dd>Español e inglés</dd>
          </div>

          <div>
            <dt>Valoración promedio</dt>
            <dd>4,8 de 5</dd>
          </div>

          <div>
            <dt>Trabajos completados</dt>
            <dd>65</dd>
          </div>
        </dl>

        <a href="usuario.php"> Ver perfil público </a>

        <a href="#perfil"> Editar información </a>
      </section>

      <section id="valoraciones" aria-labelledby="titulo-valoraciones">
        <header>
          <h2 id="titulo-valoraciones">Valoraciones recientes</h2>

          <p>Opiniones recibidas en cursos y servicios finalizados.</p>
        </header>

        <article>
          <header>
            <h3>Centro Educativo del Norte</h3>

            <p>5 de 5</p>

            <time datetime="2026-07-25"> 25 de julio de 2026 </time>
          </header>

          <p>
            El proveedor comprendió nuestras necesidades y entregó materiales de
            excelente calidad.
          </p>

          <p>Servicio: Diseño e impresión de recursos didácticos 3D.</p>
        </article>

        <article>
          <header>
            <h3>María González</h3>

            <p>5 de 5</p>

            <time datetime="2026-07-18"> 18 de julio de 2026 </time>
          </header>

          <p>
            El curso fue claro, organizado y muy útil para comenzar a
            desarrollar páginas web.
          </p>

          <p>Curso demo: Robótica para principiantes.</p>
        </article>
        <br>
        <a href="#valoraciones"> Ver todas las valoraciones </a>
      </section>
      <br>
      <section aria-labelledby="titulo-configuracion">
        <h2 id="titulo-configuracion">
          Configuración del espacio de proveedor
        </h2>
        <br>
        <nav aria-label="Configuración del proveedor">
          <ul>
            <li>
              <a href="#perfil"> Datos profesionales </a>
            </li>

            <li>
              <a href="#titulo-configuracion"> Disponibilidad </a>
            </li>

            <li>
              <a href="#titulo-configuracion"> Notificaciones </a>
            </li>

            <li>
              <a href="#titulo-configuracion">
                Datos de facturación ficticios
              </a>
            </li>

            <li>
              <a href="#titulo-configuracion"> Seguridad de la cuenta </a>
            </li>
          </ul>
        </nav>
      </section>
    </main>

    <footer class="site-footer">
      <p>
        Classia — Plataforma de comercialización de cursos y servicios
        educativos
      </p>

      <nav aria-label="Navegación secundaria">
        <ul>
          <li>
            <a href="../index.php">Inicio</a>
          </li>

          <li>
            <a href="catalogo.php">Catálogo</a>
          </li>

          <li>
            <a href="usuario.php">Mi perfil</a>
          </li>

          <li>
            <a href="../index.php">Acerca de</a>
          </li>

          <li>
            <a href="../index.php">Contacto</a>
          </li>

          <li>
            <a href="../index.php">Privacidad</a>
          </li>
        </ul>
      </nav>

      <p>
        <small> &copy; 2026 Classia. Todos los derechos reservados. </small>
      </p>
    </footer>
  </body>
</html>
