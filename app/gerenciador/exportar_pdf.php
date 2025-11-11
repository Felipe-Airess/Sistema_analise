<?php

require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: ../login/login.php');
    exit();
}

$empresa_id = $_SESSION['empresa_id'] ?? null;
$empresa_nome = $_SESSION['empresa_nome'] ?? 'Empresa';

try {
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isFontSubsettingEnabled', false);
    $options->set('defaultFont', 'DejaVu Sans');
    $dompdf = new Dompdf($options);
    $exportDate = date('d/m/Y H:i:s');

    $sql_despesas = "SELECT descricao, valor, data FROM despesas WHERE empresa_id = ? ORDER BY data DESC";
    $stmt = $pdo->prepare($sql_despesas);
    $stmt->execute([$empresa_id]);
    $despesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql_receitas = "SELECT r.descricao, r.valor, c.nome as categoria, r.data FROM receitas r LEFT JOIN categorias c ON r.categoria_id = c.id WHERE r.empresa_id = ? ORDER BY r.data DESC";
    $stmt = $pdo->prepare($sql_receitas);
    $stmt->execute([$empresa_id]);
    $receitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $sql_total_receitas = "SELECT SUM(valor) AS total_receitas FROM receitas WHERE empresa_id = ?";
    $stmt = $pdo->prepare($sql_total_receitas);
    $stmt->execute([$empresa_id]);
    $total_receitas = (float) ($stmt->fetchColumn() ?? 0);

    $sql_total_despesas = "SELECT SUM(valor) AS total_despesas FROM despesas WHERE empresa_id = ?";
    $stmt = $pdo->prepare($sql_total_despesas);
    $stmt->execute([$empresa_id]);
    $total_despesas = (float) ($stmt->fetchColumn() ?? 0);

    $lucro_total = $total_receitas - $total_despesas;

    $sql_ultimas_receitas = "
        SELECT r.descricao, r.valor, r.data, COALESCE(c.nome, 'Sem Categoria') as categoria 
        FROM receitas r
        LEFT JOIN categorias c ON r.categoria_id = c.id
        WHERE r.empresa_id = ?
        ORDER BY r.data DESC, r.id DESC";

    $stmt = $pdo->prepare($sql_ultimas_receitas);
    $stmt->execute([$empresa_id]);
    $ultimas_receitas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql_ultimas_despesas = "
        SELECT d.descricao, d.valor, d.data, COALESCE(c.nome, 'Sem Categoria') as categoria 
        FROM despesas d
        LEFT JOIN categorias c ON d.categoria_id = c.id
        WHERE d.empresa_id = ?
        ORDER BY d.data DESC, d.id DESC";

    $stmt = $pdo->prepare($sql_ultimas_despesas);
    $stmt->execute([$empresa_id]);
    $ultimas_despesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql_categorias = "SELECT * FROM categorias WHERE empresa_id = ?";
    $stmt = $pdo->prepare($sql_categorias);
    $stmt->execute([$empresa_id]);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql_evolucao_mensal = "
        SELECT 
            MONTH(data) as mes,
            YEAR(data) as ano,
            SUM(valor) as total
        FROM receitas 
        WHERE empresa_id = ? 
        GROUP BY YEAR(data), MONTH(data)
        ORDER BY ano, mes";

    $stmt = $pdo->prepare($sql_evolucao_mensal);
    $stmt->execute([$empresa_id]);
    $evolucao_mensal = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categorias_lucrativas_sql = "
        SELECT c.nome, 
            (SELECT IFNULL(SUM(r.valor), 0) FROM receitas r WHERE r.categoria_id = c.id AND r.empresa_id = ?) as receitas,
            (SELECT IFNULL(SUM(d.valor), 0) FROM despesas d WHERE d.categoria_id = c.id AND d.empresa_id = ?) as despesas,
            (SELECT IFNULL(SUM(r.valor), 0) FROM receitas r WHERE r.categoria_id = c.id AND r.empresa_id = ?) -
            (SELECT IFNULL(SUM(d.valor), 0) FROM despesas d WHERE d.categoria_id = c.id AND d.empresa_id = ?) AS lucro
        FROM categorias c
        WHERE c.empresa_id = ?
        ORDER BY lucro DESC";

    $stmt = $pdo->prepare($categorias_lucrativas_sql);
    $stmt->execute([$empresa_id, $empresa_id, $empresa_id, $empresa_id, $empresa_id]);
    $categorias_mais_lucrativas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $lucros_mes_sql = "
        SELECT 
            MONTH(r.data) AS mes,
            YEAR(r.data) AS ano,
            SUM(r.valor) as total_receitas,
            COALESCE((
                SELECT SUM(d.valor) 
                FROM despesas d 
                WHERE d.empresa_id = r.empresa_id 
                AND MONTH(d.data) = MONTH(r.data)
                AND YEAR(d.data) = YEAR(r.data)
            ), 0) as total_despesas,
            SUM(r.valor) - COALESCE((
                SELECT SUM(d.valor) 
                FROM despesas d 
                WHERE d.empresa_id = r.empresa_id 
                AND MONTH(d.data) = MONTH(r.data)
                AND YEAR(d.data) = YEAR(r.data)
            ), 0) AS lucro
        FROM receitas r
        WHERE r.empresa_id = ?
        GROUP BY YEAR(r.data), MONTH(r.data)
        ORDER BY ano, mes";

    $stmt = $pdo->prepare($lucros_mes_sql);
    $stmt->execute([$empresa_id]);
    $lucros_por_mes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $lucro_trimestral_sql = "
        SELECT 
            CONCAT('T', QUARTER(r.data)) AS trimestre,
            YEAR(r.data) AS ano,
            SUM(r.valor) as total_receitas,
            COALESCE((
                SELECT SUM(d.valor) 
                FROM despesas d 
                WHERE d.empresa_id = r.empresa_id 
                AND QUARTER(d.data) = QUARTER(r.data)
                AND YEAR(d.data) = YEAR(r.data)
            ), 0) as total_despesas,
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
        ORDER BY ano, trimestre";

    $stmt = $pdo->prepare($lucro_trimestral_sql);
    $stmt->execute([$empresa_id]);
    $lucro_trimestral = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formatDateBR = function ($dateStr) {
        if (empty($dateStr)) return '';
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $dateStr) ?: DateTime::createFromFormat('Y-m-d', $dateStr);
        if ($dt) return $dt->format('d/m/Y');
        $ts = strtotime($dateStr);
        if ($ts !== false) return date('d/m/Y', $ts);
        return $dateStr;
    };

    // CORREÇÃO: Criação robusta do diretório
    $exportsBase = __DIR__ . '/../../storage/exports';
    if (!is_dir($exportsBase)) {
        if (!mkdir($exportsBase, 0755, true)) {
            throw new \Exception('Não foi possível criar o diretório de exportação: ' . $exportsBase);
        }
    }

    // Verificar se o diretório é gravável
    if (!is_writable($exportsBase)) {
        throw new \Exception('Diretório de exportação não é gravável: ' . $exportsBase);
    }

    $uid = date('Ymd_His') . '_' . bin2hex(random_bytes(4));
    $tmpDir = $exportsBase . '/' . $uid;
    
    // CORREÇÃO: Criação do diretório temporário com verificação
    if (!mkdir($tmpDir, 0755, true)) {
        throw new \Exception('Não foi possível criar diretório temporário: ' . $tmpDir);
    }

    // Verificar se o tmpDir é gravável
    if (!is_writable($tmpDir)) {
        throw new \Exception('Diretório temporário não é gravável: ' . $tmpDir);
    }

    $exportDate = date('d/m/Y H:i:s');

    // CORREÇÃO: Função writeCsv melhorada com tratamento de erro
    $writeCsv = function ($path, $headers, $rows) use ($formatDateBR, $exportDate) {
        $f = fopen($path, 'w');
        if ($f === false) {
            throw new \Exception('Não foi possível criar arquivo: ' . $path);
        }
        
        fwrite($f, "\xEF\xBB\xBF");
        fputcsv($f, ["Exportacao: $exportDate"], ';');
        fputcsv($f, $headers, ';');
        
        foreach ($rows as $row) {
            $r = [];
            foreach ($row as $k => $v) {
                if (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}/', $v)) {
                    $v = $formatDateBR($v);
                }
                $r[] = $v;
            }
            fputcsv($f, $r, ';');
        }
        fclose($f);
        
        if (!file_exists($path) || filesize($path) === 0) {
            throw new \Exception('Arquivo CSV vazio ou não criado: ' . $path);
        }
        
        return true;
    };

    // CORREÇÃO: Função renderPdf melhorada com tratamento de erro
    $renderPdf = function ($path, $title, $headers, $rows) use ($options, $formatDateBR, $exportDate) {
        try {
            $d = new Dompdf($options);
            $html = '<!doctype html><html><head><meta charset="utf-8"><style>' .
                'body{font-family: DejaVu Sans, Helvetica, Arial, sans-serif;font-size:12px}' .
                'table{width:100%;border-collapse:collapse;margin-top:10px}' .
                'th,td{border:1px solid #ccc;padding:6px;text-align:left}' .
                'th{background:#f5f5f5}' .
                '</style></head><body>';
            $html .= '<h2>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>';
            $html .= '<p style="font-size:11px;color:#666;margin:0">Exportação: ' . htmlspecialchars($exportDate, ENT_QUOTES, 'UTF-8') . '</p>';
            $html .= '<table><thead><tr>';
            
            foreach ($headers as $h) {
                $html .= '<th>' . htmlspecialchars($h, ENT_QUOTES, 'UTF-8') . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            
            foreach ($rows as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    if (is_string($cell) && preg_match('/^\d{4}-\d{2}-\d{2}/', $cell)) {
                        $cell = $formatDateBR($cell);
                    }
                    $html .= '<td>' . htmlspecialchars((string)$cell, ENT_QUOTES, 'UTF-8') . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table></body></html>';

            $d->loadHtml($html, 'UTF-8');
            $d->setPaper('A4', 'portrait');
            $d->render();
            
            $output = $d->output();
            if (empty($output)) {
                throw new \Exception('PDF vazio gerado para: ' . $title);
            }
            
            if (file_put_contents($path, $output) === false) {
                throw new \Exception('Não foi possível salvar PDF: ' . $path);
            }
            
            if (!file_exists($path) || filesize($path) === 0) {
                throw new \Exception('PDF vazio ou não criado: ' . $path);
            }
            
        } catch (\Exception $e) {
            throw new \Exception('Erro ao gerar PDF ' . $title . ': ' . $e->getMessage());
        }
    };

    $files = [];

    // Totais
    try {
        $totalsPath = $tmpDir . '/totais.csv';
        $writeCsv($totalsPath, ['total_receitas', 'total_despesas', 'lucro_total'], [[number_format($total_receitas, 2, ',', '.'), number_format($total_despesas, 2, ',', '.'), number_format($lucro_total, 2, ',', '.')]]);
        $files[] = $totalsPath;
        
        $totalsPdf = $tmpDir . '/totais.pdf';
        $renderPdf($totalsPdf, 'Totais', ['total_receitas', 'total_despesas', 'lucro_total'], [[number_format($total_receitas, 2, ',', '.'), number_format($total_despesas, 2, ',', '.'), number_format($lucro_total, 2, ',', '.')]]);
        $files[] = $totalsPdf;
    } catch (\Exception $e) {
        throw new \Exception('Erro ao criar totais: ' . $e->getMessage());
    }

    // Categorias mais lucrativas
    try {
        $catsPath = $tmpDir . '/categorias_mais_lucrativas.csv';
        $rows = [];
        foreach ($categorias_mais_lucrativas as $c) {
            $rows[] = [ $c['nome'], number_format((float)$c['receitas'], 2, ',', '.'), number_format((float)$c['despesas'], 2, ',', '.'), number_format((float)$c['lucro'], 2, ',', '.') ];
        }
        $writeCsv($catsPath, ['categoria','receitas','despesas','lucro'], $rows);
        $files[] = $catsPath;
        
        $catsPdf = $tmpDir . '/categorias_mais_lucrativas.pdf';
        $renderPdf($catsPdf, 'Categorias mais lucrativas', ['categoria','receitas','despesas','lucro'], $rows);
        $files[] = $catsPdf;
    } catch (\Exception $e) {
        throw new \Exception('Erro ao criar categorias lucrativas: ' . $e->getMessage());
    }

    // Lucros por mes
    try {
        $mesPath = $tmpDir . '/lucros_por_mes.csv';
        $rows = [];
        $meses = [1=>'Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
        foreach ($lucros_por_mes as $lm) {
            $mesNome = $meses[(int)$lm['mes']] ?? $lm['mes'];
            $rows[] = [ $mesNome . '/' . $lm['ano'], number_format((float)$lm['total_receitas'], 2, ',', '.'), number_format((float)$lm['total_despesas'], 2, ',', '.'), number_format((float)$lm['lucro'], 2, ',', '.') ];
        }
        $writeCsv($mesPath, ['mes_ano','receitas','despesas','lucro'], $rows);
        $files[] = $mesPath;
        
        $mesPdf = $tmpDir . '/lucros_por_mes.pdf';
        $renderPdf($mesPdf, 'Lucros por mês', ['mes_ano','receitas','despesas','lucro'], $rows);
        $files[] = $mesPdf;
    } catch (\Exception $e) {
        throw new \Exception('Erro ao criar lucros por mês: ' . $e->getMessage());
    }

    // Lucro trimestral
    try {
        $triPath = $tmpDir . '/lucro_trimestral.csv';
        $rows = [];
        foreach ($lucro_trimestral as $lt) {
            $rows[] = [ $lt['trimestre'] . '/' . $lt['ano'], number_format((float)$lt['total_receitas'], 2, ',', '.'), number_format((float)$lt['total_despesas'], 2, ',', '.'), number_format((float)$lt['lucro'], 2, ',', '.') ];
        }
        $writeCsv($triPath, ['trimestre_ano','receitas','despesas','lucro'], $rows);
        $files[] = $triPath;
        
        $triPdf = $tmpDir . '/lucro_trimestral.pdf';
        $renderPdf($triPdf, 'Lucro trimestral', ['trimestre_ano','receitas','despesas','lucro'], $rows);
        $files[] = $triPdf;
    } catch (\Exception $e) {
        throw new \Exception('Erro ao criar lucro trimestral: ' . $e->getMessage());
    }

    // Ultimas receitas
    try {
        $urPath = $tmpDir . '/ultimas_receitas.csv';
        $rows = [];
        foreach ($ultimas_receitas as $ur) {
            $rows[] = [ $ur['descricao'], $ur['categoria'], $formatDateBR($ur['data']), number_format((float)$ur['valor'], 2, ',', '.') ];
        }
        $writeCsv($urPath, ['descricao','categoria','data','valor'], $rows);
        $files[] = $urPath;
        
        $urPdf = $tmpDir . '/ultimas_receitas.pdf';
        $renderPdf($urPdf, 'Últimas receitas', ['descricao','categoria','data','valor'], $rows);
        $files[] = $urPdf;
    } catch (\Exception $e) {
        throw new \Exception('Erro ao criar últimas receitas: ' . $e->getMessage());
    }

    // Ultimas despesas
    try {
        $udPath = $tmpDir . '/ultimas_despesas.csv';
        $rows = [];
        foreach ($ultimas_despesas as $ud) {
            $rows[] = [ $ud['descricao'], $ud['categoria'], $formatDateBR($ud['data']), number_format((float)$ud['valor'], 2, ',', '.') ];
        }
        $writeCsv($udPath, ['descricao','categoria','data','valor'], $rows);
        $files[] = $udPath;
        
        $udPdf = $tmpDir . '/ultimas_despesas.pdf';
        $renderPdf($udPdf, 'Últimas despesas', ['descricao','categoria','data','valor'], $rows);
        $files[] = $udPdf;
    } catch (\Exception $e) {
        throw new \Exception('Erro ao criar últimas despesas: ' . $e->getMessage());
    }

    // Todas despesas
    try {
        $allDesPath = $tmpDir . '/despesas.csv';
        $rows = [];
        foreach ($despesas as $d) {
            $rows[] = [ $d['descricao'], $formatDateBR($d['data']), number_format((float)$d['valor'], 2, ',', '.') ];
        }
        $writeCsv($allDesPath, ['descricao','data','valor'], $rows);
        $files[] = $allDesPath;
        
        $allDesPdf = $tmpDir . '/despesas.pdf';
        $renderPdf($allDesPdf, 'Despesas', ['descricao','data','valor'], $rows);
        $files[] = $allDesPdf;
    } catch (\Exception $e) {
        throw new \Exception('Erro ao criar despesas: ' . $e->getMessage());
    }

    // Todas receitas
    try {
        $allRecPath = $tmpDir . '/receitas.csv';
        $rows = [];
        foreach ($receitas as $r) {
            $rows[] = [ $r['descricao'], $formatDateBR($r['data']), number_format((float)$r['valor'], 2, ',', '.') ];
        }
        $writeCsv($allRecPath, ['descricao','data','valor'], $rows);
        $files[] = $allRecPath;
        
        $allRecPdf = $tmpDir . '/receitas.pdf';
        $renderPdf($allRecPdf, 'Receitas', ['descricao','data','valor'], $rows);
        $files[] = $allRecPdf;
    } catch (\Exception $e) {
        throw new \Exception('Erro ao criar receitas: ' . $e->getMessage());
    }

    // Categorias list
    try {
        $catsListPath = $tmpDir . '/categorias.csv';
        $rows = [];
        foreach ($categorias as $c) {
            $rows[] = [ $c['id'] ?? '', $c['nome'] ?? '' ];
        }
        $writeCsv($catsListPath, ['id','nome'], $rows);
        $files[] = $catsListPath;
        
        $catsListPdf = $tmpDir . '/categorias.pdf';
        $renderPdf($catsListPdf, 'Categorias', ['id','nome'], $rows);
        $files[] = $catsListPdf;
    } catch (\Exception $e) {
        throw new \Exception('Erro ao criar categorias: ' . $e->getMessage());
    }

    // Evolucao mensal
    try {
        $evoPath = $tmpDir . '/evolucao_mensal.csv';
        $rows = [];
        foreach ($evolucao_mensal as $em) {
            $rows[] = [ (int)$em['mes'] . '/' . $em['ano'], number_format((float)$em['total'], 2, ',', '.') ];
        }
        $writeCsv($evoPath, ['mes_ano','total_receitas'], $rows);
        $files[] = $evoPath;
        
        $evoPdf = $tmpDir . '/evolucao_mensal.pdf';
        $renderPdf($evoPdf, 'Evolução mensal', ['mes_ano','total_receitas'], $rows);
        $files[] = $evoPdf;
    } catch (\Exception $e) {
        throw new \Exception('Erro ao criar evolução mensal: ' . $e->getMessage());
    }

    // Verificar se todos os arquivos foram criados
    $missing = [];
    foreach ($files as $f) {
        if (!file_exists($f)) {
            $missing[] = basename($f);
        }
    }
    
    if (!empty($missing)) {
        throw new \Exception('Arquivos de exportação faltando: ' . implode(', ', $missing) . '. Verifique permissões e se a pasta storage/exports é gravável.');
    }

    // Criar arquivo ZIP
    $archivePath = $exportsBase . '/relatorio_' . $uid . '.zip';

    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($archivePath, ZipArchive::CREATE) !== true) {
            throw new \Exception('Não foi possível criar arquivo zip');
        }
        foreach ($files as $f) {
            $zip->addFile($f, basename($f));
        }
        $zip->close();
        $archiveMime = 'application/zip';
        $archiveName = 'relatorio_' . date('Ymd') . '.zip';
    } elseif (class_exists('PharData')) {
        $tarPath = $exportsBase . '/relatorio_' . $uid . '.tar';
        $phar = new PharData($tarPath);
        foreach ($files as $f) {
            $phar->addFile($f, basename($f));
        }
        $phar->compress(Phar::GZ);
        unset($phar);
        @unlink($tarPath);
        $archivePath = $tarPath . '.gz';
        $archiveMime = 'application/gzip';
        $archiveName = 'relatorio_' . date('Ymd') . '.tar.gz';
    } else {
        $archivePath = $exportsBase . '/relatorio_' . $uid . '.zip';
        $escapedFiles = array_map('escapeshellarg', $files);
        $filesArg = implode(', ', $escapedFiles);
        $destArg = escapeshellarg($archivePath);
        $cmd = "powershell -NoProfile -Command Compress-Archive -Path $filesArg -DestinationPath $destArg -Force";
        exec($cmd, $cmdOutput, $rc);
        if ($rc !== 0 || !file_exists($archivePath)) {
            throw new \Exception('Falha ao criar arquivo zip via PowerShell. Saída: ' . implode("\n", $cmdOutput));
        }
        $archiveMime = 'application/zip';
        $archiveName = 'relatorio_' . date('Ymd') . '.zip';
    }

    // Enviar arquivo
    if (!file_exists($archivePath)) {
        throw new \Exception('Arquivo de exportação não encontrado');
    }
    
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $archiveMime);
    header('Content-Disposition: attachment; filename="' . $archiveName . '"');
    header('Content-Length: ' . filesize($archivePath));
    
    while (ob_get_level()) ob_end_clean();
    readfile($archivePath);

    // Limpeza
    foreach ($files as $f) { 
        if (file_exists($f)) @unlink($f); 
    }
    if (is_dir($tmpDir)) @rmdir($tmpDir);
    @unlink($archivePath);
    
    exit();

} catch (\Exception $e) {
    http_response_code(500);
    echo '<h1>Erro ao gerar relatório de exportação</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<p>Por favor, verifique as permissões do diretório storage/exports</p>';
    exit();
}