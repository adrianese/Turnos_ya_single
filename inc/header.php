<?php
$user = $_SESSION['usuario'];
?>
<header>
  <div><strong>MisTurnos – Panel Admin</strong></div>
  <div>Bienvenido, <?= htmlspecialchars($user['nombre']) ?> (<?= htmlspecialchars($user['rol']) ?>) | <a href="../logout.php">Cerrar sesión</a></div>
</header>
