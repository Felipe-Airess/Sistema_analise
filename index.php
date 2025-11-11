<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://unpkg.com/scrollreveal"></script>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiByeD0iNCIgZmlsbD0iIzAwNGI4ZCIvPgo8cGF0aCBkPSJNMTYgMTBWNiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHBhdGggZD0iTTIyIDE2SDEwIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8cGF0aCBkPSJNMTYgMjZWMjIiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+CjxwYXRoIGQ9Ik0yNiAxNkgyMiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPHBhdGggZD0iTTE2IDZMMjIgMTZIMTBMMTYgNloiIGZpbGw9IndoaXRlIiBmaWxsLW9wYWNpdHk9IjAuOCIvPgo8cGF0aCBkPSJNMTYgMjZMMjIgMTZIMTBMMTYgMjZaIiBmaWxsPSJ3aGl0ZSIgZmlsbC1vcGFjaXR5PSIwLjYiLz4KPHBhdGggZD0iTTYgMTZIMiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPC9zdmc+Cg==">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <title>Sistema de Analise</title>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #004b8d 0%, #003366 100%);
        }
        .hero-gradient {
            background: linear-gradient(135deg, #004b8d 0%, #000d46ff 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body class="bg-white dark:bg-gray-900 dark:text-gray-200 transition-colors duration-300">

    <header class="w-full gradient-bg shadow-lg">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-[#004b8d] text-xl"></i>
                </div>
                <h1 class="font-['Poppins'] text-2xl text-white font-bold tracking-tight">Analise de lucros</h1>
            </div>
            
            <div class="flex items-center gap-6">
                <button id="themeToggle" class="text-white hover:text-blue-200 transition-all duration-300 p-2 rounded-full hover:bg-white/10">
                    <i id="sunIcon" class="fas fa-sun text-lg"></i>
                    <i id="moonIcon" class="fas fa-moon text-lg hidden"></i>
                </button>
                <a href="app/login/login.php" 
                   class="bg-white text-[#004b8d] font-semibold py-3 px-6 rounded-lg hover:bg-gray-100 transition-all duration-300 shadow-md hover:shadow-lg flex items-center gap-2">
                    <i class="fas fa-sign-in-alt"></i>
                    Acessar Sistema
                </a>
            </div>
        </nav>
    </header>

    <main class="mx-auto my-16 p-6 max-w-6xl">
        <section class="text-center mb-20 animate__animated animate__fadeIn">
            <div class="hero-gradient text-white rounded-2xl p-12 mb-12 shadow-2xl">
                <h1 class="font-['Poppins'] text-5xl font-bold mb-6 leading-tight">
                    Começe agora com seu Controle Financeiro
                    
                </h1>
                <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto leading-relaxed">
                    Gerencie suas finanças com precisão e tome decisões estratégicas baseadas em dados reais
                </p>
                <a href="app/login/login.php" 
                   class="bg-white text-[#004b8d] font-bold py-4 px-8 rounded-full transition-all duration-300 shadow-lg hover:shadow-xl text-lg inline-flex items-center gap-3 hover:bg-gray-100">
                    <i class="fas fa-rocket"></i>
                    Começar Agora
                </a>
            </div>
        </section>

        <div class="flex flex-col gap-20">

            <section class="slide grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="relative">
                    <div class="absolute -inset-4 bg-blue-100 dark:bg-blue-900 rounded-2xl opacity-20 blur-lg"></div>
                    <img src="app/assets/img/img3.png" alt="Dashboard Financeiro" class="relative border-2 border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl w-full card-hover">
                </div>
                <div class="flex flex-col items-start space-y-6">
                    <div class="flex items-center gap-3 text-[#004b8d] dark:text-blue-400">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-wallet text-xl"></i>
                        </div>
                        <h2 class="font-['Poppins'] text-3xl font-bold">
                            Gestão Completa de Fluxo de Caixa
                        </h2>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-lg leading-relaxed">
                        Tenha controle absoluto sobre entradas, saídas e saldos. Nossa plataforma oferece uma visão clara e organizada de todas as suas movimentações financeiras.
                    </p>
                    <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                        <li class="flex items-center gap-3"><i class="fas fa-check text-green-500"></i> Cadastro intuitivo de receitas e despesas</li>
                        <li class="flex items-center gap-3"><i class="fas fa-check text-green-500"></i> Categorização inteligente</li>
                        <li class="flex items-center gap-3"><i class="fas fa-check text-green-500"></i> Relatórios automáticos</li>
                    </ul>
                </div>
            </section>

            <section class="slide grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="flex flex-col items-start space-y-6 ">
                    <div class="flex items-center gap-3 text-[#004b8d] dark:text-blue-400">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-bar text-xl"></i>
                        </div>
                        <h2 class="font-['Poppins'] text-3xl font-bold">
                            Análises Visuais em Tempo Real
                        </h2>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-lg leading-relaxed">
                        Transforme dados complexos em insights visuais poderosos. Gráficos interativos que mostram a evolução do seu negócio de forma clara e objetiva.
                    </p>
                    <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                        <li class="flex items-center gap-3"><i class="fas fa-check text-green-500"></i> Dashboards personalizáveis</li>
                        <li class="flex items-center gap-3"><i class="fas fa-check text-green-500"></i> Métricas em tempo real</li>
                        <li class="flex items-center gap-3"><i class="fas fa-check text-green-500"></i> Comparativos mensais e anuais</li>
                    </ul>
                </div>
                <div class="relative">
                    <div class="absolute -inset-4 bg-green-100 dark:bg-green-900 rounded-2xl opacity-20 blur-lg"></div>
                    <img src="app/assets/img/img1.png" alt="Gráficos Financeiros" class="relative border-2 border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl w-full card-hover">
                </div>
            </section>

            <section class="slide grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="relative">
                    <div class="absolute -inset-4 bg-purple-100 dark:bg-purple-900 rounded-2xl opacity-20 blur-lg"></div>
                    <img src="app/assets/img/img2.png" alt="Metas Financeiras" class="relative border-2 border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl w-full card-hover">
                </div>
                <div class="flex flex-col items-start space-y-6">
                    <div class="flex items-center gap-3 text-[#004b8d] dark:text-blue-400">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-bullseye text-xl"></i>
                        </div>
                        <h2 class="font-['Poppins'] text-3xl font-bold">
                            Planejamento Estratégico com Metas
                        </h2>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-lg leading-relaxed">
                        Defina objetivos claros e acompanhe seu progresso. Nossa ferramenta de metas ajuda você a transformar sonhos em resultados mensuráveis.
                    </p>
                    <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                        <li class="flex items-center gap-3"><i class="fas fa-check text-green-500"></i> Metas personalizadas</li>
                        <li class="flex items-center gap-3"><i class="fas fa-check text-green-500"></i> Acompanhamento de progresso</li>
                        <li class="flex items-center gap-3"><i class="fas fa-check text-green-500"></i> Alertas e notificações</li>
                    </ul>
                </div>
            </section>

            <section class="slide grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="flex flex-col items-start space-y-6">
                    <div class="flex items-center gap-3 text-[#004b8d] dark:text-blue-400">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-export text-xl"></i>
                        </div>
                        <h2 class="font-['Poppins'] text-3xl font-bold">
                            Relatórios Profissionais
                        </h2>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-lg leading-relaxed">
                        Gere documentos completos para apresentações, reuniões e análises detalhadas. Exporte seus dados de forma profissional e organizada.
                    </p>
                    <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                        <li class="flex items-center gap-3"><i class="fas fa-check text-green-500"></i> Relatórios em múltiplos formatos</li>
                        <li class="flex items-center gap-3"><i class="fas fa-check text-green-500"></i> Dados exportáveis para Excel</li>
                        <li class="flex items-center gap-3"><i class="fas fa-check text-green-500"></i> Análises comparativas</li>
                    </ul>
                </div>
                <div class="relative ">
                    <div class="absolute -inset-4 bg-orange-100 dark:bg-orange-900 rounded-2xl opacity-20 blur-lg"></div>
                    <img src="app/assets/img/img6.png" alt="Relatórios Financeiros" class="relative border-2 border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl w-full card-hover">
                </div>
            </section>

        </div>

       
    </main>

    <footer class="gradient-bg text-white">
        <div class="container mx-auto px-6 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-3 mb-4 md:mb-0">
                    <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-[#004b8d] text-sm"></i>
                    </div>
                    <span class="font-['Poppins'] text-lg font-semibold">Analise de Lucros</span>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-blue-200 font-medium">
                        &copy; 2025 Analise de Lucros - Soluções Inteligentes em Gestão Financeira
                    </p>
                    
                </div>
            </div>
        </div>
    </footer>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');
        const userTheme = localStorage.getItem('theme');
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches;

        ScrollReveal().reveal('.slide', {
            duration: 1200,
            distance: '60px',
            origin: 'bottom',
            opacity: 0,
            scale: 0.9,
            easing: 'cubic-bezier(0.5, 0, 0, 1)',
            interval: 200
        });

        ScrollReveal().reveal('.hero-gradient', {
            duration: 1500,
            distance: '40px',
            origin: 'top',
            opacity: 0,
            scale: 0.95,
            easing: 'cubic-bezier(0.5, 0, 0, 1)'
        });

        const setDarkMode = (isDark) => {
            if (isDark) {
                document.documentElement.classList.add('dark');
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            } else {
                document.documentElement.classList.remove('dark');
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            }
        };

        if (userTheme === 'dark' || (!userTheme && systemTheme)) {
            setDarkMode(true);
        } else {
            setDarkMode(false);
        }

        themeToggle.addEventListener('click', () => {
            const isDark = document.documentElement.classList.contains('dark');
            setDarkMode(!isDark);
            localStorage.setItem('theme', !isDark ? 'dark' : 'light');
        });
    </script>

</body>
</html>