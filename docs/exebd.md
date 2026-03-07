Planejamento recomendado (execução)

1. Fase 1: Alinhamento funcional (1-2 dias)

- Fechar dicionário de termos (trilha=path,
  parada=node/lesson, etc.).
- Definir decisão técnica: manter questions.options
  JSON (recomendado agora) ou normalizar em
  alternatives.

2. Fase 2: Ajustes de schema mínimos (2-4 dias)

- paths: adicionar bimester, description,
  bncc_skills (JSON), xp_total,
  unlocks_after_path_id, order.
- path_nodes/lessons: garantir campos para tipo, XP
  da parada, ordem e publicação.
- Criar tabela de progresso agregado
  (student_path_progress) para status da trilha.

3. Fase 3: Regras de domínio (3-5 dias)

- Serviço de desbloqueio de trilhas por pré-
  requisito.
- Cálculo de XP/estrelas/% acerto por trilha.
- Consolidar progresso por parada e trilha a partir
  de attempts/lesson_runs.

4. Fase 4: Seed e conteúdo base (2-3 dias)

- Seed de níveis/séries/matérias/trilhas/paradas.
- Importador inicial de questões com BNCC e
  dificuldade.

5. Fase 5: Admin e operação (3-5 dias)

- CRUD de trilhas/paradas/questões (Filament).
- Fluxo de revisão/publicação de questão (draft/
  reviewed/published).
- Relatórios básicos por habilidade BNCC.

6. Fase 6: Qualidade e rollout (2-3 dias)

- Testes Pest focados em:
    - desbloqueio de trilhas
    - pontuação/XP
    - progresso por trilha/parada
    - ranking semanal
- Migração gradual em ambiente de staging +
  validação de carga.

Prioridade prática (ordem exata)

1. Fechar decisão sobre alternatives vs JSON.
2. Criar student_path_progress + campos faltantes
   em paths.
3. Implementar regras de desbloqueio e cálculo de
   progresso.
4. Só depois expandir admin/relatórios.

Se quiser, no próximo passo eu já transformo isso
em backlog técnico com tarefas prontas (migration/
model/service/test) para começar a implementar
imediatamente.
