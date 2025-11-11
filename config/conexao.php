<?php
define ("DB_HOST","localhost");
define ("DB_NAME","sistema_analise");
define ("DB_USER","root");
define ("DB_PASS","");

try {
    $dns = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => FALSE,
    ];
    $pdo = new PDO($dns,DB_USER,DB_PASS,$options);
} catch (PDOException $e) {
        die("Erro ao conectar com o banco de dados: ".$e->getMessage());
}
?>