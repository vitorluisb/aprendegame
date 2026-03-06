<?php

namespace Database\Seeders;

use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\Subject;
use Illuminate\Database\Seeder;

class BnccSeeder extends Seeder
{
    public function run(): void
    {
        $grades = $this->seedGrades();
        $subjects = $this->seedSubjects();
        $this->seedPaths($grades, $subjects);
        $this->seedSkills($grades, $subjects);
    }

    /** @return array<string, Grade> */
    private function seedGrades(): array
    {
        // BNCC cobre do 3º ano do EF até o 3º ano do EM
        $data = [
            // Anos Iniciais do Ensino Fundamental
            ['name' => '3º Ano EF', 'code' => 'EF03', 'stage' => 'fundamental_1', 'order' => 1],
            ['name' => '4º Ano EF', 'code' => 'EF04', 'stage' => 'fundamental_1', 'order' => 2],
            ['name' => '5º Ano EF', 'code' => 'EF05', 'stage' => 'fundamental_1', 'order' => 3],
            // Anos Finais do Ensino Fundamental
            ['name' => '6º Ano EF', 'code' => 'EF06', 'stage' => 'fundamental_2', 'order' => 4],
            ['name' => '7º Ano EF', 'code' => 'EF07', 'stage' => 'fundamental_2', 'order' => 5],
            ['name' => '8º Ano EF', 'code' => 'EF08', 'stage' => 'fundamental_2', 'order' => 6],
            ['name' => '9º Ano EF', 'code' => 'EF09', 'stage' => 'fundamental_2', 'order' => 7],
            // Ensino Médio
            ['name' => '1º Ano EM', 'code' => 'EM01', 'stage' => 'medio', 'order' => 8],
            ['name' => '2º Ano EM', 'code' => 'EM02', 'stage' => 'medio', 'order' => 9],
            ['name' => '3º Ano EM', 'code' => 'EM03', 'stage' => 'medio', 'order' => 10],
        ];

        $grades = [];
        foreach ($data as $item) {
            $grades[$item['code']] = Grade::updateOrCreate(['code' => $item['code']], $item);
        }

        return $grades;
    }

    /** @return array<string, Subject> */
    private function seedSubjects(): array
    {
        $data = [
            // Linguagens
            ['name' => 'Língua Portuguesa', 'slug' => 'portugues',      'icon' => 'book-open',   'color' => '#EF4444'],
            ['name' => 'Arte',               'slug' => 'arte',           'icon' => 'palette',     'color' => '#A855F7'],
            ['name' => 'Inglês',             'slug' => 'ingles',         'icon' => 'languages',   'color' => '#F97316'],
            // Matemática
            ['name' => 'Matemática',         'slug' => 'matematica',     'icon' => 'calculator',  'color' => '#3B82F6'],
            // Ciências da Natureza
            ['name' => 'Ciências',           'slug' => 'ciencias',       'icon' => 'flask',       'color' => '#10B981'],
            ['name' => 'Biologia',           'slug' => 'biologia',       'icon' => 'leaf',        'color' => '#22C55E'],
            ['name' => 'Física',             'slug' => 'fisica',         'icon' => 'zap',         'color' => '#06B6D4'],
            ['name' => 'Química',            'slug' => 'quimica',        'icon' => 'atom',        'color' => '#EC4899'],
            // Ciências Humanas
            ['name' => 'História',           'slug' => 'historia',       'icon' => 'landmark',    'color' => '#F59E0B'],
            ['name' => 'Geografia',          'slug' => 'geografia',      'icon' => 'globe',       'color' => '#8B5CF6'],
            ['name' => 'Filosofia',          'slug' => 'filosofia',      'icon' => 'lightbulb',   'color' => '#6366F1'],
            ['name' => 'Sociologia',         'slug' => 'sociologia',     'icon' => 'users',       'color' => '#84CC16'],
        ];

        $subjects = [];
        foreach ($data as $item) {
            $subjects[$item['slug']] = Subject::updateOrCreate(['slug' => $item['slug']], $item);
        }

        return $subjects;
    }

    /**
     * @param  array<string, Grade>  $grades
     * @param  array<string, Subject>  $subjects
     */
    private function seedPaths(array $grades, array $subjects): void
    {
        // ── Ensino Fundamental — Anos Iniciais (3º ao 5º) ──────────────────────
        // Linguagens, Matemática, Ciências da Natureza, Ciências Humanas, Ensino Religioso
        $iniciais = ['EF03', 'EF04', 'EF05'];
        $matériasIniciais = ['portugues', 'arte', 'matematica', 'ciencias', 'historia', 'geografia'];

        foreach ($iniciais as $gradeCode) {
            $gradeName = $grades[$gradeCode]->name;
            foreach ($matériasIniciais as $subjectSlug) {
                $subjectName = $subjects[$subjectSlug]->name;
                Path::updateOrCreate(
                    ['grade_id' => $grades[$gradeCode]->id, 'subject_id' => $subjects[$subjectSlug]->id, 'path_type' => 'regular'],
                    ['title' => "{$gradeName} — {$subjectName}", 'published' => true]
                );
            }
        }

        // ── Ensino Fundamental — Anos Finais (6º ao 9º) ────────────────────────
        // Inclui Inglês como obrigatório a partir do 6º ano
        $finais = ['EF06', 'EF07', 'EF08', 'EF09'];
        $matériasFinais = ['portugues', 'arte', 'ingles', 'matematica', 'ciencias', 'historia', 'geografia'];

        foreach ($finais as $gradeCode) {
            $gradeName = $grades[$gradeCode]->name;
            foreach ($matériasFinais as $subjectSlug) {
                $subjectName = $subjects[$subjectSlug]->name;
                Path::updateOrCreate(
                    ['grade_id' => $grades[$gradeCode]->id, 'subject_id' => $subjects[$subjectSlug]->id, 'path_type' => 'regular'],
                    ['title' => "{$gradeName} — {$subjectName}", 'published' => true]
                );
            }
        }

        // ── Ensino Médio (1º ao 3º) ─────────────────────────────────────────────
        // Linguagens e suas Tecnologias + Matemática + Ciências da Natureza + Ciências Humanas e Sociais
        $medio = ['EM01', 'EM02', 'EM03'];
        $matériasMédio = ['portugues', 'arte', 'ingles', 'matematica', 'biologia', 'fisica', 'quimica', 'historia', 'geografia', 'filosofia', 'sociologia'];

        foreach ($medio as $gradeCode) {
            $gradeName = $grades[$gradeCode]->name;
            foreach ($matériasMédio as $subjectSlug) {
                $subjectName = $subjects[$subjectSlug]->name;
                Path::updateOrCreate(
                    ['grade_id' => $grades[$gradeCode]->id, 'subject_id' => $subjects[$subjectSlug]->id, 'path_type' => 'regular'],
                    ['title' => "{$gradeName} — {$subjectName}", 'published' => true]
                );
            }
        }

        // ── ENEM — 4 grandes áreas (associadas ao 3º ano EM) ───────────────────
        $enemPaths = [
            ['subject' => 'portugues',  'title' => 'ENEM — Linguagens, Códigos e suas Tecnologias'],
            ['subject' => 'matematica', 'title' => 'ENEM — Matemática e suas Tecnologias'],
            ['subject' => 'biologia',   'title' => 'ENEM — Ciências da Natureza e suas Tecnologias'],
            ['subject' => 'historia',   'title' => 'ENEM — Ciências Humanas e Sociais Aplicadas'],
        ];

        foreach ($enemPaths as $item) {
            Path::updateOrCreate(
                ['grade_id' => $grades['EM03']->id, 'subject_id' => $subjects[$item['subject']]->id, 'path_type' => 'enem'],
                ['title' => $item['title'], 'published' => true]
            );
        }

    }

    /**
     * @param  array<string, Grade>  $grades
     * @param  array<string, Subject>  $subjects
     */
    private function seedSkills(array $grades, array $subjects): void
    {
        $skills = [
            // ── Anos Iniciais — Língua Portuguesa ──────────────────────────────
            [
                'code' => 'EF35LP01', 'grade' => 'EF03', 'subject' => 'portugues',
                'thematic_unit' => 'Leitura',
                'knowledge_object' => 'Estratégias de leitura',
                'description' => 'Ler e compreender, silenciosamente e, em seguida, em voz alta, com autonomia e fluência, textos curtos com nível de textualidade adequado.',
            ],
            [
                'code' => 'EF15LP01', 'grade' => 'EF04', 'subject' => 'portugues',
                'thematic_unit' => 'Produção de Textos',
                'knowledge_object' => 'Escrita autônoma',
                'description' => 'Escrever palavras, frases, textos curtos nas formas imprensa e cursiva, com letras maiúsculas e minúsculas, utilizando espaçamento entre as palavras, sinais de pontuação e de acentuação.',
            ],
            // ── Anos Iniciais — Matemática ──────────────────────────────────────
            [
                'code' => 'EF03MA01', 'grade' => 'EF03', 'subject' => 'matematica',
                'thematic_unit' => 'Números',
                'knowledge_object' => 'Leitura e escrita de números',
                'description' => 'Ler, escrever e comparar números naturais de até a ordem das centenas de milhar.',
            ],
            [
                'code' => 'EF04MA01', 'grade' => 'EF04', 'subject' => 'matematica',
                'thematic_unit' => 'Números',
                'knowledge_object' => 'Sistema de numeração decimal',
                'description' => 'Ler, escrever e ordenar números naturais até a ordem das dezenas de milhar.',
            ],
            [
                'code' => 'EF05MA01', 'grade' => 'EF05', 'subject' => 'matematica',
                'thematic_unit' => 'Números',
                'knowledge_object' => 'Frações',
                'description' => 'Identificar e representar frações (com denominadores 2, 3, 4, 5, 6, 8 e 10) em situações diversas, incluindo o registro em representação decimal.',
            ],
            // ── Anos Finais — Matemática ────────────────────────────────────────
            [
                'code' => 'EF06MA01', 'grade' => 'EF06', 'subject' => 'matematica',
                'thematic_unit' => 'Números',
                'knowledge_object' => 'Sistema de numeração decimal',
                'description' => 'Comparar, ordenar, ler e escrever números naturais e racionais na representação decimal, com compreensão das principais características do sistema de numeração decimal.',
            ],
            [
                'code' => 'EF06MA07', 'grade' => 'EF06', 'subject' => 'matematica',
                'thematic_unit' => 'Números',
                'knowledge_object' => 'Múltiplos e divisores',
                'description' => 'Resolver e elaborar problemas que envolvam cálculo do mínimo múltiplo comum e máximo divisor comum, apenas com números naturais.',
            ],
            [
                'code' => 'EF06MA12', 'grade' => 'EF06', 'subject' => 'matematica',
                'thematic_unit' => 'Álgebra',
                'knowledge_object' => 'Noção de variável',
                'description' => 'Reconhecer que a escrita algébrica (expressões, equações, inequações, fórmulas) é uma representação das relações entre as quantidades.',
            ],
            [
                'code' => 'EF07MA01', 'grade' => 'EF07', 'subject' => 'matematica',
                'thematic_unit' => 'Números',
                'knowledge_object' => 'Números inteiros',
                'description' => 'Resolver e elaborar problemas com números inteiros, envolvendo as operações e suas propriedades.',
            ],
            [
                'code' => 'EF07MA16', 'grade' => 'EF07', 'subject' => 'matematica',
                'thematic_unit' => 'Álgebra',
                'knowledge_object' => 'Equações do 1º grau',
                'description' => 'Resolver equações e inequações do 1º grau com uma incógnita, utilizando as propriedades da igualdade.',
            ],
            [
                'code' => 'EF08MA07', 'grade' => 'EF08', 'subject' => 'matematica',
                'thematic_unit' => 'Álgebra',
                'knowledge_object' => 'Sistemas de equações',
                'description' => 'Associar a resolução de sistema de equações do 1º grau com duas incógnitas a problemas e utilizar métodos algébricos e gráficos.',
            ],
            [
                'code' => 'EF09MA08', 'grade' => 'EF09', 'subject' => 'matematica',
                'thematic_unit' => 'Álgebra',
                'knowledge_object' => 'Equações do 2º grau',
                'description' => 'Resolver e elaborar problemas com equações do 2º grau na forma reduzida por meio de fatoração ou da fórmula de Bhaskara.',
            ],
            // ── Anos Finais — Língua Portuguesa ────────────────────────────────
            [
                'code' => 'EF06LP01', 'grade' => 'EF06', 'subject' => 'portugues',
                'thematic_unit' => 'Leitura',
                'knowledge_object' => 'Estratégias de leitura',
                'description' => 'Selecionar procedimentos de leitura adequados a diferentes objetivos e interesses, levando em conta características do gênero e suporte textuais.',
            ],
            [
                'code' => 'EF06LP10', 'grade' => 'EF06', 'subject' => 'portugues',
                'thematic_unit' => 'Produção de Textos',
                'knowledge_object' => 'Coesão e coerência',
                'description' => 'Produzir textos em diferentes gêneros, considerando sua adequação ao contexto de produção e circulação.',
            ],
            [
                'code' => 'EF09LP01', 'grade' => 'EF09', 'subject' => 'portugues',
                'thematic_unit' => 'Leitura',
                'knowledge_object' => 'Análise crítica',
                'description' => 'Analisar o fenômeno da variação linguística e reconhecer a existência de variedades do português e o caráter múltiplo e Hetérogêneo da língua portuguesa.',
            ],
            // ── Anos Finais — Ciências ──────────────────────────────────────────
            [
                'code' => 'EF06CI01', 'grade' => 'EF06', 'subject' => 'ciencias',
                'thematic_unit' => 'Matéria e Energia',
                'knowledge_object' => 'Misturas',
                'description' => 'Classificar misturas como homogêneas e heterogêneas, identificando fases e componentes.',
            ],
            [
                'code' => 'EF08CI08', 'grade' => 'EF08', 'subject' => 'ciencias',
                'thematic_unit' => 'Matéria e Energia',
                'knowledge_object' => 'Fontes e usos de energia',
                'description' => 'Identificar fontes e tipos de energia utilizados em residências, indústrias e no comércio e discutir questões de sustentabilidade.',
            ],
            // ── Ensino Médio — Matemática ───────────────────────────────────────
            [
                'code' => 'EM13MAT101', 'grade' => 'EM01', 'subject' => 'matematica',
                'thematic_unit' => 'Números e Álgebra',
                'knowledge_object' => 'Funções',
                'description' => 'Interpretar situações econômicas, sociais e das Ciências da Natureza que envolvem a variação de duas grandezas, pela análise de tabelas, gráficos e expressões algébricas.',
            ],
            [
                'code' => 'EM13MAT102', 'grade' => 'EM01', 'subject' => 'matematica',
                'thematic_unit' => 'Números e Álgebra',
                'knowledge_object' => 'Progressões',
                'description' => 'Analisar tabelas, gráficos e expressões que representam variações de grandezas, identificando padrões e realizando previsões.',
            ],
            [
                'code' => 'EM13MAT301', 'grade' => 'EM02', 'subject' => 'matematica',
                'thematic_unit' => 'Geometria',
                'knowledge_object' => 'Trigonometria',
                'description' => 'Resolver e elaborar problemas sobre leis de senos e cossenos, utilizando conceitos de semelhança e propriedades dos triângulos.',
            ],
            [
                'code' => 'EM13MAT401', 'grade' => 'EM03', 'subject' => 'matematica',
                'thematic_unit' => 'Probabilidade e Estatística',
                'knowledge_object' => 'Combinatória e probabilidade',
                'description' => 'Calcular probabilidades de eventos, aplicando os conceitos de espaço amostral, eventos mutuamente exclusivos e complementares.',
            ],
            // ── Ensino Médio — Física ───────────────────────────────────────────
            [
                'code' => 'EM13CNT101', 'grade' => 'EM01', 'subject' => 'fisica',
                'thematic_unit' => 'Matéria e Energia',
                'knowledge_object' => 'Cinemática',
                'description' => 'Analisar e representar as transformações e conservações em sistemas que envolvam quantidade de movimento e variações de energia cinética.',
            ],
            [
                'code' => 'EM13CNT102', 'grade' => 'EM02', 'subject' => 'fisica',
                'thematic_unit' => 'Matéria e Energia',
                'knowledge_object' => 'Termodinâmica',
                'description' => 'Analisar e explicar as transformações e conservações em sistemas termodinâmicos e as implicações nas mudanças de estado da matéria.',
            ],
            // ── Ensino Médio — Química ──────────────────────────────────────────
            [
                'code' => 'EM13CNT201', 'grade' => 'EM01', 'subject' => 'quimica',
                'thematic_unit' => 'Matéria e Energia',
                'knowledge_object' => 'Reações químicas',
                'description' => 'Analisar e representar as transformações e conservações em sistemas que envolvam reações químicas, incluindo estequiometria e rendimento.',
            ],
            [
                'code' => 'EM13CNT202', 'grade' => 'EM02', 'subject' => 'quimica',
                'thematic_unit' => 'Matéria e Energia',
                'knowledge_object' => 'Equilíbrio químico',
                'description' => 'Investigar e explicar o equilíbrio químico em sistemas dinâmicos, aplicando o princípio de Le Chatelier e os fatores que o deslocam.',
            ],
            // ── Ensino Médio — Biologia ─────────────────────────────────────────
            [
                'code' => 'EM13CNT301', 'grade' => 'EM01', 'subject' => 'biologia',
                'thematic_unit' => 'Vida e Evolução',
                'knowledge_object' => 'Célula',
                'description' => 'Construir e utilizar tabelas, gráficos e esquemas para sistematizar dados sobre componentes químicos da célula, sobre sua origem e evolução.',
            ],
            [
                'code' => 'EM13CNT302', 'grade' => 'EM03', 'subject' => 'biologia',
                'thematic_unit' => 'Vida e Evolução',
                'knowledge_object' => 'Evolução biológica',
                'description' => 'Analisar e discutir modelos, teorias e leis referentes à evolução biológica, seleção natural e à diversidade dos seres vivos.',
            ],
            // ── Ensino Médio — História ─────────────────────────────────────────
            [
                'code' => 'EM13CHS101', 'grade' => 'EM01', 'subject' => 'historia',
                'thematic_unit' => 'Tempo e Espaço',
                'knowledge_object' => 'Processos históricos',
                'description' => 'Analisar e comparar diferentes fontes e narrativas expressas em variadas linguagens para analisar processos e eventos históricos.',
            ],
            // ── Ensino Médio — Filosofia ────────────────────────────────────────
            [
                'code' => 'EM13CHS601', 'grade' => 'EM01', 'subject' => 'filosofia',
                'thematic_unit' => 'Política e Filosofia',
                'knowledge_object' => 'Cidadania e democracia',
                'description' => 'Identificar e analisar as demandas e os protagonismos políticos, sociais e culturais dos povos indígenas e das populações afrodescendentes no Brasil.',
            ],
            // ── Ensino Médio — Sociologia ───────────────────────────────────────
            [
                'code' => 'EM13CHS501', 'grade' => 'EM02', 'subject' => 'sociologia',
                'thematic_unit' => 'Sociedade e Cultura',
                'knowledge_object' => 'Desigualdade social',
                'description' => 'Identificar e combater as diversas formas de injustiça, preconceito e violência, adotando princípios éticos, democráticos, inclusivos e solidários.',
            ],
        ];

        foreach ($skills as $item) {
            BnccSkill::updateOrCreate(
                ['code' => $item['code']],
                [
                    'grade_id' => $grades[$item['grade']]->id,
                    'subject_id' => $subjects[$item['subject']]->id,
                    'thematic_unit' => $item['thematic_unit'],
                    'knowledge_object' => $item['knowledge_object'],
                    'description' => $item['description'],
                    'version' => 1,
                    'active' => true,
                ]
            );
        }
    }
}
