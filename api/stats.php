
<?php
require_once '../config.php';

// Totais
$today = $db->query('SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE DATE(created_at) = CURDATE()')->fetchColumn();
$month = $db->query('SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())')->fetchColumn();
$year = $db->query('SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE YEAR(created_at) = YEAR(CURDATE())')->fetchColumn();

// Dados mensais para o gráfico de linha
$monthly_data = $db->query('
    SELECT DATE(created_at) as date, SUM(amount) as total
    FROM expenses 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
')->fetchAll(PDO::FETCH_ASSOC);

$monthly_labels = array_column($monthly_data, 'date');
$monthly_values = array_column($monthly_data, 'total');

// Dados por tag para o gráfico de rosca
$tags_data = $db->query('
    SELECT t.name, t.color, COALESCE(SUM(e.amount), 0) as total
    FROM tags t
    LEFT JOIN expenses e ON t.id = e.tag_id AND MONTH(e.created_at) = MONTH(CURDATE())
    GROUP BY t.id
    ORDER BY total DESC
')->fetchAll(PDO::FETCH_ASSOC);

$response = [
    'today' => floatval($today),
    'month' => floatval($month),
    'year' => floatval($year),
    'monthly_labels' => $monthly_labels,
    'monthly_data' => $monthly_values,
    'tags_labels' => array_column($tags_data, 'name'),
    'tags_data' => array_column($tags_data, 'total'),
    'tags_colors' => array_column($tags_data, 'color')
];

header('Content-Type: application/json');
echo json_encode($response);