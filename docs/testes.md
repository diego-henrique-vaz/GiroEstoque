# Testes e critérios de aceite

**Execução em 21/11/2026.** Windows, PHP 8.2.12 com `pdo_mysql` e `mbstring` e MariaDB 10.4.32 com InnoDB. Foi criado um banco exclusivo de testes e importado `database/schema.sql`. O servidor do teste usou `127.0.0.1:8137` e o banco, `127.0.0.1:33179`. Um diretório gravável foi configurado para as sessões do PHP depois que a configuração padrão do XAMPP retornou `Permission denied` no ensaio anterior. A primeira reexecução tentou usar um banco temporário da sessão anterior e recebeu erro de conexão. Após inicializar uma instância isolada, a suíte foi repetida com sucesso.

| Caso | Pré-condição e ação | Resultado esperado | Resultado observado |
| --- | --- | --- | --- |
| Conta | Criar a Mercearia Horizonte, sair e entrar novamente. | O painel abre com a conta correta e inicialmente vazio. | Aprovado por HTTP. |
| Acesso sem sessão | Abrir o painel em uma sessão nova. | O navegador é redirecionado para a página de login. | Aprovado por HTTP. |
| Cadastro e edição | Na conta ativa, cadastrar Café 500 g com mínimo 5. Trocar para 6 e retornar para 5. | O saldo inicial é 0 e cada mínimo editado aparece no detalhe. | Aprovado por HTTP. |
| Movimentação | Com saldo 0, lançar entrada 12 e saída 8. | O detalhe mostra saldo 4 e duas linhas no histórico. O painel mostra alerta. | Aprovado por HTTP. |
| Limite do saldo | Com saldo 4, tentar saída 6. | Recusa, saldo 4 e as mesmas duas linhas. | Aprovado por HTTP. Retornou 422. |
| Dados inválidos | Enviar quantidade zero e, separadamente, enviar o formulário sem token. | Recusar ambas as solicitações e preservar os dados. | Aprovado por HTTP. Retornou 422 e 403, respectivamente. |
| Limite do alerta | Cadastrar outro produto com mínimo 5 e lançar entrada 5. | O alerta aparece quando saldo e mínimo são iguais. | Aprovado por HTTP. |
| Saída igual ao saldo | No segundo produto, retirar as 5 unidades disponíveis. | A saída é aceita, o saldo chega a 0 e o histórico fica com dois registros. | Aprovado por HTTP. |
| Separação por conta | Entrar em outra conta e abrir ou tentar movimentar o produto original. | Recusar as duas ações e preservar os registros da primeira conta. | Aprovado por HTTP. Retornou 404 nas duas rotas. |
| Persistência | Depois do fluxo do café, reiniciar o processo PHP e abrir o mesmo produto. | O saldo 4, o alerta e as duas movimentações permanecem. | Aprovado por HTTP após reinício real do PHP. |
| Demonstração no navegador | Criar uma loja fictícia e cadastrar Café 500 g com mínimo 5. Registrar entrada 12, saída 8 e tentar saída 6. | Ver saldo 0, depois 12 e depois 4. Conferir o alerta e dois registros após a recusa. | Aprovado na interface. Uma consulta no MariaDB confirmou saldo 4, mínimo 5 e dois registros. |
| Reversão | Com saldo 7 e histórico de uma entrada, usar um gatilho no banco de testes para falhar na gravação do próximo movimento. | A transação preserva o saldo 7 e a única linha anterior. | Aprovado em MariaDB pela função real do PHP. |
| Concorrência | Com saldo 7, iniciar duas conexões PHP que tentam retirar 5 unidades e liberar a linha bloqueada. | Uma retirada é aceita, uma é recusada. O saldo final é 2 e o histórico tem dois registros contando a entrada inicial. | Aprovado com dois processos PHP e o mesmo MariaDB. |

## Comandos

Na raiz, depois de criar o banco, configurar o PHP e iniciar o servidor, execute:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & $php -l $_.FullName }
python .\tests\teste_http.py http://127.0.0.1:8137
& $php .\tests\teste_transacao.php
```

Os arquivos PHP passaram no lint. O teste HTTP usa os formulários e as rotas da aplicação via servidor PHP com MariaDB. O teste transacional precisa de um banco isolado com nome terminado em `_teste` e de permissão para criar e remover seu próprio gatilho temporário. A sessão com diretório próprio permitiu confirmar a persistência ao reiniciar o servidor.

## Conferência da entrega

| Requisito | Onde conferir |
| --- | --- |
| Apostila, página 5, partes A a D | [Proposta comercial](proposta.md), com as oito perguntas, hipóteses, aceite, conta, proposta e resposta ao extra. |
| Operação completa e persistência | Interface, `public/index.php`, `app/repository.php`, `database/schema.sql` e casos acima. |
| Recriação da demonstração e da instalação | [README](../README.md), nas seções de execução, demonstração e testes. |
| Relatório e apresentação | O PDF acompanha a entrega acadêmica. O roteiro cobre o problema, a demonstração, a arquitetura, os testes e o uso de IA. |

O tempo oficial, o canal de entrega e a correspondência das técnicas com o conteúdo de aula dependem do enunciado e da confirmação do grupo.
