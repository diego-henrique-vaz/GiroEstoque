# GiroEstoque

MVP de um SaaS de controle de estoque para mercearias e pequenos comércios. Cada comércio tem uma conta própria. O sistema cadastra produtos, registra entradas e saídas, mostra alertas de estoque baixo e mantém o histórico por produto.

**Integrantes**

- Diego Henrique Pereira Gaig
- Bruno Lealdine Rodrigues de Araujo
- Luiz Guilherme Pelegatti da Rosa
- Miguel França Vita

Trabalho de Tópicos Especiais em Informática, professor David Silva.

## Tecnologias

PHP 8.2+, extensões `pdo_mysql` e `mbstring`, MariaDB com InnoDB, HTML e CSS. O navegador envia os formulários ao PHP, que valida as entradas e usa PDO para ler e gravar no MariaDB. Os arquivos de configuração ficam fora da raiz pública.

## Executar no Windows com XAMPP

1. Inicie o serviço **MySQL** do XAMPP, que usa MariaDB. Abra o PowerShell na pasta que contém este README. Ajuste `C:\xampp` se sua instalação estiver em outro caminho.
2. Crie um banco e um usuário exclusivos para o GiroEstoque. Escolha uma senha própria e substitua `SENHA_LOCAL_ESCOLHIDA`. Se os nomes já existirem, escolha outros para a instalação nova. Entre com a conta administrativa do seu MariaDB. O parâmetro `-p` solicita a senha no terminal. Em uma instalação local já configurada com a conta administrativa sem senha, retire esse parâmetro.

   ```powershell
   $xampp = 'C:\xampp'
   $mysql = Join-Path $xampp 'mysql\bin\mysql.exe'
   $php = Join-Path $xampp 'php\php.exe'
   & $mysql --protocol=TCP -h 127.0.0.1 -P 3306 -u root -p
   ```

   ```sql
   CREATE DATABASE giroestoque CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'giro_app'@'127.0.0.1' IDENTIFIED BY 'SENHA_LOCAL_ESCOLHIDA';
   GRANT SELECT, INSERT, UPDATE ON giroestoque.* TO 'giro_app'@'127.0.0.1';
   EXIT;
   ```

3. Importe o esquema e crie a configuração local. Ajuste a porta nos comandos e no arquivo se o MariaDB estiver usando outra.

   ```powershell
   Get-Content -Raw -Encoding UTF8 .\database\schema.sql |
       & $mysql --protocol=TCP -h 127.0.0.1 -P 3306 -u root -p --default-character-set=utf8mb4 giroestoque
   if (-not (Test-Path .\config\local.php)) {
       Copy-Item .\config\example.php .\config\local.php
   }
   ```

4. Edite `config/local.php` para usar o banco, o usuário e a senha criados localmente. Depois execute:

   ```powershell
   & $php -m | Select-String '^(pdo_mysql|mbstring)$'
   & $php -S 127.0.0.1:8000 -t public
   ```

5. Abra [http://127.0.0.1:8000](http://127.0.0.1:8000). Mantenha o terminal do PHP aberto. Se o PHP indicar falta de permissão para gravar sessões, use uma pasta temporária acessível. No PowerShell, execute `& $php -d "session.save_path=$env:TEMP" -S 127.0.0.1:8000 -t public`. Em outra plataforma, importe o mesmo esquema e execute `php -S 127.0.0.1:8000 -t public` com as mesmas extensões.

## Dados da demonstração

Crie a conta fictícia **Mercearia Horizonte** com `mercearia@example.test` e uma senha escolhida na hora. Em uma conta já usada, entre com a senha anterior ou crie outra conta fictícia com e-mail distinto. Cadastre **Café 500 g**, mínimo `5`. Registre entrada `12`, saída `8` e tente saída `6`. O saldo final é `4`, com estoque baixo e dois registros no histórico. A saída de `6` é recusada. O banco deve manter o saldo e o histórico após reiniciar o servidor PHP.

## Testes

Execute contra um banco e um servidor locais dedicados a testes. O teste HTTP cria contas e produtos fictícios a cada execução. O teste de transação precisa do usuário de testes com permissões para criar e remover um gatilho nesse banco isolado. A conta da aplicação continua com as permissões limitadas descritas na instalação normal.

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & $php -l $_.FullName }
python .\tests\teste_http.py http://127.0.0.1:8000
& $php .\tests\teste_transacao.php
```

Consulte [os resultados e critérios de aceite](docs/testes.md) e [a proposta comercial](docs/proposta.md).
