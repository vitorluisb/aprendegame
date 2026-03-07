I want to add a new game mode to my application called "Quiz Mestre".

The application is a gamified learning platform called AprendeGame.

This new game must exist inside a section called "Jogos" and must be separated from the BNCC curriculum question system already used in the learning trails.

IMPORTANT:
Do not break or modify existing systems. This should be implemented as a new module that integrates with the existing platform.

--------------------------------------------------

PLAYER FLOW

1. The player enters the "Jogos" section.
2. The player selects "Quiz Mestre".
3. The player clicks "Jogar agora".
4. The system creates a new game session.
5. The player answers multiple-choice questions in sequence.
6. Each correct answer:
    - advances to the next round
    - increases score
    - accumulates XP
    - accumulates gems
7. If the player answers incorrectly:
    - show incorrect feedback
    - reveal the correct answer
    - end the match
8. Difficulty increases across rounds.
9. The match ends when the player loses or completes all rounds.
10. Show final score, XP earned, gems earned, and round reached.

--------------------------------------------------

GAME STRUCTURE

The game will have 30 rounds.

Difficulty progression:
rounds 1–15 → easy
rounds 16–25 → medium
rounds 26–30 → hard

Rewards:
correct answer → XP
streak bonus → extra XP
complete run → bonus gems

--------------------------------------------------

DATABASE STRUCTURE

Create separate tables for this mode.

Tables:

gk_categories
- id
- name
- description
- icon
- created_at

gk_questions
- id
- category_id
- question_text
- option_a
- option_b
- option_c
- option_d
- correct_option
- explanation
- difficulty
- age_group
- is_active
- source_reference
- metadata_json
- created_at

gk_sessions
- id
- user_id
- score
- current_round
- correct_answers
- started_at
- finished_at
- reward_xp
- reward_gems
- status

gk_session_answers
- id
- session_id
- question_id
- selected_option
- is_correct
- response_time_ms
- created_at

--------------------------------------------------

BACKEND REQUIREMENTS

Create services responsible for:

StartGameSession
LoadNextQuestion
SubmitAnswer
CalculateScore
FinishGameSession

Rules:

- avoid repeating questions recently answered by the user
- only load active questions
- gradually increase difficulty
- randomize answer order if possible

--------------------------------------------------

ADMIN PANEL

Create an admin section:

Quiz Mestre

Inside it:

Categories
Questions
Question batches
Game sessions analytics

Admin features:

Create/Edit/Delete categories
Create/Edit/Delete questions
Filter questions by category and difficulty
Bulk question generation/import
Enable/disable questions

--------------------------------------------------

UI REQUIREMENTS

The UI must integrate visually with the existing AprendeGame interface.

Do not create a completely different design language.

Screens required:

1. Quiz Mestre Lobby
2. Game screen
3. Correct answer feedback
4. Wrong answer feedback
5. Final results screen

Components to implement:

QuizModeHero
QuizScoreBar
QuizQuestionCard
QuizAnswerButton
QuizRoundStepper
QuizResultBanner
QuizFinalSummary
QuizCategoryBadge

--------------------------------------------------

DESIGN SYSTEM

The new UI must follow the same visual identity used in the rest of the application.

Use these design principles:

Typography:
Use the same font family already used in the platform.

Border radius:
Use rounded corners consistent with the application (approx 12–16px).

Spacing scale:
Follow existing spacing tokens if available.
Use consistent padding and margin.

Buttons:
Use the same base button component used in the rest of the app.

Color palette:

Primary UI color → use existing primary color from the platform.
Success state → green.
Error state → soft red.
Highlight color for quiz → gold/yellow accent.

Example palette guidance:

Primary → existing platform blue
Accent → yellow/gold
Correct → green
Incorrect → red
Neutral → gray

Cards:
Use the same card style already present in the application:
- soft shadow
- rounded corners
- subtle borders

Animations:
Use minimal micro-interactions only:
- answer selection
- correct/incorrect feedback
- progress animation

Avoid heavy animations.

Icons:
Use the same icon library already used in the project.

--------------------------------------------------

GAME SCREEN LAYOUT

Header:
- round indicator
- current score
- exit button

Question area:
- large question card
- 4 answer buttons

Progress indicator:
- round progress bar
- current reward level

Feedback state:
Correct → green highlight + short message
Incorrect → red highlight + show correct answer

--------------------------------------------------

VISUAL GOAL

The game should feel like a special mode inside AprendeGame:

- slightly more dramatic than normal lessons
- but still consistent with the app design
- avoid TV show imitation
- avoid dashboard look

--------------------------------------------------

IMPLEMENTATION PLAN

1. analyze existing project architecture
2. create migrations and models
3. create game services
4. implement admin panel
5. implement game UI
6. connect XP and gems rewards
7. ensure responsiveness
8. keep code modular and clean

--------------------------------------------------

IMPORTANT CONSTRAINTS

Do NOT:

- modify the BNCC question system
- break existing routes
- rename existing components
- change the authentication system
- change the main design system

This module must integrate cleanly with the current project architecture.
