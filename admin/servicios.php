<?php
require_once '../inc/auth.php';
require_once '../inc/db.php';
require_once '../inc/branding.php';

if (!isLoggedIn() || ($_SESSION['usuario']['rol'] !== 'admin')) {
    header('Location: ../index.php');
    exit;
}

// Mensajes
$mensaje = '';
$error = '';

// Procesar acciones CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id = $_POST['id'] ?? 0;
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $duracion = intval($_POST['duracion'] ?? 30);
    $activo = isset($_POST['activo']) ? 1 : 0;
    $max_cupos = intval($_POST['max_cupos'] ?? 1);
    if ($max_cupos < 1) $max_cupos = 1;

    if ($accion === 'crear' && $nombre) {
        $sql = "INSERT INTO servicios (nombre, descripcion, precio, duracion, activo, max_cupos) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$nombre, $descripcion, $precio, $duracion, $activo, $max_cupos])) {
            header('Location: servicios.php?msg=creado');
            exit;
        } else {
            $error = "Error al crear el servicio.";
        }
    }
    if ($accion === 'editar' && $id && $nombre) {
        $sql = "UPDATE servicios SET nombre=?, descripcion=?, precio=?, duracion=?, activo=?, max_cupos=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$nombre, $descripcion, $precio, $duracion, $activo, $max_cupos, $id])) {
            header('Location: servicios.php?msg=editado');
            exit;
        } else {
            $error = "Error al actualizar el servicio.";
        }
    }
    if ($accion === 'eliminar' && $id) {
        $sql = "DELETE FROM servicios WHERE id=?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$id])) {
            if ($stmt->rowCount() > 0) {
                header('Location: servicios.php?msg=eliminado');
                exit;
            } else {
                $error = "No se eliminó ningún registro. Puede que el servicio ya no exista.";
            }
        } else {
            $error = "Error al eliminar el servicio: " . $stmt->errorInfo()[2] . " (SQL: $sql, id: $id)";
        }
    }
}

// Listar servicios
$stmt = $pdo->query("SELECT * FROM servicios ORDER BY activo DESC, nombre ASC");
$servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Servicios - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    
</head>
<body>
<div class="container">
    <a href="../dashboard.php" class="back-link">← Volver al Dashboard</a>
    <h2>Gestión de Servicios</h2>
    <?php if ($mensaje): ?><div style="color:green;"><?= $mensaje ?></div><?php endif; ?>
    <?php if ($error): ?><div style="color:red;"><?= $error ?></div><?php endif; ?>
    <form class="form-servicio" method="post">
        <input type="hidden" name="accion" value="crear">
        <label for="nombre">Nombre del servicio</label>
        <input type="text" id="nombre" name="nombre" placeholder="Nombre del servicio" required>
        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" placeholder="Descripción"></textarea>
        <label for="precio">Precio</label>
        <input type="number" id="precio" name="precio" step="0.01" min="0" placeholder="Precio" required>
        <label for="duracion">Duración (minutos)</label>
        <input type="number" id="duracion" name="duracion" min="1" max="480" placeholder="Duración (min)" value="30" required>
        <label for="max_cupos">Cupos máximos</label>
        <input type="number" id="max_cupos" name="max_cupos" min="1" value="1" placeholder="Cupos máximos">
        <label for="activo"><input type="checkbox" id="activo" name="activo" checked> Activo</label>
        <div class="acciones-form">
            <button type="submit">Crear servicio</button>
        </div>
    </form>
    <table class="servicios-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Duración</th>
                <th>Cupos</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($servicios as $serv): ?>
            <form method="post" class="form-editar-servicio" style="display:contents;">
                <tr>
                    <input type="hidden" name="id" value="<?= $serv['id'] ?>">
                    <td><?= $serv['id'] ?></td>
                    <td><input type="text" name="nombre" value="<?= htmlspecialchars($serv['nombre']) ?>" required></td>
                    <td><textarea name="descripcion"><?= htmlspecialchars($serv['descripcion']) ?></textarea></td>
                    <td><input type="number" name="precio" step="0.01" min="0" value="<?= $serv['precio'] ?>" required></td>
                    <td><input type="number" name="duracion" min="1" max="480" value="<?= $serv['duracion'] ?>" required></td>
                    <td><input type="number" name="max_cupos" min="1" value="<?= $serv['max_cupos'] ?? 1 ?>" required></td>
                    <td style="text-align:center;"><input type="checkbox" name="activo" <?= $serv['activo'] ? 'checked' : '' ?>></td>
                    <td class="acciones-form">
                        <button type="submit" name="accion" value="editar">Guardar</button>
                        <button type="button" class="btn-eliminar-servicio">Eliminar</button>
                    </td>
                </tr>
            </form>
        <?php endforeach; ?>
        </tbody>
    </table>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.querySelectorAll('.btn-eliminar-servicio').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            const tr = btn.closest('tr');
            const serviceIdInput = tr.querySelector('input[name="id"]');
            if (!serviceIdInput) {
                console.error("No se pudo encontrar el input del ID del servicio.");
                return;
            }
            const serviceId = serviceIdInput.value;
            const serviceName = tr.querySelector('input[name="nombre"]').value;

            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar servicio?',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33',
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'post';
                    form.style.display = 'none';

                    const inputId = document.createElement('input');
                    inputId.type = 'hidden';
                    inputId.name = 'id';
                    inputId.value = serviceId;
                    form.appendChild(inputId);

                    const inputAccion = document.createElement('input');
                    inputAccion.type = 'hidden';
                    inputAccion.name = 'accion';
                    inputAccion.value = 'eliminar';
                    form.appendChild(inputAccion);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
    // Mostrar alerta tras eliminar y limpiar el parámetro de la URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('msg') === 'eliminado') {
        Swal.fire({
            icon: 'success',
            title: 'Servicio eliminado',
            confirmButtonText: 'Cerrar'
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }
    if (urlParams.get('msg') === 'creado') {
        Swal.fire({
            icon: 'success',
            title: 'Servicio creado',
            confirmButtonText: 'Cerrar'
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }
    if (urlParams.get('msg') === 'editado') {
        Swal.fire({
            icon: 'success',
            title: 'Servicio actualizado',
            confirmButtonText: 'Cerrar'
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }
    </script>
</div>
</body>
</html>
