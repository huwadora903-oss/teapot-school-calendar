<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/bootstrap.php';
$pdo = calendarPdo();
foreach (glob(dirname(__DIR__) . '/database/*.sql') as $file) {
    $pdo->exec(file_get_contents($file));
}
echo "Isolated CI database initialized\n";
