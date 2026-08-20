<?php

require_once 'config.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não permitido.'
    ]);

    exit;
}

try {

    // =====================================================
    // 1. Receber os dados do formulário
    // =====================================================

    $nome = trim($_POST['nome'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmacaoSenha = $_POST['confirmacao-senha'] ?? '';


    // =====================================================
    // 2. Validar os campos obrigatórios
    // =====================================================

    if (
        empty($nome) ||
        empty($telefone) ||
        empty($email) ||
        empty($endereco) ||
        empty($senha) ||
        empty($confirmacaoSenha)
    ) {

        http_response_code(400);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Preencha todos os campos obrigatórios.'
        ]);

        exit;
    }


    // =====================================================
    // 3. Validar e-mail
    // =====================================================

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        http_response_code(400);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Informe um e-mail válido.'
        ]);

        exit;
    }


    // =====================================================
    // 4. Confirmar senha
    // =====================================================

    if ($senha !== $confirmacaoSenha) {

        http_response_code(400);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'As senhas não coincidem.'
        ]);

        exit;
    }


    // =====================================================
    // 5. Validar tamanho mínimo da senha
    // =====================================================

    if (strlen($senha) < 8) {

        http_response_code(400);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'A senha deve possuir pelo menos 8 caracteres.'
        ]);

        exit;
    }


    // =====================================================
    // 6. Verificar se o e-mail já está cadastrado
    // =====================================================

    $stmtCheck = $pdo->prepare("
        SELECT id
        FROM clientes
        WHERE email = :email
        LIMIT 1
    ");

    $stmtCheck->execute([
    ':email' => $email
    ]);

    if ($stmtCheck->fetch()) {

        http_response_code(409);

        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Este e-mail já está cadastrado.'
        ]);

        exit;
    }


    // =====================================================
    // 7. Criar hash seguro da senha
    // =====================================================

    $senhaHash = password_hash(
        $senha,
        PASSWORD_DEFAULT
    );


    // =====================================================
    // 8. Iniciar transação
    // =====================================================

    $pdo->beginTransaction();


    // =====================================================
    // 9. Inserir cliente
    // =====================================================

    $stmtClient = $pdo->prepare("
        INSERT INTO clientes (
            nome,
            telefone,
            email,
            endereco,
            senha_hash
        )
        VALUES (
            :nome,
            :telefone,
            :email,
            :endereco,
            :senha_hash
        )
    ");

    $stmtClient->execute([
    ':nome' => $nome,
    ':telefone' => $telefone,
    ':email' => $email,
    ':endereco' => $endereco,
    ':senha_hash' => $senhaHash
    ]);


    // =====================================================
    // 10. Recuperar ID do cliente criado
    // =====================================================

    $idCliente = $pdo->lastInsertId();


    // =====================================================
    // 11. Confirmar transação
    // =====================================================

    $pdo->commit();


    // =====================================================
    // 12. Resposta para o frontend
    // =====================================================

    http_response_code(201);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Conta criada com sucesso!',
        'cliente_id' => $idCliente
    ]);

} catch (PDOException $e) {

    // Se uma transação estiver aberta, desfaz
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao criar a conta.'
    ]);

    // Durante desenvolvimento você pode registrar o erro:
    error_log($e->getMessage());

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Ocorreu um erro inesperado.'
    ]);

    error_log($e->getMessage());
}
?>