<?php
// Obtener datos del negocio
require_once __DIR__ . '/../../inc/branding.php';
?>
<header class="business-header">
    <div class="business-info">
        <img src="<?= (strpos(LOGO_URL, 'data:image') === 0) ? LOGO_URL : '../' . LOGO_URL ?>" alt="Logo" class="business-logo">
        <div class="business-details">
            <h2><?= htmlspecialchars(NOMBRE_NEGOCIO) ?></h2>
            <p><?= htmlspecialchars(ESLOGAN_NEGOCIO) ?></p>
        </div>
    </div>
    <div class="header-actions">
        <div class="user-badge">
            <?= htmlspecialchars($_SESSION['usuario']['nombre'] ?? 'Admin') ?> | <?= strtoupper($_SESSION['usuario']['rol'] ?? 'ADMIN') ?>
        </div>
        <a href="../logout.php" class="logout-header-btn">🚪 Cerrar Sesión</a>
    </div>
</header>
