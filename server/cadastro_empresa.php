<?php

include_once("conexao.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Coletar e sanitizar dados
        $nome_empresa = filter_input(INPUT_POST, 'nome_empresa', FILTER_SANITIZE_STRING);
        $cnpj_empresa = filter_input(INPUT_POST, 'cnpj_empresa', FILTER_SANITIZE_STRING);
        $endereco_empresa = filter_input(INPUT_POST, 'endereco_empresa', FILTER_SANITIZE_STRING);
        $email_empresa = filter_input(INPUT_POST, 'email_empresa', FILTER_SANITIZE_EMAIL);
        $telefone_empresa = filter_input(INPUT_POST, 'telefone_empresa', FILTER_SANITIZE_STRING);

        $senha_empresa = $_POST['senha_empresa'];

        // Verificações básicas
        if (!$nome_empresa || !$cnpj_empresa || !$endereco_empresa || !$email_empresa || !$telefone_empresa || !$senha_empresa) {
            echo "<script>alert('Preencha todos os campos obrigatórios!'); window.history.back();</script>";
            exit();
        }

        if (!filter_var($email_empresa, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('E-mail inválido!'); window.history.back();</script>";
            exit();
        }

        if (strlen($senha_empresa) < 6) {
            echo "<script>alert('A senha deve ter pelo menos 6 caracteres!'); window.history.back();</script>";
            exit();
        }

        // Verifica se o email já existe em empresa ou desenvolvedor
        $stmt = mysqli_prepare($conn, "
    SELECT email_empresa FROM empresa WHERE email_empresa = ?
    UNION
    SELECT email_desenvolvedor AS email_empresa FROM desenvolvedor WHERE email_desenvolvedor = ?");
        mysqli_stmt_bind_param($stmt, "ss", $email_empresa, $email_empresa);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            echo "<script>alert('Este email já foi cadastrado!'); window.history.back();</script>";
            exit();
        }

        mysqli_stmt_close($stmt);

        // Verifica se o CNPJ já existe
        $stmt = mysqli_prepare($conn, "SELECT id_empresa FROM empresa WHERE cnpj_empresa = ?");
        mysqli_stmt_bind_param($stmt, "s", $cnpj_empresa);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            echo "<script>alert('Este CNPJ já foi cadastrado!'); window.history.back();</script>";
            exit();
        }
        mysqli_stmt_close($stmt);

        // Cadastro
        $senha_hash = password_hash($senha_empresa, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO empresa (nome_empresa, cnpj_empresa, endereco_empresa, email_empresa, telefone_empresa, senha_empresa) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssss", $nome_empresa, $cnpj_empresa, $endereco_empresa, $email_empresa, $telefone_empresa, $senha_hash);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Empresa cadastrada com sucesso!'); window.location.href = '../templates/pglogin.html';</script>";
            exit();
        } else {
            echo "<script>alert('Erro ao cadastrar a empresa!'); window.history.back();</script>";
            exit();
        }

    } catch (Exception $e) {
        echo "<script>alert('" . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    } finally {
        if (isset($stmt))
            $stmt->close();
        $conn->close();
    }
} else {
    echo "<script>window.location.href = '../templates/cadastro_empresa.html';</script>";
    exit();
}

?>