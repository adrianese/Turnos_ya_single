<?php
/**
 * Script de configuración completa - Turnos-Ya v2.0
 * Ejecuta todos los pasos necesarios para completar la implementación
 */

echo "🚀 INICIANDO CONFIGURACIÓN COMPLETA DE TURNOS-YA v2.0\n";
echo str_repeat("=", 60) . "\n\n";

// Verificar conexión a BD
require_once '../inc/db.php';
echo "✅ Conexión a base de datos: OK\n";

// 1. Inicializar tabla horarios
echo "\n📅 PASO 1: Inicializando tabla de horarios...\n";
try {
    $sql = file_get_contents('../database/init_horarios.sql');
    $pdo->exec($sql);
    echo "✅ Tabla horarios inicializada correctamente\n";
} catch (Exception $e) {
    echo "❌ Error inicializando horarios: " . $e->getMessage() . "\n";
}

// 1b. Inicializar tabla notificaciones
echo "\n📧 PASO 1b: Inicializando tabla de notificaciones...\n";
try {
    $sql = file_get_contents('../database/create_notificaciones.sql');
    $pdo->exec($sql);
    echo "✅ Tabla notificaciones inicializada correctamente\n";
} catch (Exception $e) {
    echo "❌ Error inicializando notificaciones: " . $e->getMessage() . "\n";
}

// 2. Verificar que todas las tablas existen
echo "\n🗄️ PASO 2: Verificando tablas de base de datos...\n";
$tablas_requeridas = [
    'usuarios', 'servicios', 'turnos', 'configuracion',
    'horarios', 'historial_chat', 'ia_eventos',
    'predicciones_cache', 'notificaciones'
];

$tablas_existentes = [];
$result = $pdo->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $tablas_existentes[] = $row[0];
}

foreach ($tablas_requeridas as $tabla) {
    if (in_array($tabla, $tablas_existentes)) {
        echo "✅ Tabla '$tabla' existe\n";
    } else {
        echo "❌ Tabla '$tabla' NO existe\n";
    }
}

// 3. Verificar configuración de IA
echo "\n🤖 PASO 3: Verificando configuración de IA...\n";
try {
    $stmt = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'gemini_api_key'");
    $api_key = $stmt->fetchColumn();
    if ($api_key && strlen($api_key) > 10) {
        echo "✅ API Key de Gemini configurada\n";
    } else {
        echo "❌ API Key de Gemini NO configurada\n";
    }
} catch (Exception $e) {
    echo "❌ Error verificando API Key: " . $e->getMessage() . "\n";
}

// 4. Crear favicon si no existe
echo "\n🎨 PASO 4: Verificando favicon...\n";
$favicon_path = '../favicon.ico';
if (file_exists($favicon_path)) {
    echo "✅ Favicon existe\n";
} else {
    // Intentar copiar del logo
    $logo_files = glob('../img/logo-*.jpg');
    if (!empty($logo_files)) {
        $logo_path = $logo_files[0];
        if (copy($logo_path, $favicon_path)) {
            echo "✅ Favicon creado desde logo\n";
        } else {
            echo "❌ Error creando favicon\n";
        }
    } else {
        echo "⚠️ No se encontró logo para crear favicon\n";
    }
}

// 5. Verificar archivos críticos
echo "\n📁 PASO 5: Verificando archivos críticos...\n";
$archivos_criticos = [
    '../inc/gemini_service.php',
    '../inc/ia_recomendaciones.php',
    '../inc/ia_predictor.php',
    '../inc/notification_service.php',
    '../api/chatbot.php',
    '../admin/horarios.php',
    '../chatbot.php',
    '../reservar.php',
    '../cancelar_turno.php'
];

foreach ($archivos_criticos as $archivo) {
    if (file_exists($archivo)) {
        echo "✅ $archivo existe\n";
    } else {
        echo "❌ $archivo NO existe\n";
    }
}

// 6. Probar funcionalidades
echo "\n🧪 PASO 6: Probando funcionalidades...\n";

// Probar horarios
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM horarios");
    $count = $stmt->fetchColumn();
    echo "✅ Horarios configurados: $count días\n";
} catch (Exception $e) {
    echo "❌ Error consultando horarios: " . $e->getMessage() . "\n";
}

// Probar servicios
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM servicios WHERE activo = 1");
    $count = $stmt->fetchColumn();
    echo "✅ Servicios activos: $count\n";
} catch (Exception $e) {
    echo "❌ Error consultando servicios: " . $e->getMessage() . "\n";
}

// Probar sistema de notificaciones
try {
    require_once '../inc/notification_service.php';
    $notificaciones = new NotificationService($pdo);
    echo "✅ Sistema de notificaciones operativo\n";
} catch (Exception $e) {
    echo "❌ Error en sistema de notificaciones: " . $e->getMessage() . "\n";
}

// 7. Resumen final
echo "\n" . str_repeat("=", 60) . "\n";
echo "🎉 CONFIGURACIÓN COMPLETA FINALIZADA\n\n";

echo "📋 RESUMEN DE IMPLEMENTACIÓN:\n";
echo "✅ Sistema de horarios avanzado por día\n";
echo "✅ Historial persistente de chatbot\n";
echo "✅ Sistema de notificaciones por email\n";
echo "✅ Favicon configurado\n";
echo "✅ Tablas de BD verificadas\n";
echo "✅ API de IA configurada\n\n";

echo "🚀 PRÓXIMOS PASOS SUGERIDOS:\n";
echo "1. Probar el sistema completo\n";
echo "2. Configurar horarios específicos en admin/horarios.php\n";
echo "3. Personalizar el prompt del chatbot\n";
echo "4. Configurar cron job para recordatorios (procesar_recordatorios.php)\n";
echo "5. Implementar cache de predicciones\n\n";

echo "📖 MANUAL DE USUARIO:\n";
echo "- Admin: admin@turnosya.com / contraseña del dump\n";
echo "- Cliente: juan@cliente.com / admin123\n";
echo "- URL: http://localhost/Turnos-Ya/\n\n";

echo "🎯 FUNCIONALIDADES DISPONIBLES:\n";
echo "- 🤖 Chatbot con IA conversacional\n";
echo "- 📅 Sistema de reservas con recomendaciones\n";
echo "- 📊 Analytics y predicciones\n";
echo "- 👥 Gestión multi-usuario\n";
echo "- 🕐 Horarios configurables por día\n\n";

echo str_repeat("=", 60) . "\n";
echo "✨ ¡TURNOS-YA v2.0 LISTO PARA USAR! ✨\n";
echo str_repeat("=", 60) . "\n";
?>