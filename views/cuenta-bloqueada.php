<?php

/**
 * Responsabilidad: Página informativa para cuentas bloqueadas o suspendidas.
 * No requiere autenticación – el usuario ya fue desautenticado antes de llegar aquí.
 */

require_once '../php/auth/sesion.php';

// Iniciar sesión para leer el motivo (si existe)
iniciar_sesion();

// Si llegaron aquí sin estar bloqueados (acceso directo), redirigir al inicio
if (esta_autenticado()) {
    header('Location: usuario.php');
    exit;
}

$motivo = '';
if (isset($_SESSION['cuenta_bloqueada_motivo'])) {
    $motivo = $_SESSION['cuenta_bloqueada_motivo'];
    unset($_SESSION['cuenta_bloqueada_motivo']);
}

// Verificar que hay un motivo real de bloqueo (no es acceso directo sin motivo)
// Si no hay motivo, aún mostramos la página pero con mensaje genérico

$title       = 'Cuenta suspendida';
$description = 'Tu cuenta ha sido suspendida por la administración de Classia.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = 'cuenta';

include '../includes/header.php';
?>

<style>
/* ── Página cuenta bloqueada ───────────────────────── */
.blocked-shell {
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem 1.25rem;
}

.blocked-card {
    background: var(--color-surface, #1a1a2e);
    border: 1px solid rgba(239, 68, 68, 0.25);
    border-radius: 1.5rem;
    box-shadow:
        0 0 0 1px rgba(239, 68, 68, 0.08),
        0 25px 60px rgba(0, 0, 0, 0.45),
        0 0 80px rgba(239, 68, 68, 0.06);
    padding: 3rem 2.5rem;
    max-width: 540px;
    width: 100%;
    text-align: center;
    animation: blocked-in 0.45s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes blocked-in {
    from { opacity: 0; transform: translateY(24px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0)   scale(1); }
}

.blocked-icon-wrap {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(239,68,68,.18) 0%, rgba(220,38,38,.08) 100%);
    border: 2px solid rgba(239, 68, 68, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.75rem;
    animation: pulse-red 2.4s ease-in-out infinite;
}

@keyframes pulse-red {
    0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,.3); }
    50%       { box-shadow: 0 0 0 14px rgba(239,68,68,0); }
}

.blocked-icon-wrap svg {
    width: 42px;
    height: 42px;
    color: #ef4444;
}

.blocked-card h1 {
    font-size: 1.65rem;
    font-weight: 700;
    color: var(--color-text, #f1f5f9);
    margin: 0 0 .75rem;
    line-height: 1.2;
}

.blocked-card .blocked-subtitle {
    color: var(--color-text-muted, #94a3b8);
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 1.75rem;
}

.blocked-reason-box {
    background: rgba(239, 68, 68, 0.08);
    border: 1px solid rgba(239, 68, 68, 0.22);
    border-radius: .875rem;
    padding: 1rem 1.25rem;
    margin-bottom: 2rem;
    text-align: left;
}

.blocked-reason-box .reason-label {
    font-size: .75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #ef4444;
    margin-bottom: .35rem;
}

.blocked-reason-box .reason-text {
    color: var(--color-text, #f1f5f9);
    font-size: .95rem;
    line-height: 1.55;
}

.blocked-actions {
    display: flex;
    flex-direction: column;
    gap: .85rem;
}

.blocked-actions .btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
    font-weight: 600;
    font-size: .95rem;
    padding: .8rem 1.5rem;
    border-radius: .75rem;
    text-decoration: none;
    transition: opacity .2s, transform .2s;
    border: none;
}

.blocked-actions .btn-primary:hover {
    opacity: .88;
    transform: translateY(-1px);
}

.blocked-actions .btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    background: transparent;
    color: var(--color-text-muted, #94a3b8);
    font-size: .9rem;
    padding: .65rem 1.25rem;
    border-radius: .75rem;
    text-decoration: none;
    border: 1px solid rgba(148,163,184,.2);
    transition: background .2s, color .2s;
}

.blocked-actions .btn-secondary:hover {
    background: rgba(148,163,184,.08);
    color: var(--color-text, #f1f5f9);
}

.blocked-help {
    margin-top: 2rem;
    font-size: .82rem;
    color: var(--color-text-muted, #64748b);
    line-height: 1.55;
}

.blocked-help a {
    color: #6366f1;
    text-decoration: none;
}

.blocked-help a:hover {
    text-decoration: underline;
}

@media (max-width: 480px) {
    .blocked-card {
        padding: 2rem 1.25rem;
    }
}
</style>

<main class="blocked-shell">
    <div class="blocked-card">

        <!-- Ícono -->
        <div class="blocked-icon-wrap" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke-width="1.6" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25
                         2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25
                         2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>

        <!-- Título -->
        <h1>Cuenta suspendida</h1>
        <p class="blocked-subtitle">
            Tu cuenta ha sido bloqueada por la administración de Classia
            y no podés acceder por el momento.
        </p>

        <!-- Motivo (si existe) -->
        <?php if (!empty($motivo)): ?>
        <div class="blocked-reason-box">
            <p class="reason-label">Motivo</p>
            <p class="reason-text"><?= $motivo ?></p>
        </div>
        <?php endif; ?>

        <!-- Acciones -->
        <div class="blocked-actions">
            <a href="../index.php" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke-width="2" stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75
                             12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125
                             1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621
                             0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Volver al inicio
            </a>
            <a href="contacto.php" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke-width="2" stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25
                             2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5
                             4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25
                             0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32
                             8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
                Contactar soporte
            </a>
        </div>

        <p class="blocked-help">
            Si creés que esto es un error, por favor contactá al equipo de administración
            a través de nuestra página de <a href="contacto.php">contacto</a>.
        </p>

    </div>
</main>

<?php include '../includes/footer.php'; ?>
