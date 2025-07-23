<?php
try {
    $host = '127.0.0.1';
    $db   = 'linkiudb';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "Connection successful!\n";
    
    // Lista de tablas a verificar y reparar
    $tablesToFix = [
        'stores',
        'products',
        'categories',
        'product_variables',
        'sliders',
        'sessions'
    ];
    
    foreach ($tablesToFix as $table) {
        echo "\nVerificando tabla: $table\n";
        
        // Verificar si la tabla existe
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $tableExists = $stmt->rowCount() > 0;
        
        if ($tableExists) {
            echo "- La tabla $table existe en la base de datos.\n";
            
            // Intentar acceder a la tabla
            try {
                $stmt = $pdo->query("SELECT 1 FROM `$table` LIMIT 1");
                echo "- La tabla $table es accesible.\n";
            } catch (\PDOException $e) {
                echo "- Error al acceder a la tabla $table: " . $e->getMessage() . "\n";
                
                // Intentar recrear la tabla con MyISAM
                try {
                    // Obtener la estructura de la tabla
                    $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
                    $createTableSql = $stmt->fetch()['Create Table'];
                    
                    // Modificar para usar MyISAM
                    $createTableSql = preg_replace('/ENGINE=InnoDB/', 'ENGINE=MyISAM', $createTableSql);
                    
                    // Eliminar la tabla
                    $pdo->exec("DROP TABLE IF EXISTS `$table`");
                    echo "- Tabla $table eliminada.\n";
                    
                    // Recrear la tabla
                    $pdo->exec($createTableSql);
                    echo "- Tabla $table recreada con MyISAM.\n";
                } catch (\PDOException $e2) {
                    echo "- Error al recrear la tabla $table: " . $e2->getMessage() . "\n";
                    
                    // Si falla, intentar crear una tabla básica
                    if ($table === 'stores') {
                        try {
                            $pdo->exec("CREATE TABLE `stores` (
                                `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                `name` varchar(191) NOT NULL,
                                `slug` varchar(191) NOT NULL,
                                `email` varchar(191) NULL,
                                `created_at` timestamp NULL DEFAULT NULL,
                                `updated_at` timestamp NULL DEFAULT NULL,
                                `deleted_at` timestamp NULL DEFAULT NULL,
                                PRIMARY KEY (`id`),
                                UNIQUE KEY `stores_slug_unique` (`slug`)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                            echo "- Tabla stores creada con estructura básica.\n";
                        } catch (\PDOException $e3) {
                            echo "- Error al crear tabla básica stores: " . $e3->getMessage() . "\n";
                        }
                    } else if ($table === 'sessions') {
                        try {
                            $pdo->exec("CREATE TABLE `sessions` (
                                `id` varchar(191) NOT NULL,
                                `user_id` bigint(20) UNSIGNED DEFAULT NULL,
                                `ip_address` varchar(45) DEFAULT NULL,
                                `user_agent` text DEFAULT NULL,
                                `payload` longtext NOT NULL,
                                `last_activity` int(11) NOT NULL,
                                PRIMARY KEY (`id`)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                            echo "- Tabla sessions creada con estructura básica.\n";
                        } catch (\PDOException $e3) {
                            echo "- Error al crear tabla básica sessions: " . $e3->getMessage() . "\n";
                        }
                    }
                }
            }
        } else {
            echo "- La tabla $table no existe en la base de datos.\n";
            
            // Crear la tabla si no existe
            if ($table === 'sessions') {
                try {
                    $pdo->exec("CREATE TABLE `sessions` (
                        `id` varchar(191) NOT NULL,
                        `user_id` bigint(20) UNSIGNED DEFAULT NULL,
                        `ip_address` varchar(45) DEFAULT NULL,
                        `user_agent` text DEFAULT NULL,
                        `payload` longtext NOT NULL,
                        `last_activity` int(11) NOT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                    echo "- Tabla sessions creada.\n";
                } catch (\PDOException $e) {
                    echo "- Error al crear tabla sessions: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "\nProceso completado.\n";
    
} catch (\PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
}