# Prompt — Revisão da BNCC + nova estrutura de trilhas do AprendeGame

Você vai atuar como **arquiteto de produto + analista educacional + desenvolvedor full stack** dentro do projeto **AprendeGame**.

## Objetivo geral

Revisar a estrutura pedagógica do sistema com base no arquivo:

`docs/bncc.md`

e aplicar uma nova organização de progressão de conteúdo, nomenclatura e formulário de geração.

---

## Regras principais

### 1) Trocar o nome “módulo”
Não quero mais usar o nome **“módulo”** na aplicação.

Quero substituir por um nome mais bonito, lúdico e alinhado com uma plataforma gamificada educacional.

Antes de implementar, analise e escolha a melhor opção entre sugestões como:

- Trilha
- Missão
- Jornada
- Etapa
- Fase
- Desafio
- Rota de Aprendizagem
- Caminho
- Expedição
- Ciclo

### Regra de escolha
O novo nome deve:

- soar bem para crianças e adolescentes
- funcionar bem no singular e plural
- ficar bom na interface
- combinar com progressão gamificada
- servir tanto para anos iniciais quanto finais e ensino médio

### Entrega esperada
Defina:
- nome oficial escolhido
- justificativa curta
- onde esse nome deve substituir “módulo” no sistema
- textos de interface impactados

---

## 2) Nova quantidade por série/faixa

Reorganizar a quantidade de unidades de progressão por disciplina conforme abaixo:

- **3º ao 5º ano:** 15 unidades por disciplina
- **6º ao 9º ano:** 25 unidades por disciplina
- **1º ao 3º ano do ensino médio:** 30 unidades por disciplina

> Observação importante:
> aplicar isso respeitando a progressão pedagógica e a densidade do conteúdo por etapa.

---

## 3) Revisar habilidades com base no arquivo `docs/bncc.md`

Leia e use **somente como base principal** o conteúdo de:

`docs/bncc.md`

Quero que você:

- revise a estrutura existente
- reorganize por **série/ano**
- reorganize por **matéria/disciplina**
- reescreva e redistribua as **habilidades**
- deixe tudo consistente com o que está no arquivo
- elimine duplicidades
- corrija inconsistências
- deixe a progressão pedagógica clara

### Estrutura desejada
Para cada série/ano e disciplina, organizar:

- série
- disciplina
- nome da unidade de progressão (novo nome escolhido no lugar de módulo)
- título da unidade
- descrição curta
- habilidades relacionadas
- ordem de progressão

### Regras pedagógicas
As habilidades devem:

- estar associadas corretamente à série
- respeitar a progressão do simples para o complexo
- ter linguagem clara para uso interno no sistema
- ser úteis para geração de questões
- servir de base para trilhas, aulas e desafios

---

## 4) Organização esperada por série e matéria

Reestruture tudo por:

- 3º ano
- 4º ano
- 5º ano
- 6º ano
- 7º ano
- 8º ano
- 9º ano
- 1º ano do ensino médio
- 2º ano do ensino médio
- 3º ano do ensino médio

E por disciplina, conforme existir no `docs/bncc.md`.

Se houver disciplinas separadas por etapa, respeite o que estiver documentado.

---

## 5) Formulário de geração

Na geração de conteúdo/questões, quero opção no formulário para escolher:

- série
- disciplina
- habilidade relacionada àquela série

### Regras do formulário
O campo de habilidade deve ser dependente dos anteriores:

1. usuário escolhe a **série**
2. depois escolhe a **disciplina**
3. então o sistema carrega apenas as **habilidades correspondentes àquela série + disciplina**

### Comportamento esperado
- nada de listar habilidades erradas de outras séries
- filtros dependentes
- experiência simples para o admin
- pronto para uso no painel

### Se o projeto estiver em Laravel + Filament
Implementar com boa UX usando:
- `Select`
- relacionamento ou carregamento dinâmico
- validação correta
- organização limpa

---

## 6) O que preciso que você faça no código

Quero que você revise e proponha a implementação completa considerando:

### A. Modelagem
Verificar se é necessário criar ou ajustar entidades como:

- series
- disciplinas
- unidades de progressão (novo nome escolhido)
- habilidades
- relacionamentos entre eles

### B. Banco de dados
Criar ou ajustar migrations para suportar:

- série
- disciplina
- unidade de progressão
- habilidade
- ordenação
- vínculo correto com geração de conteúdo/questões

### C. Seeders
Se fizer sentido, criar seeders para popular com base na estrutura reorganizada do `docs/bncc.md`.

### D. Painel administrativo
Ajustar formulários e recursos do Filament para permitir:

- cadastro
- edição
- organização
- filtragem
- seleção dependente de série > disciplina > habilidade

### E. Geração
Adaptar a lógica de geração para que as questões/conteúdos possam ser gerados com base em:

- série
- disciplina
- habilidade escolhida

---

## 7) Formato da resposta que quero de você

Quero que você entregue em etapas, nesta ordem:

### Etapa 1 — Diagnóstico
- analisar `docs/bncc.md`
- identificar estrutura atual
- apontar inconsistências
- sugerir o melhor novo nome para substituir “módulo”

### Etapa 2 — Nova estrutura pedagógica
- reorganizar por série e disciplina
- distribuir a quantidade correta por etapa:
    - 3º ao 5º: 15
    - 6º ao 9º: 25
    - 1º ao 3º EM: 30
- refazer as habilidades por série e matéria com base no arquivo

### Etapa 3 — Estrutura técnica
- mostrar a modelagem ideal
- mostrar migrations necessárias
- mostrar relacionamentos
- mostrar como ficará o formulário de geração

### Etapa 4 — Implementação
- gerar os arquivos necessários
- ajustar o que já existir
- manter padrão do projeto
- evitar quebrar funcionalidades existentes

---

## 8) Restrições importantes

- não inventar conteúdo pedagógico fora do que estiver coerente com `docs/bncc.md`
- usar o arquivo como fonte principal
- não fazer mudanças exageradas na arquitetura sem necessidade
- manter nomes claros e consistentes
- priorizar simplicidade, manutenção e escalabilidade
- preservar compatibilidade com o restante da aplicação

---

## 9) Saída esperada

Quero a resposta no seguinte formato:

### 1. Nome escolhido para substituir “módulo”
### 2. Justificativa
### 3. Nova organização por série e disciplina
### 4. Habilidades revisadas com base no `docs/bncc.md`
### 5. Ajustes no banco/modelos
### 6. Ajustes no Filament/formulários
### 7. Código sugerido
### 8. Próximos passos

---

## 10) Se o arquivo estiver confuso
Se encontrar inconsistências no `docs/bncc.md`, não ignore.

Faça assim:
- aponte os problemas
- proponha a correção
- reorganize da forma mais coerente possível
- explique o motivo da decisão

---

## 11) Prioridade de UX
Na interface da aplicação, o novo nome que substituir “módulo” deve ficar bonito em exemplos como:

- “Iniciar ___”
- “Continuar ___”
- “___ concluída”
- “15 ___ de Matemática”
- “Escolha uma ___”
- “Progresso da ___”

Escolha o termo pensando nisso.

---

## 12) Resultado final
Quero que você realmente **revise o arquivo `docs/bncc.md` e refaça a estrutura das habilidades por série e matéria**, e não apenas me dê sugestões genéricas.

Depois, implemente a opção no formulário para escolher:

- série
- disciplina
- habilidade relacionada àquela série
