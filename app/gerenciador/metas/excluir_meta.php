<?php
session_start();
require_once("../../../config/conexao.php");
if(!isset($_SESSION['logado']) || $_SESSION['logado'] !== true){
    header("Location: ../../login/login.php");
    exit();
}
$empresa_id = $_SESSION['empresa_id'];
if($_SERVER['REQUEST_METHOD']=== 'GET'){
    if(isset($_GET['id']) && is_numeric($_GET['id'])){
        $meta_id = intval($_GET['id']);
        try {
            $pdo->beginTransaction();
            $sql_delete = "DELETE FROM metas WHERE id = ? AND empresa_id = ?";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->execute([$meta_id, $empresa_id]);
            $pdo->commit();
            $_SESSION['mensagem'] = "Meta excluída com sucesso.";
            $_SESSION['mensagem_tipo'] = "success";
            header("Location: gerenciar_metas.php");
            exit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['mensagem'] = "Erro ao excluir meta: " . $e->getMessage();
            $_SESSION['mensagem_tipo'] = "error";
            header("Location: gerenciar_metas.php");
            exit();
        }
    } else {
        header("Location: gerenciar_metas.php");
        exit();
    }
} else {
    header("Location: gerenciar_metas.php");
    exit();
}