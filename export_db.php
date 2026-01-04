<?php
require_once 'inc/db.php';

$output_file = 'database/backup_' . date('Ymd_His') . '.sql';

echo "Generando backup de la base de datos...\n\n";

try {
    $fp = fopen($output_file, 'w');
    
    fwrite($fp, "-- Backup de Base de Datos: mis_turnos\n");
    fwrite($fp, "-- Fecha: " . date('Y-m-d H:i:s') . "\n");
    fwrite($fp, "-- Generado por PHP\n\n");
    fwrite($fp, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
    fwrite($fp, "START TRANSACTION;\n");
    fwrite($fp, "SET time_zone = \"+00:00\";\n\n");
    
    // Obtener lista de tablas
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tablas encontradas: " . count($tables) . "\n";
    
    foreach ($tables as $table) {
        echo "Exportando tabla: $table\n";
        
        // Estructura de la tabla
        $create_table = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
        fwrite($fp, "\n-- Estructura de tabla para la tabla `$table`\n");
        fwrite($fp, "DROP TABLE IF EXISTS `$table`;\n");
        fwrite($fp, $create_table[1] . ";\n\n");
        
        // Datos de la tabla
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($rows) > 0) {
            fwrite($fp, "-- Volcado de datos para la tabla `$table`\n");
            
            foreach ($rows as $row) {
                $columns = array_keys($row);
                $values = array_map(function($val) use ($pdo) {
                    return $val === null ? 'NULL' : $pdo->quote($val);
                }, array_values($row));
                
                $insert = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                fwrite($fp, $insert);
            }
            fwrite($fp, "\n");
        }
    }
    
    fwrite($fp, "COMMIT;\n");
    fclose($fp);
    
    echo "\n✓ Backup creado exitosamente: $output_file\n";
    echo "Tamaño: " . number_format(filesize($output_file) / 1024, 2) . " KB\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
