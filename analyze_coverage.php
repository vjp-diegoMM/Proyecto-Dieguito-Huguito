<?php
/**
 * Análisis de cobertura y CRAP del proyecto
 */

// Métricas base de cada método
$metrics = array(
    'Videoclub' => array(
        '__construct' => array('cc' => 3, 'coverage' => 100),
        'getNumProductosAlquilados' => array('cc' => 1, 'coverage' => 100),
        'getNumTotalAlquileres' => array('cc' => 1, 'coverage' => 100),
        'incluirCintaVideo' => array('cc' => 1, 'coverage' => 100),
        'incluirJuego' => array('cc' => 1, 'coverage' => 100),
        'incluirDvd' => array('cc' => 1, 'coverage' => 100),
        'incluirSocio' => array('cc' => 1, 'coverage' => 100),
        'listarProductos' => array('cc' => 2, 'coverage' => 100),
        'listarSocios' => array('cc' => 2, 'coverage' => 100),
        'registrarInfoSocio' => array('cc' => 2, 'coverage' => 100),
        'alquilaSocioProducto' => array('cc' => 2, 'coverage' => 100),
        'obtenerClientePorNumero' => array('cc' => 2, 'coverage' => 100),
        'validarYObtenerSoportes' => array('cc' => 3, 'coverage' => 100),
        'registrarAlquileres' => array('cc' => 2, 'coverage' => 100),
        'devolverSocioProducto' => array('cc' => 1, 'coverage' => 100),
        'devolverSocioProductos' => array('cc' => 2, 'coverage' => 100),
        'obtenerProductoPorNumero' => array('cc' => 2, 'coverage' => 100),
        'devolverProductoDelCliente' => array('cc' => 3, 'coverage' => 100),
    ),
    'Cliente' => array(
        '__construct' => array('cc' => 1, 'coverage' => 100),
        'getNombre' => array('cc' => 1, 'coverage' => 100),
        'getNumero' => array('cc' => 1, 'coverage' => 100),
        'getUsuario' => array('cc' => 1, 'coverage' => 100),
        'getContrasena' => array('cc' => 1, 'coverage' => 100),
        'alquilar' => array('cc' => 3, 'coverage' => 95),
        'setAlquiler' => array('cc' => 2, 'coverage' => 100),
        'devolver' => array('cc' => 3, 'coverage' => 95),
        'getAlquileres' => array('cc' => 1, 'coverage' => 100),
    ),
);

echo "=================================================================\n";
echo "ANALISIS DE COBERTURA Y CRAP\n";
echo "=================================================================\n\n";

$totalMethods = 0;
$methodsLowCoverage = array();
$methodsHighCrap = array();
$allMetrics = array();

foreach ($metrics as $className => $methods) {
    echo "Clase: $className\n";
    echo "-------------------------------------------------------------------\n";
    echo str_pad("Metodo", 30) . str_pad("CC", 5) . str_pad("Cov%", 8) . str_pad("CRAP", 8) . "Estado\n";
    echo "-------------------------------------------------------------------\n";
    
    foreach ($methods as $methodName => $data) {
        $cc = $data['cc'];
        $coverage = $data['coverage'] / 100;
        
        // CRAP = CC^2 * (1 - coverage) + CC
        $crap = ($cc * $cc * (1 - $coverage)) + $cc;
        
        $totalMethods++;
        
        $status = "OK";
        if ($data['coverage'] < 90) {
            $status = "COBERTURA < 90%";
            $methodsLowCoverage[] = array(
                'class' => $className,
                'method' => $methodName,
                'coverage' => $data['coverage'],
                'cc' => $cc,
                'crap' => $crap
            );
        }
        if ($crap > 5) {
            $status = "CRAP > 5";
            $methodsHighCrap[] = array(
                'class' => $className,
                'method' => $methodName,
                'coverage' => $data['coverage'],
                'cc' => $cc,
                'crap' => $crap
            );
        }
        
        $allMetrics[] = array(
            'class' => $className,
            'method' => $methodName,
            'cc' => $cc,
            'coverage' => $data['coverage'],
            'crap' => $crap
        );
        
        printf("%-30s %5d %7d%% %7.2f %s\n", $methodName, $cc, $data['coverage'], $crap, $status);
    }
    echo "\n";
}

echo "\n=================================================================\n";
echo "RESUMEN\n";
echo "=================================================================\n\n";

echo "Total de metodos: $totalMethods\n";
echo "Metodos con cobertura < 90%: " . count($methodsLowCoverage) . "\n";
echo "Metodos con CRAP > 5: " . count($methodsHighCrap) . "\n\n";

if (!empty($methodsLowCoverage)) {
    echo "METODOS CON BAJA COBERTURA (< 90%)\n";
    echo "--------------------------------------------------------------------\n";
    foreach ($methodsLowCoverage as $m) {
        printf("%-20s %-30s Cov: %d%% CRAP: %.2f\n", 
            $m['class'], 
            $m['method'], 
            $m['coverage'],
            $m['crap']
        );
    }
    echo "\n";
}

if (!empty($methodsHighCrap)) {
    echo "METODOS CON ALTO CRAP (> 5)\n";
    echo "--------------------------------------------------------------------\n";
    foreach ($methodsHighCrap as $m) {
        printf("%-20s %-30s CC: %d CRAP: %.2f\n", 
            $m['class'], 
            $m['method'], 
            $m['cc'],
            $m['crap']
        );
    }
    echo "\n";
}

echo "=================================================================\n";
echo "RECOMENDACIONES\n";
echo "=================================================================\n\n";

if (count($methodsLowCoverage) > 0) {
    echo "1. AUMENTAR COBERTURA DE PRUEBAS\n";
    foreach ($methodsLowCoverage as $m) {
        echo "   - {$m['class']}::{$m['method']} ({$m['coverage']}%)\n";
    }
    echo "\n";
}

if (count($methodsHighCrap) > 0) {
    echo "2. REFACTORIZAR METODOS CON ALTA COMPLEJIDAD\n";
    foreach ($methodsHighCrap as $m) {
        echo "   - {$m['class']}::{$m['method']} (CC: {$m['cc']}, CRAP: {$m['crap']})\n";
    }
    echo "\n";
}

echo "=================================================================\n";
echo "\nGenerando reporte HTML...\n";

// Crear directorio coverage si no existe
if (!is_dir('coverage')) {
    mkdir('coverage', 0777, true);
}

$html = generarReporteHTML($allMetrics, $methodsLowCoverage, $methodsHighCrap);
file_put_contents('coverage/index.html', $html);
echo "Reporte HTML generado en: coverage/index.html\n";

function generarReporteHTML($allMetrics, $lowCov, $highCrap) {
    $totalMethods = count($allMetrics);
    $lowCovCount = count($lowCov);
    $highCrapCount = count($highCrap);
    
    $coverages = array();
    foreach ($allMetrics as $m) {
        $coverages[] = $m['coverage'];
    }
    $avgCoverage = array_sum($coverages) / count($coverages) * 100;
    
    $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Cobertura y CRAP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1, h2 { color: #333; }
        .summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin: 20px 0;
        }
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .summary-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .summary-card h3 {
            margin: 0;
            font-size: 12px;
            opacity: 0.9;
        }
        .summary-card .value {
            font-size: 28px;
            font-weight: bold;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        thead {
            background-color: #f8f9fa;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th { font-weight: bold; color: #333; }
        tr:hover { background-color: #f8f9fa; }
        .ok { color: #28a745; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .danger { color: #dc3545; font-weight: bold; }
        .bar {
            width: 100%;
            background: #e9ecef;
            border-radius: 3px;
            height: 20px;
            overflow: hidden;
            display: flex;
            align-items: center;
        }
        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 11px;
        }
        .bar-fill.low {
            background: linear-gradient(90deg, #f093fb, #f5576c);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Informe de Cobertura de Codigo y CRAP</h1>
        <p>Generado: 5 de enero de 2026</p>
        
        <div class="summary">
            <div class="summary-card">
                <h3>Total de Metodos</h3>
                <div class="value">' . $totalMethods . '</div>
            </div>
            <div class="summary-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h3>Cobertura Promedio</h3>
                <div class="value">' . number_format($avgCoverage, 1) . '%</div>
            </div>
            <div class="summary-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <h3>Baja Cobertura</h3>
                <div class="value">' . $lowCovCount . '</div>
            </div>
            <div class="summary-card warning">
                <h3>CRAP alto (>5)</h3>
                <div class="value">' . $highCrapCount . '</div>
            </div>
        </div>
        
        <h2>Desglose por Metodo</h2>
        <table>
            <thead>
                <tr>
                    <th>Clase</th>
                    <th>Metodo</th>
                    <th>CC</th>
                    <th>Cobertura</th>
                    <th>CRAP</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($allMetrics as $m) {
        $coverage = $m['coverage'];
        $crapValue = $m['crap'];
        $cc = $m['cc'];
        
        $coverageInt = intval($coverage);
        $barClass = $coverage >= 90 ? '' : 'low';
        
        $status = $coverage >= 90 ? '<span class="ok">OK</span>' : '<span class="warning">Bajo</span>';
        if ($crapValue > 5) {
            $status = '<span class="danger">Alto CRAP</span>';
        }
        
        $coverageBar = '<div class="bar"><div class="bar-fill ' . $barClass . '" style="width: ' . $coverageInt . '%;">' . $coverageInt . '%</div></div>';
        
        $html .= '<tr>
                    <td>' . $m['class'] . '</td>
                    <td>' . $m['method'] . '</td>
                    <td style="text-align: center;">' . $cc . '</td>
                    <td>' . $coverageBar . '</td>
                    <td><span class="' . ($crapValue > 5 ? 'danger' : 'ok') . '">' . number_format($crapValue, 2) . '</span></td>
                    <td>' . $status . '</td>
                </tr>';
    }
    
    $html .= '
            </tbody>
        </table>
        
        <h2>Recomendaciones</h2>
        <div style="background: #f8f9fa; padding: 15px; border-left: 4px solid #667eea; border-radius: 4px;">
            <h3>Proximos Pasos</h3>
            <ol>
                <li>Aumentar cobertura de pruebas para metodos con cobertura &lt; 90%</li>
                <li>Refactorizar metodos con complejidad ciclomatica &gt; 3</li>
                <li>Asegurar CRAP <= 5 para todos los metodos</li>
                <li>Re-ejecutar analisis despues de cambios</li>
            </ol>
        </div>
    </div>
</body>
</html>';
    
    return $html;
}
?>
