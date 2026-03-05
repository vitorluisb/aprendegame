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

it('path can be created as vestibular fuvest type', function () {
    $path = Path::factory()->vestibularFuvest()->create();

    expect($path->path_type)->toBe('vestibular_fuvest');
});

it('path can be created as vestibular unicamp type', function () {
    $path = Path::factory()->vestibularUnicamp()->create();

    expect($path->path_type)->toBe('vestibular_unicamp');
});

it('scope ofType filters by path type', function () {
    Path::factory()->enem()->count(3)->create();
    Path::factory()->count(2)->create(); // regular

    expect(Path::ofType('enem')->count())->toBe(3);
    expect(Path::ofType('regular')->count())->toBe(2);
});

it('scope enem filters only enem paths', function () {
    Path::factory()->enem()->count(2)->create();
    Path::factory()->vestibularFuvest()->create();

    expect(Path::enem()->count())->toBe(2);
});

it('scope vestibular filters both vestibular types', function () {
    Path::factory()->vestibularFuvest()->count(2)->create();
    Path::factory()->vestibularUnicamp()->count(1)->create();
    Path::factory()->enem()->create();
    Path::factory()->create(); // regular

    expect(Path::vestibular()->count())->toBe(3);
});

it('path types constant contains all valid types', function () {
    expect(Path::TYPES)->toContain('regular');
    expect(Path::TYPES)->toContain('enem');
    expect(Path::TYPES)->toContain('vestibular_fuvest');
    expect(Path::TYPES)->toContain('vestibular_unicamp');
    expect(Path::TYPES)->toHaveCount(4);
});

it('enem paths reuse existing nodes and questions structure', function () {
    $path = Path::factory()->enem()->create(['title' => 'ENEM — Matemática']);

    expect($path->title)->toBe('ENEM — Matemática');
    expect($path->path_type)->toBe('enem');
    expect($path->nodes()->count())->toBe(0); // estrutura pronta para adicionar nós
});
