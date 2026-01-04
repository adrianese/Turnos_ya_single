<?php
/**
 * Cargar datos de branding (logo, portada, nombre) desde la configuración
 */

if (!isset($pdo)) {
    require_once __DIR__ . '/db.php';
}

$branding = [
    'nombre_negocio' => 'Turnos Ya',
    'eslogan' => 'Sistema de gestión de turnos',
    'logo_url' => '',
    'portada_url' => ''
];

try {
    $stmt = $pdo->query("SELECT clave, valor FROM configuracion WHERE clave IN ('nombre_unidad', 'frase', 'logo', 'foto_portada')");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['valor'])) {
            // Map keys to branding array
            if($row['clave'] === 'logo') $branding['logo_url'] = $row['valor'];
            if($row['clave'] === 'foto_portada') $branding['portada_url'] = $row['valor'];
            if($row['clave'] === 'nombre_unidad') $branding['nombre_negocio'] = $row['valor'];
            if($row['clave'] === 'frase') $branding['eslogan'] = $row['valor'];
        }
    }
} catch (Exception $e) {
    // Si hay error, usar valores por defecto
}

// Definir constante para uso en templates
define('LOGO_URL', !empty($branding['logo_url']) ? $branding['logo_url'] : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"%3E%3Ctext y="70" font-size="70"%3E🎯%3C/text%3E%3C/svg%3E');
define('PORTADA_URL', !empty($branding['portada_url']) ? $branding['portada_url'] : '');
define('NOMBRE_NEGOCIO', $branding['nombre_negocio']);
define('ESLOGAN_NEGOCIO', $branding['eslogan']);
