<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório da Turma — {{ $schoolClass->name }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            margin: 0;
            padding: 20px;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 4px;
        }

        .subtitle {
            color: #555;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .meta {
            margin-bottom: 16px;
            font-size: 11px;
            color: #444;
        }

        .summary {
            background: #f5f5f5;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 20px;
            display: flex;
            gap: 30px;
        }

        .summary-item {
            display: inline-block;
            margin-right: 30px;
        }

        .summary-item strong {
            display: block;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead tr {
            background: #4f46e5;
            color: white;
        }

        thead th {
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }

        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }

        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Relatório da Turma</h1>

    <p class="subtitle">
        {{ $schoolClass->name }}
        @if($schoolClass->grade)
            — {{ $schoolClass->grade->name }}
        @endif
        @if($schoolClass->school)
            — {{ $schoolClass->school->name }}
        @endif
    </p>

    <div class="meta">
        Gerado em: {{ $generatedAt }}
    </div>

    <div class="summary">
        <span class="summary-item">
            <strong>{{ $totalStudents }}</strong>
            Alunos
        </span>
        <span class="summary-item">
            <strong>{{ $averageXp }}</strong>
            XP Médio
        </span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Aluno</th>
                <th>XP Total</th>
                <th>Sequência Atual</th>
                <th>Melhor Sequência</th>
                <th>Última Atividade</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                <tr>
                    <td>{{ $student['name'] }}</td>
                    <td>{{ $student['total_xp'] }}</td>
                    <td>{{ $student['current_streak'] }} dias</td>
                    <td>{{ $student['best_streak'] }} dias</td>
                    <td>{{ $student['last_activity'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:#888;">Nenhum aluno matriculado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        AprendeGame — Relatório gerado automaticamente em {{ $generatedAt }}
    </div>
</body>
</html>
