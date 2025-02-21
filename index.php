<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinançaMeuBolso</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="style.css" rel="stylesheet">
</head>

<body>
    <div class="app-container">
        <div class="main-layout">
            <!-- Sidebar -->
            <aside class="sidebar-panel">
                <h3 class="sidebar-title">Resumo</h3>
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
            </aside>

            <!-- Conteúdo Principal -->
            <main class="content-area">
                <div class="chat-wrapper" id="chat-container">
                    <!-- Mensagens serão inseridas aqui -->
                </div>
                <div class="tagsemensagem">
                    <!-- Seção de Tags -->
                    <div class="tags-section" id="tags-container">
                        <div class="tags-header">
                            <div class="tags-list"></div>
                            <button class="icon-button edit-tags-btn">
                                <i class="fas fa-cog"></i>
                            </button>
                        </div>
                        <div class="tags-editor" style="display: none;">
                            <div class="tags-edit-list"></div>
                            <button class="primary-button add-tag-btn">
                                <i class="fas fa-plus"></i> Nova Tag
                            </button>
                        </div>
                    </div>

                    <!-- Formulário de Entrada -->
                    <div class="expense-input-group">
                        <span class="currency-symbol">R$</span>
                        <input type="number" id="amount" class="amount-input" step="0.01" placeholder="Valor">
                        <input type="text" id="description" class="description-input"
                            placeholder="Descrição (opcional)">
                        <button class="primary-button" id="send-expense"><i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal de Tags -->
    <div class="tag-modal" id="tagModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-body">
                    <div class="tags-list-container" id="tag-list">
                        <!-- Tags serão listadas aqui -->
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
</body>

</html>