# 🗄️ AprendeGame — Estrutura do Banco de Dados

## Visão Geral

```
PLATAFORMA → USUÁRIO → PROGRESSO
CONTEÚDO   → TRILHA  → PARADA → QUESTÃO
GAMIFICAÇÃO → XP / CONQUISTAS / RANKING
```

---

## 📐 Diagrama de Entidades (ERD simplificado)

```
usuarios ─────────────── progresso_trilhas
    │                           │
    ├── perfis_alunos        trilhas ──── paradas ──── questoes
    │                           │                         │
    └── ranking             materias               alternativas
                                │
                            series
                                │
                         nivel_ensino
```

---

## 📋 TABELAS

---

### 1. `nivel_ensino`
> Agrupa os grandes blocos: Anos Iniciais, Anos Finais, Ensino Médio

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT PK | Identificador |
| `nome` | VARCHAR(50) | Ex: "Anos Iniciais", "Anos Finais", "Ensino Médio" |
| `ordem` | INT | Ordem de exibição (1, 2, 3) |
| `paradas_por_trilha` | INT | Padrão de paradas: 3, 4 ou 5 |
| `questoes_por_trilha` | INT | Padrão: 30, 40 ou 50 |

**Registros:**
```
1 | Anos Iniciais | 1 | 3 | 30
2 | Anos Finais   | 2 | 4 | 40
3 | Ensino Médio  | 3 | 5 | 50
```

---

### 2. `series`
> 3º ano ao 3º ano EM

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT PK | Identificador |
| `nivel_ensino_id` | INT FK | Referência ao nível |
| `nome` | VARCHAR(50) | Ex: "3º Ano", "6º Ano", "1º Ano EM" |
| `ano_escolar` | INT | 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 |
| `ordem` | INT | Ordem de progressão |

**Registros:**
```
1  | 1 | 3º Ano (Fundamental)     | 3  | 1
2  | 1 | 4º Ano (Fundamental)     | 4  | 2
3  | 1 | 5º Ano (Fundamental)     | 5  | 3
4  | 2 | 6º Ano (Fundamental)     | 6  | 4
5  | 2 | 7º Ano (Fundamental)     | 7  | 5
6  | 2 | 8º Ano (Fundamental)     | 8  | 6
7  | 2 | 9º Ano (Fundamental)     | 9  | 7
8  | 3 | 1º Ano (Ensino Médio)    | 10 | 8
9  | 3 | 2º Ano (Ensino Médio)    | 11 | 9
10 | 3 | 3º Ano (Ensino Médio)    | 12 | 10
```

---

### 3. `materias`
> Disciplinas disponíveis por nível

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT PK | Identificador |
| `nome` | VARCHAR(80) | Ex: "Matemática", "Língua Portuguesa" |
| `icone` | VARCHAR(10) | Emoji ou ícone representativo |
| `cor_hex` | VARCHAR(7) | Cor da matéria na interface |
| `ativa` | BOOLEAN | Se está ativa na plataforma |

**Registros:**
```
1  | Língua Portuguesa | 📖 | #E74C3C | true
2  | Matemática        | 🔢 | #3498DB | true
3  | Ciências          | 🔬 | #2ECC71 | true
4  | Geografia         | 🌍 | #F39C12 | true
5  | História          | 📜 | #9B59B6 | true
6  | Arte              | 🎨 | #E91E63 | true
7  | Educação Física   | 🏃 | #00BCD4 | true
8  | Química           | ⚗️ | #FF5722 | true
9  | Física            | 🔭 | #607D8B | true
10 | Biologia          | 🧬 | #4CAF50 | true
11 | Filosofia         | 💭 | #795548 | true
12 | Sociologia        | 🏛️ | #FF9800 | true
13 | Língua Inglesa    | 🇬🇧 | #1565C0 | true
```

---

### 4. `series_materias`
> Relação: quais matérias pertencem a qual série (many-to-many)

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT PK | Identificador |
| `serie_id` | INT FK | Referência à série |
| `materia_id` | INT FK | Referência à matéria |
| `ativa` | BOOLEAN | Se a combinação está ativa |

---

### 5. `trilhas`
> Cada bimestre de cada matéria/série = 1 trilha (total: 240)

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT PK | Identificador |
| `serie_materia_id` | INT FK | Referência série+matéria |
| `bimestre` | INT | 1, 2, 3 ou 4 |
| `nome` | VARCHAR(120) | Nome temático da trilha (ex: "Reino dos Números") |
| `descricao` | TEXT | Descrição da trilha |
| `bncc_habilidades` | JSON | Array com códigos BNCC (ex: ["EF03MA01","EF03MA02"]) |
| `xp_total` | INT | XP disponível para ganhar na trilha |
| `desbloqueada_por` | INT FK NULL | ID da trilha anterior (prerequisito) |
| `ordem` | INT | Ordem dentro da série/matéria |
| `ativa` | BOOLEAN | Se está publicada |
| `criada_em` | TIMESTAMP | Data de criação |

---

### 6. `paradas`
> Cada estágio dentro de uma trilha (3, 4 ou 5 por trilha)

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT PK | Identificador |
| `trilha_id` | INT FK | Referência à trilha |
| `numero` | INT | Número da parada (1, 2, 3...) |
| `nome` | VARCHAR(100) | Nome da parada (ex: "Floresta das Frações") |
| `descricao` | TEXT | O que o aluno vai aprender/revisar |
| `tipo` | ENUM | `normal`, `boss`, `revisao`, `bonus` |
| `xp_recompensa` | INT | XP ao completar a parada |
| `ordem` | INT | Ordem de exibição |

**Tipos de parada:**
- `normal` — parada padrão com 10 questões
- `boss` — última parada da trilha, mais difícil (10 questões com peso maior)
- `revisao` — revisão das paradas anteriores
- `bonus` — parada extra desbloqueável

---

### 7. `questoes`
> Banco de questões — coração do sistema

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT PK | Identificador |
| `parada_id` | INT FK | Referência à parada |
| `serie_materia_id` | INT FK | Para reuso entre paradas |
| `enunciado` | TEXT | Texto da questão |
| `enunciado_html` | TEXT NULL | Versão com formatação rich text |
| `imagem_url` | VARCHAR(255) NULL | Imagem de apoio |
| `tipo` | ENUM | `multipla_escolha`, `verdadeiro_falso`, `lacuna`, `ordenacao` |
| `dificuldade` | ENUM | `facil`, `medio`, `dificil` |
| `bncc_habilidade` | VARCHAR(15) | Código da habilidade BNCC (ex: EF06MA01) |
| `explicacao` | TEXT | Explicação da resposta correta |
| `tempo_sugerido_seg` | INT | Tempo sugerido em segundos (ex: 30, 60) |
| `xp_valor` | INT | XP que a questão vale (padrão 10) |
| `ativa` | BOOLEAN | Se está publicada |
| `criada_em` | TIMESTAMP | Data de criação |
| `criada_por` | INT FK NULL | ID do professor/admin que criou |

---

### 8. `alternativas`
> Opções de resposta para cada questão

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT PK | Identificador |
| `questao_id` | INT FK | Referência à questão |
| `letra` | CHAR(1) | A, B, C, D ou E |
| `texto` | TEXT | Texto da alternativa |
| `imagem_url` | VARCHAR(255) NULL | Imagem (opcional) |
| `correta` | BOOLEAN | Se é a resposta correta |
| `ordem` | INT | Ordem de exibição |

---

### 9. `usuarios`
> Todos os usuários da plataforma

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT PK | Identificador |
| `nome` | VARCHAR(100) | Nome completo |
| `email` | VARCHAR(150) UNIQUE | E-mail de acesso |
| `senha_hash` | VARCHAR(255) | Senha criptografada |
| `tipo` | ENUM | `aluno`, `professor`, `admin` |
| `avatar_url` | VARCHAR(255) NULL | Foto de perfil |
| `ativo` | BOOLEAN | Se a conta está ativa |
| `criado_em` | TIMESTAMP | Data de cadastro |
| `ultimo_acesso` | TIMESTAMP | Último login |

---

### 10. `perfis_alunos`
> Dados específicos do aluno (1:1 com usuarios)

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT PK | Identificador |
| `usuario_id` | INT FK UNIQUE | Referência ao usuário |
| `serie_atual_id` | INT FK | Série atual do aluno |
| `escola` | VARCHAR(150) NULL | Nome da escola |
| `turma` | VARCHAR(20) NULL | Turma (ex: "7ºA") |
| `xp_total` | INT DEFAULT 0 | XP acumulado total |
| `nivel_jogador` | INT DEFAULT 1 | Nível no jogo (calculado) |
| `moedas` | INT DEFAULT 0 | Moedas para loja de itens |
| `streak_dias` | INT DEFAULT 0 | Dias consecutivos de acesso |
| `streak_record` | INT DEFAULT 0 | Recorde de streak |

---

### 11. `progresso_trilhas`
> Acompanha o progresso de cada aluno em cada trilha

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT PK | Identificador |
| `usuario_id` | INT FK | Referência ao aluno |
| `trilha_id` | INT FK | Referência à trilha |
| `status` | ENUM | `bloqueada`, `disponivel`, `em_andamento`, `concluida` |
| `parada_atual` | INT | Número da parada em que está |
| `xp_ganho` | INT DEFAULT 0 | XP ganho nesta trilha |
| `estrelas` | INT DEFAULT 0 | Estrelas (0-3) baseado no desempenho |
| `percentual_acerto` | DECIMAL(5,2) | % de acerto geral na trilha |
| `tentativas` | INT DEFAULT 0 | Quantas vezes tentou |
| `iniciado_em` | TIMESTAMP NULL | Quando começou |
| `concluido_em` | TIMESTAMP NULL | Quando completou |

---

### 12. `progresso_paradas`
> Progresso detalhado por parada

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT PK | Identificador |
| `usuario_id` | INT FK | Referência ao aluno |
| `parada_id` | INT FK | Referência à parada |
| `status` | ENUM | `nao_iniciada`, `em_andamento`, `concluida` |
| `acertos` | INT DEFAULT 0 | Número de acertos (de 10) |
| `xp_ganho` | INT DEFAULT 0 | XP ganho nesta parada |
| `tempo_gasto_seg` | INT | Tempo total gasto |
| `tentativas` | INT DEFAULT 0 | Tentativas nesta parada |
| `concluido_em` | TIMESTAMP NULL | Quando completou |

---

### 13. `respostas_alunos`
> Histórico completo de cada resposta dada

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT PK | Identificador |
| `usuario_id` | INT FK | Referência ao aluno |
| `questao_id` | INT FK | Referência à questão |
| `parada_id` | INT FK | Referência à parada |
| `alternativa_id` | INT FK NULL | Alternativa escolhida |
| `resposta_texto` | TEXT NULL | Resposta em texto (lacuna) |
| `correta` | BOOLEAN | Se acertou |
| `tempo_resposta_seg` | INT | Tempo para responder |
| `xp_ganho` | INT | XP ganho nesta resposta |
| `respondida_em` | TIMESTAMP | Quando respondeu |

---

### 14. `conquistas`
> Badges e troféus do jogo

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT PK | Identificador |
| `nome` | VARCHAR(80) | Ex: "Mestre das Frações" |
| `descricao` | TEXT | Como conquistar |
| `icone_url` | VARCHAR(255) | Imagem do badge |
| `tipo` | ENUM | `trilha`, `streak`, `acerto`, `velocidade`, `especial` |
| `criterio_json` | JSON | Regra para conquistar (ex: `{"acertos_seguidos": 10}`) |
| `xp_bonus` | INT | XP bônus ao conquistar |

---

### 15. `conquistas_alunos`
> Conquistas desbloqueadas por cada aluno

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT PK | Identificador |
| `usuario_id` | INT FK | Referência ao aluno |
| `conquista_id` | INT FK | Referência à conquista |
| `conquistado_em` | TIMESTAMP | Quando desbloqueou |

---

### 16. `ranking`
> Ranking semanal, mensal e geral

| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT PK | Identificador |
| `usuario_id` | INT FK | Referência ao aluno |
| `periodo` | ENUM | `semanal`, `mensal`, `geral` |
| `serie_id` | INT FK NULL | Ranking por série (NULL = geral) |
| `xp_periodo` | INT | XP acumulado no período |
| `posicao` | INT | Posição no ranking |
| `atualizado_em` | TIMESTAMP | Última atualização |

---

## 🔗 RELACIONAMENTOS PRINCIPAIS

```
nivel_ensino (1) ──── (N) series
series       (N) ──── (N) materias        [via series_materias]
series_materias (1) ── (N) trilhas
trilhas      (1) ──── (N) paradas
paradas      (1) ──── (N) questoes
questoes     (1) ──── (N) alternativas

usuarios     (1) ──── (1) perfis_alunos
usuarios     (1) ──── (N) progresso_trilhas
usuarios     (1) ──── (N) progresso_paradas
usuarios     (1) ──── (N) respostas_alunos
usuarios     (N) ──── (N) conquistas       [via conquistas_alunos]
```

---

## 📊 RESUMO DO BANCO

| Tabela | Estimativa de registros |
|---|---|
| nivel_ensino | 3 |
| series | 10 |
| materias | 13 |
| series_materias | ~60 |
| trilhas | 240 |
| paradas | ~940 |
| questoes | ~9.840 (banco mínimo) |
| alternativas | ~39.360 (4 por questão) |
| conquistas | ~50-100 |

---

## 💡 ÍNDICES RECOMENDADOS

```sql
-- Performance crítica
CREATE INDEX idx_progresso_usuario ON progresso_trilhas(usuario_id);
CREATE INDEX idx_progresso_trilha  ON progresso_trilhas(trilha_id);
CREATE INDEX idx_respostas_usuario ON respostas_alunos(usuario_id);
CREATE INDEX idx_respostas_questao ON respostas_alunos(questao_id);
CREATE INDEX idx_questoes_parada   ON questoes(parada_id);
CREATE INDEX idx_questoes_bncc     ON questoes(bncc_habilidade);
CREATE INDEX idx_ranking_periodo   ON ranking(periodo, serie_id);
```

---

## 🛠️ TECNOLOGIAS SUGERIDAS

| Camada | Sugestão |
|---|---|
| Banco de dados | PostgreSQL (robusto para JSON e relatórios) |
| ORM | Prisma (Node.js) ou SQLAlchemy (Python) |
| Cache | Redis (ranking, sessões, progresso em tempo real) |
| Storage (mídias) | AWS S3 ou Supabase Storage |
| Backend | Node.js + Express ou FastAPI |
| Autenticação | JWT + refresh token |

---

*AprendeGame — Estrutura de BD v1.0 — 2026*
