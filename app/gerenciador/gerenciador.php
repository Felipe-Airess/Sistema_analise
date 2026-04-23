<?php
require_once("../../config/conexao.php");
session_start();

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: ../login/login.php");
    exit();
}

$empresa_nome = $_SESSION['empresa_nome'];
$empresa_id = $_SESSION['empresa_id'];

$sql_receitas = "SELECT SUM(valor) AS total_receitas FROM receitas WHERE empresa_id = ? ORDER BY data DESC";
$stmt = $pdo->prepare($sql_receitas);
$stmt->execute([$empresa_id]);
$total_receitas = $stmt->fetchColumn() ?? 0;

$sql_ultimas_receitas = "
    SELECT r.descricao, r.valor, r.data, COALESCE(c.nome, 'Sem Categoria') as categoria_nome 
    FROM receitas r
    LEFT JOIN categorias c ON r.categoria_id = c.id
    WHERE r.empresa_id = ?
    ORDER BY r.data DESC, r.id DESC 
    LIMIT 5";

$stmt = $pdo->prepare($sql_ultimas_receitas);
$stmt->execute([$empresa_id]);
$ultimas_receitas = $stmt->fetchAll();


$sql_ultimas_despesas = "
    SELECT d.descricao, d.valor, d.data, COALESCE(c.nome, 'Sem Categoria') as categoria_nome 
    FROM despesas d
    LEFT JOIN categorias c ON d.categoria_id = c.id
    WHERE d.empresa_id = ?
    ORDER BY d.data DESC, d.id DESC 
    LIMIT 5";
$stmt = $pdo->prepare($sql_ultimas_despesas);
$stmt->execute([$empresa_id]);
$ultimas_despesas = $stmt->fetchAll();

$categorias = "SELECT * FROM categorias WHERE empresa_id = $empresa_id";
$stmt = $pdo->prepare($categorias);
$stmt->execute();
$categorias = $stmt->fetchAll();

$sql_evolucao_mensal = "SELECT 
    MONTH(data) as mes,
    SUM(valor) as total
    FROM receitas 
    WHERE empresa_id = ? 
    AND YEAR(data) = YEAR(CURDATE())
    GROUP BY MONTH(data)
    ORDER BY mes";

$stmt = $pdo->prepare($sql_evolucao_mensal);
$stmt->execute([$empresa_id]);
$evolucao_mensal = $stmt->fetchAll();

$meses_nomes = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
$meses_dados = [];
$valores_dados = [];


foreach ($evolucao_mensal as $mes) {
    $meses_dados[] = $meses_nomes[$mes['mes'] - 1];
    $valores_dados[] = floatval($mes['total']);
}

$sql_despesas = "SELECT SUM(valor) AS total_despesas FROM despesas WHERE empresa_id = ? ORDER BY data DESC";
$stmt = $pdo->prepare($sql_despesas);
$stmt->execute([$empresa_id]);
$total_despesas = $stmt->fetchColumn() ?? 0;
$lucro_total = $total_receitas - $total_despesas;

$categorias_lucrativas_sql = "SELECT c.nome, 
    (SELECT IFNULL(SUM(r.valor), 0) FROM receitas r WHERE r.categoria_id = c.id AND r.empresa_id = ?) -
    (SELECT IFNULL(SUM(d.valor), 0) FROM despesas d WHERE d.categoria_id = c.id AND d.empresa_id = ?) AS lucro
    FROM categorias c
    WHERE c.empresa_id = ?
    ORDER BY lucro DESC
    LIMIT 5";

$stmt = $pdo->prepare($categorias_lucrativas_sql);
$stmt->execute([$empresa_id, $empresa_id, $empresa_id]);
$categorias_mais_lucrativas = $stmt->fetchAll();

$lucros_mes_sql = "SELECT 
    MONTH(r.data) AS mes,
    SUM(r.valor) - COALESCE((
        SELECT SUM(d.valor) 
        FROM despesas d 
        WHERE d.empresa_id = r.empresa_id 
        AND MONTH(d.data) = MONTH(r.data)
    ), 0) AS lucro
FROM receitas r
WHERE r.empresa_id = ?
GROUP BY MONTH(r.data)
ORDER BY mes
LIMIT 12;

";
$stmt = $pdo->prepare($lucros_mes_sql);
$stmt->execute([$empresa_id]);
$lucros_por_mes = $stmt->fetchAll();

$meses_lucro = [];
$valores_lucro = [];

foreach ($lucros_por_mes as $lucro) {
    $meses_lucro[] = $meses_nomes[$lucro['mes'] - 1];
    $valores_lucro[] = floatval($lucro['lucro']);
}

$cores_lucro = [];
foreach ($valores_lucro as $lucro) {
    if ($lucro >= 0) {
        $cores_lucro[] = 'rgba(16, 185, 129, 0.8)';
    } else {
        $cores_lucro[] = 'rgba(239, 68, 68, 0.8)';
    }
}




$lucro_trimestral_sql = "SELECT 
    CONCAT('T', QUARTER(r.data)) AS trimestre,
    YEAR(r.data) AS ano,
    SUM(r.valor) - COALESCE((
        SELECT SUM(d.valor) 
        FROM despesas d 
        WHERE d.empresa_id = r.empresa_id 
        AND QUARTER(d.data) = QUARTER(r.data)
        AND YEAR(d.data) = YEAR(r.data)
    ), 0) AS lucro
FROM receitas r
WHERE r.empresa_id = ?
GROUP BY YEAR(r.data), QUARTER(r.data)
ORDER BY ano DESC, trimestre DESC
LIMIT 4";

$stmt = $pdo->prepare($lucro_trimestral_sql);
$stmt->execute([$empresa_id]);
$dados_trimestrais = $stmt->fetchAll(PDO::FETCH_ASSOC);


$trimestres = [];
$valores_trimestrais = [];
$cores_trimestrais = [];

foreach ($dados_trimestrais as $trimestre) {
    $trimestres[] = $trimestre['trimestre'] . '/' . $trimestre['ano'];
    $valores_trimestrais[] = floatval($trimestre['lucro']);
    $cores_trimestrais[] = $trimestre['lucro'] >= 0 ? 'rgba(16, 185, 129, 0.8)' : 'rgba(239, 68, 68, 0.8)';
}


?>
<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiByeD0iNCIgZmlsbD0iIzAwNGI4ZCIvPgo8cGF0aCBkPSJNMTYgMTBWNiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHBhdGggZD0iTTIyIDE2SDEwIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8cGF0aCBkPSJNMTYgMjZWMjIiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+CjxwYXRoIGQ9Ik0yNiAxNkgyMiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHBhdGggZD0iTTE2IDZMMjIgMTZIMTBMMTYgNloiIGZpbGw9IndoaXRlIiBmaWxsLW9wYWNpdHk9IjAuOCIvPgo8cGF0aCBkPSJNMTYgMjZMMjIgMTZIMTBMMTYgMjZaIiBmaWxsPSJ3aGl0ZSIgZmlsbC1vcGFjaXR5PSIwLjYiLz4KPHBhdGggZD0iTTYgMTZIMiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPC9zdmc+Cg==">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/animejs/dist/bundles/anime.umd.min.js"></script>
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.0/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        [x-cloak] { display: none !important; }
    </style>


</head>


<body class="flex min-h-screen flex-col bg-gray-100 dark:bg-gray-900 transition-colors duration-500">

    <main class="flex flex-row gap-6 max-h-screen overflow-hidden max-sm:flex-col">

        <aside class="w-48 bg-[#004b8d] dark:bg-gray-900 shadow-md min-h-screen flex flex-col transition-colors duration-500">
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
                            <a href="gerenciador.php"
                                class="flex items-center gap-3 text-white hover:white font-['Poppins'] hover:bg-white/10 dark:hover:bg-gray-800 w-full p-2 rounded-lg transition-all <?= basename($_SERVER['PHP_SELF']) == 'gerenciador.php' ? 'bg-white/20 dark:bg-gray-800' : '' ?>">
                                <i class="fas fa-home w-5 h-5"></i>
                                Inicio
                            </a>
                        </li>

                        <li>
                            <a href="../gerenciador/despesas/gerenciar_despesas.php"
                                class="flex items-center gap-3 text-white hover:white font-['Poppins'] w-full p-2 rounded-lg hover:bg-white/10 dark:hover:bg-gray-800 transition-all <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_despesas.php' ? 'bg-white/20 dark:bg-gray-800' : '' ?>">
                                <i class="fas fa-arrow-down w-5 h-5"></i>
                                Despesas
                            </a>
                        </li>

                        <li>
                            <a href="../gerenciador/receitas/gerenciar_receitas.php"
                                class="flex items-center gap-3 text-white hover:white font-['Poppins'] w-full p-2 rounded-lg hover:bg-white/10 dark:hover:bg-gray-800 transition-all <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_receitas.php' ? 'bg-white/20 dark:bg-gray-800' : '' ?>">
                                <i class="fas fa-arrow-up w-5 h-5"></i>
                                Receitas
                            </a>
                        </li>

                        <li>
                            <a href="../gerenciador/categorias/gerenciar_categorias.php"
                                class="flex items-center gap-3 text-white hover:white font-['Poppins'] w-full p-2 rounded-lg hover:bg-white/10 dark:hover:bg-gray-800 transition-all <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_categorias.php' ? 'bg-white/20 dark:bg-gray-800' : '' ?>">
                                <i class="fas fa-tags w-5 h-5"></i>
                                Categorias
                            </a>
                        </li>

                        <li>
                            <a href="../gerenciador/metas/gerenciar_metas.php"
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
                    <a href="../login/logout.php"
                        class="flex items-center gap-2 text-white bg-white/10 hover:bg-white/5 dark:hover:bg-gray-800/50 px-8 py-2 rounded-lg font-['Poppins'] transition-all">
                        <i class="fas fa-sign-out-alt"></i>
                        Sair
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Menu Flutuante Mobile com Alpine.js -->
        
        <div class="flex-1 flex-col p-6 overflow-y-auto">

            <div class="grafico flex grid grid-cols-3 gap-6">

                <div class="grafico col-span-3 grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div class="grafico col-span-1 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-lg border-l-4 <?= $lucro_total >= 0 ? 'border-green-500' : 'border-red-500' ?> transition-colors duration-500">
                        <h4 class="font-['Poppins'] text-md text-gray-600 dark:text-gray-300 font-semibold mb-2 transition-colors duration-500 flex items-center gap-2">
                            <i class="fas fa-chart-line <?= $lucro_total >= 0 ? 'text-green-500' : 'text-red-500' ?>"></i>
                            Lucro Total
                        </h4>
                        <p class="font-['Poppins'] text-3xl <?= $lucro_total >= 0 ? 'text-green-600' : 'text-red-600' ?> font-bold">
                            <i class="fas fa-dollar-sign text-2xl"></i>
                            R$ <?= number_format($lucro_total, 2, ',', '.'); ?>
                        </p>
                    </div>

                    <div class="grafico col-span-1 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-lg border-l-4 border-blue-500 transition-colors duration-500">
                        <h4 class="font-['Poppins'] text-md text-gray-600 dark:text-gray-300 font-semibold mb-2 transition-colors duration-500 flex items-center gap-2">
                            <i class="fas fa-arrow-up text-blue-500"></i>
                            Total de Receitas
                        </h4>
                        <p class="font-['Poppins'] text-3xl text-blue-600 font-bold">
                            <i class="fas fa-dollar-sign text-2xl"></i>
                            R$ <?= number_format($total_receitas, 2, ',', '.'); ?>
                        </p>
                    </div>

                    <div class="grafico col-span-1 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-lg border-l-4 border-red-500 transition-colors duration-500">
                        <h4 class="font-['Poppins'] text-md text-gray-600 dark:text-gray-300 font-semibold mb-2 transition-colors duration-500 flex items-center gap-2">
                            <i class="fas fa-arrow-down text-red-500"></i>
                            Total de Despesas
                        </h4>
                        <p class="font-['Poppins'] text-3xl text-red-600 font-bold">
                            <i class="fas fa-dollar-sign text-2xl"></i>
                            R$ <?= number_format($total_despesas, 2, ',', '.'); ?>
                        </p>
                    </div>

                </div>

                <div class="grafico col-span-3 lg:col-span-2 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md transition-colors duration-500">
                    <h4 class="font-['Poppins'] text-md text-[#004b8d] dark:text-gray-100 font-semibold mb-4 transition-colors duration-500 flex items-center gap-2">
                        <i class="fas fa-chart-line text-blue-500"></i>
                        Evolução Mensal das Receitas
                    </h4>
                    <canvas id="evolucaoMensal" height="150"></canvas>
                </div>

                <div class="grafico col-span-3 lg:col-span-1 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md transition-colors duration-500">
                    <h4 class="font-['Poppins'] text-md text-[#004b8d] dark:text-gray-100 font-semibold mb-4 transition-colors duration-500 flex items-center gap-2">
                        <i class="fas fa-pie-chart text-purple-500"></i>
                        Lucro por Categoria
                    </h4>
                    <canvas id="categoriasLucrativas" height="150"></canvas>
                </div>

                <div class="grafico col-span-3 lg:col-span-2 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md transition-colors duration-500">
                    <h4 class="font-['Poppins'] text-md text-[#004b8d] dark:text-gray-100 font-semibold mb-4 transition-colors duration-500 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-green-500"></i>
                        Lucro Mensal
                    </h4>
                    <canvas id="lucroMensal" height="70"></canvas>
                </div>

                <div class="grafico col-span-3 lg:col-span-1 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md transition-colors duration-500">
                    <h4 class="font-['Poppins'] text-md text-[#004b8d] dark:text-gray-100 font-semibold mb-4 transition-colors duration-500 flex items-center gap-2">
                        <i class="fas fa-balance-scale text-orange-500"></i>
                        Receitas vs Despesas
                    </h4>
                    <canvas id="comparacaoReceitasDespesas" height="225"></canvas>
                </div>

                <div class="col-span-3 mt-4">
                    <h3 class="font-['Poppins'] text-lg text-[#004b8d] dark:text-gray-100 font-semibold mb-4 transition-colors duration-500 flex items-center gap-2">
                        <i class="fas fa-plus-circle text-green-500"></i>
                        Últimas Receitas Adicionadas
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg shadow-md transition-colors duration-500">
                            <thead>
                                <tr>
                                    <th class="py-3 px-6 bg-gray-200 dark:bg-gray-700 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-align-left mr-2"></i>Descrição
                                    </th>
                                    <th class="py-3 px-6 bg-gray-200 dark:bg-gray-700 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-tag mr-2"></i>Categoria
                                    </th>
                                    <th class="py-3 px-6 bg-gray-200 dark:bg-gray-700 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-dollar-sign mr-2"></i>Valor
                                    </th>
                                    <th class="py-3 px-6 bg-gray-200 dark:bg-gray-700 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-calendar mr-2"></i>Data
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimas_receitas as $receita): ?>
                                    <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-300">
                                        <td class="py-4 px-6 text-sm text-gray-700 dark:text-gray-300">
                                            <i class="fas fa-receipt text-gray-400 mr-2"></i>
                                            <?= htmlspecialchars($receita['descricao']); ?>
                                        </td>
                                        <td class="py-4 px-6 text-sm text-gray-700 dark:text-gray-300">
                                            <i class="fas fa-folder text-gray-400 mr-2"></i>
                                            <?= htmlspecialchars($receita['categoria_nome']); ?>
                                        </td>
                                        <td class="py-4 px-6 text-sm text-green-600 font-semibold">
                                            <i class="fas fa-arrow-up text-green-500 mr-2"></i>
                                            R$ <?= number_format($receita['valor'], 2, ',', '.'); ?>
                                        </td>
                                        <td class="py-4 px-6 text-sm text-gray-700 dark:text-gray-300">
                                            <i class="fas fa-clock text-gray-400 mr-2"></i>
                                            <?= date('d/m/Y', strtotime($receita['data'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-span-3 mt-4">
                    <h3 class="font-['Poppins'] text-lg text-[#004b8d] dark:text-gray-100 font-semibold mb-4 transition-colors duration-500 flex items-center gap-2">
                        <i class="fas fa-minus-circle text-red-500"></i>
                        Últimas Despesas Adicionadas
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg shadow-md transition-colors duration-500">
                            <thead>
                                <tr>
                                    <th class="py-3 px-6 bg-gray-200 dark:bg-gray-700 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-align-left mr-2"></i>Descrição
                                    </th>
                                    <th class="py-3 px-6 bg-gray-200 dark:bg-gray-700 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-tag mr-2"></i>Categoria
                                    </th>
                                    <th class="py-3 px-6 bg-gray-200 dark:bg-gray-700 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-dollar-sign mr-2"></i>Valor
                                    </th>
                                    <th class="py-3 px-6 bg-gray-200 dark:bg-gray-700 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-calendar mr-2"></i>Data
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimas_despesas as $despesa): ?>
                                    <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-300">
                                        <td class="py-4 px-6 text-sm text-gray-700 dark:text-gray-300">
                                            <i class="fas fa-receipt text-gray-400 mr-2"></i>
                                            <?= htmlspecialchars($despesa['descricao']); ?>
                                        </td>
                                        <td class="py-4 px-6 text-sm text-gray-700 dark:text-gray-300">
                                            <i class="fas fa-folder text-gray-400 mr-2"></i>
                                            <?= htmlspecialchars($despesa['categoria_nome']); ?>
                                        </td>
                                        <td class="py-4 px-6 text-sm text-red-600 font-semibold">
                                            <i class="fas fa-arrow-down text-red-500 mr-2"></i>
                                            R$ <?= number_format($despesa['valor'], 2, ',', '.'); ?>
                                        </td>
                                        <td class="py-4 px-6 text-sm text-gray-700 dark:text-gray-300">
                                            <i class="fas fa-clock text-gray-400 mr-2"></i>
                                            <?= date('d/m/Y', strtotime($despesa['data'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-span-3 lg:col-span-2 bg-white dark:bg-gray-800 px-4 py-6 rounded-lg shadow-md transition-colors duration-500">
                    <h4 class="font-['Poppins'] text-md text-[#004b8d] dark:text-gray-100 font-semibold mb-4 transition-colors duration-500 flex items-center gap-2">
                        <i class="fas fa-chart-area text-teal-500"></i>
                        Lucro Trimestre
                    </h4>
                    <canvas id="lucroTrimestral" height="100"></canvas>
                </div>
                
                <div class="col-span-3 lg:col-span-1 bg-white flex flex-col justify-center gap-4 rounded-lg items-center p-6 dark:bg-gray-800 shadow-md transition-colors duration-500">
                    <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2 flex items-center gap-2">
                        <i class="fas fa-rocket text-blue-600"></i>
                        Ações Rápidas
                    </h2>
                    
                    <a href="exportar_pdf.php" class="flex items-center justify-center gap-2 bg-[#004b8d] text-center w-full hover:bg-[#003366] text-white px-4 py-3 rounded-lg transition-all duration-300 hover:scale-105">
                        <i class="fas fa-file-pdf"></i>
                        Gerar Relatório 
                    </a>
                    
                    <button id="exportar_graficos" class="flex items-center justify-center gap-2 bg-[#004b8d] w-full hover:bg-[#003366] text-white px-4 py-3 rounded-lg transition-all duration-300 hover:scale-105">
                        <i class="fas fa-chart-bar"></i>
                        Exportar Gráficos
                    </button>

                    
                </div>                 
                
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
    <div x-data="{ open: false }" class="sm:hidden" x-cloak>
            <!-- Botão Flutuante -->
            <button @click="open = true"
                    class="fixed bottom-6 right-6 z-40 w-14 h-14 shrink-0 rounded-full
                           bg-[#004b8d] text-white shadow-2xl shadow-blue-900/40
                           hover:bg-[#003d73] hover:scale-110 active:scale-95
                           transition-all flex items-center justify-center focus:outline-none">
                <i class="fas fa-bars text-xl leading-none"></i>
            </button>

            <!-- Backdrop e Drawer -->
            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-30 flex">

                <div @click="open = false" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

                <aside x-show="open"
                       x-transition:enter="transition ease-out duration-200"
                       x-transition:enter-start="-translate-x-full"
                       x-transition:enter-end="translate-x-0"
                       x-transition:leave="transition ease-in duration-150"
                       x-transition:leave-start="translate-x-0"
                       x-transition:leave-end="-translate-x-full"
                       class="relative w-64 h-full bg-[#004b8d] dark:bg-gray-900 border-r border-white/10 shadow-2xl z-40 flex flex-col">

                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
                        <span class="font-bold text-xl text-white">
                            Menu
                        </span>
                        <button @click="open = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 hover:text-white hover:bg-white/10 transition">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Navigation -->
                    <nav class="flex flex-col gap-0.5 p-4 flex-1 overflow-y-auto">
                        <a href="gerenciador.php" @click="open = false"
                           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-semibold transition
                                  <?= basename($_SERVER['PHP_SELF']) == 'gerenciador.php' ? 'text-white bg-white/20' : 'text-gray-200 hover:text-white hover:bg-white/10' ?>">
                            <i class="fas fa-home text-base shrink-0"></i>
                            Inicio
                        </a>

                        <a href="../gerenciador/despesas/gerenciar_despesas.php" @click="open = false"
                           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-semibold transition
                                  <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_despesas.php' ? 'text-white bg-white/20' : 'text-gray-200 hover:text-white hover:bg-white/10' ?>">
                            <i class="fas fa-arrow-down text-base shrink-0"></i>
                            Despesas
                        </a>

                        <a href="../gerenciador/receitas/gerenciar_receitas.php" @click="open = false"
                           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-semibold transition
                                  <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_receitas.php' ? 'text-white bg-white/20' : 'text-gray-200 hover:text-white hover:bg-white/10' ?>">
                            <i class="fas fa-arrow-up text-base shrink-0"></i>
                            Receitas
                        </a>

                        <a href="../gerenciador/categorias/gerenciar_categorias.php" @click="open = false"
                           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-semibold transition
                                  <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_categorias.php' ? 'text-white bg-white/20' : 'text-gray-200 hover:text-white hover:bg-white/10' ?>">
                            <i class="fas fa-tags text-base shrink-0"></i>
                            Categorias
                        </a>

                        <a href="../gerenciador/metas/gerenciar_metas.php" @click="open = false"
                           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-semibold transition
                                  <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_metas.php' ? 'text-white bg-white/20' : 'text-gray-200 hover:text-white hover:bg-white/10' ?>">
                            <i class="fas fa-bullseye text-base shrink-0"></i>
                            Metas
                        </a>

                        <button id="openSettingsModalSm" @click="open = false" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-semibold text-gray-200 hover:text-white hover:bg-white/10 transition w-full text-left">
    <i class="fas fa-cog text-base shrink-0"></i> Configurações
</button>

                        <div class="my-4 pt-4 border-t border-white/10"></div>

                        <a href="../login/logout.php" @click="open = false"
                           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-semibold text-red-300 hover:text-white hover:bg-red-900/20 transition">
                            <i class="fas fa-sign-out-alt text-base shrink-0"></i>
                            Sair
                        </a>
                    </nav>
                </aside>
            </div>
        </div>
    <script>
        window.revelar = ScrollReveal();
        revelar.reveal('.grafico,main aside', {
            duration: 1000,
            origin: 'left',
            distance: '50px'
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

        const dadosGraficos = {
            evolucaoMensal: {
                meses: <?php echo json_encode($meses_dados); ?>,
                valores: <?php echo json_encode($valores_dados); ?>
            },
            comparacao: {
                receitas: <?php echo $total_receitas; ?>,
                despesas: <?php echo $total_despesas; ?>
            },
            categorias: <?php echo json_encode($categorias_mais_lucrativas ?: []); ?>,
            lucrosMensais: {
                meses: <?php echo json_encode($meses_lucro); ?>,
                lucros: <?php echo json_encode($valores_lucro); ?>,
                cores: <?php echo json_encode($cores_lucro); ?>
            },
            lucroTrimestral: {
                labels: <?php echo json_encode($trimestres); ?>,
                valores: <?php echo json_encode($valores_trimestrais); ?>,
                cores: <?php echo json_encode($cores_trimestrais); ?>
            }
        };

        window.charts = [];

        window.updateChartStyles = (isDarkMode) => {
            if (typeof Chart === 'undefined') {
                console.error("Biblioteca Chart.js não encontrada.");
                return;
            }

            if (window.charts && window.charts.length > 0) {
                window.charts.forEach(chart => chart.destroy());
                window.charts = [];
            }

            const textColor = isDarkMode ? '#f9fafb' : '#374151';
            const gridColor = isDarkMode ? 'rgba(75, 85, 99, 0.5)' : 'rgba(209, 213, 219, 0.5)';

            const commonChartOptions = {
                scales: {
                    x: { ticks: { color: textColor }, grid: { color: gridColor, drawBorder: false } },
                    y: { ticks: { color: textColor }, grid: { color: gridColor, drawBorder: false } }
                },
                plugins: { legend: { labels: { color: textColor } } }
            };

            const currencyCallback = (context) =>
                `R$ ${context.raw.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
            const yAxisCallback = (value) =>
                'R$ ' + value.toLocaleString('pt-BR');

            const ctxEvolucao = document.getElementById('evolucaoMensal');
            if (ctxEvolucao) {
                const evolucaoChart = new Chart(ctxEvolucao.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: dadosGraficos.evolucaoMensal.meses,
                        datasets: [{
                            label: 'Receitas Mensais',
                            data: dadosGraficos.evolucaoMensal.valores,
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2, fill: true, tension: 0.4
                        }]
                    },
                    options: {
                        ...commonChartOptions,
                        responsive: true,
                        plugins: {
                            ...commonChartOptions.plugins,
                            tooltip: { callbacks: { label: currencyCallback } }
                        },
                        scales: {
                            ...commonChartOptions.scales,
                            y: {
                                ...commonChartOptions.scales.y,
                                beginAtZero: true,
                                ticks: { ...commonChartOptions.scales.y.ticks, callback: yAxisCallback }
                            }
                        }
                    }
                });
                window.charts.push(evolucaoChart);
            }

            const ctxComparacao = document.getElementById('comparacaoReceitasDespesas');
            if (ctxComparacao) {
                const comparacaoChart = new Chart(ctxComparacao.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['Receitas', 'Despesas'],
                        datasets: [{
                            label: 'Valores',
                            data: [dadosGraficos.comparacao.receitas, dadosGraficos.comparacao.despesas],
                            backgroundColor: ['rgba(16, 185, 129, 0.8)', 'rgba(239, 68, 68, 0.8)'],
                            borderColor: ['#10B981', '#EF4444'], borderWidth: 1
                        }]
                    },
                    options: {
                        ...commonChartOptions,
                        responsive: true,
                        plugins: {
                            ...commonChartOptions.plugins,
                            legend: { display: false },
                            tooltip: { callbacks: { label: currencyCallback } }
                        },
                        scales: {
                            ...commonChartOptions.scales,
                            y: {
                                ...commonChartOptions.scales.y,
                                beginAtZero: true,
                                ticks: { ...commonChartOptions.scales.y.ticks, callback: yAxisCallback }
                            }
                        }
                    }
                });
                window.charts.push(comparacaoChart);
            }

            const categoriasData = dadosGraficos.categorias;
            const ctxCategorias = document.getElementById('categoriasLucrativas');
            if (ctxCategorias && categoriasData && categoriasData.length > 0) {
                const categoriasChart = new Chart(ctxCategorias.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: categoriasData.map(c => c.nome),
                        datasets: [{
                            data: categoriasData.map(c => c.lucro),
                            backgroundColor: ['#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#EF4444',
                                '#06B6D4', '#84CC16', '#F97316', '#8B5CF6', '#EC4899'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            ...commonChartOptions.plugins,
                            legend: { ...commonChartOptions.plugins.legend, position: 'bottom' },
                            tooltip: { callbacks: { label: (context) => `${context.label}: R$ ${context.raw.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}` } }
                        }
                    }
                });
                window.charts.push(categoriasChart);
            }

            const ctxLucro = document.getElementById('lucroMensal');
            if (ctxLucro) {
                const lucroChart = new Chart(ctxLucro.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: dadosGraficos.lucrosMensais.meses,
                        datasets: [{
                            label: 'Lucro Mensal',
                            data: dadosGraficos.lucrosMensais.lucros,
                            backgroundColor: dadosGraficos.lucrosMensais.cores,
                            borderColor: dadosGraficos.lucrosMensais.cores.map(color => color.replace('0.8', '1')),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        ...commonChartOptions,
                        responsive: true,
                        plugins: {
                            ...commonChartOptions.plugins,
                            tooltip: { callbacks: { label: currencyCallback } }
                        },
                        scales: {
                            ...commonChartOptions.scales,
                            y: {
                                ...commonChartOptions.scales.y,
                                beginAtZero: false,
                                ticks: { ...commonChartOptions.scales.y.ticks, callback: yAxisCallback }
                            }
                        }
                    }
                });
                window.charts.push(lucroChart);
            }
            
            const ctxLucroTrimestral = document.getElementById('lucroTrimestral');
            if (ctxLucroTrimestral) {
                const lucroTrimestralChart = new Chart(ctxLucroTrimestral.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: dadosGraficos.lucroTrimestral.labels,
                        datasets: [{
                            label: 'Lucro Trimestral',
                            data: dadosGraficos.lucroTrimestral.valores,
                            backgroundColor: dadosGraficos.lucroTrimestral.cores,
                            borderColor: dadosGraficos.lucroTrimestral.cores.map(cor => cor.replace('0.8', '1')),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        ...commonChartOptions,
                        responsive: true,
                        plugins: {
                            ...commonChartOptions.plugins,
                            tooltip: { callbacks: { label: currencyCallback } }
                        },
                        scales: {
                            ...commonChartOptions.scales,
                            y: {
                                ...commonChartOptions.scales.y,
                                beginAtZero: false,
                                ticks: { ...commonChartOptions.scales.y.ticks, callback: yAxisCallback }
                            }
                        }
                    }
                });
                window.charts.push(lucroTrimestralChart);
            }
        };

        window.addEventListener('load', () => {
            const isInitialDark = document.documentElement.classList.contains('dark');
            window.updateChartStyles(isInitialDark);
        });

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            const isDarkMode = e.matches;
            window.updateChartStyles(isDarkMode);
        });

        document.getElementById('exportar_graficos').addEventListener('click', async () => {
    const canvasIds = ['evolucaoMensal','categoriasLucrativas','lucroMensal','comparacaoReceitasDespesas','lucroTrimestral'];
    const zip = new JSZip();
    const folder = zip.folder('graficos');

    for (let id of canvasIds) {
        const el = document.getElementById(id);
        if (!el) continue;

        
        if (el.tagName.toLowerCase() === 'canvas') {
            try {
                const dataUrl = el.toDataURL('image/png', 1.0);
                const base64 = dataUrl.split(',')[1];
                const byteArray = Uint8Array.from(atob(base64), c => c.charCodeAt(0));
                folder.file(`${id}.png`, byteArray);
            } catch (err) {
                console.error('Erro ao converter canvas', id, err);
            }
        } else {
            
            if (el.tagName.toLowerCase() === 'img' && el.src) {
                
                try {
                    const resp = await fetch(el.src);
                    const blob = await resp.blob();
                    const buf = await blob.arrayBuffer();
                    folder.file(`${id}.png`, buf);
                } catch (err) {
                    console.error('Erro ao buscar imagem', id, err);
                }
            } else {
                
                console.warn('Elemento não suportado para exportação direta:', id);
            }
        }
    }

    try {
        const blob = await zip.generateAsync({ type: 'blob' }, metadata => {
            
        });
        saveAs(blob, `graficos_${new Date().toISOString().slice(0,10)}.zip`);
    } catch (err) {
        console.error('Erro ao gerar ZIP', err);
    }
});
       
        
  

    </script>

    <script src="../assets/js/darkmode.js"></script>

</body>
</html>