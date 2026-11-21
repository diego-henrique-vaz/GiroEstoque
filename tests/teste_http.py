"""Valida os formulários e a autenticação da aplicação com PHP e MariaDB locais."""

import http.cookiejar
import re
import secrets
import sys
from urllib.error import HTTPError
from urllib.parse import urlencode
from urllib.request import HTTPCookieProcessor, ProxyHandler, build_opener


class Sessao:
    def __init__(self, endereco):
        self.endereco = endereco.rstrip("/") + "/"
        self.navegador = build_opener(ProxyHandler({}), HTTPCookieProcessor(http.cookiejar.CookieJar()))

    def pagina(self, rota="", campos=None, status=200):
        dados = None if campos is None else urlencode(campos).encode()
        try:
            resposta = self.navegador.open(self.endereco + rota, dados, timeout=8)
        except HTTPError as erro:
            resposta = erro
        with resposta:
            html = resposta.read().decode("utf-8")
            assert resposta.code == status, (rota, resposta.code, status, html[:500])
            return html


def token(html):
    valor = re.search(r'name="csrf" value="([a-f0-9]+)"', html)
    assert valor, "Formulário sem token"
    return valor.group(1)


def verificar_produto(html, saldo, movimentos, situacao):
    assert f"data-saldo>{saldo}</strong>" in html, f"Saldo esperado: {saldo}"
    assert html.count("<tr data-movimentacao>") == movimentos, (saldo, movimentos)
    assert situacao in html, situacao


def criar_conta(sessao, nome, email, senha):
    rota = "?pagina=criar-conta"
    return sessao.pagina(rota, {
        "csrf": token(sessao.pagina(rota)), "comercio": nome, "email": email, "senha": senha,
    })


def executar(endereco):
    loja = Sessao(endereco)
    outra_loja = Sessao(endereco)
    assert "Acesse seu estoque" in outra_loja.pagina("?pagina=estoque")
    email = f"giro-{secrets.token_hex(5)}@example.test"
    senha = secrets.token_urlsafe(18)
    html = criar_conta(loja, "Mercearia Horizonte", email, senha)
    assert "Cadastre o primeiro produto" in html
    html = loja.pagina("?pagina=sair", {"csrf": token(html)})
    assert "Acesse seu estoque" in html
    html = loja.pagina("?pagina=entrar", {"csrf": token(html), "email": email, "senha": senha})
    assert "Mercearia Horizonte" in html and "Novo produto" in html
    print("OK conta, saída e nova autenticação")

    novo = "?pagina=novo"
    html = loja.pagina(novo, {"csrf": token(loja.pagina(novo)), "nome": "Café 500 g", "minimo": "5"})
    verificar_produto(html, 0, 0, "Estoque baixo")
    produto = re.search(r"pagina=editar&amp;id=([0-9]+)", html).group(1)
    editar = f"?pagina=editar&id={produto}"
    html = loja.pagina(editar, {"csrf": token(loja.pagina(editar)), "nome": "Café 500 g", "minimo": "6"})
    assert "Mínimo de 6 unidades" in html
    html = loja.pagina(editar, {"csrf": token(loja.pagina(editar)), "nome": "Café 500 g", "minimo": "5"})
    assert "Mínimo de 5 unidades" in html
    print("OK cadastro com saldo zero e edição")

    movimento = f"?pagina=movimentar&id={produto}"
    html = loja.pagina(movimento, {"csrf": token(html), "tipo": "entrada", "quantidade": "12"})
    verificar_produto(html, 12, 1, "Em dia")
    html = loja.pagina(movimento, {"csrf": token(html), "tipo": "saida", "quantidade": "8"})
    verificar_produto(html, 4, 2, "Estoque baixo")
    print("OK entrada 12, saída 8, alerta e histórico")

    html = loja.pagina(movimento, {"csrf": token(html), "tipo": "saida", "quantidade": "6"}, status=422)
    assert "Saldo insuficiente" in html
    verificar_produto(html, 4, 2, "Estoque baixo")
    html = loja.pagina(movimento, {"csrf": token(html), "tipo": "entrada", "quantidade": "0"}, status=422)
    assert "quantidade inteira positiva" in html
    verificar_produto(html, 4, 2, "Estoque baixo")
    loja.pagina(movimento, {"tipo": "entrada", "quantidade": "1"}, status=403)
    assert "Café 500 g" in loja.pagina() and "Estoque baixo" in loja.pagina()
    verificar_produto(loja.pagina(f"?pagina=produto&id={produto}"), 4, 2, "Estoque baixo")
    print("OK saída acima do saldo, quantidade zero e formulário sem token recusados")

    igualdade = loja.pagina(novo, {"csrf": token(loja.pagina(novo)), "nome": "Arroz", "minimo": "5"})
    arroz = re.search(r"pagina=editar&amp;id=([0-9]+)", igualdade).group(1)
    igualdade = loja.pagina(f"?pagina=movimentar&id={arroz}", {
        "csrf": token(igualdade), "tipo": "entrada", "quantidade": "5",
    })
    verificar_produto(igualdade, 5, 1, "Estoque baixo")
    print("OK alerta quando saldo é igual ao mínimo")
    igualdade = loja.pagina(f"?pagina=movimentar&id={arroz}", {
        "csrf": token(igualdade), "tipo": "saida", "quantidade": "5",
    })
    verificar_produto(igualdade, 0, 2, "Estoque baixo")
    print("OK saída igual ao saldo zera o estoque")

    html = criar_conta(outra_loja, "Loja vizinha", f"outra-{email}", senha)
    assert "Café 500 g" not in html
    html = outra_loja.pagina(f"?pagina=produto&id={produto}", status=404)
    assert "Produto indisponível" in html
    outra_loja.pagina(movimento, {"csrf": token(html), "tipo": "entrada", "quantidade": "1"}, status=404)
    verificar_produto(loja.pagina(f"?pagina=produto&id={produto}"), 4, 2, "Estoque baixo")
    print("OK isolamento de consulta e movimentação por conta")

    print(f"Verificação após reiniciar PHP: {endereco.rstrip('/')}/?pagina=produto&id={produto}")
    return loja, produto


if __name__ == "__main__":
    executar(sys.argv[1] if len(sys.argv) > 1 else "http://127.0.0.1:8000")
