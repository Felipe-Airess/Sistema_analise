<?php
require_once("config/conexao.php");

// Defina o ID do seu usuário/empresa de teste
$empresa_id = 1; 

$sql = "
-- 1. Inserir Categorias (Mais opções)
INSERT INTO categorias (nome, empresa_id) VALUES ('Salário', $empresa_id);
INSERT INTO categorias (nome, empresa_id) VALUES ('Freelance', $empresa_id);
INSERT INTO categorias (nome, empresa_id) VALUES ('Investimentos', $empresa_id);
INSERT INTO categorias (nome, empresa_id) VALUES ('Moradia', $empresa_id);
INSERT INTO categorias (nome, empresa_id) VALUES ('Alimentação', $empresa_id);
INSERT INTO categorias (nome, empresa_id) VALUES ('Transporte', $empresa_id);
INSERT INTO categorias (nome, empresa_id) VALUES ('Lazer', $empresa_id);
INSERT INTO categorias (nome, empresa_id) VALUES ('Saúde', $empresa_id);
INSERT INTO categorias (nome, empresa_id) VALUES ('Educação', $empresa_id);

-- ==========================================
-- JANEIRO
-- ==========================================
INSERT INTO receitas (descricao, valor, data, categoria_id, empresa_id) VALUES 
('Salário Mensal', 5500.00, '2024-01-05', 1, $empresa_id),
('Desenvolvimento Site', 2200.00, '2024-01-18', 2, $empresa_id);

INSERT INTO despesas (descricao, valor, data, categoria_id, empresa_id) VALUES 
('Aluguel', 1500.00, '2024-01-10', 4, $empresa_id),
('Internet e Luz', 300.00, '2024-01-12', 4, $empresa_id),
('Supermercado', 850.00, '2024-01-15', 5, $empresa_id),
('Uber', 120.00, '2024-01-20', 6, $empresa_id),
('Restaurante', 250.00, '2024-01-25', 7, $empresa_id);

-- ==========================================
-- FEVEREIRO (Mês de Carnaval - Gastos altos)
-- ==========================================
INSERT INTO receitas (descricao, valor, data, categoria_id, empresa_id) VALUES 
('Salário Mensal', 5500.00, '2024-02-05', 1, $empresa_id),
('Rendimento Tesouro', 180.00, '2024-02-28', 3, $empresa_id);

INSERT INTO despesas (descricao, valor, data, categoria_id, empresa_id) VALUES 
('Aluguel', 1500.00, '2024-02-10', 4, $empresa_id),
('Internet e Luz', 320.00, '2024-02-12', 4, $empresa_id),
('Supermercado', 900.00, '2024-02-14', 5, $empresa_id),
('Viagem de Carnaval', 2100.00, '2024-02-16', 7, $empresa_id),
('Farmácia', 150.00, '2024-02-22', 8, $empresa_id);

-- ==========================================
-- MARÇO
-- ==========================================
INSERT INTO receitas (descricao, valor, data, categoria_id, empresa_id) VALUES 
('Salário Mensal', 5500.00, '2024-03-05', 1, $empresa_id),
('Consultoria', 1500.00, '2024-03-15', 2, $empresa_id),
('Venda de Monitor', 600.00, '2024-03-20', 2, $empresa_id);

INSERT INTO despesas (descricao, valor, data, categoria_id, empresa_id) VALUES 
('Aluguel', 1500.00, '2024-03-10', 4, $empresa_id),
('Internet e Luz', 290.00, '2024-03-12', 4, $empresa_id),
('Supermercado', 780.00, '2024-03-15', 5, $empresa_id),
('Curso Online', 450.00, '2024-03-18', 9, $empresa_id),
('Gasolina', 300.00, '2024-03-25', 6, $empresa_id);

-- ==========================================
-- ABRIL
-- ==========================================
INSERT INTO receitas (descricao, valor, data, categoria_id, empresa_id) VALUES 
('Salário Mensal', 5500.00, '2024-04-05', 1, $empresa_id),
('Dividendos Ações', 320.00, '2024-04-30', 3, $empresa_id);

INSERT INTO despesas (descricao, valor, data, categoria_id, empresa_id) VALUES 
('Aluguel', 1500.00, '2024-04-10', 4, $empresa_id),
('Internet e Luz', 310.00, '2024-04-12', 4, $empresa_id),
('Supermercado', 820.00, '2024-04-16', 5, $empresa_id),
('Cinema e Shopping', 350.00, '2024-04-20', 7, $empresa_id),
('Uber', 90.00, '2024-04-26', 6, $empresa_id);

-- ==========================================
-- MAIO
-- ==========================================
INSERT INTO receitas (descricao, valor, data, categoria_id, empresa_id) VALUES 
('Salário Mensal', 5500.00, '2024-05-05', 1, $empresa_id),
('Manutenção de Sistema', 1800.00, '2024-05-18', 2, $empresa_id);

INSERT INTO despesas (descricao, valor, data, categoria_id, empresa_id) VALUES 
('Aluguel', 1500.00, '2024-05-10', 4, $empresa_id),
('Internet e Luz', 295.00, '2024-05-12', 4, $empresa_id),
('Supermercado', 860.00, '2024-05-15', 5, $empresa_id),
('Dentista', 400.00, '2024-05-22', 8, $empresa_id),
('Gasolina', 300.00, '2024-05-28', 6, $empresa_id);

-- ==========================================
-- JUNHO
-- ==========================================
INSERT INTO receitas (descricao, valor, data, categoria_id, empresa_id) VALUES 
('Salário Mensal', 5500.00, '2024-06-05', 1, $empresa_id),
('1ª Parcela 13º', 2750.00, '2024-06-20', 1, $empresa_id),
('Rendimento Tesouro', 200.00, '2024-06-30', 3, $empresa_id);

INSERT INTO despesas (descricao, valor, data, categoria_id, empresa_id) VALUES 
('Aluguel', 1500.00, '2024-06-10', 4, $empresa_id),
('Internet e Luz', 305.00, '2024-06-12', 4, $empresa_id),
('Supermercado', 950.00, '2024-06-16', 5, $empresa_id),
('Presente Dia dos Namorados', 450.00, '2024-06-12', 7, $empresa_id),
('Ifood e Lanches', 280.00, '2024-06-25', 5, $empresa_id);

-- ==========================================
-- INSERIR METAS (AGORA COM AS COLUNAS CORRETAS)
-- ==========================================
INSERT INTO metas (descricao, tipo, valor_meta, data_inicio, data_fim, cor, empresa_id) VALUES 
('Atingir Lucro Anual', 'lucro', 25000.00, '2024-01-01', '2024-12-31', '#10B981', $empresa_id),
('Teto de Despesas Anual', 'despesa', 30000.00, '2024-01-01', '2024-12-31', '#EF4444', $empresa_id),
('Receitas de Freelance', 'receita', 10000.00, '2024-01-01', '2024-12-31', '#3B82F6', $empresa_id);
";

try {
    // Executa as queries
    $pdo->exec($sql);
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h1 style='color: #10B981;'>✅ Sucesso Gigante!</h1>";
    echo "<p>Foram inseridos 6 meses de receitas e despesas, além de várias categorias e metas!</p>";
    echo "<a href='app/gerenciador/gerenciador.php' style='padding: 10px 20px; background: #004b8d; color: white; text-decoration: none; border-radius: 5px;'>Ir para o Dashboard</a>";
    echo "</div>";
} catch (PDOException $e) {
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h1 style='color: #EF4444;'>❌ Ops, deu erro!</h1>";
    echo "<p><strong>Detalhe:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>