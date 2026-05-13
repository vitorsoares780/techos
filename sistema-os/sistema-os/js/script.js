// Função para calcular valores automaticamente
function calcularValores() {
    const valorServico = parseFloat(document.getElementById('valor_servico')?.value) || 0;
    const valorPecas = parseFloat(document.getElementById('valor_pecas')?.value) || 0;
    const valorTotal = valorServico + valorPecas;
    
    const totalElement = document.getElementById('valor_total');
    if (totalElement) {
        totalElement.value = valorTotal.toFixed(2);
    }
}

// Função para gerar número da ordem automaticamente
function gerarNumeroOrdem() {
    const data = new Date();
    const ano = data.getFullYear();
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const dia = String(data.getDate()).padStart(2, '0');
    const hora = String(data.getHours()).padStart(2, '0');
    const minuto = String(data.getMinutes()).padStart(2, '0');
    
    return `OS${ano}${mes}${dia}${hora}${minuto}`;
}

// Função para preencher dados do cliente selecionado
function carregarDadosCliente(clienteId) {
    if (!clienteId) return;
    
    fetch(`/sistema-os/includes/get_cliente.php?id=${clienteId}`)
        .then(response => response.json())
        .then(data => {
            if (data) {
                document.getElementById('cliente_nome').textContent = data.nome_completo;
                document.getElementById('cliente_telefone').textContent = data.telefone;
                document.getElementById('cliente_email').textContent = data.email || 'Não informado';
                document.getElementById('cliente_endereco').textContent = data.endereco || 'Não informado';
            }
        })
        .catch(error => console.error('Erro:', error));
}

// Validação de formulários
function validarFormulario(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#e53e3e';
            isValid = false;
        } else {
            field.style.borderColor = '#e2e8f0';
        }
    });
    
    return isValid;
}

// Máscaras para campos
function aplicarMascaras() {
    // Máscara para CPF/CNPJ
    const cpfInput = document.getElementById('cpf_rg');
    if (cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            }
            
            e.target.value = value;
        });
    }
    
    // Máscara para telefone
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length <= 10) {
                value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            } else {
                value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
            }
            
            e.target.value = value;
        });
    }
    
    // Máscara para valor monetário
    const valorInputs = document.querySelectorAll('.valor-input');
    valorInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (parseInt(value) / 100).toFixed(2);
            value = value.replace('.', ',');
            e.target.value = value;
        });
    });
}

// Filtros e busca
function filtrarOrdens() {
    const status = document.getElementById('filtro_status')?.value;
    const cliente = document.getElementById('filtro_cliente')?.value;
    const dataInicio = document.getElementById('filtro_data_inicio')?.value;
    const dataFim = document.getElementById('filtro_data_fim')?.value;
    
    const tabela = document.querySelector('.orders-table');
    const linhas = tabela.querySelectorAll('tbody tr');
    
    linhas.forEach(linha => {
        let mostrar = true;
        const celulaStatus = linha.querySelector('.status-badge').textContent;
        const celulaCliente = linha.children[1].textContent;
        const celulaData = linha.children[4].textContent;
        
        if (status && status !== 'todos' && celulaStatus !== status) {
            mostrar = false;
        }
        
        if (cliente && !celulaCliente.toLowerCase().includes(cliente.toLowerCase())) {
            mostrar = false;
        }
        
        if (dataInicio || dataFim) {
            const dataOrdem = new Date(celulaData.split('/').reverse().join('-'));
            
            if (dataInicio) {
                const inicio = new Date(dataInicio);
                if (dataOrdem < inicio) mostrar = false;
            }
            
            if (dataFim) {
                const fim = new Date(dataFim);
                if (dataOrdem > fim) mostrar = false;
            }
        }
        
        linha.style.display = mostrar ? '' : 'none';
    });
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    aplicarMascaras();
    
    // Configurar calculadora de valores
    const valorInputs = document.querySelectorAll('#valor_servico, #valor_pecas');
    valorInputs.forEach(input => {
        if (input) {
            input.addEventListener('input', calcularValores);
        }
    });
    
    // Configurar filtros
    const filtroInputs = document.querySelectorAll('#filtro_status, #filtro_cliente, #filtro_data_inicio, #filtro_data_fim');
    filtroInputs.forEach(input => {
        if (input) {
            input.addEventListener('change', filtrarOrdens);
        }
    });
    
    // Auto-complete para clientes
    const clienteInput = document.getElementById('cliente_search');
    if (clienteInput) {
        clienteInput.addEventListener('input', function(e) {
            const termo = e.target.value;
            if (termo.length < 2) return;
            
            fetch(`/sistema-os/includes/search_clientes.php?q=${termo}`)
                .then(response => response.json())
                .then(clientes => {
                    // Implementar dropdown de sugestões
                });
        });
    }
});

// Confirmações importantes
function confirmarExclusao() {
    return confirm('Tem certeza que deseja excluir este registro? Esta ação não pode ser desfeita.');
}

function confirmarConclusao() {
    return confirm('Deseja marcar esta ordem como concluída?');
}