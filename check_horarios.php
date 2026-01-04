<?php
require_once 'inc/db.php';
$stmt = $pdo->query('SELECT * FROM horarios');
$horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo 'Horarios configurados: ' . count($horarios) . PHP_EOL;
foreach($horarios as $h) {
    echo $h['dia'] . ': ' . ($h['abierto'] ? 'Abierto' : 'Cerrado') . ' - ' . $h['hora_inicio'] . ' a ' . $h['hora_fin'] . PHP_EOL;
}
?>