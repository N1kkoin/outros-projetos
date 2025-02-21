let selectedTagId = null;
let tags = [];

// Carregar tags iniciais
async function loadTags() {
    try {
        const response = await fetch('api/tags.php');
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        tags = await response.json();
        renderTags();
    } catch (error) {
        console.error('Erro ao carregar tags:', error);
    }
}

let isEditingTags = false;

// Atualizar a função renderTags
function renderTags() {
    const container = document.querySelector('.tags-list');
    const editContainer = document.querySelector('.tags-edit-list');

    // Modo normal
    container.innerHTML = '';
    tags.forEach(tag => {
        const button = document.createElement('button');
        button.className = `tag-item ${tag.id === selectedTagId ? 'selected' : ''}`;
        button.style.backgroundColor = tag.color;
        button.style.borderColor = tag.color; // Define a borda com a mesma cor
        button.textContent = tag.name;
        button.onclick = () => selectTag(tag.id);
        container.appendChild(button);
    });

    // Modo edição
    editContainer.innerHTML = '';
    tags.forEach(tag => {
        const tagItem = document.createElement('div');
        tagItem.className = 'tag-editor-item';
        tagItem.innerHTML = `
            <input type="text" class="color-picker" data-coloris value="${tag.color}" style="background-color: ${tag.color}; color: #fff; text-align: center;">
            <input type="text" class="tag-name" value="${tag.name}">
            <div class="tag-actions">
                <button class="primary-button save-tag-btn">
                    <i class="fas fa-save"></i>
                </button>
                <button class="danger-button delete-tag-btn">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

        const nameInput = tagItem.querySelector('.tag-name');
        const colorInput = tagItem.querySelector('.color-picker');
        const saveBtn = tagItem.querySelector('.save-tag-btn');
        const deleteBtn = tagItem.querySelector('.delete-tag-btn');

        saveBtn.onclick = async () => {
            await updateTag({
                id: tag.id,
                name: nameInput.value,
                color: colorInput.value
            });
        };

        deleteBtn.onclick = async () => {
            if (confirm('Tem certeza que deseja excluir esta tag?')) {
                await deleteTag(tag.id);
            }
        };

        editContainer.appendChild(tagItem);
    });
}

// Função para alternar modo de edição
function toggleEditMode() {
    isEditingTags = !isEditingTags;
    document.querySelector('.tags-editor').style.display = isEditingTags ? 'block' : 'none';
    document.querySelector('.tags-list').style.display = isEditingTags ? 'none' : 'flex';
}

// Funções de CRUD para tags
async function updateTag(tag) {
    const response = await fetch('api/tags.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(tag)
    });
    if (response.ok) await loadTags();
}

async function createNewTag() {
    const tagItem = document.createElement('div');
    tagItem.className = 'tag-editor-item';
    tagItem.innerHTML = `
        <input type="color" class="color-picker" value="#6c757d">
        <input type="text" class="tag-name" placeholder="Nova Tag">
        <div class="tag-actions">
            <button class="primary-button save-tag-btn">
                <i class="fas fa-save"></i>
            </button>
        </div>
    `;

    const editContainer = document.querySelector('.tags-edit-list');
    editContainer.appendChild(tagItem);

    const saveBtn = tagItem.querySelector('.save-tag-btn');
    saveBtn.onclick = async () => {
        const nameInput = tagItem.querySelector('.tag-name');
        const colorInput = tagItem.querySelector('.color-picker');

        const response = await fetch('api/tags.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: nameInput.value,
                color: colorInput.value
            })
        });

        if (response.ok) await loadTags();
    };
}

// Selecionar uma tag
function selectTag(tagId) {
    if (selectedTagId === tagId) {
        selectedTagId = tagId;  // 🔥 Garante que não seja null
    } else {
        selectedTagId = tagId;
    }
    console.log("Tag selecionada:", selectedTagId); // 🔥 Verificar se está certo
    renderTags();
}


// Gerenciamento de despesas
async function sendExpense() {
    const amount = document.getElementById('amount').value;
    const description = document.getElementById('description').value;

    if (!amount || amount <= 0) return alert('Por favor, insira um valor válido');
    if (!selectedTagId) return alert('Por favor, selecione uma tag');

    const response = await fetch('api/expenses.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ amount, tag_id: selectedTagId, description })
    });

    if (response.ok) {
        document.getElementById('amount').value = '';
        document.getElementById('description').value = '';
        selectedTagId = null;
        renderTags();
        loadExpenses();
        updateStats();
    }
}

async function loadExpenses() {
    const response = await fetch('api/expenses.php');
    renderExpenses(await response.json());
}

async function editExpense(expense) {
    document.getElementById('amount').value = expense.amount;
    document.getElementById('description').value = expense.description;
    selectTag(expense.tag_id);

    const sendButton = document.getElementById('send-expense');
    sendButton.textContent = 'Atualizar';
    sendButton.onclick = async () => {
        console.log("Tag selecionada antes do envio:", selectedTagId); // 🔥 Verificar se está certo

        const response = await fetch('api/expenses.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: expense.id,
                amount: document.getElementById('amount').value,
                tag_id: selectedTagId || 0,  // Se for null, envia 0
                description: document.getElementById('description').value
            })

        });

        if (response.ok) {
            document.getElementById('amount').value = '';
            document.getElementById('description').value = '';
            selectedTagId = null;
            sendButton.textContent = 'Enviar';
            sendButton.onclick = sendExpense;
            renderTags();
            loadExpenses();
            updateStats();
        }
    };
}

async function deleteExpense(id) {
    if (!confirm('Tem certeza que deseja excluir esta despesa?')) return;

    const response = await fetch(`api/expenses.php?id=${id}`, { method: 'DELETE' });
    if (response.ok) {
        loadExpenses();
        updateStats();
    }
}

function renderExpenses(expenses) {
    const container = document.getElementById('chat-container');
    container.innerHTML = '';

    expenses.forEach(expense => {
        const tag = tags.find(t => t.id === expense.tag_id);
        const message = document.createElement('div');
        message.className = 'transaction-message';
        message.innerHTML = `
            <div class="transaction-header">
                <span class="transaction-tag" style="background-color: ${tag.color}">${tag.name}</span>
                <span class="transaction-amount">R$ ${parseFloat(expense.amount).toFixed(2)}</span>
               
            </div>
            ${expense.description ? `<p>${expense.description}</p>` : ''}
            <div class="transaction-time">${new Date(expense.created_at).toLocaleString()}</div>
             <div class="transaction-actions">
                    <button class="edit-button">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="danger-button">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
        `;

        // Evento para mostrar/ocultar botões ao clicar
        message.addEventListener('click', () => {
            document.querySelectorAll('.transaction-message').forEach(msg => {
                if (msg !== message) msg.classList.remove('active');
            });
            message.classList.toggle('active');
        });

        message.querySelector('.edit-button').onclick = (e) => {
            e.stopPropagation(); // Evita fechar os botões ao clicar
            editExpense(expense);
        };

        message.querySelector('.danger-button').onclick = (e) => {
            e.stopPropagation(); // Evita fechar os botões ao clicar
            deleteExpense(expense.id);
        };

        container.prepend(message); // Adiciona no topo

    });

    container.scrollTop = container.scrollHeight; // Mantém o scroll no final
}

// Estatísticas e gráficos
async function updateStats() {
    const response = await fetch('api/stats.php');
    const stats = await response.json();

    document.getElementById('today-total').textContent = `R$ ${stats.today.toFixed(2)}`;
    document.getElementById('month-total').textContent = `R$ ${stats.month.toFixed(2)}`;
    document.getElementById('year-total').textContent = `R$ ${stats.year.toFixed(2)}`;

    updateCharts(stats);
}

let monthlyChartInstance = null;
let tagChartInstance = null;

function updateCharts(stats) {
    // Se os gráficos já existem, destrua-os antes de recriar
    if (monthlyChartInstance) {
        monthlyChartInstance.destroy();
    }
    if (tagChartInstance) {
        tagChartInstance.destroy();
    }

    // Criar novo gráfico de gastos por dia
    monthlyChartInstance = new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: stats.monthly_labels,
            datasets: [{
                label: 'Gastos por Dia',
                data: stats.monthly_data,
                borderColor: '#007bff'
            }]
        }
    });

    // Criar novo gráfico de tags
    tagChartInstance = new Chart(document.getElementById('tagChart'), {
        type: 'doughnut',
        data: {
            labels: stats.tags_labels,
            datasets: [{
                data: stats.tags_data,
                backgroundColor: stats.tags_colors
            }]
        }
    });
}


async function deleteTag(id) {
    const response = await fetch(`api/tags.php?id=${id}`, { method: 'DELETE' });
    if (response.ok) await loadTags();
}

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    loadTags();
    loadExpenses();
    updateStats();

    document.querySelector('.edit-tags-btn').onclick = toggleEditMode;
    document.querySelector('.add-tag-btn').onclick = createNewTag;
    document.getElementById('send-expense').onclick = sendExpense;

    // Carregar as estatísticas
    async function loadStats() {
        const response = await fetch('api/stats.php');
        const stats = await response.json();

        // Preencher as estatísticas no sidebar
        document.getElementById('today-total').textContent = `R$ ${stats.today.toFixed(2)}`;
        document.getElementById('month-total').textContent = `R$ ${stats.month.toFixed(2)}`;
        document.getElementById('year-total').textContent = `R$ ${stats.year.toFixed(2)}`;

        updateCharts(stats);
    }

    // Atualizar os gráficos com os dados de stats
    function updateCharts(stats) {
        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: stats.monthly_labels,
                datasets: [{
                    label: 'Gastos por Dia',
                    data: stats.monthly_data,
                    borderColor: '#007bff'
                }]
            }
        });

        new Chart(document.getElementById('tagChart'), {
            type: 'doughnut',
            data: {
                labels: stats.tags_labels,
                datasets: [{
                    data: stats.tags_data,
                    backgroundColor: stats.tags_colors
                }]
            }
        });
    }
  // Delegação de evento para inputs color-picker
  document.addEventListener("coloris:pick", (event) => {
    const input = event.target;
    const newColor = event.detail.color;
    input.style.backgroundColor = newColor;
    input.style.color = getTextColor(newColor);
});

document.addEventListener("input", (event) => {
    if (event.target.classList.contains("color-picker")) {
        const input = event.target;
        input.style.backgroundColor = input.value;
        input.style.color = getTextColor(input.value);
    }
});
});

// Função para definir cor do texto (preto ou branco) dependendo do fundo
function getTextColor(bgColor) {
const r = parseInt(bgColor.substring(1, 3), 16);
const g = parseInt(bgColor.substring(3, 5), 16);
const b = parseInt(bgColor.substring(5, 7), 16);
return (r * 0.299 + g * 0.587 + b * 0.114) > 186 ? "#000" : "#fff";
}

Coloris({
    theme: 'polaroid',
    themeMode: 'light', // ou 'light'
    format: 'hex',
    margin: 2,
    alpha: false
});
