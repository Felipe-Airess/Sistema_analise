<?php
session_start();
require_once("../../../config/conexao.php");

if(!isset($_SESSION['logado']) || $_SESSION['logado'] !== true){
    header("Location: ../../login/login.php");
    exit();
}

$meta_dados = null;
$id_meta = (int)($_GET['id'] ?? 0);
$empresa_id = $_SESSION['empresa_id'];
$mensagem_status = '';

if ($id_meta > 0) {
    $sql_meta = "SELECT tipo, valor_meta, descricao, data_inicio, data_fim, cor FROM metas WHERE id = ? AND empresa_id = ?";
    $stmt = $pdo->prepare($sql_meta);
    $stmt->execute([$id_meta, $empresa_id]);
    $meta_dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$meta_dados) {
        header("Location: gerenciar_metas.php");
        exit();
    }
} else {
    header("Location: gerenciar_metas.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $descricao = $_POST['descricao'];
    $valor_meta = $_POST['valor_meta'];
    $tipo = $_POST['tipo'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $cor = $_POST['cor'];
    
    try {
        $pdo->beginTransaction();
        $sql_update = "UPDATE metas 
                       SET tipo = ?, valor_meta = ?, descricao = ?, data_inicio = ?, data_fim = ?, cor = ? 
                       WHERE id = ? AND empresa_id = ?";
        
        $stmt = $pdo->prepare($sql_update);
        $stmt->execute([$tipo, $valor_meta, $descricao, $data_inicio, $data_fim, $cor, $id_meta, $empresa_id]);
        
        $meta_dados = [
            'tipo' => $tipo,
            'valor_meta' => $valor_meta,
            'descricao' => $descricao,
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim,
            'cor' => $cor
        ];

        $pdo->commit();

        header("Location:gerenciar_metas.php");
        exit();
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $pdo->rollBack();

        $mensagem_status = "<div class='p-3 bg-red-100 text-red-700 rounded-lg'>Erro ao editar meta: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Meta</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiByeD0iNCIgZmlsbD0iIzAwNGI4ZCIvPgo8cGF0aCBkPSJNMTYgMTBWNiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHBhdGggZD0iTTIyIDE2SDEwIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8cGF0aCBkPSJNMTYgMjZWMjIiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+CjxwYXRoIGQ9Ik0yNiAxNkgyMiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHBhdGggZD0iTTE2IDZMMjIgMTZIMTBMMTYgNloiIGZpbGw9IndoaXRlIiBmaWxsLW9wYWNpdHk9IjAuOCIvPgo8cGF0aCBkPSJNMTYgMjZMMjIgMTZIMTBMMTYgMjZaIiBmaWxsPSJ3aGl0ZSIgZmlsbC1vcGFjaXR5PSIwLjYiLz4KPHBhdGggZD0iTTYgMTZIMiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPC9zdmc+Cg==">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'primary-dark': '#004b8d',
                    }
                }
            }
        }
    </script>
    <script>
        (function() {
            const root = document.documentElement;
            const savedTheme = localStorage.getItem('color-theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                root.classList.add('dark');
            }
        })();
    </script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 min-h-screen font-['Poppins'] transition-colors duration-500">
    <main class="container mx-auto py-8 px-4">
        <div class="flex justify-center">
            <div class="w-full max-w-md">
                
                <h2 class="text-2xl text-center text-primary-dark dark:text-gray-100 font-semibold mb-6 animate__animated animate__fadeInDown">
                    Editar Meta 
                </h2>
                
                <?php if ($mensagem_status): ?>
                    <div class="mb-4 animate__animated animate__fadeIn">
                        <?= $mensagem_status ?>
                    </div>
                <?php endif; ?>

                <form action="editar_metas.php?id=<?= $id_meta ?>" method="post" 
                      class="w-full p-8 rounded-xl shadow-2xl space-y-5 transition-colors duration-300
                             bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-850
                             shadow-gray-300/50 dark:shadow-black/50
                             animate__animated animate__fadeInUp animate__delay-0.5s"> 
                    
                    <div class="w-full">
                        <label for="descricao" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Descrição</label>
                        <input type="text" id="descricao" name="descricao" value="<?= htmlspecialchars($meta_dados['descricao'] ?? '') ?>" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg 
                                   focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark 
                                   dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 transition-all">
                    </div>
                    
                    <div class="w-full">
                        <label for="tipo" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Tipo da Meta</label>
                        <select id="tipo" name="tipo" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg 
                                   focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark
                                   dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 appearance-none transition-all">
                            <option value="lucro" <?= ($meta_dados['tipo'] == 'lucro') ? 'selected' : '' ?>>Lucro (Receitas - Despesas)</option>
                            <option value="receita" <?= ($meta_dados['tipo'] == 'receita') ? 'selected' : '' ?>>Receita Total</option>
                            <option value="despesa" <?= ($meta_dados['tipo'] == 'despesa') ? 'selected' : '' ?>>Despesa (Teto Máximo)</option>
                        </select>
                    </div>

                    <div class="w-full">
                        <label for="valor_meta" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Valor Meta (R$)</label>
                        <div class="relative">
                            <input type="number" step="0.01" id="valor_meta" name="valor_meta" value="<?= htmlspecialchars($meta_dados['valor_meta'] ?? '') ?>" required
                                class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg 
                                       focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark 
                                       dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 transition-all">
                            <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500 text-sm">R$</span>
                        </div>
                    </div>
                    
                    <div class="w-full">
                        <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Data de Início</label>
                        <input type="date" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($meta_dados['data_inicio'] ?? date('Y-m-d')) ?>" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg 
                                   focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark 
                                   dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 transition-all">
                    </div>
                    
                    <div class="w-full">
                        <label for="data_fim" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Data Final</label>
                        <input type="date" id="data_fim" name="data_fim" value="<?= htmlspecialchars($meta_dados['data_fim'] ?? date('Y-m-d')) ?>" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg 
                                   focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark 
                                   dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 transition-all">
                    </div>

                    <div class="w-full">
                        <label for="cor" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Cor de Destaque</label>
                        <input type="color" id="cor" name="cor" value="<?= htmlspecialchars($meta_dados['cor'] ?? '#004b8d') ?>" required
                            class="mt-1 block w-full h-10 rounded-lg border-gray-300 shadow-sm dark:border-gray-600">
                    </div>
                    
                    <button type="submit"
                        class="w-full bg-primary-dark text-white py-3 px-4 mt-6 rounded-lg font-semibold
                               hover:bg-blue-800 transition-all duration-300 shadow-md hover:shadow-lg
                               focus:outline-none focus:ring-2 focus:ring-primary-dark focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        Salvar Alterações
                    </button>
                    
                    <button type="button" onclick="window.location.href='gerenciar_metas.php'"
                        class="w-full bg-gray-400 text-white py-3 px-4 rounded-lg font-semibold
                               hover:bg-gray-500 transition-all duration-300 shadow-md hover:shadow-lg
                               focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        Voltar para Metas
                    </button>

                </form>
            </div>
        </div>
    </main>
    <script src="../../assets/js/darkmode.js"></script>
</body>
</html>