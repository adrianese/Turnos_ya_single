<?php
require_once '../inc/auth.php';
require_once '../inc/db.php';

if (!isLoggedIn() || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['importar_feriados'])) {
    $anio = date('Y'); // Current year, or allow selection

    // API URL for Argentina holidays
    $api_url = "https://nolaborables.com.ar/api/v2/feriados/$anio";

    $context = stream_context_create([
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: Turnos-Ya/1.0\r\n"
        ]
    ]);

    $response = file_get_contents($api_url, false, $context);

    if ($response === false) {
        $mensaje = "Error al conectar con la API de feriados.";
    } else {
        $feriados = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $mensaje = "Error al decodificar respuesta de la API.";
        } else {
            $count = 0;
            foreach ($feriados as $feriado) {
                $fecha = $feriado['dia'];
                $descripcion = $feriado['motivo'];
                $tipo = 'nacional'; // Assuming all are national

                $sql = "INSERT IGNORE INTO feriados (fecha, descripcion, tipo) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$fecha, $descripcion, $tipo]);
                $count++;
            }
            $mensaje = "Se importaron $count feriados para el año $anio.";
        }
    }
}

// Mostrar feriados existentes
$sql = "SELECT * FROM feriados ORDER BY fecha";
$stmt = $pdo->query($sql);
$feriados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Feriados - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <h1>Gestión de Feriados</h1>

        <form method="post">
            <button type="submit" name="importar_feriados" class="btn-primary">Importar Feriados desde API</button>
        </form>

        <?php if ($mensaje): ?>
            <p style="color: green;"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>

        <h2>Feriados Registrados</h2>
        <table border="1">
            <tr>
                <th>Fecha</th>
                <th>Descripción</th>
                <th>Tipo</th>
            </tr>
            <?php foreach ($feriados as $feriado): ?>
            <tr>
                <td><?php echo htmlspecialchars($feriado['fecha']); ?></td>
                <td><?php echo htmlspecialchars($feriado['descripcion']); ?></td>
                <td><?php echo htmlspecialchars($feriado['tipo']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <a href="plantilla.php">Volver al Panel Admin</a>
    </div>
</body>
</html>