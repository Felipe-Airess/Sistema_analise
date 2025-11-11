<?php
session_start();
require_once("../../../config/conexao.php");
if(!isset($_SESSION['logado']) || $_SESSION['logado'] !== true){
    header("Location: ../../login/login.php");
    exit();
}
if(isset($_GET['id'])){
    $receita_id = $_GET['id'];
    $empresa_id = $_SESSION['empresa_id'];
    $mensagem = null;
    $mensagem_tipo = null;
    try {
        $pdo->beginTransaction();
        $sql_delete = "DELETE FROM receitas WHERE id = ? AND empresa_id = ?";
        $stmt = $pdo->prepare($sql_delete);
        $stmt->execute([$receita_id, $empresa_id]);
        $pdo->commit();
        $_SESSION['mensagem'] = "Receita excluída com sucesso!";
        $_SESSION['mensagem_tipo'] = 'success';
        header("Location: gerenciar_receitas.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['mensagem_tipo'] = 'error';
        $_SESSION['mensagem'] = "Erro ao excluir receita: " . $e->getMessage();
        $pdo->rollBack();
        header("Location: gerenciar_receitas.php");
        exit();
    }
} else {
    echo "ID da receita não fornecido.";
    exit();
}