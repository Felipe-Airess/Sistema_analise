<?php
require_once("../../../config/conexao.php");
session_start();

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: ../../login/login.php");
    exit();
}

$empresa_nome = $_SESSION['empresa_nome'];
$empresa_id = $_SESSION['empresa_id'];
$mensagem_status = '';


function calcularProgressoAtual($pdo, $empresa_id, $tipo, $data_inicio, $data_fim)
{
    $data_inicio_formatada = date('Y-m-d', strtotime($data_inicio));
    $data_fim_formatada = date('Y-m-d', strtotime($data_fim));

    $total_receitas = 0;
    $total_despesas = 0;


    $sql_receitas = "SELECT SUM(valor) FROM receitas WHERE empresa_id = ? AND data BETWEEN ? AND ?";
    $stmt_receitas = $pdo->prepare($sql_receitas);
    $stmt_receitas->execute([$empresa_id, $data_inicio_formatada, $data_fim_formatada]);
    $total_receitas = $stmt_receitas->fetchColumn() ?? 0;


    $sql_despesas = "SELECT SUM(valor) FROM despesas WHERE empresa_id = ? AND data BETWEEN ? AND ?";
    $stmt_despesas = $pdo->prepare($sql_despesas);
    $stmt_despesas->execute([$empresa_id, $data_inicio_formatada, $data_fim_formatada]);
    $total_despesas = $stmt_despesas->fetchColumn() ?? 0;


    if ($tipo === 'receita') {
        return (float) $total_receitas;
    } elseif ($tipo === 'despesa') {

        return (float) $total_despesas;
    } elseif ($tipo === 'lucro') {
        return (float) ($total_receitas - $total_despesas);
    }

    return 0.00;
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_meta'])) {
    $tipo = $_POST['tipo'] ?? '';
    $valor_meta = filter_var($_POST['valor_meta'], FILTER_VALIDATE_FLOAT);
    $descricao = filter_var($_POST['descricao'], FILTER_SANITIZE_STRING);
    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_fim = $_POST['data_fim'] ?? '';
    $cor = $_POST['cor'] ?? '#004b8d';

    if ($valor_meta === false || $valor_meta <= 0) {
        $mensagem_status = "<div class='p-3 bg-red-100 text-red-700 rounded-lg'>Erro: O valor da meta é inválido.</div>";
    } elseif (!in_array($tipo, ['receita', 'despesa', 'lucro'])) {
        $mensagem_status = "<div class='p-3 bg-red-100 text-red-700 rounded-lg'>Erro: Tipo de meta inválido.</div>";
    } else {
        try {
            $sql = "INSERT INTO metas (empresa_id, tipo, valor_meta, descricao, data_inicio, data_fim, cor) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$empresa_id, $tipo, $valor_meta, $descricao, $data_inicio, $data_fim, $cor]);
            $mensagem_status = "<div class='p-3 bg-green-100 text-green-700 rounded-lg'>Sucesso! Meta adicionada.</div>";

            header("Location: gerenciar_metas.php?status=success");
            exit();
        } catch (PDOException $e) {
            error_log("Erro ao adicionar meta: " . $e->getMessage());
            $mensagem_status = "<div class='p-3 bg-red-100 text-red-700 rounded-lg'>Erro ao adicionar meta. Tente novamente.</div>";
        }
    }
}


$metas = [];
try {
    $sql_metas = "SELECT id, tipo, valor_meta, descricao, data_inicio, data_fim, cor FROM metas WHERE empresa_id = ? ORDER BY data_fim DESC";
    $stmt_metas = $pdo->prepare($sql_metas);
    $stmt_metas->execute([$empresa_id]);
    $metas_db = $stmt_metas->fetchAll(PDO::FETCH_ASSOC);

    foreach ($metas_db as $meta) {

        $valor_atual = calcularProgressoAtual($pdo, $empresa_id, $meta['tipo'], $meta['data_inicio'], $meta['data_fim']);

        $meta['valor_atual'] = $valor_atual;
        $meta['progresso_percentual'] = ($meta['valor_meta'] > 0) ? min(100, round(($valor_atual / $meta['valor_meta']) * 100, 2)) : 0;


        if ($meta['tipo'] === 'despesa') {

            $meta['status_class'] = ($valor_atual > $meta['valor_meta']) ? 'bg-red-500' : 'bg-yellow-500';
            $meta['status_text'] = ($valor_atual > $meta['valor_meta']) ? 'Excedida' : 'Em Andamento';
        } else {

            $meta['status_class'] = ($valor_atual >= $meta['valor_meta']) ? 'bg-green-500' : 'bg-blue-500';
            $meta['status_text'] = ($valor_atual >= $meta['valor_meta']) ? 'Atingida' : 'Em Andamento';
        }

        $metas[] = $meta;
    }

} catch (PDOException $e) {
    error_log("Erro ao buscar metas: " . $e->getMessage());
    $mensagem_status = "<div class='p-3 bg-red-100 text-red-700 rounded-lg'>Não foi possível carregar as metas.</div>";
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metas Financeiras</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiByeD0iNCIgZmlsbD0iIzAwNGI4ZCIvPgo8cGF0aCBkPSJNMTYgMTBWNiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHBhdGggZD0iTTIyIDE2SDEwIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8cGF0aCBkPSJNMTYgMjZWMjIiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+CjxwYXRoIGQ9Ik0yNiAxNkgyMiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHBhdGggZD0iTTE2IDZMMjIgMTZIMTBMMTYgNloiIGZpbGw9IndoaXRlIiBmaWxsLW9wYWNpdHk9IjAuOCIvPgo8cGF0aCBkPSJNMTYgMjZMMjIgMTZIMTBMMTYgMjZaIiBmaWxsPSJ3aGl0ZSIgZmlsbC1vcGFjaXR5PSIwLjYiLz4KPHBhdGggZD0iTTYgMTZIMiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPC9zdmc+Cg==">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/animejs/dist/bundles/anime.umd.min.js"></script>
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>


<body class="flex min-h-screen flex-col bg-gray-100 dark:bg-gray-900 transition-colors duration-500">

    <main class="flex flex-row gap-6 min-h-screen max-sm:flex-col">
        <aside class="w-48 bg-[#004b8d] dark:bg-gray-900 shadow-md max-h-screen flex flex-col max-sm:w-full max-sm:h-full max-sm:flex-row transition-colors duration-500">
            <div class="py-6 px-6 justify-start flex items-center flex-row">
                <div class="rounded-full py-2 px-1 flex items-center justify-center">
                    <i class="fas fa-user-circle text-white text-2xl"></i>
                </div>
                <h2 class="font-['Poppins'] text-lg text-white font-regular ml-2">
                    
                    <?= htmlspecialchars($empresa_nome); ?>
                </h2>
            </div>

            <nav class="flex flex-col pt-4 pb-12 h-full items-center overflow-y-auto justify-between max-sm:hidden">
                <div class="pb-4 gap-8 flex px-4 items-center w-full">
                    <ul class="flex flex-col gap-4 w-full">
                        <li>
                            <a href="../gerenciador.php"
                                class="flex items-center gap-3 text-white hover:white font-['Poppins'] hover:bg-white/10 dark:hover:bg-gray-800 w-full p-2 rounded-lg transition-all <?= basename($_SERVER['PHP_SELF']) == 'gerenciador.php' ? 'bg-white/20 dark:bg-gray-800' : '' ?>">
                                <i class="fas fa-home w-5 h-5"></i>
                                Inicio
                            </a>
                        </li>

                        <li>
                            <a href="../despesas/gerenciar_despesas.php"
                                class="flex items-center gap-3 text-white hover:white font-['Poppins'] w-full p-2 rounded-lg hover:bg-white/10 dark:hover:bg-gray-800 transition-all <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_despesas.php' ? 'bg-white/20 dark:bg-gray-800' : '' ?>">
                                <i class="fas fa-arrow-down w-5 h-5"></i>
                                Despesas
                            </a>
                        </li>

                        <li>
                            <a href="../receitas/gerenciar_receitas.php"
                                class="flex items-center gap-3 text-white hover:white font-['Poppins'] w-full p-2 rounded-lg hover:bg-white/10 dark:hover:bg-gray-800 transition-all <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_receitas.php' ? 'bg-white/20 dark:bg-gray-800' : '' ?>">
                                <i class="fas fa-arrow-up w-5 h-5"></i>
                                Receitas
                            </a>
                        </li>

                        <li>
                            <a href="../categorias/gerenciar_categorias.php"
                                class="flex items-center gap-3 text-white hover:white font-['Poppins'] w-full p-2 rounded-lg hover:bg-white/10 dark:hover:bg-gray-800 transition-all <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_categorias.php' ? 'bg-white/20 dark:bg-gray-800' : '' ?>">
                                <i class="fas fa-tags w-5 h-5"></i>
                                Categorias
                            </a>
                        </li>

                        <li>
                            <a href="gerenciar_metas.php"
                                class="flex items-center gap-3 text-white hover:white font-['Poppins'] w-full p-2 rounded-lg hover:bg-white/10 dark:hover:bg-gray-800 transition-all <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_metas.php' ? 'bg-white/20 dark:bg-gray-800' : '' ?>">
                                <i class="fas fa-bullseye w-5 h-5"></i>
                                Metas
                            </a>
                        </li>

                        <li>
                            <button id="openSettingsModal"
                                class="flex items-center gap-3 text-white hover:white font-['Poppins'] w-full p-2 rounded-lg hover:bg-white/10 dark:hover:bg-gray-800 transition-all">
                                <i class="fas fa-cog w-5 h-5"></i>
                                Configs
                            </button>
                        </li>

                    </ul>
                </div>
                <div class="mt-4 p-4 flex justify-center rounded-lg ">
                    <a href="../../login/logout.php"
                        class="flex items-center gap-2 text-white bg-white/10 hover:bg-white/5 dark:hover:bg-gray-800/50 px-8 py-2 rounded-lg font-['Poppins'] transition-all">
                        <i class="fas fa-sign-out-alt"></i>
                        Sair
                    </a>
                </div>
            </nav>

            <div x-data="{ open: false }" class="sm:hidden" x-cloak>
                <button @click="open = true" class="fixed bottom-6 right-6 z-40 w-14 h-14 flex items-center justify-center rounded-full bg-[#004b8d] text-white shadow-lg hover:bg-[#003d6b] transition-all">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <div x-show="open" @click="open = false" class="fixed inset-0 z-30 flex">
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

                    <aside x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative w-64 h-full bg-gradient-to-r from-blue-500 to-blue-700 shadow-lg flex flex-col">
                        <div class="p-4 border-b border-white/10 flex items-center justify-between">
                            <span class="text-lg font-semibold text-white">Menu</span>
                            <button @click="open = false" class="text-white hover:bg-white/10 p-2 rounded-lg transition-colors">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <nav class="flex-1 overflow-y-auto flex flex-col gap-2 p-4">
                            <a href="../gerenciador.php" @click="open = false" class="p-3 flex items-center gap-2 text-white font-['Poppins'] rounded-lg transition-colors <?= basename($_SERVER['PHP_SELF']) == 'gerenciador.php' ? 'bg-white/20' : 'hover:bg-white/10' ?>">
                                <i class="fas fa-home"></i> Inicio
                            </a>
                            <a href="../despesas/gerenciar_despesas.php" @click="open = false" class="p-3 flex items-center gap-2 text-white font-['Poppins'] rounded-lg transition-colors <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_despesas.php' ? 'bg-white/20' : 'hover:bg-white/10' ?>">
                                <i class="fas fa-arrow-down"></i> Despesas
                            </a>
                            <a href="../receitas/gerenciar_receitas.php" @click="open = false" class="p-3 flex items-center gap-2 text-white font-['Poppins'] rounded-lg transition-colors <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_receitas.php' ? 'bg-white/20' : 'hover:bg-white/10' ?>">
                                <i class="fas fa-arrow-up"></i> Receitas
                            </a>
                            <a href="../categorias/gerenciar_categorias.php" @click="open = false" class="p-3 flex items-center gap-2 text-white font-['Poppins'] rounded-lg transition-colors <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_categorias.php' ? 'bg-white/20' : 'hover:bg-white/10' ?>">
                                <i class="fas fa-tags"></i> Categorias
                            </a>
                            <a href="gerenciar_metas.php" @click="open = false" class="p-3 flex items-center gap-2 text-white font-['Poppins'] rounded-lg transition-colors <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_metas.php' ? 'bg-white/20' : 'hover:bg-white/10' ?>">
                                <i class="fas fa-bullseye"></i> Metas
                            </a>
                            <button id="openSettingsModalSm" @click="open = false" class="p-3 flex items-center gap-2 text-white font-['Poppins'] rounded-lg hover:bg-white/10 transition-colors text-left w-full">
                                <i class="fas fa-cog"></i> Configurações
                            </button>
                        </nav>

                        <div class="border-t border-white/10 p-4">
                            <a href="../../login/logout.php" class="p-3 flex items-center gap-2 text-white font-['Poppins'] rounded-lg hover:bg-red-600 transition-colors w-full">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a>
                        </div>
                    </aside>
                </div>
            </div>
        </aside>


        <div class="slide flex-1 flex flex-col max-h-screen p-6 overflow-y-auto">
            <h1 class="text-3xl font-bold text-[#004b8d] dark:text-gray-100 mb-6 transition-colors duration-500 flex items-center gap-2">
                <i class="fas fa-bullseye text-blue-500"></i>
                Gerenciamento de Metas
            </h1>


            <?php echo $mensagem_status; ?>
            <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="p-3 mb-4 bg-green-100 text-green-700 rounded-lg flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    Sucesso! Meta adicionada ou atualizada.
                </div>
            <?php endif; ?>


            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl mb-8 transition-colors duration-500">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 border-b pb-2 flex items-center gap-2">
                    <i class="fas fa-plus-circle text-green-500"></i>
                    Definir Nova Meta
                </h2>
                <form method="POST" action="gerenciar_metas.php" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="hidden" name="adicionar_meta" value="1">

                    <div>
                        <label for="descricao" class="block text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <i class="fas fa-align-left text-gray-500"></i>
                            Descrição
                        </label>
                        <input type="text" name="descricao" id="descricao" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring-[#004b8d] focus:border-[#004b8d]">
                    </div>

                    <div>
                        <label for="tipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <i class="fas fa-chart-line text-gray-500"></i>
                            Tipo da Meta
                        </label>
                        <select name="tipo" id="tipo" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring-[#004b8d] focus:border-[#004b8d]">
                            <option value="lucro">Lucro (Receitas - Despesas)</option>
                            <option value="receita">Receita Total</option>
                            <option value="despesa">Despesa (Teto Máximo)</option>
                        </select>
                    </div>

                    <div>
                        <label for="valor_meta" class="block text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <i class="fas fa-dollar-sign text-gray-500"></i>
                            Valor Meta (R$)
                        </label>
                        <input type="number" step="0.01" name="valor_meta" id="valor_meta" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring-[#004b8d] focus:border-[#004b8d]">
                    </div>

                    <div>
                        <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-gray-500"></i>
                            Data de Início
                        </label>
                        <input type="date" name="data_inicio" id="data_inicio" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring-[#004b8d] focus:border-[#004b8d]">
                    </div>

                    <div>
                        <label for="data_fim" class="block text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <i class="fas fa-calendar-check text-gray-500"></i>
                            Data Final
                        </label>
                        <input type="date" name="data_fim" id="data_fim" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring-[#004b8d] focus:border-[#004b8d]">
                    </div>

                    <div>
                        <label for="cor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <i class="fas fa-palette text-gray-500"></i>
                            Cor
                        </label>
                        <input type="color" name="cor" id="cor" value="#004b8d" required
                            class="mt-1 block w-full h-10 rounded-md border-gray-300 shadow-sm dark:border-gray-600">
                    </div>


                    <div class="md:col-span-3 pt-4 flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-[#004b8d] text-white font-semibold rounded-lg hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 transition-colors shadow-md flex items-center gap-2">
                            <i class="fas fa-save"></i>
                            Salvar Meta
                        </button>
                    </div>
                </form>
            </div>


            <h2 class="text-2xl font-bold text-[#004b8d] dark:text-gray-100 mb-4 transition-colors duration-500 flex items-center gap-2">
                <i class="fas fa-list-check text-blue-500"></i>
                Metas Ativas
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($metas)): ?>
                    <div class="md:col-span-3 text-center py-8">
                        <i class="fas fa-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                        <p class="text-gray-500 dark:text-gray-400">Nenhuma meta definida ainda. Crie uma acima para começar a monitorar!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($metas as $meta):

                        $data_inicio_f = date('d/m/Y', strtotime($meta['data_inicio']));
                        $data_fim_f = date('d/m/Y', strtotime($meta['data_fim']));
                        $valor_meta_f = number_format($meta['valor_meta'], 2, ',', '.');
                        $valor_atual_f = number_format($meta['valor_atual'], 2, ',', '.');


                        $progresso_visual = min(100, $meta['progresso_percentual']);
                        ?>
                        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-lg border-t-4 transition-colors duration-500"
                            style="border-top-color: <?= htmlspecialchars($meta['cor']); ?>;">

                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 leading-tight">
                                    <i class="fas fa-target mr-2" style="color: <?= htmlspecialchars($meta['cor']); ?>;"></i>
                                    <?= htmlspecialchars($meta['descricao']); ?>
                                </h3>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full text-white"
                                    style="background-color: <?= htmlspecialchars($meta['cor']); ?>;">
                                    <?= ucfirst($meta['tipo']); ?>
                                </span>
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 flex items-center gap-2">
                                <i class="fas fa-calendar text-gray-400"></i>
                                <?= $data_inicio_f ?> a <?= $data_fim_f ?>
                            </p>

                            <div class="mb-4">
                                <div class="flex justify-between text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-chart-bar"></i>
                                        Progresso: **<?= $meta['progresso_percentual']; ?>%**
                                    </span>
                                    <span class="<?= ($meta['progresso_percentual'] >= 100 && $meta['tipo'] !== 'despesa') ? 'text-green-500' : ($meta['tipo'] === 'despesa' && $meta['progresso_percentual'] > 100 ? 'text-red-500' : 'text-gray-500 dark:text-gray-400') ?> flex items-center gap-1">
                                        <i class="fas <?= ($meta['progresso_percentual'] >= 100 && $meta['tipo'] !== 'despesa') ? 'fa-check-circle' : ($meta['tipo'] === 'despesa' && $meta['progresso_percentual'] > 100 ? 'fa-exclamation-triangle' : 'fa-clock') ?>"></i>
                                        <?= $meta['status_text'] ?>
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="h-2.5 rounded-full <?= $meta['status_class'] ?>"
                                        style="width: <?= $progresso_visual ?>%;"></div>
                                </div>
                            </div>


                            <div class="grid grid-cols-2 text-sm font-['Poppins'] mb-3">
                                <div class="text-gray-600 dark:text-gray-300 flex items-center gap-1">
                                    <i class="fas fa-flag text-gray-400"></i>
                                    Meta:
                                </div>
                                <div class="font-bold text-right text-gray-800 dark:text-gray-100 flex items-center gap-1 justify-end">
                                    <i class="fas fa-dollar-sign text-green-500"></i>
                                    R$ <?= $valor_meta_f ?>
                                </div>

                                <div class="text-gray-600 dark:text-gray-300 flex items-center gap-1">
                                    <i class="fas fa-chart-line text-gray-400"></i>
                                    Realizado:
                                </div>
                                <div class="font-bold text-right text-lg <?= ($meta['tipo'] === 'despesa' && $meta['valor_atual'] > $meta['valor_meta']) ? 'text-red-500' : 'text-[#004b8d] dark:text-blue-400' ?> flex items-center gap-1 justify-end">
                                    <i class="fas fa-dollar-sign"></i>
                                    R$ <?= $valor_atual_f ?>
                                </div>
                            </div>
                            <div class="flex item-center justify-center gap-2">
                                <a href="editar_metas.php?id=<?= $meta['id'] ?>"
                                    class="w-8 h-8 flex items-center justify-center transform hover:text-blue-500 dark:hover:text-blue-400 hover:scale-110 icon-animacao text-gray-600 dark:text-gray-300 transition-colors duration-500 rounded-full hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#"
                                    class="w-8 h-8 flex items-center justify-center transform hover:text-red-500 dark:hover:text-red-400 hover:scale-110 icon-animacao text-gray-600 dark:text-gray-300 transition-colors duration-500 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20"
                                    title="Excluir" onclick="confirmarExclusao(<?= $meta['id'] ?>); return false;">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </main>


    <div id="settingsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50 transition-opacity duration-300 dark:bg-gray-900 dark:bg-opacity-75">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-11/12 md:max-w-lg mx-auto p-6 transition-transform transform duration-300">

            <div class="flex justify-between items-center mb-6 border-b pb-3 border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <i class="fas fa-cog text-blue-500"></i>
                    Configurações
                </h3>
                <button id="closeModalBtn" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="flex items-center gap-3">
                    <i class="fas fa-palette text-blue-500 text-xl"></i>
                    <span class="text-gray-700 dark:text-gray-100 font-semibold">Tema da Aplicação</span>
                </div>

                <button id="themeToggle" class="flex items-center gap-2 px-4 py-2 rounded-full font-['Poppins'] transition-all text-sm bg-gray-200 dark:bg-[#004b8d] text-gray-700 dark:text-white hover:opacity-90">
                    <i id="moonIcon" class="fas fa-moon hidden"></i>
                    <i id="sunIcon" class="fas fa-sun"></i>
                    <span id="themeText">Claro</span>
                </button>
            </div>

        </div>
    </div>
    <script src="../../assets/js/darkmode.js"></script>
    <script>
        window.revelar = ScrollReveal();
        revelar.reveal('main aside,.slide', {
            duration: 1000,
            origin: 'left',
            distance: '50px'
        });

        function confirmarExclusao(metaId) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Esta ação não pode ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `excluir_meta.php?id=${metaId}`;
                }
            });
        }
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

    // Menu Mobile Toggle
    const menuToggleMobile = document.getElementById('menuToggleMobile');
    const mobileMenuDropdown = document.getElementById('mobileMenuDropdown');

    if (menuToggleMobile) {
        menuToggleMobile.addEventListener('click', function(e) {
            e.stopPropagation();
            mobileMenuDropdown.classList.toggle('hidden');
        });

        // Fechar menu ao clicar em um link
        const menuLinks = mobileMenuDropdown.querySelectorAll('a, button');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileMenuDropdown.classList.add('hidden');
            });
        });

        // Fechar menu ao clicar fora
        document.addEventListener('click', function(e) {
            if (!mobileMenuDropdown.contains(e.target) && !menuToggleMobile.contains(e.target)) {
                mobileMenuDropdown.classList.add('hidden');
            }
        });
    }
    </script>
</body>
</html>
