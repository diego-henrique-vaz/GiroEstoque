# GiroEstoque | Do problema da loja à proposta

**Tópicos Especiais em Informática · Professor David Silva**  
Diego Henrique Pereira Gaig, Bruno Lealdine Rodrigues de Araujo, Luiz Guilherme Pelegatti da Rosa e Miguel França Vita.

Atividade da página 5 de *Do problema do cliente ao orçamento de um sistema*. O cenário, as respostas, os custos e os valores comerciais são didáticos. A confirmação e a aprovação do cliente estão pendentes na simulação.

## Parte A · Descoberta

> “Tenho uma loja pequena e preciso de um sistema para controlar minha loja. Quero saber quanto custa e quando fica pronto.”

| Pergunta para o cliente | Hipótese do grupo a validar |
| --- | --- |
| Como vocês registram hoje a chegada, a retirada e a contagem de produtos? | As entradas e saídas ficam em papel ou planilha, com atualização manual no fim do dia. |
| Onde vocês mais perdem tempo ou percebem erros no estoque? | Conferir o saldo e descobrir qual movimentação gerou a diferença são as tarefas mais demoradas. |
| Quem usaria o sistema, em quantas lojas e com qual volume aproximado? | Uma loja e uma pessoa responsável pelo estoque, com aproximadamente 200 produtos e 30 movimentações por dia. Os números servem ao levantamento inicial. |
| Quais operações precisam funcionar primeiro para a entrega ser útil? | Cadastrar itens, movimentar quantidades, consultar saldo, ver alerta de reposição e consultar o histórico. |
| O sistema precisa trocar dados com caixa, comércio eletrônico, WhatsApp ou outra ferramenta? | A primeira versão pode operar com lançamentos manuais. |
| Existe uma data em que o sistema precisa estar pronto e uma faixa de investimento? | A loja consegue analisar uma proposta inicial na faixa de até R$ 3.500 e aceita combinar o prazo após validar o escopo. |
| Quem confirma os requisitos, fornece os dados e dá o aceite? | A pessoa proprietária fornece os dados e aprova a entrega. |
| Como serão informados o estoque mínimo e a quantidade inicial? | A pessoa responsável define o mínimo por produto e lança uma entrada com a contagem inicial. |

### Três prioridades da conversa

1. **Onde aparecem os erros ou a perda de tempo?** Identifica se o problema é descobrir o saldo, rastrear a origem das alterações ou outra tarefa. Isso determina se o MVP resolve a necessidade central.
2. **Quais operações precisam funcionar primeiro?** Define as funções indispensáveis e o que poderá ficar para outra entrega. Muda as horas de desenvolvimento e teste.
3. **Quais integrações são indispensáveis?** Uma integração obrigatória exige levantamento do sistema externo, regras de troca de dados e testes próprios. Isso pode alterar prazo e preço.

**Confirmação para a loja.** “Hoje você registra entradas e saídas no papel ou em planilha. O problema é saber quanto sobrou e por que o saldo mudou. A primeira entrega deve permitir cadastrar produtos, registrar movimentações e consultar o saldo, os alertas e o histórico. Está correto?”

Uma planilha compartilhada pode atender a uma operação simples. O MVP tem valor quando a loja precisa ver saldo, alerta e histórico no mesmo painel e aplicar a regra de saída sem saldo em cada lançamento.

## Parte B · Primeira versão e aceite

**Problema em uma frase.** Pequenos comércios que registram entradas e saídas de forma dispersa têm dificuldade de conferir o saldo, entender diferenças e decidir o que repor.

**Público-alvo.** Pessoa proprietária ou responsável pelo estoque de uma mercearia, mercado de bairro ou pequena loja. Na proposta simulada, a loja usa uma conta e uma pessoa responsável confere o aceite.

| Funcionalidade | Limite e critério de aceite |
| --- | --- |
| Conta do comércio | Criar conta com dados fictícios, sair e entrar novamente. Uma segunda conta fica impedida de abrir produto da primeira. |
| Cadastro e edição de produtos | Cadastrar Café 500 g com mínimo 5 e verificar saldo inicial zero. Editar o nome ou o mínimo e conferir o valor salvo. |
| Registro de entradas e saídas | Lançar entrada 12 e saída 8 e conferir saldo 4. Recusar saída 6 ou quantidade zero e conservar o saldo. O registro inicial do estoque é feito como entrada. |
| Consulta de saldo e alerta | Encontrar o café no painel com saldo 4 e alerta **Estoque baixo**. Confirmar o alerta também quando o saldo é exatamente igual ao mínimo. |
| Histórico por produto | Conferir duas movimentações aceitas, suas quantidades e seus saldos após a operação. Confirmar que a tentativa recusada ficou fora do histórico. |

**Exclusões.** Loja virtual, integração com WhatsApp, integração com caixa ou emissão fiscal, importação em massa de cadastros, e permissões individuais para vários usuários da mesma loja. A interface é web.

**Insumos da loja.** Nome comercial e contato responsável, exemplos de produtos, quantidades iniciais e mínimos, acesso ao ambiente para instalação e uma pessoa disponível para conferir a entrega. Use dados fictícios na apresentação acadêmica.

**Aceite.** Apresentar o fluxo do café, entregar as instruções de execução e orientar a pessoa responsável. Divergências dos critérios entram como correção. Uma função nova passa por levantamento, estimativa de preço e prazo e aprovação por escrito.

## Parte C · Memória de cálculo e proposta

| Etapa | Horas |
| --- | ---: |
| Conversa, levantamento e escopo | 3 |
| Desenho de telas e fluxo | 4 |
| Desenvolvimento | 18 |
| Testes e ajustes | 7 |
| Instalação e orientação | 3 |
| Reuniões e acompanhamento | 2 |
| **Esforço total** | **37** |

**Custo por hora.** Remuneração mensal desejada de R$ 3.600,00 mais R$ 1.200,00 de despesas mensais, divididas por 80 horas faturáveis, resultam em **R$ 60,00/h**. As demais horas de trabalho cobrem atividades como atendimento inicial e organização. Os gastos diretamente ligados a esta implantação aparecem apenas na tabela seguinte.

| Componente | Valor |
| --- | ---: |
| Trabalho, 37 h × R$ 60,00 | R$ 2.220,00 |
| Despesas específicas, deslocamento e material de orientação | R$ 120,00 |
| Reserva para imprevistos do escopo | R$ 260,00 |
| Provisão didática de tributos e taxas | R$ 180,00 |
| Lucro planejado além da remuneração | R$ 620,00 |
| **Preço total da implantação** | **R$ 3.400,00** |

### Proposta para a Mercearia Horizonte

- **Cliente e responsável.** Mercearia Horizonte, representada pela pessoa proprietária. Nome da pessoa a confirmar.
- **Objetivo e entrega.** Implantar o GiroEstoque para uma loja, com conta própria, cadastro de produtos, movimentações, painel com alertas e histórico. As exclusões e critérios de aceite descritos na Parte B fazem parte da proposta.
- **Preço e pagamento.** R$ 3.400,00 pelo escopo definido após confirmação. R$ 1.360,00 no início (40%), R$ 1.020,00 na demonstração (30%) e R$ 1.020,00 após o aceite (30%). A soma é R$ 3.400,00.
- **Prazo.** 12 dias úteis depois da aprovação por escrito, do pagamento inicial e do recebimento dos insumos. Com cerca de quatro horas disponíveis por dia, 37 horas ocupam aproximadamente dez dias úteis. Dois dias ficam para agendamento, retorno e aceite.
- **Instalação e custos recorrentes.** A implantação prevê instalação local e orientação. Se a loja escolher hospedagem externa, o provedor e o custo mensal serão submetidos à aprovação em separado.
- **Suporte.** Durante 30 dias corridos após o aceite, a loja pode pedir por e-mail orientação de uso e correções dos critérios contratados. A loja indica um contato para relatar e confirmar os problemas. Evoluções passam por novo orçamento.
- **Acesso e código-fonte.** A implantação inclui acesso ao sistema e instruções de uso. Condições comerciais de entrega e licenciamento do código-fonte seriam negociadas à parte. O código acadêmico segue as regras de entrega da disciplina.
- **Validade e aprovação.** Dez dias corridos a partir do envio, após validar as hipóteses de escopo. Formalizar o aceite da proposta por escrito antes de iniciar uma contratação real.

### Oferta recorrente do SaaS

A alternativa comercial é uma assinatura de **R$ 29,90 por mês por loja**, com o acesso pelo navegador e a hospedagem inclusa. É uma hipótese para validação com lojistas. Num cenário de 12 lojas pagantes e R$ 180,00 mensais de infraestrutura compartilhada, a parcela de infraestrutura seria R$ 15,00 por loja. A hipótese reserva R$ 6,00 por loja para atendimento, R$ 3,00 para tributos e taxas e R$ 5,90 de contribuição para outros custos e resultado. A cobrança recorrente, a operação pública e o atendimento fazem parte da oferta futura. A proposta de implantação de R$ 3.400,00 representa uma contratação sob escopo próprio.

## Parte D · Pedido extra

> A loja virtual e a integração com WhatsApp ficam fora desta proposta.  
> Podemos conversar sobre o checkout e as mensagens que vocês precisam.  
> Com os requisitos definidos, envio um orçamento separado com preço e prazo para aprovação.

**Fechamento individual, sugestão para adaptação.** A necessidade de integrar o sistema ao caixa mudaria mais o orçamento porque acrescentaria a troca de dados e testes com o sistema da loja.

### Roteiro do exercício comercial · Até 2 minutos

| Tempo | Fala sugerida |
| --- | --- |
| 0:00–0:30 | “A loja pediu um sistema genérico. Começamos levantando onde aparecem os erros, o que precisa funcionar primeiro e se uma integração é indispensável. Assumimos uma única loja com lançamentos manuais.” |
| 0:30–0:55 | “A primeira entrega cobre acesso da loja, cadastro, movimentação, consulta de saldo com alerta e histórico. Conferimos com um produto que recebe 12 unidades, tem uma saída de 8 e recusa uma retirada de 6 quando o saldo é 4.” |
| 0:55–1:25 | “Estimamos 37 horas a 60 reais por hora. Com as despesas do projeto, a reserva, a provisão e o lucro, o valor fica em 3.400 reais, pagos em três marcos. São 12 dias úteis após aprovação, primeiro pagamento e recebimento dos dados.” |
| 1:25–1:55 | “Loja virtual e WhatsApp exigem levantar requisitos e estimar um novo prazo e preço. Como oferta futura, a assinatura de 29,90 por loja é uma hipótese que testaríamos com os lojistas.” |
