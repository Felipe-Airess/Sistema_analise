<?php
require_once("config/conexao.php");

// Defina o ID do seu usuário/empresa de teste (Baseado no seu banco, empresa1 = 1)
$empresa_id = 1; 

$sql = "
-- ==========================================
-- 1. CRIAR NOVAS CATEGORIAS (Ignora se já existir)
-- ==========================================
INSERT IGNORE INTO categorias (nome, empresa_id) VALUES ('Desenvolvimento de Software', $empresa_id);
INSERT IGNORE INTO categorias (nome, empresa_id) VALUES ('Consultoria Especializada', $empresa_id);
INSERT IGNORE INTO categorias (nome, empresa_id) VALUES ('Marketing e Anúncios', $empresa_id);
INSERT IGNORE INTO categorias (nome, empresa_id) VALUES ('Manutenção de Equipamentos', $empresa_id);

-- ==========================================
-- 2. INSERIR RECEITAS (Nov 2025 a Abr 2026)
-- ==========================================
INSERT INTO receitas (empresa_id, descricao, valor, data, categoria_id) VALUES 
($empresa_id, 'Projeto Alpha', 15000.00, '2025-11-10', (SELECT id FROM categorias WHERE nome = 'Desenvolvimento de Software' AND empresa_id = $empresa_id LIMIT 1)),
($empresa_id, 'Consultoria Mensal', 3500.00, '2025-11-15', (SELECT id FROM categorias WHERE nome = 'Consultoria Especializada' AND empresa_id = $empresa_id LIMIT 1)),

($empresa_id, 'Projeto Beta', 18000.00, '2025-12-05', (SELECT id FROM categorias WHERE nome = 'Desenvolvimento de Software' AND empresa_id = $empresa_id LIMIT 1)),
($empresa_id, 'Consultoria Mensal', 3500.00, '2025-12-15', (SELECT id FROM categorias WHERE nome = 'Consultoria Especializada' AND empresa_id = $empresa_id LIMIT 1)),

($empresa_id, 'Atualização de Sistema', 5000.00, '2026-01-10', (SELECT id FROM categorias WHERE nome = 'Desenvolvimento de Software' AND empresa_id = $empresa_id LIMIT 1)),
($empresa_id, 'Consultoria Mensal', 3500.00, '2026-01-15', (SELECT id FROM categorias WHERE nome = 'Consultoria Especializada' AND empresa_id = $empresa_id LIMIT 1)),

($empresa_id, 'Projeto Gama', 22000.00, '2026-02-08', (SELECT id FROM categorias WHERE nome = 'Desenvolvimento de Software' AND empresa_id = $empresa_id LIMIT 1)),
($empresa_id, 'Treinamento de Equipe', 4000.00, '2026-02-20', (SELECT id FROM categorias WHERE nome = 'Consultoria Especializada' AND empresa_id = $empresa_id LIMIT 1)),

($empresa_id, 'Licenciamento de Software', 8000.00, '2026-03-05', (SELECT id FROM categorias WHERE nome = 'Desenvolvimento de Software' AND empresa_id = $empresa_id LIMIT 1)),
($empresa_id, 'Consultoria Mensal', 3500.00, '2026-03-15', (SELECT id FROM categorias WHERE nome = 'Consultoria Especializada' AND empresa_id = $empresa_id LIMIT 1)),

($empresa_id, 'Projeto Delta', 19500.00, '2026-04-02', (SELECT id FROM categorias WHERE nome = 'Desenvolvimento de Software' AND empresa_id = $empresa_id LIMIT 1)),
($empresa_id, 'Consultoria Mensal', 3500.00, '2026-04-15', (SELECT id FROM categorias WHERE nome = 'Consultoria Especializada' AND empresa_id = $empresa_id LIMIT 1));

-- ==========================================
-- 3. INSERIR DESPESAS (Nov 2025 a Abr 2026)
-- Usando as categorias que já existem no seu banco (5=Energia, 8=Limpeza, 9=Aluguel) e as novas
-- ==========================================
INSERT INTO despesas (empresa_id, descricao, valor, data, categoria_id) VALUES 
($empresa_id, 'Aluguel do Escritório', 3000.00, '2025-11-05', 9),
($empresa_id, 'Conta de Energia', 850.00, '2025-11-12', 5),
($empresa_id, 'Campanha Google Ads', 1200.00, '2025-11-20', (SELECT id FROM categorias WHERE nome = 'Marketing e Anúncios' AND empresa_id = $empresa_id LIMIT 1)),

($empresa_id, 'Aluguel do Escritório', 3000.00, '2025-12-05', 9),
($empresa_id, 'Conta de Energia', 920.00, '2025-12-12', 5),
($empresa_id, 'Materiais de Limpeza', 400.00, '2025-12-18', 8),

($empresa_id, 'Aluguel do Escritório', 3200.00, '2026-01-05', 9), -- Reajuste de aluguel
($empresa_id, 'Conta de Energia', 950.00, '2026-01-12', 5),
($empresa_id, 'Manutenção de Servidores', 1500.00, '2026-01-25', (SELECT id FROM categorias WHERE nome = 'Manutenção de Equipamentos' AND empresa_id = $empresa_id LIMIT 1)),

($empresa_id, 'Aluguel do Escritório', 3200.00, '2026-02-05', 9),
($empresa_id, 'Conta de Energia', 880.00, '2026-02-12', 5),
($empresa_id, 'Campanha Facebook Ads', 1800.00, '2026-02-18', (SELECT id FROM categorias WHERE nome = 'Marketing e Anúncios' AND empresa_id = $empresa_id LIMIT 1)),

($empresa_id, 'Aluguel do Escritório', 3200.00, '2026-03-05', 9),
($empresa_id, 'Conta de Energia', 890.00, '2026-03-12', 5),
($empresa_id, 'Produtos de Limpeza', 450.00, '2026-03-22', 8),

($empresa_id, 'Aluguel do Escritório', 3200.00, '2026-04-05', 9),
($empresa_id, 'Conta de Energia', 870.00, '2026-04-12', 5),
($empresa_id, 'Troca de Roteadores', 2100.00, '2026-04-20', (SELECT id FROM categorias WHERE nome = 'Manutenção de Equipamentos' AND empresa_id = $empresa_id LIMIT 1));

-- ==========================================
-- 4. INSERIR METAS FINANCEIRAS PARA 2026
-- ==========================================
INSERT INTO metas (empresa_id, tipo, valor_meta, descricao, data_inicio, data_fim, cor) VALUES 
($empresa_id, 'receita', 150000.00, 'Meta de Receitas Anuais 2026', '2026-01-01', '2026-12-31', '#3B82F6'),
($empresa_id, 'despesa', 60000.00, 'Teto de Despesas 2026', '2026-01-01', '2026-12-31', '#EF4444'),
($empresa_id, 'lucro', 90000.00, 'Meta de Lucro Líquido 2026', '2026-01-01', '2026-12-31', '#10B981');
";

try {
    // Executa as queries
    $pdo->exec($sql);
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h1 style='color: #10B981;'>✅ Banco populado com Sucesso!</h1>";
    echo "<p>As transações dos últimos 6 meses e as metas foram inseridas usando a sua estrutura exata.</p>";
    echo "<a href='app/gerenciador/gerenciador.php' style='padding: 10px 20px; background: #004b8d; color: white; text-decoration: none; border-radius: 5px;'>Voltar para o Dashboard</a>";
    echo "</div>";
} catch (PDOException $e) {
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h1 style='color: #EF4444;'>❌ Ops, ocorreu um erro!</h1>";
    echo "<p><strong>Detalhe do SQL:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>