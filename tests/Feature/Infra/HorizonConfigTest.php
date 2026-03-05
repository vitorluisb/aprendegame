<?php

use App\Jobs\AwardXP;
use App\Jobs\UpdateMastery;
use App\Jobs\UpdateQuestionMetrics;
use App\Jobs\UpdateStreak;
use App\Domain\AI\Jobs\GenerateQuestionsForSkill;

it('horizon has critical supervisor configured', function () {
    $defaults = config('horizon.defaults');

    expect($defaults)->toHaveKey('supervisor-critical');
    expect($defaults['supervisor-critical']['queue'])->toContain('critical');
});

it('horizon has default supervisor configured', function () {
    $defaults = config('horizon.defaults');

    expect($defaults)->toHaveKey('supervisor-default');
    expect($defaults['supervisor-default']['queue'])->toContain('default');
});

it('horizon has ai supervisor configured', function () {
    $defaults = config('horizon.defaults');

    expect($defaults)->toHaveKey('supervisor-ai');
    expect($defaults['supervisor-ai']['queue'])->toContain('ai');
});

it('critical jobs are assigned to critical queue', function () {
    $awardXP = new AwardXP(1, 10, 'lesson', 1);
    $updateStreak = new UpdateStreak(1);

    expect($awardXP->queue)->toBe('critical');
    expect($updateStreak->queue)->toBe('critical');
});

it('default jobs are assigned to default queue', function () {
    $updateMastery = new UpdateMastery(1, []);
    $updateMetrics = new UpdateQuestionMetrics(1);

    expect($updateMastery->queue)->toBe('default');
    expect($updateMetrics->queue)->toBe('default');
});

it('ai jobs are assigned to ai queue', function () {
    $aiJob = new GenerateQuestionsForSkill(1, 5);

    expect($aiJob->queue)->toBe('ai');
});

it('production environment has more processes than local', function () {
    $prod = config('horizon.environments.production.supervisor-critical.maxProcesses');
    $local = config('horizon.environments.local.supervisor-critical.maxProcesses');

    expect($prod)->toBeGreaterThan($local);
});
