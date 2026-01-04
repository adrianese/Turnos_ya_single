<?php
require_once '../inc/auth.php';

if (!isLoggedIn() || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$pageTitle = "Panel de Administración";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<?php include 'inc/sidebar.php'; ?>

<main class="main-content">
    <?php include 'inc/header.php'; ?>

    <section class="content">
        <h1><?= $pageTitle ?></h1>
        <p>Este es el contenido del panel. Puedes reemplazar esta sección según la página.</p>
    </section>

    <?php include 'inc/footer.php'; ?>
</main>

</body>
</html>
