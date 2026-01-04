<?php
/**
 * Script maestro de testing - Turnos-Ya
 * Ejecuta todos los tests disponibles automáticamente
 */

echo "🧪 SUITE COMPLETA DE TESTING - TURNOS-YA\n";
echo str_repeat("=", 50) . "\n\n";

$tests = [
    'setup_completo.php' => 'Verificación completa del sistema',
    'test_chatbot_api.php' => 'API del chatbot',
    'test_notificaciones.php' => 'Sistema de notificaciones',
    'debug_api.php' => 'Debug de API (solo output básico)'
];

$resultados = [];
$errores = 0;

foreach ($tests as $archivo => $descripcion) {
    echo "🔍 Ejecutando: $descripcion\n";
    echo "📄 Archivo: $archivo\n";

    // Capturar output del test
    ob_start();
    $exitCode = 0;

    try {
        // Solo ejecutar si el archivo existe
        if (file_exists($archivo)) {
            include $archivo;
        } else {
            echo "❌ Archivo no encontrado: $archivo\n";
            $exitCode = 1;
        }
    } catch (Exception $e) {
        echo "❌ Error ejecutando $archivo: " . $e->getMessage() . "\n";
        $exitCode = 1;
    }

    $output = ob_get_clean();

    if ($exitCode === 0) {
        echo "✅ PASÓ\n";
        $resultados[$archivo] = 'PASÓ';
    } else {
        echo "❌ FALLÓ\n";
        $resultados[$archivo] = 'FALLÓ';
        $errores++;
    }

    echo "📝 Output:\n";
    echo str_repeat("-", 30) . "\n";
    echo $output;
    echo str_repeat("-", 30) . "\n\n";
}

// Resumen final
echo str_repeat("=", 50) . "\n";
echo "📊 RESUMEN DE RESULTADOS\n";
echo str_repeat("=", 50) . "\n";

foreach ($resultados as $test => $resultado) {
    $icono = $resultado === 'PASÓ' ? '✅' : '❌';
    echo "$icono $test: $resultado\n";
}

echo "\n" . str_repeat("=", 50) . "\n";

if ($errores === 0) {
    echo "🎉 TODOS LOS TESTS PASARON EXITOSAMENTE!\n";
    echo "✨ El sistema Turnos-Ya está funcionando correctamente.\n";
} else {
    echo "⚠️ $errores test(s) fallaron.\n";
    echo "🔧 Revisa los errores arriba y ejecuta los tests individuales para más detalles.\n";
}

echo str_repeat("=", 50) . "\n";

// Información adicional
echo "\n💡 RECOMENDACIONES:\n";
echo "- Si falló setup_completo: Verifica la configuración de BD\n";
echo "- Si falló test_chatbot_api: Inicia sesión primero\n";
echo "- Si falló test_notificaciones: Configura servidor SMTP\n";
echo "- Si falló debug_api: Verifica conectividad con Gemini API\n";

echo "\n📁 Logs disponibles en: test/chatbot_errors.log\n";
echo "📊 Más información en: test/README.md\n";
?>