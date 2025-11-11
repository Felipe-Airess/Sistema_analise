<?php
session_start();
require_once("../../../config/conexao.php");
if(!isset($_SESSION['logado']) || $_SESSION['logado'] !== true){
    header("Location: ../../login/login.php");
    exit();
}
if(isset($_GET['id'])){
    $empresa_id = $_SESSION['empresa_id'];
    $despesa_id = $_GET['id'];
    try {
        $pdo->beginTransaction();
        $sql_delete = "DELETE FROM despesas WHERE id = ? AND empresa_id = ?";
        $stmt = $pdo->prepare($sql_delete);
        $stmt->execute([$despesa_id, $empresa_id]);
        $pdo->commit();
        $_SESSION['mensagem'] = "Despesa excluída com sucesso!";
        $_SESSION['mensagem_tipo'] = 'success';
        header("Location: gerenciar_despesas.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mensagem_tipo'] = 'error';
        $_SESSION['mensagem'] = "Erro ao excluir despesa: " . $e->getMessage();
        header("Location: gerenciar_despesas.php");
        exit();
    }
} else {
    echo "ID da despesa não fornecido.";
    exit();
}
            