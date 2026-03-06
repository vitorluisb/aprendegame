<?php

use App\Domain\Content\Models\Path;

it('paths default to regular type', function () {
    $path = Path::factory()->create();

    expect($path->path_type)->toBe('regular');
});

it('path can be created as enem type', function () {
    $path = Path::factory()->enem()->create();

    expect($path->path_type)->toBe('enem');
});

it('scope ofType filters by path type', function () {
    Path::factory()->enem()->count(3)->create();
    Path::factory()->count(2)->create(); // regular

    expect(Path::ofType('enem')->count())->toBe(3);
    expect(Path::ofType('regular')->count())->toBe(2);
});

it('scope enem filters only enem paths', function () {
    Path::factory()->enem()->count(2)->create();
    Path::factory()->create();

    expect(Path::enem()->count())->toBe(2);
});

it('path types constant contains all valid types', function () {
    expect(Path::TYPES)->toContain('regular');
    expect(Path::TYPES)->toContain('enem');
    expect(Path::TYPES)->toHaveCount(2);
});

it('enem paths reuse existing nodes and questions structure', function () {
    $path = Path::factory()->enem()->create(['title' => 'ENEM — Matemática']);

    expect($path->title)->toBe('ENEM — Matemática');
    expect($path->path_type)->toBe('enem');
    expect($path->nodes()->count())->toBe(0); // estrutura pronta para adicionar nós
});
