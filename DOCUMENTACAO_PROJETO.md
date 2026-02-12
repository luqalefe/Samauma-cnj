# Documentação do Projeto: Samaúma CNJ (Prêmio CNJ de Qualidade)

## 1. Visão Geral
Este projeto é um sistema profissional de gestão e acompanhamento das metas do **Prêmio CNJ de Qualidade**, desenvolvido para atender às necessidades específicas dos **Tribunais de Justiça (TJs)** e dos **Tribunais Regionais Eleitorais (TREs)**.

O objetivo é fornecer uma plataforma centralizada onde cada tribunal possa acompanhar seu desempenho em tempo real, importando as metas dos editais oficiais, configurando regras de cálculo específicas para seu segmento de justiça e visualizando o progresso através de painéis gerenciais.

## 2. Segmentação e Aplicabilidade (TJ e TRE)
O sistema foi arquitetado para ser flexível e atender a múltiplos segmentos de justiça simultaneamente, respeitando as particularidades de cada um conforme os manuais do CNJ.

### 2.1 Suporte Multi-Segmento
A estrutura do banco de dados permite diferenciar quais regras se aplicam a qual tribunal:
*   **Tribunais de Justiça (Estaduais):** O sistema suporta a carga de regras específicas para a Justiça Estadual, incluindo as metas de produtividade e governança exclusivas deste segmento.
*   **Tribunais Regionais Eleitorais (Eleitoral):** Além das regras gerais, o sistema trata as exceções e especificidades da Justiça Eleitoral (Ex: períodos não-eleitorais, suspensão de prazos, etc.).

### 2.2 Controle de Regras por Segmento
Na tabela `itens_regras_tecnicas`, o campo `segmento_justica` define a abrangência da regra:
*   `'TODOS'`: Regra geral que se aplica a qualquer tribunal.
*   `'ESTADUAL'`: Regra exclusiva para TJs.
*   `'ELEITORAL'`: Regra exclusiva para TREs.

Isso permite que uma única instalação do sistema possa gerenciar métricas de diferentes naturezas sem conflito, ou que a importação de dados filtre apenas o que é relevante para o tribunal em questão (como feito no script de importação atual, que filtra para TRE, mas pode ser configurado para TJ).

---

## 3. Estrutura do Banco de Dados
O banco de dados foi remodelado para suportar a hierarquia complexa e as regras de pontuação do CNJ.

### 2.1 Tabela Principal: `itens`
Armazena toda a estrutura de metas, desde os eixos temáticos até os subitens (alíneas).

| Campo | Tipo | Descrição |
| :--- | :--- | :--- |
| `id` | PK | Identificador único. |
| `parent_id` | FK | Relacionamento hierárquico (Pai/Filho). Ex: Uma alínea pertence a um Artigo. |
| `codigo_exibicao` | String | Código visual (Ex: "Art. 9º", "a)", "12.VII"). |
| `nome` | String | Título curto ou nome do requisito. |
| `descricao` | Text | Texto completo do requisito extraído do edital. |
| `tipo` | String | `eixo` (Categoria), `grupo` (Artigo), `criterio` (Alínea/Item pontuável). |
| `eixo` | String | Eixo temático (Governança, Produtividade, etc.). |
| `pontos_maximos` | Decimal | Pontuação máxima possível para o item. |
| `pontos_obtidos` | Decimal | Pontuação atingida até o momento. |
| `pontos_teto_grupo` | Decimal | (Novo) Pontuação máxima limitada para grupos (Ex: Artigo vale até 40 pontos, mesmo que a soma dos filhos seja 50). |
| `tipo_calculo` | String | Define como a nota do pai é calculada (veja seção Fórmulas). |
| `formula_expressao` | String | Expressão personalizada para cálculos complexos (Ex: `(A / (A+B)) * 100`). |
| `ano_exercicio` | Int | Ano de referência do prêmio (Ex: 2026). |

### 2.2 Tabela de Regras Técnicas: `itens_regras_tecnicas`
Define como um item é calculado automaticamente via SQL (Data Warehouse) ou API.

| Campo | Tipo | Descrição |
| :--- | :--- | :--- |
| `item_id` | FK | Vínculo com a tabela `itens`. |
| `segmento_justica` | String | Segmento aplicável (Ex: 'ELEITORAL', 'TODOS'). |
| `variavel_codinome` | String | Nome da variável para uso em fórmulas (Ex: `P_Amb`). |
| `campo_analise` | String | Campo específico a ser auditado (Ex: `movimento.data`). |
| `query_config_json` | JSON | Configuração técnica para montar a query SQL de validação. |
| `meta_percentual` | Decimal | Meta a ser atingida (Ex: 90%). |

### 2.3 Tabela de Histórico: `avaliacoes_mensais`
Armazena a evolução da nota mês a mês.

| Campo | Tipo | Descrição |
| :--- | :--- | :--- |
| `item_id` | FK | - |
| `mes_referencia` | Date | Mês da avaliação. |
| `percentual_alcancado`| Decimal | % atingido naquele mês. |
| `pontos_conquistados` | Decimal | Pontos efetivos naquele mês. |
| `log_calculo` | Text | Log detalhado (JSON) de como o cálculo foi feito (variáveis usadas). |

---

## 3. Fórmulas e Lógica de Cálculo
O sistema suporta diferentes estratégias para calcular a nota de um item ("Pai") com base em seus filhos ou em regras técnicas.

Isso é definido pelo campo `tipo_calculo` na tabela `itens`:

### 3.1 `soma_com_teto` (Padrão para Artigos)
*   **Lógica:** Soma os pontos de todos os filhos (alíneas). Se a soma ultrapassar o `pontos_teto_grupo`, a nota é limitada ao teto.
*   **Exemplo:** Um artigo tem teto de **10 pontos**. Possui 3 alíneas de 4 pontos cada (Soma = 12). A nota do artigo será **10**.

### 3.2 `soma_simples`
*   **Lógica:** Apenas soma os pontos dos filhos sem limitação.
*   **Uso:** Grupos que não possuem trava de pontuação máxima.

### 3.3 `formula_customizada`
*   **Lógica:** Utiliza o campo `formula_expressao` para calcular a nota.
*   **Exemplo:** `(VarA / (VarA + VarB)) * 100`. O sistema busca os valores de `VarA` e `VarB` nas regras técnicas (`variavel_codinome`) e executa a expressão matemática.

### 3.4 `faixa_percentual`
*   **Lógica:** Atribui pontos baseados em faixas de cumprimento.
*   **Exemplo:**
    *   > 90% = 10 pontos
    *   > 80% = 5 pontos
    *   < 80% = 0 pontos

---

## 4. Importação Automática (Script PDF)
O comando `php artisan metas:importar-pdf` é responsável por ler o edital oficial e alimentar o banco.

### Como funciona:
1.  **Parsing:** Lê o arquivo PDF (`data/anexos-premio-cnj...pdf`).
2.  **Segmentação:** Quebra o texto em blocos baseados nos "Artigos" (Regex: `Art. X`).
3.  **Extração de Metadados:**
    *   **Pontos:** Busca padrões como "Até 10 pontos", "valendo 5,5 pontos". (Suporta decimais).
    *   **Hierarquia:** Identifica alíneas "a)", "b)" e sub-alíneas "a.1)" pela indentação e numeração.
4.  **Filtro TRE:** Ignora automaticamente itens que contenham "não se aplica à justiça eleitoral" ou "exceto eleitoral".
5.  **Inserção:** Popula a tabela `itens` mantendo a árvore de parentesco (`parent_id`).

### Expressões Regulares (Regex) Chave:
*   **Pontos:** `/(?:Até|valendo|valor)?\s*(\d+(?:[.,]\d+)?)\s*(?:pontos?|pts)/iu` -> Captura "10 pontos", "5,5 pontos".
*   **Artigos:** `/(?=Art\.\s*\d+[º°]?)/u` -> Identifica onde começa um novo artigo.
*   **Alíneas:** `/(?:^|\n)\s*([a-z])(?:\.(\d+))?\)\s+/u` -> Captura "a)", "a.1)".

---

## 5. Dashboard (Painel Administrativo)
O sistema utiliza **FilamentPHP** para a interface.

*   **`CnjItensTable`:** Widget principal que lista os itens.
    *   Permite **edição direta** dos pontos (`pontos_maximos`) caso a importação automática não tenha capturado corretamente (campo _inline edit_).
    *   Exibe o percentual de cumprimento com base nos pontos obtidos / máximos.
*   **`PainelCnj`:** Página que agrupa os widgets e métricas gerais.

---

## 6. Próximos Passos Sugeridos
1.  **Preencher Regras Técnicas:** Para cada item importado, definir na tabela `itens_regras_tecnicas` qual a query SQL ou regra que valida aquele item.
2.  **Automatizar Avaliação:** Criar um Job (`Schedule`) que roda mensalmente, executa as queries configuradas e insere o resultado em `avaliacoes_mensais`, atualizando a nota do item.
