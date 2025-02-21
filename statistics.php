<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatísticas - FinançaMeuBolso</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="style.css" rel="stylesheet">
</head>

<body>
    <div class="app-container">
        <div class="main-layout">
            <!-- Sidebar -->
            <aside class="sidebar-panel">
            <button class="primary-button" onclick="window.location.href='index.php'">
            <i class="fa-solid fa-house"></i>
            </button>
                <h3 class="sidebar-title">Resumo</h3>
                <div class="sidebar-content">
                <div class="stats-container">
                    <div class="stat-item">
                        <h5 class="stat-period">Hoje</h5>
                        <span class="stat-value" id="today-total">R$ 0,00</span>
                    </div>
                    <div class="stat-item">
                        <h5 class="stat-period">Este Mês</h5>
                        <span class="stat-value" id="month-total">R$ 0,00</span>
                    </div>
                    <div class="stat-item">
                        <h5 class="stat-period">Este Ano</h5>
                        <span class="stat-value" id="year-total">R$ 0,00</span>
                    </div>
                </div>
                <div class="charts-container">
                    <canvas class="chart-canvas" id="monthlyChart"></canvas>
                    <canvas class="chart-canvas" id="tagChart"></canvas>
                </div>
                </div>
            </aside>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
</body>

</html>
