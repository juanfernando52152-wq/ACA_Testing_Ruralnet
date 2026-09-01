<?php
// ============================================================================
// PORTAL CLIENTE - CAMBIAR CONTRASEÑA
// ----------------------------------------------------------------------------
// El cliente llega aquí automáticamente la primera vez (forzado por
// auth_cliente.php). También puede entrar voluntariamente desde su perfil.
// ============================================================================

session_start();
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../core/auditoria.php';

// El cliente debe estar logueado (pero NO aplicamos el redirect forzado
// porque esta es justamente la página donde resuelve el forzado)
if (empty($_SESSION['cliente_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $actual    = $_POST['actual']    ?? '';
    $nueva     = $_POST['nueva']     ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    // Validaciones de campos vacíos y de coincidencia
    if (empty($actual) || empty($nueva) || empty($confirmar)) {
        $error = "Por favor llena todos los campos.";
    } elseif (strlen($nueva) < 6) {
        $error = "La nueva contraseña debe tener al menos 6 caracteres.";
    } elseif ($nueva !== $confirmar) {
        $error = "La confirmación no coincide con la nueva contraseña.";
    } else {
        // Validar la contraseña actual contra lo que tenga en BD
        $stmt = $conn->prepare("SELECT password_portal, cedula FROM clientes WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['cliente_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $valida_actual = false;
        if ($row['password_portal'] === 'CEDULA') {
            // Si todavía está en el estado inicial, su contraseña actual es la cédula
            $valida_actual = ($actual === $row['cedula']);
        } elseif (str_starts_with($row['password_portal'], '$2y$')) {
            $valida_actual = password_verify($actual, $row['password_portal']);
        }

        if (!$valida_actual) {
            $error = "Tu contraseña actual no es correcta.";
        } elseif ($nueva === $row['cedula']) {
            // Bloqueamos que ponga la cédula como contraseña nueva (sería volver al inicio)
            $error = "Por seguridad, no puedes usar tu cédula como contraseña.";
        } else {
            // Guardar contraseña nueva hasheada con bcrypt
            $hash = password_hash($nueva, PASSWORD_BCRYPT);
            $stmt = $conn->prepare(
                "UPDATE clientes SET password_portal = ?, portal_cambio_inicial = 0 WHERE id = ?"
            );
            $stmt->bind_param("si", $hash, $_SESSION['cliente_id']);
            $stmt->execute();
            $stmt->close();

            // Quitar el flag de forzado en la sesión
            unset($_SESSION['cliente_forzar_cambio']);

            // Auditar el cambio de contraseña
            registrar_auditoria('editar', 'portal_cliente', 'clientes', $_SESSION['cliente_id'],
                "Cliente {$_SESSION['cliente_nombre']} cambió su contraseña del portal");

            // Mostrar confirmación y enviar al dashboard
            $_SESSION['portal_ok_inicio'] = "Tu contraseña se actualizó correctamente.";
            header("Location: inicio.php");
            exit();
        }
    }
}

// Detectar si es primer cambio (mostrar texto distinto)
$primer_cambio = !empty($_SESSION['cliente_forzar_cambio']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar contraseña | Portal Ruralnet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5fff8 0%, #e8f7ed 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px; margin: 0;
        }
        .card-cambio {
            background: white; border-radius: 20px;
            box-shadow: 0 20px 60px rgba(33, 177, 78, 0.15);
            padding: 36px; max-width: 480px; width: 100%;
            border-top: 6px solid #21B14E;
        }
        h1 { color: #178a3c; font-weight: 700; font-size: 1.4rem; }
        .form-control { border-radius: 10px; padding: 12px 16px; border: 1.5px solid #e5e7eb; }
        .form-control:focus { border-color: #21B14E; box-shadow: 0 0 0 3px rgba(33,177,78,0.15); }
        .btn-guardar { background: #21B14E; color: white; padding: 12px; border-radius: 10px; border: 0; width: 100%; font-weight: 600; }
        .btn-guardar:hover { background: #178a3c; color: white; }
        .nota-bienvenida {
            background: #fff8e1; border-left: 4px solid #ffc107;
            padding: 12px 14px; border-radius: 8px; font-size: 0.82rem; color: #6b5100;
            margin-bottom: 18px;
        }
    </style>
</head>
<body>

<div class="card-cambio">
    <div class="text-center mb-3">
        <i class="bi bi-shield-lock-fill text-success" style="font-size: 2.5rem;"></i>
    </div>
    <h1 class="text-center mb-1">
        <?php echo $primer_cambio ? 'Crea tu contraseña' : 'Cambiar contraseña'; ?>
    </h1>
    <p class="text-center text-muted small mb-3">
        Hola, <strong><?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?></strong>
    </p>

    <?php if ($primer_cambio): ?>
        <div class="nota-bienvenida">
            <i class="bi bi-info-circle-fill"></i>
            <strong>Es tu primer ingreso.</strong>
            Por seguridad, necesitamos que cambies tu contraseña antes de continuar.
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger small py-2">
            <i class="bi bi-x-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <?php echo csrf_input(); ?>

        <div class="mb-3">
            <label class="small fw-semibold text-secondary mb-1">
                <?php echo $primer_cambio ? 'Contraseña actual (tu cédula)' : 'Contraseña actual'; ?>
            </label>
            <input type="password" name="actual" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="small fw-semibold text-secondary mb-1">Nueva contraseña</label>
            <input type="password" name="nueva" class="form-control" required minlength="6"
                   placeholder="Mínimo 6 caracteres">
        </div>
        <div class="mb-4">
            <label class="small fw-semibold text-secondary mb-1">Confirmar nueva contraseña</label>
            <input type="password" name="confirmar" class="form-control" required minlength="6">
        </div>

        <button type="submit" class="btn-guardar">
            <i class="bi bi-check2-circle"></i> GUARDAR CONTRASEÑA
        </button>

        <?php if (!$primer_cambio): ?>
            <div class="text-center mt-3">
                <a href="inicio.php" class="small text-muted text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        <?php endif; ?>
    </form>
</div>

</body>
</html>
