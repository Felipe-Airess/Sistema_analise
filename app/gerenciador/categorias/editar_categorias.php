<?php
session_start();
require_once("../../../config/conexao.php");


if(!isset($_SESSION['logado']) || $_SESSION['logado'] !== true){
    header("Location: ../../login/login.php");
    exit();
}

$empresa_id = $_SESSION['empresa_id'];
$empresa_nome = $_SESSION['empresa_nome'];
$mensagem = null;
$mensagem_tipo = null;
$categoria = null; 


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: gerenciar_categorias.php"); 
    exit();
}

$categoria_id = $_GET['id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_nome = trim($_POST['categoria_nome']);
    
    if (empty($novo_nome)) {
        $mensagem = "O nome da categoria não pode estar vazio.";
        $mensagem_tipo = 'error';
    } else {
        try {
          
            $sql_check = "SELECT id FROM categorias WHERE empresa_id = ? AND nome = ? AND id != ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$empresa_id, $novo_nome, $categoria_id]);

            if ($stmt_check->rowCount() > 0) {
                $mensagem = "A categoria '{$novo_nome}' já está cadastrada.";
                $mensagem_tipo = 'error';
            } else {
                
                $pdo->beginTransaction();
                $sql_update = "UPDATE categorias SET nome = ? WHERE id = ? AND empresa_id = ?";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute([$novo_nome, $categoria_id, $empresa_id]);
                $pdo->commit();
                
                
                $_SESSION['mensagem'] = "Categoria atualizada para '{$novo_nome}' com sucesso!";
                $_SESSION['mensagem_tipo'] = 'success';
                header("location:gerenciar_categorias.php");
                exit();
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $mensagem = "Erro ao atualizar categoria: " . $e->getMessage();
            $mensagem_tipo = 'error';
        }
    }
}


$sql_select = "SELECT id, nome FROM categorias WHERE id = ? AND empresa_id = ?";
$stmt_select = $pdo->prepare($sql_select);
$stmt_select->execute([$categoria_id, $empresa_id]);
$categoria = $stmt_select->fetch();

if (!$categoria) {
    
    header("Location: gerenciar_categorias.php");
    exit();
}


$nome_exibicao = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($novo_nome) && $mensagem_tipo === 'error' ? $novo_nome : $categoria['nome'];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoria | <?php echo htmlspecialchars($empresa_nome); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiByeD0iNCIgZmlsbD0iIzAwNGI4ZCIvPgo8cGF0aCBkPSJNMTYgMTBWNiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHBhdGggZD0iTTIyIDE2SDEwIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8cGF0aCBkPSJNMTYgMjZWMjIiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+CjxwYXRoIGQ9Ik0yNiAxNkgyMiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHBhdGggZD0iTTE2IDZMMjIgMTZIMTBMMTYgNloiIGZpbGw9IndoaXRlIiBmaWxsLW9wYWNpdHk9IjAuOCIvPgo8cGF0aCBkPSJNMTYgMjZMMjIgMTZIMTBMMTYgMjZaIiBmaWxsPSJ3aGl0ZSIgZmlsbC1vcGFjaXR5PSIwLjYiLz4KPHBhdGggZD0iTTYgMTZIMiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPC9zdmc+Cg==">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script>
        tailwind.config = {
            darkMode: 'class', 
            theme: {
                extend: {
                    colors: {
                        'primary-gain': '#047857', 
                        'primary-blue': '#004b8d',
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
                
                <h2 class="text-2xl text-center text-primary-gain dark:text-green-300 font-semibold mb-6 animate__animated animate__fadeInDown">
                    Editar Categoria
                </h2>

                <?php if (isset($mensagem) && $mensagem_tipo === 'error'): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 animate__animated animate__fadeIn" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($mensagem); ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="editar_categorias.php?id=<?php echo $categoria_id; ?>"
                      class="w-full p-8 rounded-xl shadow-2xl space-y-5 transition-colors duration-300
                             bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-850
                             shadow-gray-300/50 dark:shadow-black/50
                             animate__animated animate__fadeInUp animate__delay-0.5s">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1" for="categoria_nome">
                            Novo Nome da Categoria
                        </label>
                        <input type="text" id="categoria_nome" name="categoria_nome" required maxlength="100"
                            value="<?php echo htmlspecialchars($nome_exibicao); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg 
                                   focus:outline-none focus:ring-2 focus:ring-primary-gain focus:border-primary-gain 
                                   dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 transition-all">
                    </div>
                    
                    <button type="submit"
                        class="w-full bg-primary-gain text-white py-3 px-4 mt-6 rounded-lg font-semibold
                               hover:bg-green-700 transition-all duration-300 shadow-md hover:shadow-lg
                               focus:outline-none focus:ring-2 focus:ring-primary-gain focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        Salvar Alterações
                    </button>
                    
                    <div class="flex space-x-4 mt-4">
                        <button type="button" onclick="window.location.href='gerenciar_categorias.php'"
                            class="w-full bg-gray-400 text-white py-3 px-4 rounded-lg font-semibold
                                   hover:bg-gray-500 transition-all duration-300 shadow-md hover:shadow-lg
                                   focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            Cancelar e Voltar
                        </button>
                    </div>

                </form>
                
            </div>
        </div>
    </main>
    <script src="../../assets/js/darkmode.js"></script>
</body>
</html>