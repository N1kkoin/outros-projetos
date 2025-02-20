let selectedTagId = null;
let tags = [];

// Carregar tags iniciais
async function loadTags() {
    try {
        const response = await fetch('api/tags.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        console.log('Tags carregadas:', data); // Para debug
        tags = data;
        renderTags();
        renderTagsList();
    } catch (error) {
        console.error('Erro ao carregar tags:', error);
    }
}

// Renderizar tags no container principal
function renderTags() {
    const container = document.getElementById('tags-container');
    container.innerHTML = '';
    
    tags.forEach(tag => {
        const button = document.createElement('button');
        button.className = `tag-button ${tag.id === selectedTagId ? 'selected' : ''}`;
        button.style.backgroundColor = tag.color;
        button.textContent = tag.name;
        button.onclick = () => selectTag(tag.id);
        container.appendChild(button);
    });
}

// Selecionar uma tag
function selectTag(tagId) {
    selectedTagId = selectedTagId === tagId ? null : tagId;
    renderTags();
}

// Enviar nova despesa
async function sendExpense() {
    const amount = document.getElementById('amount').value;
    const description = document.getElementById('description').value;
    
    if (!amount || amount <= 0) {
        alert('Por favor, insira um valor válido');
        return;
    }

    if (!selectedTagId) {
        alert('Por favor, selecione uma tag');
        return;
    }

    const response = await fetch('api/expenses.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            amount,
            tag_id: selectedTagId,
            description
        })
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

// Carregar despesas
async function loadExpenses() {
    const response = await fetch('api/expenses.php');
    const expenses = await response.json();
    renderExpenses(expenses);
}

// Função para editar despesa
async function editExpense(expense) {
    document.getElementById('amount').value = expense.amount;
    document.getElementById('description').value = expense.description;
    selectTag(expense.tag_id);
    
    // Atualizar o botão de enviar
    const sendButton = document.getElementById('send-expense');
    sendButton.textContent = 'Atualizar';
    sendButton.onclick = async () => {
        const updatedExpense = {
            id: expense.id,
            amount: document.getElementById('amount').value,
            tag_id: selectedTagId,
            description: document.getElementById('description').value
        };

        const response = await fetch('api/expenses.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(updatedExpense)
        });

        if (response.ok) {
            // Resetar formulário
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

// Função para deletar despesa
async function deleteExpense(id) {
    if (confirm('Tem certeza que deseja excluir esta despesa?')) {
        const response = await fetch(`api/expenses.php?id=${id}`, {
            method: 'DELETE'
        });

        if (response.ok) {
            loadExpenses();
            updateStats();
        }
    }
}

// Atualizar a função renderExpenses
function renderExpenses(expenses) {
    const container = document.getElementById('chat-container');
    container.innerHTML = '';
    
    expenses.forEach(expense => {
        const tag = tags.find(t => t.id === expense.tag_id);
        const message = document.createElement('div');
        message.className = 'expense-message';
        message.innerHTML = `
            <div class="expense-header">
                <span class="tag" style="background-color: ${tag.color}">${tag.name}</span>
                <span class="amount">R$ ${parseFloat(expense.amount).toFixed(2)}</span>
                <div class="expense-actions">
                    <button class="btn btn-sm btn-outline-primary edit-btn">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            ${expense.description ? `<p class="description">${expense.description}</p>` : ''}
            <div class="timestamp">${new Date(expense.created_at).toLocaleString()}</div>
        `;

        // Adicionar event listeners aos botões
        const editBtn = message.querySelector('.edit-btn');
        const deleteBtn = message.querySelector('.delete-btn');
        
        editBtn.onclick = () => editExpense(expense);
        deleteBtn.onclick = () => deleteExpense(expense.id);

        container.appendChild(message);
    });
    
    container.scrollTop = container.scrollHeight;
}

// Atualizar estatísticas
async function updateStats() {
    const response = await fetch('api/stats.php');
    const stats = await response.json();
    
    document.getElementById('today-total').textContent = `R$ ${stats.today.toFixed(2)}`;
    document.getElementById('month-total').textContent = `R$ ${stats.month.toFixed(2)}`;
    document.getElementById('year-total').textContent = `R$ ${stats.year.toFixed(2)}`;
    
    updateCharts(stats);
}

// Atualizar gráficos
function updateCharts(stats) {
    // Gráfico mensal
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

    // Gráfico por tags
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

// Gerenciamento de tags
document.getElementById('manage-tags').onclick = () => {
    new bootstrap.Modal(document.getElementById('tagModal')).show();
};

// Adicionar nova tag
document.getElementById('add-tag').onclick = async () => {
    const name = document.getElementById('new-tag-name').value;
    const color = document.getElementById('new-tag-color').value;
    
    if (!name) {
        alert('Por favor, insira um nome para a tag');
        return;
    }

    const response = await fetch('api/tags.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name, color })
    });

    if (response.ok) {
        document.getElementById('new-tag-name').value = '';
        loadTags();
    }
};

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    loadTags();
    loadExpenses();
    updateStats();
    
    document.getElementById('send-expense').onclick = sendExpense;
});