<?php
session_start();
require_once("../../../config/conexao.php");
if(!isset($_SESSION['logado']) || $_SESSION['logado'] !== true){
    header("Location: ../../login/login.php");
    exit();
}
$empresa_id = $_SESSION['empresa_id'];
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: gerenciar_categorias.php");
    exit();
}
$categoria_id = $_GET['id'];
try {
    $pdo->beginTransaction();
    $sql_delete = "DELETE FROM categorias WHERE id = ? AND empresa_id = ?";
    $stmt_delete = $pdo->prepare($sql_delete);
    $stmt_delete->execute([$categoria_id, $empresa_id]);
    $pdo->commit();
    $_SESSION['mensagem'] = "Categoria excluída com sucesso!";
    $_SESSION['mensagem_tipo'] = 'success';
    header("location:gerenciar_categorias.php");
    exit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['mensagem'] = "Erro ao excluir categoria: " . $e->getMessage();
    $_SESSION['mensagem_tipo'] = 'error';
    header("location:gerenciar_categorias.php");
    exit();
}
?>