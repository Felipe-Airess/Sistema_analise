<?php
session_start();
require_once("../../../config/conexao.php");
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: ../../login/login.php");
    exit();
}
$empresa_nome = $_SESSION['empresa_nome'];
$empresa_id = $_SESSION['empresa_id'];
$mensagem = null;

$sql = "SELECT 
            r.*, 
            COALESCE(c.nome, 'Sem Categoria') AS categoria_nome 
        FROM receitas r
        LEFT JOIN categorias c ON r.categoria_id = c.id
        WHERE r.empresa_id = ? 
        ORDER BY r.data DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$empresa_id]);
$receitas = $stmt->fetchAll();

$sql_categorias = "SELECT * FROM categorias WHERE empresa_id = ?";
$stmt = $pdo->prepare($sql_categorias);
$stmt->execute([$empresa_id]);
$categorias = $stmt->fetchAll();

$sql_total = "SELECT SUM(valor) FROM receitas WHERE empresa_id = ?";
$stmt = $pdo->prepare($sql_total);
$stmt->execute([$empresa_id]);
$total_receitas = $stmt->fetchColumn() ?? 0;



?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Receitas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.0/list.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
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

        .sort:after {
            content: ' \2195';
        }

        .sort.asc:after {
            content: ' \2191';
        }

        .sort.desc:after {
            content: ' \2193';
        }

        @media (max-width: 640px) {
            .sm-hidden {
                display: none;
            }
        }

        .delay-1 {
            animation-delay: 0.1s;
        }

        .delay-2 {
            animation-delay: 0.2s;
        }

        .delay-3 {
            animation-delay: 0.3s;
        }

        .delay-4 {
            animation-delay: 0.4s;
        }

        .delay-5 {
            animation-delay: 0.5s;
        }

        .delay-6 {
            animation-delay: 0.6s;
        }

        .delay-7 {
            animation-delay: 0.7s;
        }
    </style>
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>


<body class="flex min-h-screen flex-col bg-gray-100 dark:bg-gray-900 transition-colors duration-500">
    <main class="flex flex-row gap-6 max-h-screen max-sm:flex-col">
        <aside class="w-48 bg-[#004b8d] dark:bg-gray-900 shadow-md min-h-screen flex flex-col max-sm:hidden transition-colors duration-500">
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
                            <a href="gerenciar_receitas.php"
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
                            <a href="../metas/gerenciar_metas.php"
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

            
        </aside>

        

        <div class="flex flex-1 flex-col p-6 overflow-y-auto max-h-screen">
            <div class="flex flex-row justify-between items-center flex-wrap">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6 max-sm:text-xl max-sm:mb-3 transition-colors duration-500 flex items-center gap-2">
                    <i class="fas fa-arrow-up text-green-500"></i>
                    Gerenciamento de Receitas
                </h1>

                <div class="flex gap-3 max-sm:w-full max-sm:justify-between max-sm:mb-4">
                    <a href="exportar_receitas.php"
                        class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors font-['Poppins'] text-sm animate__animated animate__pulse animate__delay-1s">
                        <i class="fas fa-file-export"></i>
                        Exportar CSV
                    </a>

                    <a href="adicionar_receitas.php"
                        class="flex items-center gap-2 bg-[#004b8d] text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors font-['Poppins'] text-sm animate__animated animate__pulse animate__delay-1s">
                        <i class="fas fa-plus-circle"></i>
                        Adicionar Receita
                    </a>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6 border-l-4 border-green-500 animate__animated animate__fadeInDown transition-colors duration-500">
                <h4 class="font-['Poppins'] text-lg text-gray-600 dark:text-gray-300 font-semibold mb-2 transition-colors duration-500 flex items-center gap-2">
                    <i class="fas fa-chart-line text-green-500"></i>
                    Total de Receitas Cadastradas
                </h4>
                <p class="font-['Poppins'] text-3xl text-green-600 font-bold flex items-center gap-2">
                    <i class="fas fa-dollar-sign text-2xl"></i>
                    R$ <?= number_format($total_receitas, 2, ',', '.'); ?>
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 animate__animated animate__fadeInUp transition-colors duration-500">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 flex-wrap">

                    <div class="w-full md:w-64 relative">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        <input id="search" type="text" placeholder="Buscar por descrição, categoria..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-500">
                    </div>

                    <div class="flex gap-2 max-sm:w-full max-sm:justify-between">
                        <button class="sort px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 max-sm:flex-1 transition-colors duration-500 flex items-center gap-2"
                            data-sort="data">
                            <i class="fas fa-calendar"></i>
                            Data
                        </button>
                        <button class="sort px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 max-sm:flex-1 transition-colors duration-500 flex items-center gap-2"
                            data-sort="valor">
                            <i class="fas fa-dollar-sign"></i>
                            Valor
                        </button>
                    </div>
                </div>

                <div id="receitas-list" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-200 uppercase text-xs leading-normal transition-colors duration-500">
                                <th class="py-3 px-6 text-left sort whitespace-nowrap" data-sort="data">
                                    <i class="fas fa-calendar mr-2"></i>Data
                                </th>
                                <th class="py-3 px-6 text-left sort whitespace-nowrap" data-sort="categoria">
                                    <i class="fas fa-tag mr-2"></i>Categoria
                                </th>
                                <th class="py-3 px-6 text-left sort whitespace-nowrap" data-sort="descricao">
                                    <i class="fas fa-align-left mr-2"></i>Descrição
                                </th>
                                <th class="py-3 px-6 text-right sort whitespace-nowrap" data-sort="valor">
                                    <i class="fas fa-dollar-sign mr-2"></i>Valor (R$)
                                </th>
                                <th class="py-3 px-6 text-center whitespace-nowrap">
                                    <i class="fas fa-tools mr-2"></i>Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody class="list text-gray-600 dark:text-gray-300 text-sm font-light">
                            <?php foreach ($receitas as $receita): ?>
                                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-500">
                                    <td class="py-3 px-6 text-left data whitespace-nowrap"
                                        data-data="<?= date('Y-m-d', strtotime($receita['data'])) ?>">
                                        <?= htmlspecialchars(date('d/m/Y', strtotime($receita['data']))) ?>
                                    </td>
                                    <td class="py-3 px-6 text-left categoria whitespace-nowrap">
                                        <?= htmlspecialchars($receita['categoria_nome']) ?>
                                    </td>
                                    <td class="py-3 px-6 text-left descricao whitespace-nowrap">
                                        <?= htmlspecialchars($receita['descricao']) ?>
                                    </td>
                                    <td class="py-3 px-6 text-right valor whitespace-nowrap"
                                        data-valor="<?= $receita['valor'] ?>">
                                        R$ <?= number_format($receita['valor'], 2, ',', '.') ?>
                                    </td>
                                    <td class="py-3 px-6 text-center whitespace-nowrap">
                                        <div class="flex item-center justify-center gap-2">
                                            <a href="editar_receita.php?id=<?= $receita['id'] ?>"
                                                class="w-8 h-8 flex items-center justify-center transform hover:text-blue-500 dark:hover:text-blue-400 hover:scale-110 icon-animacao text-gray-600 dark:text-gray-300 transition-colors duration-500 rounded-full hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                                title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="#"
                                                class="w-8 h-8 flex items-center justify-center transform hover:text-red-500 dark:hover:text-red-400 hover:scale-110 icon-animacao text-gray-600 dark:text-gray-300 transition-colors duration-500 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20"
                                                title="Excluir"
                                                onclick="confirmarExclusao(<?= $receita['id'] ?>); return false;">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="flex justify-between items-center mt-4 flex-wrap text-gray-600 dark:text-gray-300 transition-colors duration-500">
                        <div class="text-sm max-sm:mb-2 flex items-center gap-1">
                            <i class="fas fa-list"></i>
                            Mostrando <span class="page-start font-semibold"></span> a <span class="page-end font-semibold"></span> de <span class="list-total font-semibold"></span> receitas
                        </div>
                        <ul class="pagination flex gap-1"></ul>
                    </div>
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
                        <a href="../gerenciador.php" @click="open = false"
                           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-semibold transition
                                  <?= basename($_SERVER['PHP_SELF']) == 'gerenciador.php' ? 'text-white bg-white/20' : 'text-gray-200 hover:text-white hover:bg-white/10' ?>">
                            <i class="fas fa-home text-base shrink-0"></i>
                            Inicio
                        </a>

                        <a href="../despesas/gerenciar_despesas.php" @click="open = false"
                           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-semibold transition
                                  <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_despesas.php' ? 'text-white bg-white/20' : 'text-gray-200 hover:text-white hover:bg-white/10' ?>">
                            <i class="fas fa-arrow-down text-base shrink-0"></i>
                            Despesas
                        </a>

                        <a href="../receitas/gerenciar_receitas.php" @click="open = false"
                           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-semibold transition
                                  <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_receitas.php' ? 'text-white bg-white/20' : 'text-gray-200 hover:text-white hover:bg-white/10' ?>">
                            <i class="fas fa-arrow-up text-base shrink-0"></i>
                            Receitas
                        </a>

                        <a href="../categorias/gerenciar_categorias.php" @click="open = false"
                           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-semibold transition
                                  <?= basename($_SERVER['PHP_SELF']) == 'gerenciar_categorias.php' ? 'text-white bg-white/20' : 'text-gray-200 hover:text-white hover:bg-white/10' ?>">
                            <i class="fas fa-tags text-base shrink-0"></i>
                            Categorias
                        </a>

                        <a href="../metas/gerenciar_metas.php" @click="open = false"
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
    <script src="../../assets/js/darkmode.js"></script>
    <script src="../../assets/js/receitas_listJS.js"></script>
    <script>
        window.revelar = ScrollReveal();
        revelar.reveal('main', {
            duration: 1000,
            origin: 'left',
            distance: '50px'
        });
        function confirmarExclusao(receitaId) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Você não poderá reverter esta ação! Esta receita será excluída permanentemente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'excluir_receita.php?id=' + receitaId;
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