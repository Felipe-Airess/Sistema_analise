<?php
session_start();
require_once("../../config/conexao.php");

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];
    
    $sql = "SELECT u.*, e.nome AS empresa_nome, e.id AS empresa_id 
    FROM usuarios AS u 
    INNER JOIN empresas AS e ON u.empresa_id = e.id
    WHERE u.nome = ?";
   
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario]);
        $user_data = $stmt->fetch();
    } catch (\PDOException $e) {
        $user_data = false;
    }

    if(isset($user_data) && $user_data && password_verify($senha, $user_data['senha'])){
        $_SESSION['usuario_id'] = $user_data['id'];
        $_SESSION['usuario_nome'] = $user_data['nome'];
        $_SESSION['empresa_id'] = $user_data['empresa_id'];
        $_SESSION['empresa_nome'] = $user_data['empresa_nome'];
        $_SESSION['logado'] = true;
        $_SESSION['mensagem'] = "Login realizado com sucesso.";
        $_SESSION['mensagem_tipo'] = "success";
        header("Location:../gerenciador/gerenciador.php");
        exit();
    } else {
        $_SESSION['mensagem'] = "Usuário ou senha inválidos.";
        $_SESSION['mensagem_tipo'] = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso ao Sistema</title>
    
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiByeD0iNCIgZmlsbD0iIzAwNGI4ZCIvPgo8cGF0aCBkPSJNMTYgMTBWNiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHBhdGggZD0iTTIyIDE2SDEwIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8cGF0aCBkPSJNMTYgMjZWMjIiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+CjxwYXRoIGQ9Ik0yNiAxNkgyMiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHBhdGggZD0iTTE2IDZMMjIgMTZIMTBMMTYgNloiIGZpbGw9IndoaXRlIiBmaWxsLW9wYWNpdHk9IjAuOCIvPgo8cGF0aCBkPSJNMTYgMjZMMjIgMTZIMTBMMTYgMjZaIiBmaWxsPSJ3aGl0ZSIgZmlsbC1vcGFjaXR5PSIwLjYiLz4KPHBhdGggZD0iTTYgMTZIMiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPC9zdmc+Cg==">
    <style>
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F8F9FA; 
        }
       
        .btn-primary {
            background-color: #007BFF; 
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3; 
        }
       
        .link-highlight {
            color: #007BFF;
            transition: color 0.3s ease;
        }
        .link-highlight:hover {
            color: #0056b3;
        }
    </style>

</head>
<body class="flex items-center justify-center bg-gradient-to-r from-blue-400 to-[#004b8d] min-h-screen">

   
    
    <div class="scrol w-full max-w-md p-8 bg-white shadow-xl rounded-xl border border-gray-200">
        
        
        <div class="text-center mb-8">
            <i class="fas fa-chart-line text-4xl link-highlight mb-3"></i> 
            <h1 class="text-2xl font-semibold text-gray-800">Acesso ao Sistema</h1>
            <p class="text-sm text-gray-500 mt-1">Insira suas credenciais para continuar</p>
        </div>

        
       

        
        <form action="" method="POST">
            
            <div class="mb-5">
                <label for="usuario" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user-circle mr-2 text-gray-500"></i> Usuário
                </label>
                <input type="text" name="usuario" id="usuario" required
                    class="appearance-none border border-gray-300 p-3 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-[#007BFF] focus:border-transparent text-gray-800 placeholder-gray-400 shadow-sm"
                    placeholder="Seu nome de usuário">
            </div>

            <div class="mb-6">
                <label for="senha" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-gray-500"></i> Senha
                </label>
                <input type="password" name="senha" id="senha" required
                    class="appearance-none border border-gray-300 p-3 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-[#007BFF] focus:border-transparent text-gray-800 placeholder-gray-400 shadow-sm"
                    placeholder="Sua senha secreta">
            </div>
            
            
            <button type="submit"
                class="btn-primary cursor-pointer text-white font-semibold rounded-lg p-3 w-full shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#007BFF] focus:ring-offset-2">
                <i class="fas fa-sign-in-alt mr-2"></i> Entrar
            </button>
        </form>

        
        <div class="text-center mt-6">
            <a href="../cadastro/cadastro.php" class="text-sm font-medium link-highlight">
                Não possui uma conta? Cadastre-se
            </a>
        </div>

    </div>
    <script>
        ScrollReveal().reveal('.scrol', {
            duration: 1200,
            distance: '60px',
            origin: 'bottom',
            opacity: 0,
            scale: 0.9,
            easing: 'cubic-bezier(0.5, 0, 0, 1)',
            interval: 200
        });
        document.addEventListener('DOMContentLoaded', function () {
            const mensagem = "<?php echo isset($_SESSION['mensagem']) ? $_SESSION['mensagem'] : ''; ?>";
            const mensagemTipo = "<?php echo isset($_SESSION['mensagem_tipo']) ? $_SESSION['mensagem_tipo'] : ''; ?>";

            if (mensagem && mensagemTipo) {
                Swal.fire({
                    icon: mensagemTipo === 'success' ? 'success' : 'error',
                    title: mensagemTipo === 'success' ? 'Sucesso!' : 'Erro!',
                    text: mensagem,
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });


                <?php
                unset($_SESSION['mensagem']);
                unset($_SESSION['mensagem_tipo']);
                ?>
            }
        });
    </script>
</body>
</html>