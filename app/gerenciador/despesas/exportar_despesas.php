<?php
session_start();
require_once("../../../config/conexao.php");

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: ../../login/login.php");
    exit();
}

$empresa_id = $_SESSION['empresa_id'];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=despesas_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

fputcsv($output, array('ID', 'Data', 'Valor', 'Descricao', 'Categoria'), ';');

$sql = "SELECT d.id, d.data, d.valor, d.descricao, c.nome AS categoria_nome 
        FROM despesas d
        INNER JOIN categorias c ON d.categoria_id = c.id
        WHERE d.empresa_id = ? 
        ORDER BY d.data DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$empresa_id]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
    $data_formatada = date('d/m/Y', strtotime($row['data']));
    $valor_formatado = str_replace('.', ',', $row['valor']);
    
    fputcsv($output, 
        array(
            $row['id'],
            $data_formatada,
            $valor_formatado,
            $row['descricao'],
            $row['categoria_nome']
        ), 
        ';'
    );
}

fclose($output);
exit();
?>