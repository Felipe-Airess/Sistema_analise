# Sistema de Análise

## Instruções para Executar o Projeto PHP com MySQL

Este projeto é uma aplicação PHP que utiliza um banco de dados MySQL. Siga as instruções abaixo para configurá-lo e executá-lo corretamente.

### Pré-requisitos
- PHP 7.4 ou superior
- MySQL 5.7 ou superior

### Configuração do Banco de Dados
1. **Crie o Banco de Dados**:
   - Abra o MySQL Workbench ou o terminal MySQL.
   - Execute o seguinte comando para criar o banco de dados:
     ```sql
     CREATE DATABASE sistema_analise;
     ```

2. **Importe o Esquema do Banco de Dados**:
   - Se você tiver um arquivo `.sql` para o esquema do banco de dados, importe-o utilizando o comando:
     ```sql
     USE sistema_analise;
     SOURCE /caminho/para/o/seu/arquivo.sql;
     ```

3. **Configuração de Conexão**:
   - Atualize as credenciais de conexão no arquivo `config/conexao.php`:
     ```php
     define("DB_HOST","localhost");
     define("DB_USER","seu_usuario");
     define("DB_PASS","sua_senha");
     define("DB_NAME","sistema_analise");
     ```

### Executando o Projeto
1. **Inicie o Servidor Embutido do PHP**:
   - Navegue até o diretório do projeto:
     ```bash
     cd /caminho/para/seu/projeto/Sistema_analise
     ```
   - Execute o seguinte comando para iniciar o servidor embutido:
     ```bash
     php -S localhost:8000
     ```

2. **Acesse a Aplicação**:
   - Abra um navegador e acesse a URL:
     ```plaintext
     http://localhost:8000
     ```

### Tecnologias Utilizadas
- **PHP** - Backend e lógica da aplicação
- **MySQL** - Banco de dados
- **Tailwind CSS** - Estilização e design responsivo
- **JavaScript** - Interatividade e validações no frontend
- **SweetAlert2** - Alertas e notificações
- **ScrollReveal** - Animações de scroll

### Estrutura do Projeto
```
Sistema_analise/
├── index.php           - Página inicial da aplicação
├── config/
│   └── conexao.php     - Configuração de conexão com MySQL
├── app/
│   ├── login/          - Módulo de autenticação
│   ├── cadastro/       - Módulo de cadastro de usuários
│   ├── gerenciador/    - Painel principal do sistema
│   ├── assets/         - Imagens e recursos estáticos
│   └── js/             - Scripts JavaScript
├── storage/            - Pasta para armazenar arquivos temporários
└── vendor/             - Dependências do projeto
```

### Como Usar
1. Clone o repositório
2. Crie o banco de dados conforme instruções acima
3. Configure as credenciais de conexão em `config/conexao.php`
4. Execute `php -S localhost:8000` no diretório raiz
5. Acesse `http://localhost:8000` no navegador
6. Faça o cadastro ou login com suas credenciais

---