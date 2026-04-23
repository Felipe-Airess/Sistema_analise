<?php
require_once("../../config/conexao.php");
session_start();

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: ../login/login.php");
    exit();
}

$empresa_nome = $_SESSION['empresa_nome'];
$empresa_id = $_SESSION['empresa_id'];

// Consultas SQL mantidas exatamente como as suas
$sql_receitas = "SELECT SUM(valor) AS total_receitas FROM receitas WHERE empresa_id = ?";
$stmt = $pdo->prepare($sql_receitas);
$stmt->execute([$empresa_id]);
$total_receitas = $stmt->fetchColumn() ?? 0;

$sql_ultimas_receitas = "
    SELECT r.descricao, r.valor, r.data, COALESCE(c.nome, 'Sem Categoria') as categoria_nome 
    FROM receitas r
    LEFT JOIN categorias c ON r.categoria_id = c.id
    WHERE r.empresa_id = ?
    ORDER BY r.data DESC, r.id DESC LIMIT 5";
$stmt = $pdo->prepare($sql_ultimas_receitas);
$stmt->execute([$empresa_id]);
$ultimas_receitas = $stmt->fetchAll();

$sql_ultimas_despesas = "
    SELECT d.descricao, d.valor, d.data, COALESCE(c.nome, 'Sem Categoria') as categoria_nome 
    FROM despesas d
    LEFT JOIN categorias c ON d.categoria_id = c.id
    WHERE d.empresa_id = ?
    ORDER BY d.data DESC, d.id DESC LIMIT 5";
$stmt = $pdo->prepare($sql_ultimas_despesas);
$stmt->execute([$empresa_id]);
$ultimas_despesas = $stmt->fetchAll();

$categorias = "SELECT * FROM categorias WHERE empresa_id = $empresa_id";
$stmt = $pdo->prepare($categorias);
$stmt->execute();
$categorias = $stmt->fetchAll();

$sql_evolucao_mensal = "SELECT MONTH(data) as mes, SUM(valor) as total FROM receitas 
    WHERE empresa_id = ? AND YEAR(data) = YEAR(CURDATE()) GROUP BY MONTH(data) ORDER BY mes";
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

$sql_despesas = "SELECT SUM(valor) AS total_despesas FROM despesas WHERE empresa_id = ?";
$stmt = $pdo->prepare($sql_despesas);
$stmt->execute([$empresa_id]);
$total_despesas = $stmt->fetchColumn() ?? 0;
$lucro_total = $total_receitas - $total_despesas;

$categorias_lucrativas_sql = "SELECT c.nome, 
    (SELECT IFNULL(SUM(r.valor), 0) FROM receitas r WHERE r.categoria_id = c.id AND r.empresa_id = ?) -
    (SELECT IFNULL(SUM(d.valor), 0) FROM despesas d WHERE d.categoria_id = c.id AND d.empresa_id = ?) AS lucro
    FROM categorias c WHERE c.empresa_id = ? ORDER BY lucro DESC LIMIT 5";
$stmt = $pdo->prepare($categorias_lucrativas_sql);
$stmt->execute([$empresa_id, $empresa_id, $empresa_id]);
$categorias_mais_lucrativas = $stmt->fetchAll();

$lucros_mes_sql = "SELECT MONTH(r.data) AS mes, SUM(r.valor) - COALESCE((
        SELECT SUM(d.valor) FROM despesas d WHERE d.empresa_id = r.empresa_id AND MONTH(d.data) = MONTH(r.data)
    ), 0) AS lucro FROM receitas r WHERE r.empresa_id = ? GROUP BY MONTH(r.data) ORDER BY mes LIMIT 12;";
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
    $cores_lucro[] = $lucro >= 0 ? 'rgba(16, 185, 129, 0.8)' : 'rgba(239, 68, 68, 0.8)';
}

$lucro_trimestral_sql = "SELECT CONCAT('T', QUARTER(r.data)) AS trimestre, YEAR(r.data) AS ano,
    SUM(r.valor) - COALESCE((SELECT SUM(d.valor) FROM despesas d WHERE d.empresa_id = r.empresa_id 
    AND QUARTER(d.data) = QUARTER(r.data) AND YEAR(d.data) = YEAR(r.data)), 0) AS lucro
    FROM receitas r WHERE r.empresa_id = ? GROUP BY YEAR(r.data), QUARTER(r.data) ORDER BY ano DESC, trimestre DESC LIMIT 4";
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
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.0/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-500 text-gray-800 dark:text-gray-100 h-screen overflow-hidden" x-data="{ mobileMenuOpen: false }">

    <main class="flex h-full w-full flex-col sm:flex-row">
        
        <header class="sm:hidden flex items-center justify-between bg-[#004b8d] dark:bg-gray-900 text-white px-5 py-4 shadow-md shrink-0 border-b border-white/10 transition-colors duration-500 z-20">
            <div class="flex items-center gap-3">
                <i class="fas fa-user-circle text-2xl"></i>
                <h2 class="font-semibold text-lg truncate w-40"><?= htmlspecialchars($empresa_nome); ?></h2>
            </div>
            <button @click="mobileMenuOpen = true" class="text-white hover:text-gray-300 focus:outline-none transition-transform active:scale-95">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </header>

        <aside class="hidden sm:flex w-64 bg-[#004b8d] dark:bg-gray-900 shadow-md h-full flex-col shrink-0 transition-colors duration-500 border-r border-white/10">
            <div class="py-6 px-6 flex items-center gap-3">
                <i class="fas fa-user-circle text-white text-3xl"></i>
                <h2 class="font-semibold text-lg text-white truncate"><?= htmlspecialchars($empresa_nome); ?></h2>
            </div>

            <nav class="flex flex-col flex-1 overflow-y-auto justify-between px-4 pb-6">
                <ul class="flex flex-col gap-2">
                    <li><a href="gerenciador.php" class="flex items-center gap-3 text-white p-3 rounded-lg bg-white/20 dark:bg-gray-800 transition"><i class="fas fa-home w-5"></i> Inicio</a></li>
                    <li><a href="../gerenciador/despesas/gerenciar_despesas.php" class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-white/10 p-3 rounded-lg transition"><i class="fas fa-arrow-down w-5"></i> Despesas</a></li>
                    <li><a href="../gerenciador/receitas/gerenciar_receitas.php" class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-white/10 p-3 rounded-lg transition"><i class="fas fa-arrow-up w-5"></i> Receitas</a></li>
                    <li><a href="../gerenciador/categorias/gerenciar_categorias.php" class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-white/10 p-3 rounded-lg transition"><i class="fas fa-tags w-5"></i> Categorias</a></li>
                    <li><a href="../gerenciador/metas/gerenciar_metas.php" class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-white/10 p-3 rounded-lg transition"><i class="fas fa-bullseye w-5"></i> Metas</a></li>
                    <li><button id="openSettingsModal" class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-white/10 w-full text-left p-3 rounded-lg transition"><i class="fas fa-cog w-5"></i> Configs</button></li>
                </ul>
                <div class="mt-6 pt-6 border-t border-white/20">
                    <a href="../login/logout.php" class="flex items-center justify-center gap-2 text-white bg-white/10 hover:bg-white/20 py-3 rounded-lg transition"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </div>
            </nav>
        </aside>

        <div x-show="mobileMenuOpen" x-cloak class="sm:hidden fixed inset-0 z-50 flex">
            <div @click="mobileMenuOpen = false" x-transition.opacity class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
            <aside x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative w-64 h-full bg-[#004b8d] dark:bg-gray-900 shadow-2xl flex flex-col border-r border-white/10">
                <div class="flex items-center justify-between px-6 py-5 border-b border-white/10">
                    <span class="font-bold text-xl text-white">Navegação</span>
                    <button @click="mobileMenuOpen = false" class="text-gray-300 hover:text-white text-2xl"><i class="fas fa-times"></i></button>
                </div>
                <nav class="flex flex-col gap-1 p-4 flex-1 overflow-y-auto">
                    <a href="gerenciador.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-white/20 text-white font-semibold transition"><i class="fas fa-home"></i> Inicio</a>
                    <a href="../gerenciador/despesas/gerenciar_despesas.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-200 hover:bg-white/10 transition"><i class="fas fa-arrow-down"></i> Despesas</a>
                    <a href="../gerenciador/receitas/gerenciar_receitas.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-200 hover:bg-white/10 transition"><i class="fas fa-arrow-up"></i> Receitas</a>
                    <a href="../gerenciador/categorias/gerenciar_categorias.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-200 hover:bg-white/10 transition"><i class="fas fa-tags"></i> Categorias</a>
                    <a href="../gerenciador/metas/gerenciar_metas.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-200 hover:bg-white/10 transition"><i class="fas fa-bullseye"></i> Metas</a>
                    <button id="openSettingsModalSm" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-200 hover:bg-white/10 text-left transition w-full"><i class="fas fa-cog"></i> Configurações</button>
                </nav>
            </aside>
        </div>

        <div class="flex-1 overflow-y-auto p-4 sm:p-6 w-full">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-7xl mx-auto w-full pb-10">

                <div class="col-span-1 lg:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md border-l-4 <?= $lucro_total >= 0 ? 'border-green-500' : 'border-red-500' ?> transition-colors duration-500">
                        <h4 class="text-sm font-semibold mb-1 flex items-center gap-2 text-gray-500 dark:text-gray-400"><i class="fas fa-chart-line <?= $lucro_total >= 0 ? 'text-green-500' : 'text-red-500' ?>"></i> Lucro Total</h4>
                        <p class="text-3xl font-bold <?= $lucro_total >= 0 ? 'text-green-600' : 'text-red-600' ?>">R$ <?= number_format($lucro_total, 2, ',', '.'); ?></p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md border-l-4 border-blue-500 transition-colors duration-500">
                        <h4 class="text-sm font-semibold mb-1 flex items-center gap-2 text-gray-500 dark:text-gray-400"><i class="fas fa-arrow-up text-blue-500"></i> Receitas</h4>
                        <p class="text-3xl font-bold text-blue-600">R$ <?= number_format($total_receitas, 2, ',', '.'); ?></p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md border-l-4 border-red-500 transition-colors duration-500">
                        <h4 class="text-sm font-semibold mb-1 flex items-center gap-2 text-gray-500 dark:text-gray-400"><i class="fas fa-arrow-down text-red-500"></i> Despesas</h4>
                        <p class="text-3xl font-bold text-red-600">R$ <?= number_format($total_despesas, 2, ',', '.'); ?></p>
                    </div>
                </div>

                <div class="col-span-1 lg:col-span-2 bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md transition-colors duration-500">
                    <h4 class="font-semibold mb-4 flex items-center gap-2 text-gray-700 dark:text-gray-200"><i class="fas fa-chart-line text-blue-500"></i> Evolução Mensal</h4>
                    <div class="w-full relative" style="height: 300px; min-height: 300px;">
                        <canvas id="evolucaoMensal"></canvas>
                    </div>
                </div>

                <div class="col-span-1 lg:col-span-1 bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md transition-colors duration-500">
                    <h4 class="font-semibold mb-4 flex items-center gap-2 text-gray-700 dark:text-gray-200"><i class="fas fa-pie-chart text-purple-500"></i> Lucro por Categoria</h4>
                    <div class="w-full relative" style="height: 300px; min-height: 300px;">
                        <canvas id="categoriasLucrativas"></canvas>
                    </div>
                </div>

                <div class="col-span-1 lg:col-span-2 bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md transition-colors duration-500">
                    <h4 class="font-semibold mb-4 flex items-center gap-2 text-gray-700 dark:text-gray-200"><i class="fas fa-chart-bar text-green-500"></i> Lucro Mensal</h4>
                    <div class="w-full relative" style="height: 300px; min-height: 300px;">
                        <canvas id="lucroMensal"></canvas>
                    </div>
                </div>

                <div class="col-span-1 lg:col-span-1 bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md transition-colors duration-500">
                    <h4 class="font-semibold mb-4 flex items-center gap-2 text-gray-700 dark:text-gray-200"><i class="fas fa-balance-scale text-orange-500"></i> Receitas vs Despesas</h4>
                    <div class="w-full relative" style="height: 300px; min-height: 300px;">
                        <canvas id="comparacaoReceitasDespesas"></canvas>
                    </div>
                </div>

                <div class="col-span-1 lg:col-span-3 mt-2">
                    <h3 class="font-semibold mb-3 flex items-center gap-2 text-gray-700 dark:text-gray-200"><i class="fas fa-plus-circle text-green-500"></i> Últimas Receitas</h3>
                    <div class="overflow-x-auto rounded-lg shadow-md bg-white dark:bg-gray-800">
                        <table class="min-w-full text-left">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="py-3 px-4 font-semibold text-sm">Descrição</th>
                                    <th class="py-3 px-4 font-semibold text-sm">Categoria</th>
                                    <th class="py-3 px-4 font-semibold text-sm">Valor</th>
                                    <th class="py-3 px-4 font-semibold text-sm">Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimas_receitas as $receita): ?>
                                    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600/50">
                                        <td class="py-3 px-4 text-sm"><?= htmlspecialchars($receita['descricao']); ?></td>
                                        <td class="py-3 px-4 text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($receita['categoria_nome']); ?></td>
                                        <td class="py-3 px-4 text-sm text-green-600 font-semibold">R$ <?= number_format($receita['valor'], 2, ',', '.'); ?></td>
                                        <td class="py-3 px-4 text-sm text-gray-500 dark:text-gray-400"><?= date('d/m/Y', strtotime($receita['data'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-span-1 lg:col-span-3 mt-2">
                    <h3 class="font-semibold mb-3 flex items-center gap-2 text-gray-700 dark:text-gray-200"><i class="fas fa-minus-circle text-red-500"></i> Últimas Despesas</h3>
                    <div class="overflow-x-auto rounded-lg shadow-md bg-white dark:bg-gray-800">
                        <table class="min-w-full text-left">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="py-3 px-4 font-semibold text-sm">Descrição</th>
                                    <th class="py-3 px-4 font-semibold text-sm">Categoria</th>
                                    <th class="py-3 px-4 font-semibold text-sm">Valor</th>
                                    <th class="py-3 px-4 font-semibold text-sm">Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimas_despesas as $despesa): ?>
                                    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600/50">
                                        <td class="py-3 px-4 text-sm"><?= htmlspecialchars($despesa['descricao']); ?></td>
                                        <td class="py-3 px-4 text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($despesa['categoria_nome']); ?></td>
                                        <td class="py-3 px-4 text-sm text-red-600 font-semibold">R$ <?= number_format($despesa['valor'], 2, ',', '.'); ?></td>
                                        <td class="py-3 px-4 text-sm text-gray-500 dark:text-gray-400"><?= date('d/m/Y', strtotime($despesa['data'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-span-1 lg:col-span-2 bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md transition-colors duration-500">
                    <h4 class="font-semibold mb-4 flex items-center gap-2 text-gray-700 dark:text-gray-200"><i class="fas fa-chart-area text-teal-500"></i> Lucro Trimestral</h4>
                    <div class="w-full relative" style="height: 300px; min-height: 300px;">
                        <canvas id="lucroTrimestral"></canvas>
                    </div>
                </div>
                
                <div class="col-span-1 lg:col-span-1 bg-white dark:bg-gray-800 p-5 rounded-lg shadow-md flex flex-col justify-center gap-4 transition-colors duration-500">
                    <h2 class="text-lg font-semibold flex items-center gap-2 text-gray-700 dark:text-gray-200 mb-2"><i class="fas fa-rocket text-blue-600"></i> Ações Rápidas</h2>
                    <a href="exportar_pdf.php" class="flex items-center justify-center gap-2 bg-[#004b8d] hover:bg-[#003366] text-white py-3 rounded-lg transition-transform hover:-translate-y-1 shadow-md">
                        <i class="fas fa-file-pdf"></i> Gerar Relatório 
                    </a>
                    <button id="exportar_graficos" class="flex items-center justify-center gap-2 bg-[#004b8d] hover:bg-[#003366] text-white py-3 rounded-lg transition-transform hover:-translate-y-1 shadow-md">
                        <i class="fas fa-chart-bar"></i> Exportar Gráficos
                    </button>
                </div>                 
            </div>
        </div>
    </main>

    <div id="settingsModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm hidden flex items-center justify-center z-[100]">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-11/12 max-w-md p-6 transform transition-all">
            <div class="flex justify-between items-center mb-5 border-b border-gray-200 dark:border-gray-700 pb-3">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2"><i class="fas fa-cog text-blue-500"></i> Configurações</h3>
                <button id="closeModalBtn" class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white text-xl"><i class="fas fa-times"></i></button>
            </div>
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <span class="font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2"><i class="fas fa-palette text-blue-500"></i> Tema</span>
                <button id="themeToggle" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-[#004b8d] text-gray-700 dark:text-white font-medium hover:opacity-80 transition flex items-center gap-2">
                    <i id="moonIcon" class="fas fa-moon hidden"></i><i id="sunIcon" class="fas fa-sun"></i><span id="themeText">Claro</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        // 1. GARANTE QUE O MODAL ABRA SEMPRE, INDEPENDENTE DO DARKMODE.JS
        const settingsModal = document.getElementById('settingsModal');
        document.getElementById('openSettingsModal')?.addEventListener('click', () => settingsModal.classList.remove('hidden'));
        document.getElementById('openSettingsModalSm')?.addEventListener('click', () => settingsModal.classList.remove('hidden'));
        document.getElementById('closeModalBtn')?.addEventListener('click', () => settingsModal.classList.add('hidden'));
        
        // Clica fora para fechar o modal
        settingsModal.addEventListener('click', (e) => {
            if (e.target === settingsModal) settingsModal.classList.add('hidden');
        });

        // 2. DADOS DOS GRÁFICOS DO PHP
        const dadosGraficos = {
            evolucaoMensal: { meses: <?= json_encode($meses_dados); ?>, valores: <?= json_encode($valores_dados); ?> },
            comparacao: { receitas: <?= $total_receitas; ?>, despesas: <?= $total_despesas; ?> },
            categorias: <?= json_encode($categorias_mais_lucrativas ?: []); ?>,
            lucrosMensais: { meses: <?= json_encode($meses_lucro); ?>, lucros: <?= json_encode($valores_lucro); ?>, cores: <?= json_encode($cores_lucro); ?> },
            lucroTrimestral: { labels: <?= json_encode($trimestres); ?>, valores: <?= json_encode($valores_trimestrais); ?>, cores: <?= json_encode($cores_trimestrais); ?> }
        };

        window.charts = [];

        // 3. FUNÇÃO DE RENDERIZAR OS GRÁFICOS
        window.updateChartStyles = (isDarkMode) => {
            if (typeof Chart === 'undefined') return;

            // Destrói os gráficos antigos antes de repintar
            window.charts.forEach(chart => chart.destroy());
            window.charts = [];

            const textColor = isDarkMode ? '#f9fafb' : '#374151';
            const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

            const commonOptions = {
                maintainAspectRatio: false, // ISSO EVITA O GRÁFICO SUMIR NO MOBILE
                responsive: true,
                plugins: { legend: { labels: { color: textColor } } },
                scales: {
                    x: { ticks: { color: textColor }, grid: { color: gridColor, drawBorder: false } },
                    y: { ticks: { color: textColor }, grid: { color: gridColor, drawBorder: false }, beginAtZero: true }
                }
            };

            const currencyCallback = (ctx) => `R$ ${ctx.raw.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;

            // Gráfico 1: Evolução
            const ctxEvolucao = document.getElementById('evolucaoMensal');
            if (ctxEvolucao) {
                window.charts.push(new Chart(ctxEvolucao.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: dadosGraficos.evolucaoMensal.meses,
                        datasets: [{
                            label: 'Receitas Mensais', data: dadosGraficos.evolucaoMensal.valores,
                            borderColor: '#10B981', backgroundColor: 'rgba(16, 185, 129, 0.1)', borderWidth: 2, fill: true, tension: 0.4
                        }]
                    },
                    options: { ...commonOptions, plugins: { ...commonOptions.plugins, tooltip: { callbacks: { label: currencyCallback } } } }
                }));
            }

            // Gráfico 2: Receitas x Despesas
            const ctxComp = document.getElementById('comparacaoReceitasDespesas');
            if (ctxComp) {
                window.charts.push(new Chart(ctxComp.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['Receitas', 'Despesas'],
                        datasets: [{
                            data: [dadosGraficos.comparacao.receitas, dadosGraficos.comparacao.despesas],
                            backgroundColor: ['rgba(16, 185, 129, 0.8)', 'rgba(239, 68, 68, 0.8)'],
                            borderColor: ['#10B981', '#EF4444'], borderWidth: 1
                        }]
                    },
                    options: { ...commonOptions, plugins: { legend: { display: false }, tooltip: { callbacks: { label: currencyCallback } } } }
                }));
            }

            // Gráfico 3: Categorias (Pie/Doughnut)
            const ctxCat = document.getElementById('categoriasLucrativas');
            if (ctxCat && dadosGraficos.categorias.length > 0) {
                window.charts.push(new Chart(ctxCat.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: dadosGraficos.categorias.map(c => c.nome),
                        datasets: [{
                            data: dadosGraficos.categorias.map(c => c.lucro),
                            backgroundColor: ['#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#EF4444', '#06B6D4'],
                            borderWidth: 1
                        }]
                    },
                    options: { 
                        maintainAspectRatio: false, responsive: true, 
                        plugins: { legend: { position: 'bottom', labels: { color: textColor } } } 
                    }
                }));
            }

            // Gráfico 4: Lucro Mensal
            const ctxLucro = document.getElementById('lucroMensal');
            if (ctxLucro) {
                window.charts.push(new Chart(ctxLucro.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: dadosGraficos.lucrosMensais.meses,
                        datasets: [{
                            label: 'Lucro Mensal', data: dadosGraficos.lucrosMensais.lucros,
                            backgroundColor: dadosGraficos.lucrosMensais.cores, borderWidth: 1
                        }]
                    },
                    options: { ...commonOptions, plugins: { ...commonOptions.plugins, tooltip: { callbacks: { label: currencyCallback } } } }
                }));
            }

            // Gráfico 5: Lucro Trimestral
            const ctxTrim = document.getElementById('lucroTrimestral');
            if (ctxTrim) {
                window.charts.push(new Chart(ctxTrim.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: dadosGraficos.lucroTrimestral.labels,
                        datasets: [{
                            label: 'Lucro Trimestral', data: dadosGraficos.lucroTrimestral.valores,
                            backgroundColor: dadosGraficos.lucroTrimestral.cores, borderWidth: 1
                        }]
                    },
                    options: { ...commonOptions, plugins: { ...commonOptions.plugins, tooltip: { callbacks: { label: currencyCallback } } } }
                }));
            }
        };

        // 4. OBSERVADOR DO DARK MODE: É ISSO QUE FAZ OS GRÁFICOS MUDAREM DE COR PERFEITAMENTE
        // Ele fica "vigiando" a tag <html>. Quando o seu darkmode.js adiciona a classe "dark",
        // ele recarrega os gráficos na mesma fração de segundo!
        const htmlElement = document.documentElement;
        const observer = new MutationObserver(() => {
            window.updateChartStyles(htmlElement.classList.contains('dark'));
        });
        observer.observe(htmlElement, { attributes: true, attributeFilter: ['class'] });

        // Pintar pela primeira vez no carregamento
        window.updateChartStyles(htmlElement.classList.contains('dark'));

        // Mensagens do PHP via SweetAlert
        <?php if(isset($_SESSION['mensagem']) && isset($_SESSION['mensagem_tipo'])): ?>
            Swal.fire({
                icon: '<?= $_SESSION['mensagem_tipo'] ?>',
                title: '<?= $_SESSION['mensagem_tipo'] === 'success' ? 'Sucesso!' : 'Erro!' ?>',
                text: '<?= $_SESSION['mensagem'] ?>',
                timer: 3000, showConfirmButton: false, toast: true, position: 'top-end'
            });
            <?php unset($_SESSION['mensagem'], $_SESSION['mensagem_tipo']); ?>
        <?php endif; ?>

        // Exportação de gráficos
        document.getElementById('exportar_graficos')?.addEventListener('click', async () => {
            const zip = new JSZip();
            const folder = zip.folder('graficos');
            for (let chart of window.charts) {
                try {
                    const dataUrl = chart.toBase64Image('image/png', 1.0);
                    const base64 = dataUrl.split(',')[1];
                    const byteArray = Uint8Array.from(atob(base64), c => c.charCodeAt(0));
                    folder.file(`${chart.canvas.id}.png`, byteArray);
                } catch (e) { console.error('Erro ZIP', e); }
            }
            try {
                const blob = await zip.generateAsync({ type: 'blob' });
                saveAs(blob, `graficos_${new Date().toISOString().slice(0,10)}.zip`);
            } catch (err) {}
        });
    </script>
    <script src="../../assets/js/darkmode.js"></script>
</body>
</html>