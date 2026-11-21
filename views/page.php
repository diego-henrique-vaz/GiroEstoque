<?php
$titulos = ['entrar' => 'Acesse seu estoque', 'criar-conta' => 'Crie a conta da loja',
    'estoque' => 'Estoque', 'novo' => 'Novo produto', 'editar' => 'Editar produto',
    'produto' => $dados['produto']['nome'] ?? 'Produto', 'erro' => 'Operação indisponível'];
$produto = $dados['produto'] ?? null;
$comercio = $_SESSION['comercio'] ?? null;
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($titulos[$pagina]) ?> | GiroEstoque</title>
  <link rel="stylesheet" href="assets/app.css">
</head>
<body>
<header class="topo">
  <div class="container barra">
    <a class="marca" href="./"><span class="marca-simbolo" aria-hidden="true">G</span>GiroEstoque</a>
    <?php if ($comercio !== null): ?>
      <nav aria-label="Conta" class="acoes"><span><?= h($comercio) ?></span>
        <form method="post" action="<?= h(caminho('sair')) ?>"><?php csrf(); ?><button class="secundario">Sair</button></form>
      </nav>
    <?php endif; ?>
  </div>
</header>
<main class="container <?= in_array($pagina, ['entrar', 'criar-conta'], true) ? 'estreito' : '' ?>">
  <?php if (in_array($pagina, ['produto', 'novo', 'editar', 'erro'], true)): ?>
    <a class="voltar" href="<?= h($pagina === 'editar' && $produto ? caminho('produto', (int) $produto['id']) : './') ?>">← <?= $pagina === 'editar' ? 'Voltar ao produto' : ($comercio ? 'Voltar ao estoque' : 'Voltar ao início') ?></a>
  <?php endif; ?>
  <div class="cabecalho">
    <div><h1><?= h($titulos[$pagina]) ?></h1>
      <?php if ($pagina === 'estoque'): ?><p>Consulte saldos e encontre produtos que precisam de reposição.</p><?php endif; ?>
    </div>
    <?php if ($pagina === 'estoque'): ?><a class="botao" href="<?= h(caminho('novo')) ?>">Novo produto</a><?php endif; ?>
    <?php if ($pagina === 'produto'): ?><a class="botao secundario" href="<?= h(caminho('editar', (int) $produto['id'])) ?>">Editar produto</a><?php endif; ?>
  </div>
  <?php if (isset($dados['erro'])): ?><p class="aviso erro" role="alert"><?= h($dados['erro']) ?></p><?php endif; ?>
  <?php if ($mensagem !== null): ?><p class="aviso sucesso" role="status"><?= h($mensagem) ?></p><?php endif; ?>

  <?php if (in_array($pagina, ['entrar', 'criar-conta'], true)): ?>
    <form class="cartao formulario" method="post" action="<?= h(caminho($pagina)) ?>">
      <?php csrf(); ?>
      <?php if ($pagina === 'criar-conta'): ?>
        <label>Nome do comércio<input name="comercio" maxlength="100" required value="<?= h(campo('comercio')) ?>"></label>
      <?php endif; ?>
      <label>E-mail<input name="email" type="email" maxlength="254" autocomplete="username" required value="<?= h(campo('email')) ?>"></label>
      <label>Senha<input name="senha" type="password" autocomplete="<?= $pagina === 'criar-conta' ? 'new-password' : 'current-password' ?>" required></label>
      <?php if ($pagina === 'criar-conta'): ?><small>Use pelo menos 8 caracteres.</small><?php endif; ?>
      <button><?= $pagina === 'entrar' ? 'Entrar' : 'Criar conta' ?></button>
      <a href="<?= h(caminho($pagina === 'entrar' ? 'criar-conta' : 'entrar')) ?>"><?= $pagina === 'entrar' ? 'Criar conta da loja' : 'Já tenho uma conta' ?></a>
    </form>
  <?php elseif ($pagina === 'estoque'): ?>
    <?php if (empty($dados['produtos'])): ?><p class="cartao vazio">Cadastre o primeiro produto para começar.</p>
    <?php else: ?>
      <div class="cartao tabela"><table><caption>Produtos cadastrados</caption><thead><tr><th>Produto</th><th>Saldo</th><th>Mínimo</th><th>Situação</th></tr></thead><tbody>
        <?php foreach ($dados['produtos'] as $item): ?>
          <tr><td><a href="<?= h(caminho('produto', (int) $item['id'])) ?>"><?= h($item['nome']) ?></a></td>
            <td><?= h($item['saldo']) ?></td><td><?= h($item['estoque_minimo']) ?></td>
            <td><span class="estado <?= $item['saldo'] <= $item['estoque_minimo'] ? 'baixo' : 'em-dia' ?>"><?= $item['saldo'] <= $item['estoque_minimo'] ? 'Estoque baixo' : 'Em dia' ?></span></td></tr>
        <?php endforeach; ?>
      </tbody></table></div>
    <?php endif; ?>
  <?php elseif (in_array($pagina, ['novo', 'editar'], true)): ?>
    <form class="cartao formulario estreito" method="post" action="<?= h(caminho($pagina, isset($produto['id']) ? (int) $produto['id'] : null)) ?>">
      <?php csrf(); ?>
      <label>Nome do produto<input name="nome" maxlength="120" required value="<?= h(isset($dados['erro']) ? campo('nome') : ($produto['nome'] ?? '')) ?>"></label>
      <label>Estoque mínimo<input name="minimo" type="number" min="0" max="2147483647" required value="<?= h(isset($dados['erro']) ? campo('minimo') : ($produto['estoque_minimo'] ?? '0')) ?>"></label>
      <button>Salvar produto</button>
    </form>
  <?php elseif ($pagina === 'produto'): ?>
    <div class="grade">
      <section class="cartao resumo" aria-label="Saldo do produto">
        <h2>Saldo atual</h2><strong data-saldo><?= h($produto['saldo']) ?></strong>
        <p>Mínimo de <?= h($produto['estoque_minimo']) ?> unidades</p>
        <span class="estado <?= $produto['saldo'] <= $produto['estoque_minimo'] ? 'baixo' : 'em-dia' ?>"><?= $produto['saldo'] <= $produto['estoque_minimo'] ? 'Estoque baixo' : 'Em dia' ?></span>
      </section>
      <form class="cartao formulario" method="post" action="<?= h(caminho('movimentar', (int) $produto['id'])) ?>">
        <?php csrf(); ?><h2>Registrar movimentação</h2>
        <label>Tipo<select name="tipo"><option value="entrada">Entrada</option><option value="saida" <?= campo('tipo') === 'saida' ? 'selected' : '' ?>>Saída</option></select></label>
        <label>Quantidade<input name="quantidade" type="number" min="1" max="2147483647" required value="<?= h(campo('quantidade')) ?>"></label>
        <button>Registrar</button>
      </form>
    </div>
    <section class="cartao tabela historico">
      <h2>Histórico</h2>
      <?php if (empty($dados['historico'])): ?><p>Nenhuma movimentação registrada.</p>
      <?php else: ?><table><caption>Movimentações do produto</caption><thead><tr><th>Tipo</th><th>Quantidade</th><th>Saldo após</th><th>Data e hora</th></tr></thead><tbody>
        <?php foreach ($dados['historico'] as $mov): ?>
          <tr data-movimentacao><td><?= $mov['tipo'] === 'entrada' ? 'Entrada' : 'Saída' ?></td><td><?= h($mov['quantidade']) ?></td>
            <td><?= h($mov['saldo_apos']) ?></td><td><?= h(date('d/m/Y H:i', strtotime($mov['registrado_em']))) ?></td></tr>
        <?php endforeach; ?>
      </tbody></table><?php endif; ?>
    </section>
  <?php endif; ?>
</main>
</body>
</html>
