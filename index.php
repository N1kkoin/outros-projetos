<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Gastos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Adicione esta linha no <head> do seu HTML -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar com as estatísticas -->
            <div class="col-md-3 sidebar">
                <h3>Resumo</h3>
                <div class="stats">
                    <div class="stat-card">
                        <h5>Hoje</h5>
                        <span id="today-total">R$ 0,00</span>
                    </div>
                    <div class="stat-card">
                        <h5>Este Mês</h5>
                        <span id="month-total">R$ 0,00</span>
                    </div>
                    <div class="stat-card">
                        <h5>Este Ano</h5>
                        <span id="year-total">R$ 0,00</span>
                    </div>
                </div>
                <div class="charts">
                    <canvas id="monthlyChart"></canvas>
                    <canvas id="tagChart"></canvas>
                </div>
            </div>

            <!-- Chat principal -->
            <div class="col-md-9 main-content">
                <div id="chat-container">
                    <!-- As mensagens serão inseridas aqui -->
                </div>
                
                <!-- Tags section -->
                <div id="tags-container" class="mb-3">
                    <!-- Tags serão inseridas aqui -->
                </div>

                <!-- Input form -->
                <div class="input-group mb-3">
                    <span class="input-group-text">R$</span>
                    <input type="number" id="amount" class="form-control" step="0.01" placeholder="Valor">
                    <input type="text" id="description" class="form-control" placeholder="Descrição (opcional)">
                    <button class="btn btn-primary" id="send-expense">Enviar</button>
                </div>

                <!-- Tag management -->
                <button class="btn btn-secondary btn-sm" id="manage-tags">Gerenciar Tags</button>
            </div>
        </div>
    </div>

    <!-- Modal para gerenciar tags -->
    <div class="modal fade" id="tagModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gerenciar Tags</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="tag-list">
                        <!-- Tags serão listadas aqui -->
                    </div>
                    <div class="mt-3">
                        <input type="text" id="new-tag-name" class="form-control" placeholder="Nome da nova tag">
                        <input type="color" id="new-tag-color" class="form-control" value="#6c757d">
                        <button class="btn btn-success mt-2" id="add-tag">Adicionar Tag</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
</body>
</html>