-- Corrige las cuentas demo en bases ya inicializadas.
-- La contraseña de las cuentas demo es: 12345678

UPDATE usuarios
SET
    password_hash = '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe',
    email_verificado = 1,
    onboarding_step = 10
WHERE email IN (
    'admin@classia.com',
    'docente@classia.com',
    'estudiante@classia.com'
);
