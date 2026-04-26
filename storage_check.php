<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=coaching', 'root', '');
foreach ($pdo->query('SHOW ENGINES') as $row) {
    echo $row['Engine'] . '|' . $row['Support'] . PHP_EOL;
}
echo 'default_storage_engine=' . $pdo->query("SHOW VARIABLES LIKE 'default_storage_engine'")->fetch(PDO::FETCH_ASSOC)['Value'] . PHP_EOL;
echo 'storage_engine=' . $pdo->query("SHOW VARIABLES LIKE 'storage_engine'")->fetch(PDO::FETCH_ASSOC)['Value'] . PHP_EOL;
?>
