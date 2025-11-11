<?php
session_start();
require_once("../../../config/conexao.php");

if(!isset($_SESSION['logado']) || $_SESSION['logado'] !== true){
    header("Location: ../../login/login.php");
    exit();
}

$despesa_dados = null;
$id_despesa = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$empresa_id = $_SESSION['empresa_id'];

if ($id_despesa > 0) {
    $sql_despesa = "SELECT * FROM despesas WHERE id = ? AND empresa_id = ?";
    $stmt = $pdo->prepare($sql_despesa);
    $stmt->execute([$id_despesa, $empresa_id]);
    $despesa_dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$despesa_dados) {
        header("Location: gerenciar_despesas.php");
        exit();
    }
} else {
    echo "ID da despesa não fornecido.";
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $data = $_POST['data'];
    $categoria_id = $_POST['categoria_id'];
    
    try {
        $pdo->beginTransaction();
        
        $sql_update = "UPDATE despesas 
                       SET descricao = ?, valor = ?, data = ?, categoria_id = ? 
                       WHERE id = ? AND empresa_id = ?";
        
        $stmt = $pdo->prepare($sql_update);
        $stmt->execute([$descricao, $valor, $data, $categoria_id, $id_despesa, $empresa_id]);
        
        $pdo->commit();
        header("Location: gerenciar_despesas.php?status=success");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erro ao editar despesa: " . $e->getMessage();
    }
}

$sql_categorias = "SELECT * FROM categorias WHERE empresa_id = ?";
$stmt = $pdo->prepare($sql_categorias);
$stmt->execute([$_SESSION['empresa_id']]);
$categorias = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Despesa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiByeD0iNCIgZmlsbD0iIzAwNGI4ZCIvPgo8cGF0aCBkPSJNMTYgMTBWNiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHBhdGggZD0iTTIyIDE2SDEwIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8cGF0aCBkPSJNMTYgMjZWMjIiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+CjxwYXRoIGQ9Ik0yNiAxNkgyMiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHBhdGggZD0iTTE2IDZMMjIgMTZIMTBMMTYgNloiIGZpbGw9IndoaXRlIiBmaWxsLW9wYWNpdHk9IjAuOCIvPgo8cGF0aCBkPSJNMTYgMjZMMjIgMTZIMTBMMTYgMjZaIiBmaWxsPSJ3aGl0ZSIgZmlsbC1vcGFjaXR5PSIwLjYiLz4KPHBhdGggZD0iTTYgMTZIMiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPC9zdmc+Cg==">
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
                    Editar Despesa
                </h2>
                
                <form action="editar_despesa.php?id=<?= $id_despesa ?>" method="post" 
                      class="w-full p-8 rounded-xl shadow-2xl space-y-5 transition-colors duration-300
                             bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-850
                             shadow-gray-300/50 dark:shadow-black/50
                             animate__animated animate__fadeInUp animate__delay-0.5s"> 
                    
                    <div class="w-full">
                        <label for="descricao" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Descrição</label>
                        <input type="text" id="descricao" name="descricao" value="<?= htmlspecialchars($despesa_dados['descricao'] ?? '') ?>" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg 
                                   focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark 
                                   dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 transition-all">
                    </div>
                    
                    <div class="w-full">
                        <label for="valor" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Valor (R$)</label>
                        <div class="relative">
                            <input type="number" step="0.01" id="valor" name="valor" value="<?= htmlspecialchars($despesa_dados['valor'] ?? '') ?>" required
                                class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg 
                                       focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark 
                                       dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 transition-all">
                            <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500 text-sm">R$</span>
                        </div>
                    </div>
                    
                    <div class="w-full">
                        <label for="categoria_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Categoria</label>
                        <select id="categoria_id" name="categoria_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg 
                                   focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark
                                   dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 appearance-none transition-all">
                            <option value="" class="dark:bg-gray-700">Selecione uma categoria</option>
                            <?php foreach($categorias as $categoria): ?>
                            <option value="<?= $categoria['id']; ?>" class="dark:bg-gray-700"
                                <?= (isset($despesa_dados['categoria_id']) && $despesa_dados['categoria_id'] == $categoria['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($categoria['nome']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="w-full">
                        <label for="data" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Data</label>
                        <input type="date" id="data" name="data" value="<?= htmlspecialchars($despesa_dados['data'] ?? date('Y-m-d')) ?>" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg 
                                   focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark 
                                   dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 transition-all">
                    </div>
                    
                    <button type="submit"
                        class="w-full bg-primary-dark text-white py-3 px-4 mt-6 rounded-lg font-semibold
                               hover:bg-blue-800 transition-all duration-300 shadow-md hover:shadow-lg
                               focus:outline-none focus:ring-2 focus:ring-primary-dark focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        Salvar Alterações
                    </button>
                    
                    <button type="button" onclick="window.history.back()"
                        class="w-full bg-gray-400 text-white py-3 px-4 rounded-lg font-semibold
                               hover:bg-gray-500 transition-all duration-300 shadow-md hover:shadow-lg
                               focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        Voltar
                    </button>

                </form>
            </div>
        </div>
    </main>
    <script src="../../assets/js/darkmode.js"></script>
</body>
</html>