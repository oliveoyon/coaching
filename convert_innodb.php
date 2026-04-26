<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=coaching', 'root', '');
$tables = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    $pdo->exec("ALTER TABLE `{$table}` ENGINE=InnoDB");
    echo "converted|{$table}" . PHP_EOL;
}
?>
