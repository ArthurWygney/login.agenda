// Aguarda o documento carregar
document.addEventListener('DOMContentLoaded', () => {
    
    const formCliente = document.getElementById('formCliente');

    // Escuta o evento de envio do formulário
    formCliente.addEventListener('submit', function(event) {
        event.preventDefault(); // Impede a página de recarregar

        // Pega todos os dados preenchidos no formulário
        const formData = new FormData(this);

        // Envia os dados para o arquivo salvar.php usando a API Fetch
        fetch('salvar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) // Converte a resposta do PHP para JSON
        .then(data => {
            if (data.sucesso === true) {
                alert(data.mensagem); // Mostra mensagem de sucesso
                formCliente.reset(); // Limpa os campos do formulário
                // Aqui você pode chamar a função para atualizar a tabela depois
            } else {
                alert('Erro: ' + data.mensagem); // Mostra o erro retornado pelo PHP
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            alert('Erro ao tentar se comunicar com o servidor.');
        });
    });
});

// Função que você já chamou no HTML para alternar PF/PJ
function alternarTipoPessoa() {
    const tipo = document.getElementById('tipo_pessoa').value;
    const lblCpfCnpj = document.getElementById('lblCpfCnpj');
    const lblRgIe = document.getElementById('lblRgIe');
    const lblNome = document.getElementById('lblNome');

    if (tipo === 'PJ') {
        lblCpfCnpj.textContent = 'CNPJ';
        lblRgIe.textContent = 'Inscrição Estadual';
        lblNome.textContent = 'Razão Social';
    } else {
        lblCpfCnpj.textContent = 'CPF';
        lblRgIe.textContent = 'RG';
        lblNome.textContent = 'Nome Completo';
    }
}