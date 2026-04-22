<?php
require_once("../../config/conexao.php");



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cnpj = preg_replace('/\D/', '', $_POST['cnpj']); // Remove caracteres não numéricos
    $nome = $_POST['nome'];
    $telefone = preg_replace('/\D/', '', $_POST['telefone']); // Remove caracteres não numéricos
    $email = $_POST['email'];
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];
    
    // Validações
    $erros = [];
    
    if (strlen($cnpj) !== 14) {
        $erros[] = "CNPJ deve conter 14 dígitos.";
    }
    
    if (strlen($telefone) !== 11) {
        $erros[] = "Telefone deve conter 11 dígitos.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Email inválido.";
    }
    
    if (strlen($usuario) < 3) {
        $erros[] = "Nome de usuário deve ter no mínimo 3 caracteres.";
    }
    
    if (strlen($senha) < 6) {
        $erros[] = "Senha deve ter no mínimo 6 caracteres.";
    }
    
    if (!empty($erros)) {
        $_SESSION['mensagem'] = implode(' ', $erros);
        $_SESSION['mensagem_tipo'] = "error";
        header('Location: cadastro.php');
        exit;
    }
    
    try {
        $pdo->beginTransaction();

        $sql_empresas = "INSERT INTO empresas (cnpj, nome, telefone, email) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql_empresas);
        $stmt->execute([$cnpj, $nome, $telefone, $email]);

        $empresa_id = $pdo->lastInsertId();

        $senha_encriptografada = password_hash($senha, PASSWORD_DEFAULT);
        $sql_usuarios = "INSERT INTO usuarios (nome, senha, empresa_id, email) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql_usuarios);
        $stmt->execute([$usuario, $senha_encriptografada, $empresa_id, $email]);

        $pdo->commit();
        
        $_SESSION['mensagem'] = "Cadastro realizado com sucesso! Você já pode fazer login.";
        $_SESSION['mensagem_tipo'] = "success";
        header('Location: ../login/login.php');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
             $_SESSION['mensagem'] = "Erro: CNPJ, Email ou Usuário já cadastrado.";
             $_SESSION['mensagem_tipo'] = "error";

        } else {
             $_SESSION['mensagem'] = "Erro: CNPJ, Email ou Usuário já cadastrado.";
             $_SESSION['mensagem_tipo'] = "error";
        }
    }
} 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Sistema de Análise</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiByeD0iNCIgZmlsbD0iIzAwNGI4ZCIvPgo8cGF0aCBkPSJNMTYgMTBWNiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHBhdGggZD0iTTIyIDE2SDEwIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8cGF0aCBkPSJNMTYgMjZWMjIiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+CjxwYXRoIGQ9Ik0yNiAxNkgyMiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHBhdGggZD0iTTE2IDZMMjIgMTZIMTBMMTYgNloiIGZpbGw9IndoaXRlIiBmaWxsLW9wYWNpdHk9IjAuOCIvPgo8cGF0aCBkPSJNMTYgMjZMMjIgMTZIMTBMMTYgMjZaIiBmaWxsPSJ3aGl0ZSIgZmlsbC1vcGFjaXR5PSIwLjYiLz4KPHBhdGggZD0iTTYgMTZIMiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPC9zdmc+Cg==">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/imask"></script>
    <script src="../assets/js/masks.js"></script>
    
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
        .input-focus:focus {
            --tw-ring-color: #007BFF;
            box-shadow: 0 0 0 2px var(--tw-ring-color);
            border-color: transparent;
        }
    </style>
</head>
<body class="flex items-center bg-gradient-to-r from-blue-400 to-[#004b8d] justify-center min-h-screen">

    <div class="scrol w-full max-w-lg p-8 bg-white shadow-xl rounded-xl border border-gray-200">
        
        <div class="text-center mb-6">
            <i class="fas fa-user-plus text-4xl link-highlight mb-3"></i> 
            <h1 class="text-2xl font-semibold text-gray-800">Crie sua Conta</h1>
            <p class="text-sm text-gray-500 mt-1">Dados da Empresa e Credenciais de Acesso</p>
        </div>

        

        <form action="" method="POST" class="space-y-4">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3 border-b pb-1">Dados da Empresa</h3>
                    <div class="space-y-4">
                        <div class="form-group">
                            <label for="cnpj" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-building mr-2 text-gray-500"></i> CNPJ
                            </label>
                            <input type="text" name="cnpj" id="cnpj" required
                                class="border border-gray-300 p-3 rounded-lg w-full input-focus focus:ring-2 focus:ring-[#007BFF] focus:border-transparent shadow-sm"
                                placeholder="00.000.000/0001-00">
                        </div>
                        <div class="form-group">
                            <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-briefcase mr-2 text-gray-500"></i> Nome da Empresa
                            </label>
                            <input type="text" name="nome" id="nome" required
                                class="border border-gray-300 p-3 rounded-lg w-full input-focus focus:ring-2 focus:ring-[#007BFF] focus:border-transparent shadow-sm"
                                placeholder="Minha Empresa S.A.">
                        </div>
                        <div class="form-group">
                            <label for="telefone" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-phone mr-2 text-gray-500"></i> Telefone
                            </label>
                            <input type="text" name="telefone" id="telefone" required
                                class="border border-gray-300 p-3 rounded-lg w-full input-focus focus:ring-2 focus:ring-[#007BFF] focus:border-transparent shadow-sm"
                                placeholder="(XX) XXXXX-XXXX">
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3 border-b pb-1">Dados de Acesso</h3>
                    <div class="space-y-4">
                        <div class="form-group">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-envelope mr-2 text-gray-500"></i> Email
                            </label>
                            <input type="email" name="email" id="email" required
                                class="border border-gray-300 p-3 rounded-lg w-full input-focus focus:ring-2 focus:ring-[#007BFF] focus:border-transparent shadow-sm"
                                placeholder="seu.email@exemplo.com">
                        </div>
                        <div class="form-group">
                            <label for="usuario" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-user-circle mr-2 text-gray-500"></i> Usuário
                            </label>
                            <input type="text" name="usuario" id="usuario" required
                                class="border border-gray-300 p-3 rounded-lg w-full input-focus focus:ring-2 focus:ring-[#007BFF] focus:border-transparent shadow-sm"
                                placeholder="Escolha um nome de usuário">
                        </div>
                        <div class="form-group">
                            <label for="senha" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-lock mr-2 text-gray-500"></i> Senha
                            </label>
                            <input type="password" name="senha" id="senha" required
                                class="border border-gray-300 p-3 rounded-lg w-full input-focus focus:ring-2 focus:ring-[#007BFF] focus:border-transparent shadow-sm"
                                placeholder="Crie uma senha forte">
                        </div>
                    </div>
                </div>

            </div>

            <button type="submit"
                class="btn-primary cursor-pointer text-white font-semibold rounded-lg p-3 w-full shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#007BFF] focus:ring-offset-2 mt-6">
                <i class="fas fa-check-circle mr-2"></i> Confirmar Cadastro
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="../login/login.php" class="text-sm font-medium link-highlight">
                Já possui uma conta? Faça Login
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