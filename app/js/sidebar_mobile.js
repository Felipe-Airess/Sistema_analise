 const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const menuOverlay = document.getElementById('menuOverlay');
    
    function toggleMenu() {
        sidebar.classList.toggle('-translate-x-full');
        menuOverlay.classList.toggle('hidden');
        document.body.classList.toggle('overflow-hidden');
    }
    
    menuToggle.addEventListener('click', toggleMenu);
    menuOverlay.addEventListener('click', toggleMenu);
    
    // Fechar menu ao clicar em um link (mobile)
    const menuLinks = sidebar.querySelectorAll('a');
    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) {
                toggleMenu();
            }
        });
    });