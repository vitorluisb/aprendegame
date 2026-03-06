# AprendeGame — Plano de Animações, Sons e Feedback Visual

## Objetivo

Este documento define o plano de implementação de animações visuais, sons e microinterações do frontend do **AprendeGame**, com foco em:

- melhorar retenção
- aumentar sensação de recompensa
- deixar a experiência mais lúdica
- reforçar feedback imediato
- manter boa performance em celular e navegador

A proposta é criar uma interface com cara de produto moderno e gamificado, sem exageros visuais.

---

# Princípios do sistema

## 1. Feedback imediato
Toda ação importante do aluno deve gerar uma resposta visual clara.

Exemplos:
- acertou → sucesso visual + som curto
- errou → feedback leve + explicação
- completou fase → recompensa + celebração
- subiu de nível → destaque visual forte

---

## 2. Microinterações primeiro
O sistema deve priorizar pequenas animações rápidas em vez de efeitos exagerados.

Priorizar:
- transições curtas
- barras animadas
- mudanças de estado visíveis
- elementos reagindo ao clique

Evitar:
- animações longas
- excesso de partículas
- sons constantes
- efeitos em tudo

---

## 3. Recompensa proporcional
Nem toda ação precisa de animação forte.

Escala recomendada:
- ação simples → microanimação
- ação importante → animação média
- grande conquista → animação completa

---

## 4. Performance e acessibilidade
O sistema precisa funcionar bem em:
- celular intermediário
- navegador mobile
- conexões comuns

Também deve respeitar:
- usuário com som desligado
- usuário com redução de animação
- contexto infantil e familiar

---

# Stack recomendada

## Base principal
- Vue 3
- CSS Transitions
- Vue Transition / TransitionGroup
- HTML5 Audio

## Camada premium
- GSAP
- Lottie

## Efeitos especiais
- biblioteca leve de confete/partículas

Exemplo de stack:

```text
Vue 3
CSS transitions
GSAP
Lottie
HTML5 Audio
Canvas/confetti library


Papel de cada tecnologia
Vue Transition + CSS

Usar para:

fade

zoom

slide

shake

bounce

entrada e saída de elementos

troca de estados simples

Ideal para:

mensagens de acerto

troca de cards

modais

avisos rápidos

mudança de tela interna

HTML5 Audio

Usar para:

som de acerto

som de erro

recompensa

level up

baú

botão importante

Regras:

sons curtos

volume controlado

tocar apenas após interação do usuário

permitir mute global

GSAP

Usar para:

gems voando

barra de XP enchendo

reward cards surgindo

combos

level up

avatar reagindo

sequência de elementos

GSAP entra onde a animação precisa parecer mais polida.

Lottie

Usar para:

confete

troféu

estrela

mascote comemorando

baú abrindo

badge desbloqueada

Lottie deve ser usada em momentos especiais, não como padrão em tudo.

Partículas / confete

Usar apenas em:

concluir fase

subir de nível

completar trilha

abrir recompensa importante

concluir missão especial

Arquitetura de feedback do sistema
Categorias de feedback
1. Feedback de resposta

Acontece ao responder questões.

Inclui:

acerto

erro

explicação

progresso

XP

recompensa curta

2. Feedback de progressão

Acontece ao avançar no jogo.

Inclui:

barra de progresso

estrela conquistada

fase liberada

streak atualizado

nível aumentado

3. Feedback de recompensa

Acontece ao ganhar algo.

Inclui:

gems

badge

avatar item

baú

recompensa diária

4. Feedback de sistema

Acontece na navegação.

Inclui:

loading

transição de tela

troca de aba

modal abrindo

notificações

Eventos do AprendeGame e comportamento visual
1. Ao acertar uma questão
Objetivo

Gerar sensação imediata de sucesso.

Comportamento visual

alternativa correta fica verde

aparece ícone de check com animação

pequeno pop ou bounce

barra de progresso avança

XP sobe visualmente

gems podem animar para o topo em casos especiais

Som

som curto e leve de sucesso

Tecnologias

Vue Transition

CSS

HTML5 Audio

GSAP opcional para XP/gems

2. Ao errar uma questão
Objetivo

Mostrar erro sem frustrar a criança.

Comportamento visual

card ou alternativa treme levemente

cor muda de forma suave

explicação aparece com transição

evitar efeito agressivo

Som

som curto neutro, não punitivo

Tecnologias

CSS shake

Vue Transition

HTML5 Audio

3. Ao concluir uma lição
Objetivo

Dar sensação de fechamento e recompensa.

Comportamento visual

tela/resumo de conclusão

progresso da trilha aumenta

estrelas aparecem

XP total conta animado

possível confete leve

Som

som de conclusão

volume discreto

Tecnologias

GSAP

Lottie opcional

partícula leve

HTML5 Audio

4. Ao subir de nível
Objetivo

Marcar momento importante.

Comportamento visual

overlay leve escurecendo fundo

texto “Level Up”

brilho no avatar

contador de nível mudando com animação

recompensa aparecendo

Som

som especial de progressão

Tecnologias

GSAP

Lottie

HTML5 Audio

5. Ao abrir um baú
Objetivo

Criar expectativa e recompensa emocional.

Comportamento visual

baú entra na tela

pequena pausa dramática

tampa abre

item/recompensa surge

brilho / estrela / confete

Som

som de abertura

som de recompensa

Tecnologias

Lottie ou GSAP

HTML5 Audio

partícula leve

6. Ao completar missão diária
Objetivo

Reforçar retorno recorrente.

Comportamento visual

missão marcada como concluída

recompensa aparece

moeda sobe

selo de concluída

Som

som de missão concluída

Tecnologias

Vue Transition

GSAP

HTML5 Audio

7. Ao manter streak
Objetivo

Aumentar retenção.

Comportamento visual

chama acende

número do streak sobe

pequena animação de destaque

Som

opcional e discreto

Tecnologias

CSS/GSAP

Lottie opcional para chama

8. Ao desbloquear nova trilha ou fase
Objetivo

Dar senso de progresso real.

Comportamento visual

cadeado abre

fase ilumina

botão de jogar aparece

mapa faz pequeno movimento de foco

Som

som de desbloqueio

Tecnologias

GSAP

CSS transitions

HTML5 Audio

Design de som
Objetivos do áudio

Os sons devem:

reforçar feedback

ser curtos

não irritar

ter identidade do produto

Categorias de som
Sons de resposta

acerto

erro

resposta enviada

Sons de progresso

XP

level up

streak

fase concluída

Sons de recompensa

moeda

baú

badge

item liberado

Sons de interface

clique especial

abrir modal

confirmar ação

Diretrizes

máximo de 0.2s a 1.2s na maioria dos casos

evitar sons longos

evitar sons estridentes

criar botão de mute

salvar preferência do usuário

respeitar modo silencioso

Sistema de configuração do usuário
Preferências necessárias

Criar configurações para:

som ligado/desligado

volume geral

reduzir animações

efeitos especiais ligados/desligados

Exemplo de estrutura:

{
  "sound_enabled": true,
  "music_enabled": false,
  "effects_volume": 0.7,
  "reduce_motion": false,
  "particles_enabled": true
}
Estratégia de implementação por fases
Fase 1 — Base funcional

Objetivo: entregar o essencial sem complexidade.

Implementar:

Vue Transition

transições simples

som de acerto

som de erro

barra de progresso animada

feedback de resposta

preferência de som on/off

Entregas:

componente de toast/feedback

componente de resposta correta/incorreta

helper de áudio simples

transições padrão do sistema

Fase 2 — Recompensa e progressão

Objetivo: aumentar sensação de jogo.

Implementar:

animação de XP

gems voando

conclusão de lição

missão diária concluída

streak animado

fase desbloqueada

Entregas:

componente de reward animation

animação de XP com GSAP

efeito de gems

sistema de desbloqueio visual

Fase 3 — Momentos premium

Objetivo: criar momentos memoráveis.

Implementar:

level up completo

baú animado

confete

badges

trilha concluída

mascote ou efeito especial

Entregas:

integração com Lottie

confete/partículas

animação de baú

modal de level up

Fase 4 — Polimento

Objetivo: consolidar identidade visual do produto.

Implementar:

biblioteca interna de animações

padronização de timing

estados loading mais bonitos

ajustes para mobile

modo reduzir movimento

Entregas:

tokens de animação

mapa de som

guia de UX motion

auditoria de performance

Biblioteca interna recomendada
Criar uma camada própria no projeto

Organizar algo assim:

src/
  animations/
    transitions/
    feedback/
    rewards/
    progress/
  audio/
    success/
    error/
    rewards/
    interface/
  lottie/
    badges/
    chest/
    level-up/
    confetti/
  composables/
    useAudio.ts
    useMotion.ts
    useRewards.ts
Padrões visuais recomendados
Tempos de animação

Sugestão:

microinteração: 150ms a 250ms

feedback comum: 250ms a 400ms

recompensa média: 400ms a 700ms

celebração grande: 700ms a 1200ms

Curvas de animação

Priorizar:

ease-out

ease-in-out

spring leve quando fizer sentido

Evitar:

timing duro demais

exagero de bounce

Componentes recomendados
Componentes base

AnimatedButton

FeedbackBadge

SuccessToast

ErrorFeedback

ProgressBar

XpCounter

RewardPopup

LevelUpModal

ChestOpenModal

StreakIndicator

Composables/helpers

useAudio()

useMotionPreferences()

useRewardEffects()

useProgressAnimation()

Fluxos prioritários do MVP
Fluxo 1 — Responder questão

Deve incluir:

clique na resposta

estado selecionado

validação

acerto/erro visual

som

explicação

progresso

Fluxo 2 — Final de lição

Deve incluir:

resumo

XP

recompensa

botão próxima fase

Fluxo 3 — Login diário

Deve incluir:

streak

recompensa diária

missão do dia

Métricas para avaliar se funcionou
Métricas de engajamento

tempo médio por sessão

quantidade de lições por sessão

taxa de retorno diário

taxa de conclusão de fase

Métricas de UX

tempo até responder

taxa de abandono durante lição

uso de mute

uso de reduzir animações

Métricas de performance

tempo de carregamento

FPS médio em telas críticas

peso dos assets de animação

taxa de erro de áudio

Boas práticas técnicas
Áudio

pré-carregar sons mais usados

limitar reprodução simultânea

centralizar controle de áudio

evitar tocar muitos sons juntos

Animação

preferir transform e opacity

evitar animar propriedades caras

reduzir re-render desnecessário

usar GSAP só onde agrega valor real

Lottie

usar apenas em momentos importantes

otimizar arquivos JSON

evitar dezenas de Lotties simultâneos

Mobile

testar em aparelho intermediário

reduzir partículas em telas menores

simplificar efeitos em dispositivos fracos

Regras de UX para público infantil/juvenil
Para crianças

feedback claro

cores amigáveis

recompensa frequente

erro sem punição agressiva

Para alunos mais velhos

visual menos infantil

efeitos mais limpos

celebração mais discreta

linguagem menos caricata

O que evitar

som automático sem interação

confete em toda ação

animações longas em tarefas repetidas

excesso de elementos piscando

erro com efeito humilhante

bloquear fluxo com efeitos desnecessários

Resultado esperado

Ao aplicar este plano, o AprendeGame deve passar de um sistema de perguntas para uma experiência gamificada com:

feedback forte

progressão visível

recompensa emocional

interface viva

experiência mais próxima de um jogo educativo moderno

Próximos passos recomendados
Etapa 1

Implementar:

useAudio

transições base

feedback de acerto e erro

barra de progresso animada

Etapa 2

Implementar:

XP animado

gems voando

missão concluída

streak

Etapa 3

Implementar:

level up

baú

Lottie

confete premium

Resumo executivo

A estratégia ideal para o AprendeGame é:

usar Vue Transition e CSS para o básico

usar HTML5 Audio para sons curtos

usar GSAP para animações premium

usar Lottie para celebrações especiais

usar partículas com moderação

manter foco em performance, clareza e recompensa

Esse sistema deve ser implementado por fases, começando no feedback essencial e evoluindo até momentos premium de progressão.
