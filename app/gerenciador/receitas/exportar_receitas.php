<?php
session_start();
require_once("../../../config/conexao.php");

if(!isset($_SESSION['logado']) || $_SESSION['logado'] !== true){
    header("Location: ../../login/login.php");
    exit();
}

$empresa_id = $_SESSION['empresa_id'];


$filename = "receitas_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');


$output = fopen('php://output', 'w');


$header = ['Data', 'Categoria', 'Descricao', 'Valor'];

fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); 
fputcsv($output, $header, ';');


$sql = "SELECT 
            r.data, 
            c.nome AS categoria_nome, 
            r.descricao, 
            r.valor 
        FROM receitas r
        INNER JOIN categorias c ON r.categoria_id = c.id
        WHERE r.empresa_id = ? 
        ORDER BY r.data DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$empresa_id]);


while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
    $row['data'] = date('d/m/Y', strtotime($row['data']));
    
   
    $row['valor'] = str_replace('.', ',', (string)number_format($row['valor'], 2, '.', ''));
    
    
    fputcsv($output, $row, ';');
}


fclose($output);
exit;

?>