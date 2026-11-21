<?php

declare(strict_types=1);

function iniciarSessao(): void
{
    ini_set('display_errors', '0');
    ini_set('session.use_strict_mode', '1');
    if (!session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax',
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'])) {
        http_response_code(500);
        exit('Não foi possível iniciar a sessão.');
    }
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function autenticar(int $id, string $comercio): void
{
    session_regenerate_id(true);
    $_SESSION = ['usuario_id' => $id, 'comercio' => $comercio, 'csrf' => bin2hex(random_bytes(32))];
}

function encerrarSessao(): void
{
    $_SESSION = [];
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 3600, 'path' => $cookie['path'], 'domain' => $cookie['domain'],
        'secure' => $cookie['secure'], 'httponly' => $cookie['httponly'], 'samesite' => 'Lax',
    ]);
    session_destroy();
}

function redirecionar(string $url = './'): never
{
    header('Location: ' . $url, true, 303);
    exit;
}

function caminho(string $pagina, ?int $id = null): string
{
    return '?' . http_build_query(['pagina' => $pagina, 'id' => $id]);
}

function campo(string $chave): string
{
    return is_string($_POST[$chave] ?? null) ? $_POST[$chave] : '';
}

function inteiro(string $valor, int $minimo, int $maximo = 2_147_483_647): ?int
{
    $numero = filter_var($valor, FILTER_VALIDATE_INT, ['options' => [
        'min_range' => $minimo, 'max_range' => $maximo,
    ]]);
    return $numero === false ? null : $numero;
}

function texto(string $valor, int $limite, string $rotulo): string
{
    $valor = trim($valor);
    if ($valor === '' || mb_strlen($valor) > $limite) {
        throw new DomainException("Informe $rotulo com até $limite caracteres.");
    }
    return $valor;
}

function h(string|int $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf(): void
{
    echo '<input type="hidden" name="csrf" value="' . h($_SESSION['csrf']) . '">';
}

function exibir(string $pagina, array $dados = [], int $status = 200): never
{
    http_response_code($status);
    $mensagem = $_SESSION['mensagem'] ?? null;
    unset($_SESSION['mensagem']);
    require __DIR__ . '/../views/page.php';
    exit;
}
