<?php

declare(strict_types=1);

require __DIR__ . '/../app/http.php';
require __DIR__ . '/../app/repository.php';

iniciarSessao();
header('Cache-Control: no-store');
$usuarioId = $_SESSION['usuario_id'] ?? null;
$pagina = $_GET['pagina'] ?? ($usuarioId === null ? 'entrar' : 'estoque');
$id = is_string($_GET['id'] ?? null) ? inteiro($_GET['id'], 1, 4_294_967_295) : null;
$anonimas = ['entrar', 'criar-conta'];
$privadas = ['estoque', 'novo', 'produto', 'editar', 'movimentar', 'sair'];
$post = $_SERVER['REQUEST_METHOD'] === 'POST';

if ($post && !hash_equals($_SESSION['csrf'], campo('csrf'))) {
    exibir('erro', ['erro' => 'Sua sessão mudou. Abra a página novamente.'], 403);
}
if (in_array($pagina, $privadas, true) && $usuarioId === null) {
    redirecionar(caminho('entrar'));
}
if (in_array($pagina, $anonimas, true) && $usuarioId !== null) {
    redirecionar();
}
if (!in_array($pagina, [...$anonimas, ...$privadas], true)) {
    exibir('erro', ['erro' => 'Página indisponível.'], 404);
}
if (!$post && in_array($pagina, ['movimentar', 'sair'], true)) {
    header('Allow: POST');
    exibir('erro', ['erro' => 'Abra o formulário para executar esta ação.'], 405);
}
if ($pagina === 'sair') {
    encerrarSessao();
    redirecionar();
}
if (!$post && (in_array($pagina, $anonimas, true) || $pagina === 'novo')) {
    exibir($pagina);
}

try {
    $config = __DIR__ . '/../config/local.php';
    if (!is_file($config)) {
        throw new RuntimeException('Crie config/local.php a partir de config/example.php.');
    }
    $db = conectar(require $config);

    if ($post && in_array($pagina, $anonimas, true)) {
        $email = mb_strtolower(trim(campo('email')));
        $senha = campo('senha');
        if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Informe um e-mail válido.');
        }
        if ($pagina === 'criar-conta') {
            $comercio = texto(campo('comercio'), 100, 'o nome do comércio');
            if (strlen($senha) < 8 || strlen($senha) > 72 || str_contains($senha, "\0")) {
                throw new DomainException('Informe uma senha de 8 a 72 bytes, sem caracteres nulos.');
            }
            autenticar(criarUsuario($db, $comercio, $email, $senha), $comercio);
        } else {
            $usuario = buscarUsuario($db, $email);
            if ($usuario === null || strlen($senha) > 72 || str_contains($senha, "\0") ||
                !password_verify($senha, $usuario['senha_hash'])) {
                throw new DomainException('E-mail ou senha inválidos.');
            }
            autenticar((int) $usuario['id'], $usuario['comercio']);
        }
        redirecionar();
    }

    if ($pagina === 'estoque') {
        exibir('estoque', ['produtos' => listarProdutos($db, $usuarioId)]);
    }

    $produto = $pagina === 'novo' || $id === null ? null : buscarProduto($db, $usuarioId, $id);
    if ($pagina !== 'novo' && $produto === null) {
        exibir('erro', ['erro' => 'Produto indisponível.'], 404);
    }

    if ($post && in_array($pagina, ['novo', 'editar'], true)) {
        $nome = texto(campo('nome'), 120, 'o nome do produto');
        $minimo = inteiro(campo('minimo'), 0);
        if ($minimo === null) {
            throw new DomainException('Informe um estoque mínimo inteiro a partir de zero.');
        }
        $produtoId = salvarProduto($db, $usuarioId, $nome, $minimo, $pagina === 'novo' ? null : $id);
        $_SESSION['mensagem'] = 'Produto salvo.';
        redirecionar(caminho('produto', $produtoId));
    }
    if ($pagina === 'movimentar') {
        registrarMovimentacao($db, $usuarioId, $id, campo('tipo'), inteiro(campo('quantidade'), 1) ?? 0);
        $_SESSION['mensagem'] = 'Movimentação registrada.';
        redirecionar(caminho('produto', $id));
    }
    if ($pagina === 'editar') {
        exibir('editar', ['produto' => $produto]);
    }
    exibir('produto', ['produto' => $produto, 'historico' => listarMovimentacoes($db, $usuarioId, $id)]);
} catch (DomainException $erro) {
    $dados = ['erro' => $erro->getMessage()];
    if ($pagina === 'movimentar') {
        $dados['produto'] = buscarProduto($db, $usuarioId, $id);
        $dados['historico'] = listarMovimentacoes($db, $usuarioId, $id);
        exibir('produto', $dados, 422);
    }
    $dados['produto'] = $produto ?? null;
    exibir($pagina, $dados, 422);
} catch (Throwable $erro) {
    error_log($erro->__toString());
    exibir('erro', ['erro' => 'Não foi possível concluir. Confira a configuração e tente novamente.'], 500);
}
