<?php
require_once '../inc/auth.php';
require_once '../inc/db.php';
require_once '../inc/branding.php';

if (!isLoggedIn() || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Cargar configuraciones existentes
$configs = [];
$stmt = $pdo->query("SELECT clave, valor FROM configuracion");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $configs[$row['clave']] = $row['valor'];
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_unidad = $_POST['nombre_unidad'] ?? '';
    $frase = $_POST['frase'] ?? '';
    $horario_inicio = $_POST['horario_inicio'] ?? '09:00';
    $horario_fin = $_POST['horario_fin'] ?? '18:00';
    $dias_laborables = $_POST['dias_laborables'] ?? '2,3,4,5,6';
    $gemini_api_key = $_POST['gemini_api_key'] ?? '';
    
    // Crear directorio img si no existe
    $uploadDir = '../img/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Manejar logo
    if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = 'logo-'.time().'.'.$extension;
        $destLogo = $uploadDir . $nombreArchivo;
        
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $destLogo)) {
            $sql = "INSERT INTO configuracion (clave, valor) VALUES ('logo', ?) ON DUPLICATE KEY UPDATE valor = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['img/'.$nombreArchivo, 'img/'.$nombreArchivo]);
        }
    }
    
    // Manejar portada
    if (!empty($_FILES['portada']['name']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($_FILES['portada']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = 'portada-'.time().'.'.$extension;
        $destPort = $uploadDir . $nombreArchivo;
        
        if (move_uploaded_file($_FILES['portada']['tmp_name'], $destPort)) {
            $sql = "INSERT INTO configuracion (clave, valor) VALUES ('foto_portada', ?) ON DUPLICATE KEY UPDATE valor = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['img/'.$nombreArchivo, 'img/'.$nombreArchivo]);
        }
    }
    // Guardar nombre de unidad
    $sql = "INSERT INTO configuracion (clave, valor) VALUES ('nombre_unidad', ?) ON DUPLICATE KEY UPDATE valor = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre_unidad, $nombre_unidad]);
    // Guardar frase
    $sql = "INSERT INTO configuracion (clave, valor) VALUES ('frase', ?) ON DUPLICATE KEY UPDATE valor = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$frase, $frase]);

    // Guardar horario_inicio
    $sql = "INSERT INTO configuracion (clave, valor) VALUES ('horario_inicio', ?) ON DUPLICATE KEY UPDATE valor = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$horario_inicio, $horario_inicio]);

    // Guardar horario_fin
    $sql = "INSERT INTO configuracion (clave, valor) VALUES ('horario_fin', ?) ON DUPLICATE KEY UPDATE valor = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$horario_fin, $horario_fin]);

    // Guardar dias_laborables
    $sql = "INSERT INTO configuracion (clave, valor) VALUES ('dias_laborables', ?) ON DUPLICATE KEY UPDATE valor = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dias_laborables, $dias_laborables]);

    // Guardar API key de Gemini
    if (!empty($gemini_api_key)) {
        $sql = "INSERT INTO configuracion (clave, valor) VALUES ('gemini_api_key', ?) ON DUPLICATE KEY UPDATE valor = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$gemini_api_key, $gemini_api_key]);
    }

    $mensaje = "Configuración guardada correctamente.";
}

// Obtener valores actuales
$sql = "SELECT clave, valor FROM configuracion WHERE clave IN ('nombre_unidad','frase_opcional','logo','foto_portada')";
$stmt = $pdo->query($sql);
$configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración – Panel Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        <?php if (PORTADA_URL): ?>
        body::before {
            background-image: url('../<?= PORTADA_URL ?>');
        }
        <?php endif; ?>
        
        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
        }
        
        form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        button {
            margin-top: 20px;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        button:hover {
            background: #5568d3;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            padding: 5px;
        }
        
        .file-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../dashboard.php" class="back-link">← Volver al Dashboard</a>
        
        <h2>⚙️ Configuración del Negocio</h2>
        <?php if (!empty($mensaje)) echo "<p style='color:green; padding: 15px; background: #d4edda; border-radius: 5px;'>$mensaje</p>"; ?>
        
        <form method="post" enctype="multipart/form-data">
            <label for="nombre_unidad">Nombre de la Unidad de Negocio</label>
            <input type="text" id="nombre_unidad" name="nombre_unidad" value="<?= htmlspecialchars($configs['nombre_unidad'] ?? '') ?>" required>

            <label for="frase">Frase Opcional</label>
            <input type="text" id="frase" name="frase" value="<?= htmlspecialchars($configs['frase'] ?? '') ?>">

            <label for="horario_inicio">Horario de Inicio (HH:MM)</label>
            <input type="time" id="horario_inicio" name="horario_inicio" value="<?= htmlspecialchars($configs['horario_inicio'] ?? '09:00') ?>">

            <label for="horario_fin">Horario de Fin (HH:MM)</label>
            <input type="time" id="horario_fin" name="horario_fin" value="<?= htmlspecialchars($configs['horario_fin'] ?? '18:00') ?>">

            <label for="dias_laborables">Días Laborables (números separados por coma: 1=Lunes, 7=Domingo)</label>
            <input type="text" id="dias_laborables" name="dias_laborables" value="<?= htmlspecialchars($configs['dias_laborables'] ?? '2,3,4,5,6') ?>" placeholder="Ej: 2,3,4,5,6">

            <label for="gemini_api_key">API Key de Gemini (IA)</label>
            <input type="password" id="gemini_api_key" name="gemini_api_key" value="<?= htmlspecialchars($configs['gemini_api_key'] ?? '') ?>" placeholder="Ingresa tu clave de Google Gemini">

            <label for="logo">Logo (PNG/JPG)</label>
            <?php if (!empty($configs['logo']) && file_exists('../' . $configs['logo'])): ?>
                <div class="file-info">Actual: 
                    <img src="../<?= htmlspecialchars($configs['logo']) ?>" class="preview-image" alt="Logo actual">
                </div>
            <?php endif; ?>
            <input type="file" id="logo" name="logo" accept="image/png, image/jpeg">

            <label for="portada">Foto de Portada (PNG/JPG)</label>
            <?php if (!empty($configs['foto_portada']) && file_exists('../' . $configs['foto_portada'])): ?>
                <div class="file-info">Actual: 
                    <img src="../<?= htmlspecialchars($configs['foto_portada']) ?>" class="preview-image" alt="Portada actual">
                </div>
            <?php endif; ?>
            <input type="file" id="portada" name="portada" accept="image/png, image/jpeg">

            <button type="submit">💾 Guardar Configuración</button>
        </form>
    </div>
</body>
</html>
