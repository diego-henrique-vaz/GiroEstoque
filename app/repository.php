<?php

declare(strict_types=1);

function conectar(array $config): PDO
{
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $config['host'], $config['port'], $config['database']);

    return new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function buscarUsuario(PDO $db, string $email): ?array
{
    $sql = $db->prepare('SELECT id, comercio, senha_hash FROM usuarios WHERE email = ?');
    $sql->execute([$email]);
    return $sql->fetch() ?: null;
}

function criarUsuario(PDO $db, string $comercio, string $email, string $senha): int
{
    $sql = $db->prepare('INSERT INTO usuarios (comercio, email, senha_hash) VALUES (?, ?, ?)');
    try {
        $sql->execute([$comercio, $email, password_hash($senha, PASSWORD_DEFAULT)]);
    } catch (PDOException $erro) {
        if (($erro->errorInfo[1] ?? null) === 1062) {
            throw new DomainException('Este e-mail já tem uma conta.', previous: $erro);
        }
        throw $erro;
    }
    return (int) $db->lastInsertId();
}

function listarProdutos(PDO $db, int $usuarioId): array
{
    $sql = $db->prepare('SELECT id, nome, estoque_minimo, saldo FROM produtos
        WHERE usuario_id = ? ORDER BY saldo <= estoque_minimo DESC, nome, id');
    $sql->execute([$usuarioId]);
    return $sql->fetchAll();
}

function buscarProduto(PDO $db, int $usuarioId, int $produtoId, bool $bloquear = false): ?array
{
    $consulta = 'SELECT id, nome, estoque_minimo, saldo FROM produtos WHERE id = ? AND usuario_id = ?';
    $sql = $db->prepare($consulta . ($bloquear ? ' FOR UPDATE' : ''));
    $sql->execute([$produtoId, $usuarioId]);
    return $sql->fetch() ?: null;
}

function salvarProduto(PDO $db, int $usuarioId, string $nome, int $minimo, ?int $id): int
{
    if ($id === null) {
        $sql = $db->prepare('INSERT INTO produtos (usuario_id, nome, estoque_minimo) VALUES (?, ?, ?)');
        $sql->execute([$usuarioId, $nome, $minimo]);
        return (int) $db->lastInsertId();
    }

    $sql = $db->prepare('UPDATE produtos SET nome = ?, estoque_minimo = ? WHERE id = ? AND usuario_id = ?');
    $sql->execute([$nome, $minimo, $id, $usuarioId]);
    return $id;
}

function listarMovimentacoes(PDO $db, int $usuarioId, int $produtoId): array
{
    $sql = $db->prepare('SELECT m.tipo, m.quantidade, m.saldo_apos, m.registrado_em
        FROM movimentacoes m JOIN produtos p ON p.id = m.produto_id
        WHERE p.id = ? AND p.usuario_id = ? ORDER BY m.id DESC');
    $sql->execute([$produtoId, $usuarioId]);
    return $sql->fetchAll();
}

function registrarMovimentacao(PDO $db, int $usuarioId, int $produtoId, string $tipo, int $quantidade): int
{
    $limite = 2_147_483_647;
    if (!in_array($tipo, ['entrada', 'saida'], true) || $quantidade < 1 || $quantidade > $limite) {
        throw new DomainException('Informe um tipo válido e uma quantidade inteira positiva.');
    }

    $db->beginTransaction();
    try {
        $produto = buscarProduto($db, $usuarioId, $produtoId, bloquear: true);
        if ($produto === null) {
            throw new DomainException('Produto indisponível.');
        }

        $saldo = (int) $produto['saldo'];
        if ($tipo === 'saida' && $quantidade > $saldo) {
            throw new DomainException('Saldo insuficiente para registrar esta saída.');
        }
        if ($tipo === 'entrada' && $quantidade > $limite - $saldo) {
            throw new DomainException('A entrada ultrapassa o limite de saldo do produto.');
        }

        $novoSaldo = $tipo === 'entrada' ? $saldo + $quantidade : $saldo - $quantidade;
        $atualizar = $db->prepare('UPDATE produtos SET saldo = ? WHERE id = ? AND usuario_id = ?');
        $atualizar->execute([$novoSaldo, $produtoId, $usuarioId]);
        $historico = $db->prepare('INSERT INTO movimentacoes (produto_id, tipo, quantidade, saldo_apos)
            VALUES (?, ?, ?, ?)');
        $historico->execute([$produtoId, $tipo, $quantidade, $novoSaldo]);
        $db->commit();
        return $novoSaldo;
    } catch (Throwable $erro) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $erro;
    }
}
