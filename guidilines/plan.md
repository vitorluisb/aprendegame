# Plataforma Gamificada de Estudos (BNCC) — Planejamento Completo
**Público:** alunos (3º fundamental → 3º médio), pais/responsáveis, professores e escolas  
**Objetivo:** aumentar frequência de estudo e desempenho com trilhas gamificadas, simulados e revisão inteligente (spaced repetition), tudo alinhado à BNCC e com apoio de IA para geração/explicação de conteúdo.

---

## 1) Visão do Produto

### 1.1 Problema
- Alunos têm dificuldade de manter rotina de estudos e foco.
- Pais e escolas querem acompanhamento real e simples.
- Professores gastam tempo preparando listas/avaliando.
- Conteúdo precisa respeitar BNCC por série (varia muito).

### 1.2 Solução
Um “Duolingo escolar” com:
- **Trilhas (path) por série + matéria + habilidades BNCC**
- **Lições curtas** (3–7 min) + **chefões** (checkpoints)
- **Revisão inteligente** (1d/3d/7d/14d) com foco no que o aluno erra
- **Ranking por ligas** e conquistas
- **Painel escolar/professor** com turma, relatórios e criação/curadoria
- **IA** para gerar exercícios em lote e explicar erros com linguagem por faixa etária
- **PWA** para abrir rápido e suportar internet instável

---

## 2) Personas e Permissões

### 2.1 Personas
- **Aluno:** estuda em trilhas e recebe feedback instantâneo.
- **Pai/Responsável:** acompanha progresso, hábitos, dificuldades.
- **Professor:** cria turmas, acompanha alunos, aplica tarefas/simulados.
- **Gestor da Escola (Admin Escola):** gerencia professores, turmas, relatórios gerais.
- **Admin Plataforma (Super Admin):** gerencia BNCC, conteúdos globais, moderação, billing e configurações.

### 2.2 Modelo de acesso (RBAC)
- Roles:
    - `super_admin`
    - `school_admin`
    - `teacher`
    - `guardian`
    - `student`
- Permissões por módulo (checkbox no admin):
    - Conteúdo BNCC, Trilhas, Questões, Turmas, Relatórios, Gamificação, Moderação, Integrações, Billing
- **Recomendação:** RBAC com **policies/gates** + tabela de permissões (cacheada).

---

## 3) Escopo Funcional (Módulos)

## 3.1 Módulo: Autenticação e Contas (Segurança Profissional)
**Requisitos:**
- Login por e-mail/senha com política forte
- **Verificação de e-mail obrigatória**
- **Google OAuth** (login social)
- Recuperação de senha segura
- Sessões e dispositivos
- 2FA (fase 2, recomendado)

**Fluxos:**
1. Cadastro → e-mail de verificação → acesso liberado
2. Login Google → se e-mail não verificado (Google geralmente vem verificado; ainda assim marcar como verificado e registrar provider)
3. Convites:
    - Escola convida professor por e-mail
    - Professor/Escola convida aluno/responsável (link com expiração)
4. Separação de perfis:
    - Responsável cria/associa múltiplos alunos (subcontas)
    - Aluno pode ter login próprio (opcional, por idade/política da escola)

**Controles de segurança:**
- Rate limit por IP/usuário (login, reset, verificação)
- ReCAPTCHA/Turnstile (em login suspeito)
- Proteção CSRF, SameSite cookies, HttpOnly
- Logs de auditoria

---

## 3.2 Módulo: Estrutura BNCC e Conteúdo
**Base:** BNCC por etapa, componente curricular, unidade temática, objeto de conhecimento, habilidades (códigos).

**Funcionalidades:**
- Cadastro/curadoria de:
    - Etapas/anos/séries
    - Disciplinas por etapa
    - Habilidades BNCC (com códigos)
    - Tópicos (mapa pedagógico interno)
- Versionamento:
    - Conteúdo tem versão/edição para ajustes sem quebrar histórico
- Importação:
    - Import BNCC via CSV/JSON (no admin)
- Curadoria:
    - Questões geradas pela IA passam por status: `draft → reviewed → published`

---

## 3.3 Módulo: Trilhas “Duolingo” (Path)
**Conceito:**
- Cada **trilha** = (série + matéria) composta por **nós**.
- Cada nó = conjunto de lições + checkpoint (“chefão”).

**Regras:**
- Desbloqueio progressivo
- Lição curta: 8–12 interações
- Chefão: 15–25 questões ou bloco misto (inclui revisão)
- Adaptação: se aluno errar muito uma habilidade, reintroduz nós/atividades de reforço

---

## 3.4 Módulo: Exercícios e Simulados
**Exercícios:**
- Tipos:
    - Múltipla escolha
    - Verdadeiro/falso
    - Completar lacunas
    - Ordenar passos
    - Arrastar e soltar (Vue)
    - Resposta curta (com correção por rubrica/IA - opcional)

**Simulados:**
- Por:
    - unidade/nó
    - matéria
    - trimestre/bimestre (escola)
    - “mix” geral
- Correção + relatório de habilidades dominadas vs fracas
- Banco de questões:
    - dificuldade (1–5)
    - tags BNCC
    - tempo médio
    - taxa de erro

---

## 3.5 Módulo: Gamificação (Core do Engajamento)
- XP por lição (baseado em acertos, tempo e consistência)
- Gemas/moedas (economia do app)
- Níveis
- Streak (sequência diária) + freeze (1/semana)
- Corações/vidas (limitado no “desafio”, livre no treino)
- Conquistas (badges)
- Loja (skins, avatares, temas)
- Missões diárias e semanais
- Ranking:
    - Global (opcional)
    - Por escola/turma
    - **Ligas semanais** (Bronze/Prata/Ouro/… reset semanal)

---

## 3.6 Módulo: Revisão Inteligente (Spaced Repetition)
**Objetivo:** maximizar retenção e reduzir esquecimento.

**Modelo:**
- Cada habilidade BNCC do aluno tem:
    - `mastery_score` (0–100)
    - `last_seen_at`
    - `next_review_at`
- Agenda padrão:
    - após acerto: +intervalo (1d → 3d → 7d → 14d → 30d)
    - após erro: reduz mastery e agenda revisão mais cedo
- Em cada sessão:
    - 20–30% da lição = revisão do que está vencendo

---

## 3.7 Módulo: Escola e Professor
**Escola (Admin Escola):**
- Cadastro de turmas, anos e calendário
- Gestão de professores
- Relatórios agregados:
    - tempo médio de estudo
    - evolução por matéria/ano
    - habilidades críticas (heatmap)
- Políticas:
    - permitir login próprio do aluno ou só por responsável
    - configurar gamificação (limites por idade)

**Professor:**
- Turmas e alunos
- Atribuir tarefas:
    - “faça nós 3–5 até sexta”
    - “simulado unidade 2”
- Criar listas/avaliar:
    - banco de questões sugeridas
    - gerar simulado com IA (com curadoria)
- Acompanhamento:
    - ranking da turma
    - alunos em risco (baixa frequência / baixo mastery)

---

## 3.8 Módulo: Pais/Responsáveis
- Dashboard por filho:
    - streak, tempo, progresso, notas de simulados
    - matérias com dificuldade
    - recomendações: “reforçar frações”
- Alertas:
    - sem estudo há X dias
    - queda de desempenho
- Controles:
    - limite diário de uso
    - bloquear chat tutor (se necessário)

---

## 3.9 Módulo: IA (Geração e Tutor)
**Usos recomendados:**
- Geração em lote de exercícios por habilidade (job assíncrono)
- Variações de exercícios (não repetir)
- Explicação personalizada do erro
- Tutor: perguntas curtas (“me explica fração”)

**Controle de custo:**
- Pré-geração (80–90% do conteúdo)
- Cache de explicações
- “Ao vivo” só quando:
    - aluno erra 2–3 vezes
    - pergunta ao tutor
- Validação automática:
    - checar coerência da resposta
    - checar nível da série

**Modelos sugeridos (OpenRouter):**
- Principal (geração e explicação): **DeepSeek V3.2**
- Barato para reescrita/variações: **Mistral Small**
- Rápido para tutor curto (opcional): **Gemini Flash**
> Estratégia: “barato gera / padrão valida” quando precisar.

---

## 3.10 Módulo: PWA (Experiência Mobile)
- Instalável
- Cache de assets e rotas principais
- Modo internet ruim:
    - salvar tentativas localmente e sincronizar
- Sincronização:
    - fila offline de respostas
    - resolver conflitos por timestamp

---

## 4) Stack Técnica Recomendada

### 4.1 Backend
- Laravel 12
- Filament (admin e backoffice)
- MySQL
- Redis (cache + filas)
- Queue worker (Horizon recomendado)
- Storage: S3 compatível (MinIO, Wasabi, AWS S3)

### 4.2 Frontend
- Inertia + Vue 3 (experiência lúdica do aluno)
- Vite
- PWA plugin
- Biblioteca de UI (opcional): Headless + tokens próprios

### 4.3 Auth
- Laravel Fortify/Breeze/Jetstream (com verificação de e-mail)
- Socialite (Google OAuth)
- Sanctum (sessões/tokens)

---

## 5) System Design (Arquitetura e Componentes)

## 5.1 Diagrama lógico (alto nível)
- **Web App (Vue/Inertia)**: aluno/pais/professor
- **Admin (Filament)**: super admin + school admin
- **API Laravel**: conteúdo, gameplay, relatórios
- **MySQL**: dados transacionais
- **Redis**: cache e filas
- **Workers**: geração IA, ranking, relatórios
- **Object Storage**: uploads
- **Observabilidade**: logs + métricas + erros

## 5.2 Domínios (DDD leve)
- `Accounts`: usuários, permissões, escolas
- `Content`: BNCC, tópicos, trilhas, questões
- `Gameplay`: lições, tentativas, XP, streak
- `Analytics`: relatórios, agregações
- `AI`: jobs, prompts, validação
- `Notifications`: e-mail, push (fase 2)

## 5.3 Multi-tenant (Escolas)
- Modelo simples e seguro:
    - Uma base única (single DB)
    - `school_id` em todas entidades escolares
    - policies sempre filtram por `school_id`
- Evitar “DB por tenant” no MVP (complexo e caro).

## 5.4 Ranking escalável
- Calcular scores semanalmente por turma/escola/global
- Manter leaderboard no Redis (sorted sets)
- Persistir snapshot semanal no MySQL (auditoria)

## 5.5 Jobs principais
- `GenerateQuestionsForSkill`
- `ValidateQuestionBatch`
- `RecomputeWeeklyLeagues`
- `BuildStudentReports`
- `SendGuardianDigestEmail`

---

## 6) Banco de Dados (Entidades Principais)

### 6.1 Contas e Escolas
- `users` (pai/professor/admin)
- `schools`
- `school_members` (user_id, school_id, role)
- `students` (perfil do aluno, school_id opcional)
- `student_guardians` (guardian user ↔ student)

### 6.2 BNCC e Conteúdo
- `grades` (3EF…3EM)
- `subjects`
- `bncc_skills` (code, description, grade_id, subject_id)
- `paths` (grade_id, subject_id)
- `path_nodes` (path_id, order, skill_group/skills)
- `lessons` (node_id)
- `questions` (skill_id, difficulty, type, prompt, options, answer, explanation, status)

### 6.3 Gameplay
- `lesson_runs` (student_id, lesson_id, started_at, finished_at, score)
- `attempts` (student_id, question_id, correct, time_ms)
- `xp_transactions` (student_id, amount, reason)
- `streaks` (student_id, current, best, last_day)
- `mastery` (student_id, skill_id, mastery_score, next_review_at)
- `reviews` (student_id, skill_id, scheduled_at, status)

### 6.4 Escola/Professor
- `classes` (school_id, grade_id, name)
- `class_students` (class_id, student_id)
- `assignments` (class_id, type, due_at)
- `assignment_items` (assignment_id, node_id/lesson_id/simulado)

---

## 7) Segurança (Checklist Profissional)

### 7.1 Autenticação e Sessão
- E-mail verification obrigatório
- OAuth Google com Socialite
- Senhas: Argon2id + política forte
- Session fixation protection
- Device/session management (listar e revogar sessões)

### 7.2 Proteções
- Rate limit: login, reset, verify, API sensível
- CSRF (Inertia padrão)
- Cookies: HttpOnly + Secure + SameSite
- Headers: HSTS, X-Frame-Options, CSP (fase 2)
- Auditoria:
    - logs de login
    - mudanças de permissão
    - criação/edição de conteúdo BNCC

### 7.3 LGPD / Crianças
- Minimizar dados coletados
- Consentimento do responsável
- Exportar/Excluir dados (admin)
- Política de retenção de logs
- Moderação do tutor IA (limites, filtros, bloqueios)

---

## 8) Design System (UI/UX)

## 8.1 Princípios
- **Lúdico, mas legível**
- Feedback instantâneo (som + animação)
- Progressão visível sempre
- Acessibilidade (contraste, tamanhos, foco)
- Diferentes faixas etárias:
    - 8–10: mais visual e poucos textos
    - 11–14: mais texto e desafios
    - 15–18: visual clean e foco em performance

## 8.2 Tokens (base)
- **Cores**
    - Primária (ação): `Primary`
    - Secundária (suporte): `Secondary`
    - Sucesso: `Success`
    - Alerta: `Warning`
    - Erro: `Danger`
    - Neutros: `Gray-50 … Gray-900`
- **Tipografia**
    - Títulos: 20–32
    - Corpo: 14–18
    - Botões: 14–16 (peso 600)
- **Espaçamento**
    - escala 4px (4/8/12/16/24/32/48)
- **Raio**
    - cards: 16–24
    - botões: 14–18
- **Sombras**
    - suaves (cards)
    - destaque (modal / recompensa)

## 8.3 Componentes (biblioteca)
**Navegação**
- TopBar (perfil, streak, gems)
- BottomNav (mobile)

**Gamificação**
- XPBar
- StreakChip
- GemCounter
- Badge
- RewardModal (confete/anim)

**Trilha**
- PathMap
- PathNode (locked/unlocked/completed)
- CheckpointBoss

**Lição**
- LessonPlayer
- QuestionCard
- OptionButton
- DragDropBoard
- TimerChip
- Hearts

**Feedback**
- CorrectToast / WrongToast
- HintPopover
- ExplanationSheet

**Pais/Escola**
- ProgressCard
- SkillHeatmap
- ReportTable

## 8.4 Padrões de interação
- Animação curta: 150–250ms
- Confete só em marcos (chefão/conquista)
- Sons com toggle e volume reduzido
- Loading com skeleton (não spinner)

---

## 9) Conteúdo BNCC + IA (Pipeline)

### 9.1 Pipeline de geração
1. Admin cadastra habilidade BNCC + objetivo + exemplos
2. Job: gera lote (100–300) de questões por habilidade
3. Job: valida (consistência, nível, ambiguidade)
4. Curadoria humana (amostragem)
5. Publica
6. Telemetria realimenta (questões com alta taxa de erro → revisar)

### 9.2 Prompting (diretrizes)
- sempre informar:
    - série/idade
    - habilidade BNCC (código e descrição)
    - nível de dificuldade
    - formato da questão
    - resposta correta e explicação curta
- linguagem por faixa etária
- evitar pegadinhas
- garantir enunciado claro

---

## 10) Observabilidade e Qualidade

### 10.1 Logs e métricas
- Erros (Sentry ou similar)
- Métricas:
    - DAU/WAU
    - tempo médio por sessão
    - retenção D1/D7
    - conclusão de lições
    - mastery médio por matéria
- Auditoria:
    - alterações em BNCC/questões
    - permissões

### 10.2 Testes
- Unit: cálculo de XP/streak/mastery
- Feature: fluxos de login/verificação/convite
- E2E (Playwright/Cypress): lição completa e checkpoint

---

## 11) Roadmap (Fases)

### Fase 0 — Fundação (2–3 semanas)
- setup Laravel + Filament + Inertia Vue
- auth completo (email verify + Google)
- modelos base BNCC + trilhas
- PWA básico

### Fase 1 — MVP Aluno + Pais + Professor (6–10 semanas)
- Path + lição + chefão
- XP/gemas/streak
- revisão inteligente
- ranking por ligas semanais
- dashboards:
    - pai (progresso)
    - professor (turma + tarefas simples)
    - escola (visão geral)
- geração de questões em lote via IA (jobs)

### Fase 2 — Qualidade e Escala (4–8 semanas)
- loja/avatares
- mais tipos de questão (drag drop, ordenar)
- relatórios PDF
- moderação do tutor IA
- otimizações: redis ranking, cache agressivo

### Fase 3 — Produto “premium” (contínuo)
- 2FA
- push notifications (PWA)
- personalização por escola
- trilhas de vestibular/ENEM
- recomendações avançadas (ML leve)

---

## 12) Entregáveis (o que você terá ao final do planejamento)
- MVP funcional com:
    - trilhas BNCC por série/matéria
    - gamificação completa base
    - revisão inteligente
    - dashboards pai/professor/escola
    - IA gerando e explicando (com custo controlado)
    - PWA instalável
    - segurança de autenticação profissional

---

## 13) Próximo passo sugerido (para começar certo)
1. Definir **lista inicial de matérias por etapa** (EF e EM) e mapear BNCC (import).
2. Montar o **esqueleto do banco** e policies multi-tenant (school_id).
3. Implementar o **LessonPlayer Vue** (o “coração” do app).
4. Implementar **mastery + review scheduler**.
5. Só então plugar IA (batch generation + explanations).

---
