document.addEventListener('DOMContentLoaded', function() {
    
    
    const options = {
        valueNames: [
            { name: 'data', attr: 'data-data' },
            'categoria', 
            'descricao',
            { name: 'valor', attr: 'data-valor' }
        ],
        page: 10,
        pagination: {
            innerWindow: 1,
            outerWindow: 1
        }
    };
    
    const despesasList = new List('despesas-list', options);
    
   
    document.getElementById('search').addEventListener('input', function(e) {
        despesasList.search(e.target.value);
    });
    

    document.querySelectorAll('.sort').forEach(button => {
        button.addEventListener('click', function() {
            const sortKey = this.getAttribute('data-sort');
            const isAsc = this.classList.contains('asc');
            
            
            document.querySelectorAll('.sort').forEach(btn => {
                if (btn !== this) {
                    btn.classList.remove('asc', 'desc');
                }
            });

            
            if (isAsc) {
                despesasList.sort(sortKey, { order: 'desc' });
                this.classList.remove('asc');
                this.classList.add('desc');
            } else {
                despesasList.sort(sortKey, { order: 'asc' });
                this.classList.remove('desc');
                this.classList.add('asc');
            }
        });
    });

   
    const iconesAcao = document.querySelectorAll('.icon-animacao');

    iconesAcao.forEach(icone => {
        icone.addEventListener('mouseover', function() {
            this.classList.add('animate__animated', 'animate__tada');
        });

        icone.addEventListener('animationend', function() {
            this.classList.remove('animate__animated', 'animate__tada');
        });
    });
});