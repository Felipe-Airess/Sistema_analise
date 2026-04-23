<?php
define ("DB_HOST", getenv('DB_HOST'));
define ("DB_NAME", getenv('DB_NAME'));
define ("DB_USER", getenv('DB_USER'));
define ("DB_PASS", getenv('DB_PASSWORD'));

try {
    $dns = "mysql:host=".DB_HOST.";port=3306;dbname=".DB_NAME.";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => FALSE,
    ];
    $pdo = new PDO($dns, DB_USER, DB_PASS, $options);
    $pdo->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: ".$e->getMessage());
}
?>