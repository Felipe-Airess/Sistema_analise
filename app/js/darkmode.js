document.addEventListener('DOMContentLoaded', function() {
    const root = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');
    
    if (!themeToggle) return; 
    
    const applyTheme = (isDark) => {
        const themeText = document.getElementById('themeText');
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');

        if (isDark) {
            root.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
            if (themeText) themeText.textContent = 'Escuro';
            if (sunIcon) sunIcon.classList.add('hidden');
            if (moonIcon) moonIcon.classList.remove('hidden');
        } else {
            root.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
            if (themeText) themeText.textContent = 'Claro';
            if (sunIcon) sunIcon.classList.remove('hidden');
            if (moonIcon) moonIcon.classList.add('hidden');
        }
        
        if (typeof window.updateChartStyles === 'function') {
            window.updateChartStyles(isDark);
        }
    };

    const savedTheme = localStorage.getItem('color-theme');
    let isDarkModeInitial = (savedTheme === 'dark') || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches);
    
    applyTheme(isDarkModeInitial); 

    themeToggle.addEventListener('click', () => {
        const isCurrentlyDark = root.classList.contains('dark');
        applyTheme(!isCurrentlyDark); 
    });


    const modal = document.getElementById('settingsModal');
    const openModalBtn = document.getElementById('openSettingsModal');
    const openModalBtnSm = document.getElementById('openSettingsModalSm');
    const closeModalBtn = document.getElementById('closeModalBtn');

    function openModal() {
        if (!modal) return;
        modal.classList.remove('hidden');
        setTimeout(() => { modal.classList.add('opacity-100'); }, 10);
    }
    
    function closeModal() {
        if (!modal) return;
        modal.classList.remove('opacity-100');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    if(openModalBtn) openModalBtn.addEventListener('click', openModal);
    if(openModalBtnSm) openModalBtnSm.addEventListener('click', openModal);
    if(closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
});