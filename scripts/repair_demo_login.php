<?php

require_once __DIR__ . '/../config/database.php';

$hash = password_hash('12345678', PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    "UPDATE usuarios
     SET password_hash = :hash,
         email_verificado = 1,
         onboarding_step = 10
     WHERE email IN ('admin@classia.com', 'docente@classia.com', 'estudiante@classia.com')"
);

$stmt->execute(['hash' => $hash]);

echo "Cuentas demo reparadas: " . $stmt->rowCount() . PHP_EOL;
echo "Credenciales demo: admin@classia.com, docente@classia.com y estudiante@classia.com / 12345678" . PHP_EOL;
