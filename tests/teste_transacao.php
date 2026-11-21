<?php

declare(strict_types=1);

require __DIR__ . '/../app/repository.php';
$config = require __DIR__ . '/../config/local.php';
if (!str_ends_with($config['database'], '_teste')) {
    throw new RuntimeException('Use um banco dedicado com nome terminado em _teste.');
}
$db = conectar($config);

if (($argv[1] ?? '') === 'retirar') {
    echo "PRONTO\n";
    flush();
    try {
        registrarMovimentacao($db, (int) $argv[2], (int) $argv[3], 'saida', 5);
        echo "ACEITA\n";
    } catch (DomainException $erro) {
        echo $erro->getMessage() . "\n";
    }
    exit;
}

function conferir(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        throw new RuntimeException($mensagem);
    }
}

$usuario = criarUsuario($db, 'Loja de teste de transação', bin2hex(random_bytes(8)) . '@example.test', bin2hex(random_bytes(12)));
$produto = salvarProduto($db, $usuario, 'Produto de concorrência', 2, null);
registrarMovimentacao($db, $usuario, $produto, 'entrada', 7);

$db->exec("CREATE TRIGGER giro_teste_bloquear_historico BEFORE INSERT ON movimentacoes
    FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falha proposital do teste' ");
try {
    try {
        registrarMovimentacao($db, $usuario, $produto, 'entrada', 2);
        throw new RuntimeException('O gatilho de teste deveria ter recusado a gravação.');
    } catch (PDOException $erro) {
        conferir($erro->getCode() === '45000', $erro->getMessage());
    }
    conferir((int) buscarProduto($db, $usuario, $produto)['saldo'] === 7, 'Saldo alterado após o rollback.');
    conferir(count(listarMovimentacoes($db, $usuario, $produto)) === 1, 'Histórico alterado após o rollback.');
    echo "OK rollback após falha real na inclusão do histórico (saldo 7, um registro)\n";
} finally {
    $db->exec('DROP TRIGGER giro_teste_bloquear_historico');
}

$db->beginTransaction();
buscarProduto($db, $usuario, $produto, bloquear: true);
$processos = [];
try {
    for ($i = 0; $i < 2; $i++) {
        $processo = proc_open([PHP_BINARY, __FILE__, 'retirar', (string) $usuario, (string) $produto],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $canais);
        conferir(is_resource($processo), 'Falha ao iniciar segunda conexão.');
        $processos[] = [$processo, $canais];
        conferir(trim((string) fgets($canais[1])) === 'PRONTO', 'O processo de retirada não iniciou.');
    }
    usleep(150_000);
    foreach ($processos as [$processo]) {
        conferir(proc_get_status($processo)['running'], 'Uma retirada passou enquanto a linha estava bloqueada.');
    }
} finally {
    $db->commit();
}

$respostas = [];
foreach ($processos as [$processo, $canais]) {
    $respostas[] = trim(stream_get_contents($canais[1]));
    $erro = stream_get_contents($canais[2]);
    fclose($canais[1]);
    fclose($canais[2]);
    conferir(proc_close($processo) === 0, $erro);
}
sort($respostas);
conferir($respostas === ['ACEITA', 'Saldo insuficiente para registrar esta saída.'], 'Resultado inesperado nas duas retiradas.');
conferir((int) buscarProduto($db, $usuario, $produto)['saldo'] === 2, 'Saldo incorreto após concorrência.');
conferir(count(listarMovimentacoes($db, $usuario, $produto)) === 2, 'Histórico incorreto após concorrência.');
echo "OK duas saídas concorrentes de 5 em saldo 7 (uma aceita, uma recusada, saldo 2)\n";
