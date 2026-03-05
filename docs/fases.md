# 🎓 Plataforma Gamificada BNCC — Plano de Execução por Fases

> **Filosofia:** cada etapa termina com testes obrigatórios antes de avançar.  
> Migrations seguem ordem de dependência para nunca gerar FK quebrada.  
> Models, Factories e Seeders criados junto com cada migration.

---

## Índice

- [Fase 0 — Fundação](#fase-0--fundação)
    - [Etapa 0.1 — Setup do Projeto](#etapa-01--setup-do-projeto)
    - [Etapa 0.2 — Banco de Dados Base (Accounts)](#etapa-02--banco-de-dados-base-accounts)
    - [Etapa 0.3 — Autenticação Completa](#etapa-03--autenticação-completa)
    - [Etapa 0.4 — RBAC e Multi-tenant](#etapa-04--rbac-e-multi-tenant)
    - [Etapa 0.5 — PWA Shell e Frontend Base](#etapa-05--pwa-shell-e-frontend-base)
- [Fase 1 — MVP Core](#fase-1--mvp-core)
    - [Etapa 1.1 — Estrutura BNCC (Content)](#etapa-11--estrutura-bncc-content)
    - [Etapa 1.2 — Trilhas e Nós (Path)](#etapa-12--trilhas-e-nós-path)
    - [Etapa 1.3 — Questões e Banco de Exercícios](#etapa-13--questões-e-banco-de-exercícios)
    - [Etapa 1.4 — LessonPlayer (Engine de Lição)](#etapa-14--lessonplayer-engine-de-lição)
    - [Etapa 1.5 — Gamificação Core](#etapa-15--gamificação-core)
    - [Etapa 1.6 — Revisão Inteligente (Spaced Repetition)](#etapa-16--revisão-inteligente-spaced-repetition)
    - [Etapa 1.7 — Ranking e Ligas](#etapa-17--ranking-e-ligas)
    - [Etapa 1.8 — Gestão Escolar (Turmas e Tarefas)](#etapa-18--gestão-escolar-turmas-e-tarefas)
    - [Etapa 1.9 — Dashboards](#etapa-19--dashboards)
    - [Etapa 1.10 — IA: Geração e Explicação](#etapa-110--ia-geração-e-explicação)
- [Fase 2 — Qualidade e Escala](#fase-2--qualidade-e-escala)
    - [Etapa 2.1 — Tipos de Questão Avançados](#etapa-21--tipos-de-questão-avançados)
    - [Etapa 2.2 — Loja e Avatares](#etapa-22--loja-e-avatares)
    - [Etapa 2.3 — Tutor IA (Chat)](#etapa-23--tutor-ia-chat)
    - [Etapa 2.4 — Relatórios PDF](#etapa-24--relatórios-pdf)
    - [Etapa 2.5 — Otimizações de Infra](#etapa-25--otimizações-de-infra)
- [Fase 3 — Premium](#fase-3--premium)

---

## Convenções deste documento

```
✅ GATE DE TESTES  →  obrigatório antes de avançar
⚠️  Armadilha       →  ponto onde erros são comuns
💡 Dica            →  atalho ou boa prática
🔴 Crítico         →  não pular em hipótese alguma
```

---

---

# Fase 0 — Fundação

> **Meta:** projeto rodando com auth completo, multi-tenant seguro e PWA shell.  
> **Duração estimada:** 2–3 semanas  
> **Regra de ouro:** nenhum código de feature entra antes de auth + RBAC estarem 100% testados.

---

## Etapa 0.1 — Setup do Projeto

### O que fazer

```bash
# 1. Criar projeto Laravel 12
composer create-project laravel/laravel bncc-platform
cd bncc-platform

# 2. Instalar Inertia + Vue 3 + Vite
composer require inertiajs/inertia-laravel
npm install @inertiajs/vue3 vue@next @vitejs/plugin-vue

# 3. Instalar Filament 3 (admin)
composer require filament/filament:"^3.0" -W
php artisan filament:install --panels

# 4. Instalar pacotes de auth
composer require laravel/fortify
composer require laravel/socialite
composer require laravel/sanctum

# 5. PWA
npm install vite-plugin-pwa

# 6. Instalar Horizon (filas)
composer require laravel/horizon
php artisan horizon:install

# 7. Telescope (dev)
composer require laravel/telescope --dev
php artisan telescope:install

# 8. Configurar .env
# DB_CONNECTION=mysql
# REDIS_CLIENT=phpredis
# QUEUE_CONNECTION=redis
# SESSION_DRIVER=redis
```

### Estrutura de pastas a criar

```
app/
├── Domain/
│   ├── Accounts/
│   ├── Content/
│   ├── Gameplay/
│   ├── Analytics/
│   └── AI/
├── Http/
│   ├── Controllers/
│   └── Middleware/
└── Filament/
    └── Resources/

resources/
├── js/
│   ├── Pages/
│   ├── Components/
│   └── Layouts/
└── views/
    └── app.blade.php
```

### Configurações obrigatórias

```php
// config/app.php
'password_timeout' => 10800,

// config/session.php
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'same_site' => 'lax',

// config/hashing.php
'driver' => 'argon',
'argon' => ['memory' => 65536, 'threads' => 1, 'time' => 4],
```

---

### ✅ GATE 0.1 — Testes de Setup

```bash
# Verificar que a aplicação sobe sem erros
php artisan serve

# Checar que Filament panel abre em /admin
# Checar que Horizon roda
php artisan horizon

# Checar que queue worker processa jobs
php artisan queue:work --once

# Rodar testes base (deve passar 100%)
php artisan test
```

**Critérios de aprovação:**
- [ ] `php artisan test` → 0 erros, 0 falhas
- [ ] `/admin` abre o painel Filament
- [ ] Redis conectado (`php artisan tinker` → `Redis::ping()` retorna `PONG`)
- [ ] Horizon dashboard acessível em `/horizon`
- [ ] Nenhum erro no log após subir

> ⚠️ **Não avance se Redis não estiver funcionando** — filas e sessões dependem disso.

---

## Etapa 0.2 — Banco de Dados Base (Accounts)

### Ordem obrigatória de migrations

> 🔴 **Siga exatamente esta ordem** — cada tabela depende da anterior via FK.

```
1. schools
2. users  (adicionar school_id, role, provider)
3. school_members
4. students
5. student_guardians
6. audit_logs
```

### Migration: `schools`

```php
Schema::create('schools', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('plan')->default('basic'); // basic | pro | enterprise
    $table->json('settings')->nullable();
    $table->boolean('active')->default(true);
    $table->timestamps();
    $table->softDeletes();
});
```

### Migration: modificar `users` (padrão Laravel)

```php
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
    $table->string('role')->default('student');
    // student | guardian | teacher | school_admin | super_admin
    $table->string('provider')->nullable(); // google | null
    $table->string('provider_id')->nullable();
    $table->string('avatar_url')->nullable();
    $table->index(['school_id', 'role']);
});
```

### Migration: `school_members`

```php
Schema::create('school_members', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('role'); // teacher | school_admin
    $table->timestamp('invited_at')->nullable();
    $table->timestamp('accepted_at')->nullable();
    $table->string('invited_by_email')->nullable();
    $table->timestamps();

    $table->unique(['user_id', 'school_id']);
    $table->index('school_id');
});
```

### Migration: `students`

```php
Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    // nullable: aluno pode não ter login próprio
    $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
    $table->string('name');
    $table->date('birth_date')->nullable();
    $table->string('avatar_url')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['school_id']);
    $table->index(['user_id']);
});
```

### Migration: `student_guardians`

```php
Schema::create('student_guardians', function (Blueprint $table) {
    $table->id();
    $table->foreignId('guardian_user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->string('relationship')->default('parent'); // parent | guardian | other
    $table->boolean('consent_given')->default(false); // LGPD
    $table->timestamp('consent_given_at')->nullable();
    $table->timestamps();

    $table->unique(['guardian_user_id', 'student_id']);
});
```

### Migration: `audit_logs`

```php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('action'); // login | logout | role_change | content_edit | etc
    $table->string('target_type')->nullable(); // User | School | Question
    $table->unsignedBigInteger('target_id')->nullable();
    $table->json('meta')->nullable();
    $table->string('ip', 45)->nullable();
    $table->string('user_agent')->nullable();
    $table->timestamp('created_at')->useCurrent();

    $table->index(['user_id', 'action']);
    $table->index('created_at');
});
```

### Models obrigatórios nesta etapa

```php
// app/Domain/Accounts/Models/School.php
class School extends Model {
    use SoftDeletes;
    protected $casts = ['settings' => 'array', 'active' => 'boolean'];

    public function members() { return $this->hasMany(SchoolMember::class); }
    public function students() { return $this->hasMany(Student::class); }
}

// app/Domain/Accounts/Models/Student.php
class Student extends Model {
    use SoftDeletes;

    public function user() { return $this->belongsTo(User::class); }
    public function school() { return $this->belongsTo(School::class); }
    public function guardians() {
        return $this->belongsToMany(User::class, 'student_guardians', 'student_id', 'guardian_user_id')
                    ->withPivot('relationship', 'consent_given');
    }
}
```

### Factories e Seeders

```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    // ORDEM IMPORTA
    $this->call([
        SchoolSeeder::class,      // 1. escolas
        UserSeeder::class,        // 2. usuários (usa school_id)
        SchoolMemberSeeder::class, // 3. membros (usa user_id + school_id)
        StudentSeeder::class,     // 4. alunos
        StudentGuardianSeeder::class, // 5. vínculos
    ]);
}
```

---

### ✅ GATE 0.2 — Testes de Banco

```bash
php artisan migrate:fresh --seed
php artisan test --filter=AccountsTest
```

```php
// tests/Feature/Accounts/SchoolTest.php
it('creates school with unique slug', function () {
    $school = School::factory()->create(['slug' => 'escola-a']);
    expect(School::where('slug', 'escola-a')->exists())->toBeTrue();
});

it('soft deletes school without destroying users', function () {
    $school = School::factory()->has(User::factory()->count(3))->create();
    $school->delete();
    expect(School::withTrashed()->find($school->id))->not->toBeNull();
    expect(User::where('school_id', $school->id)->count())->toBe(3);
});

it('student can exist without user account', function () {
    $student = Student::factory()->create(['user_id' => null]);
    expect($student->user_id)->toBeNull();
    expect($student->exists)->toBeTrue();
});

it('student_guardians records consent', function () {
    $guardian = User::factory()->create(['role' => 'guardian']);
    $student = Student::factory()->create();
    $guardian->guardiansOf()->attach($student->id, [
        'relationship' => 'parent',
        'consent_given' => true,
        'consent_given_at' => now(),
    ]);
    expect($student->guardians()->where('guardian_user_id', $guardian->id)->exists())->toBeTrue();
});
```

**Critérios de aprovação:**
- [ ] `migrate:fresh --seed` roda sem erros de FK
- [ ] Todos os testes de Accounts passam
- [ ] Soft delete não destrói registros relacionados
- [ ] `student_id` nullable funciona no banco

---

## Etapa 0.3 — Autenticação Completa

### Configurar Fortify

```php
// config/fortify.php
'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
    // Features::twoFactorAuthentication(), // fase 3
],
'passwords' => 'users',
'username' => 'email',
```

### Regras de senha

```php
// app/Providers/FortifyServiceProvider.php
Fortify::registerView(fn() => inertia('Auth/Register'));
Fortify::loginView(fn() => inertia('Auth/Login'));

// Política de senha forte
$rules = Password::min(12)
    ->mixedCase()
    ->numbers()
    ->symbols()
    ->uncompromised();
```

### Google OAuth com Socialite

```php
// routes/web.php
Route::get('/auth/google', [SocialAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [SocialAuthController::class, 'callback']);

// app/Http/Controllers/SocialAuthController.php
public function callback(): RedirectResponse
{
    $socialUser = Socialite::driver('google')->user();

    $user = User::updateOrCreate(
        ['email' => $socialUser->getEmail()],
        [
            'name' => $socialUser->getName(),
            'provider' => 'google',
            'provider_id' => $socialUser->getId(),
            'avatar_url' => $socialUser->getAvatar(),
            'email_verified_at' => now(), // Google já verifica
        ]
    );

    Auth::login($user);
    AuditLog::record($user, 'login', ['provider' => 'google']);

    return redirect()->intended('/dashboard');
}
```

### Middleware de verificação obrigatória

```php
// app/Http/Middleware/EnsureEmailIsVerified.php
// Aplicar em todas as rotas autenticadas exceto /verify-email
Route::middleware(['auth', 'verified'])->group(function () {
    // todas as rotas do app
});
```

### Rate Limiting

```php
// app/Providers/RouteServiceProvider.php
RateLimiter::for('login', function (Request $request) {
    return [
        Limit::perMinute(5)->by($request->ip()),
        Limit::perMinute(3)->by($request->input('email')),
    ];
});

RateLimiter::for('password-reset', function (Request $request) {
    return Limit::perHour(3)->by($request->ip());
});

RateLimiter::for('email-verification', function (Request $request) {
    return Limit::perHour(3)->by($request->user()?->id ?: $request->ip());
});
```

### Sistema de Convites

```php
// app/Domain/Accounts/Services/InviteService.php
class InviteService
{
    public function invite(string $email, School $school, string $role): void
    {
        $token = Str::random(64);
        Cache::put("invite:{$token}", [
            'email' => $email,
            'school_id' => $school->id,
            'role' => $role,
        ], now()->addHours(48));

        Mail::to($email)->send(new InviteMail($token, $school));
    }

    public function accept(string $token, User $user): void
    {
        $data = Cache::pull("invite:{$token}");
        throw_if(!$data, \Exception::class, 'Convite inválido ou expirado');

        SchoolMember::create([
            'user_id' => $user->id,
            'school_id' => $data['school_id'],
            'role' => $data['role'],
            'accepted_at' => now(),
        ]);
    }
}
```

### AuditLog Service

```php
// app/Domain/Accounts/Services/AuditLog.php
class AuditLog
{
    public static function record(User $user, string $action, array $meta = []): void
    {
        AuditLogModel::create([
            'user_id' => $user->id,
            'action' => $action,
            'meta' => $meta,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

// Usar em eventos:
// Login::class, Logout::class, Registered::class
```

---

### ✅ GATE 0.3 — Testes de Auth

```bash
php artisan test --filter=AuthTest
```

```php
// tests/Feature/Auth/RegistrationTest.php
it('registers user and sends verification email', function () {
    Mail::fake();
    $response = $this->post('/register', [
        'name' => 'Teste',
        'email' => 'teste@exemplo.com',
        'password' => 'Senha@Forte123',
        'password_confirmation' => 'Senha@Forte123',
    ]);
    $response->assertRedirect('/email/verify');
    Mail::assertSent(VerifyEmail::class);
});

it('blocks unverified user from dashboard', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user)->get('/dashboard')
         ->assertRedirect('/email/verify');
});

it('rejects weak password', function () {
    $this->post('/register', [
        'name' => 'Fraco',
        'email' => 'fraco@exemplo.com',
        'password' => '123456',
        'password_confirmation' => '123456',
    ])->assertSessionHasErrors('password');
});

it('rate limits login after 5 attempts', function () {
    for ($i = 0; $i < 6; $i++) {
        $this->post('/login', ['email' => 'x@x.com', 'password' => 'wrong']);
    }
    $this->post('/login', ['email' => 'x@x.com', 'password' => 'wrong'])
         ->assertStatus(429);
});

it('invite expires after 48 hours', function () {
    $service = app(InviteService::class);
    $school = School::factory()->create();
    $service->invite('novo@prof.com', $school, 'teacher');

    // simular expiração
    Carbon::setTestNow(now()->addHours(49));
    $user = User::factory()->create(['email' => 'novo@prof.com']);

    expect(fn() => $service->accept('token-invalido', $user))
        ->toThrow(\Exception::class, 'Convite inválido ou expirado');
});

it('google oauth marks email as verified', function () {
    $socialUser = Mockery::mock(AbstractUser::class);
    $socialUser->shouldReceive('getEmail')->andReturn('google@user.com');
    $socialUser->shouldReceive('getName')->andReturn('Google User');
    $socialUser->shouldReceive('getId')->andReturn('google-id-123');
    $socialUser->shouldReceive('getAvatar')->andReturn('https://avatar.url');

    Socialite::shouldReceive('driver->user')->andReturn($socialUser);

    $this->get('/auth/google/callback');

    $user = User::where('email', 'google@user.com')->first();
    expect($user->email_verified_at)->not->toBeNull();
    expect($user->provider)->toBe('google');
});
```

**Critérios de aprovação:**
- [ ] Registro → e-mail de verificação enviado
- [ ] Rota `/dashboard` bloqueada para não-verificados
- [ ] Senha fraca rejeitada com mensagem clara
- [ ] Rate limit retorna 429 após 5 tentativas
- [ ] Google OAuth cria usuário com `email_verified_at` preenchido
- [ ] Convite expirado lança exceção

---

## Etapa 0.4 — RBAC e Multi-tenant

### Policy base com school_id

```php
// app/Policies/BaseSchoolPolicy.php
abstract class BaseSchoolPolicy
{
    protected function belongsToSchool(User $user, int $schoolId): bool
    {
        return $user->school_id === $schoolId
            || SchoolMember::where('user_id', $user->id)
                           ->where('school_id', $schoolId)
                           ->exists();
    }

    protected function hasRole(User $user, string|array $roles): bool
    {
        return in_array($user->role, (array) $roles);
    }
}
```

### Global Scope anti-vazamento

```php
// app/Domain/Accounts/Scopes/SchoolScope.php
class SchoolScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && auth()->user()->role !== 'super_admin') {
            $builder->where($model->getTable() . '.school_id', auth()->user()->school_id);
        }
    }
}

// Aplicar em todos os Models escolares:
protected static function booted(): void
{
    static::addGlobalScope(new SchoolScope());
}
```

> 🔴 **Super admin NUNCA recebe o scope** — ele precisa ver tudo.

### Cache de permissões

```php
// app/Domain/Accounts/Services/PermissionCache.php
class PermissionCache
{
    public static function get(User $user): array
    {
        return Cache::remember(
            "permissions:{$user->id}:{$user->school_id}",
            now()->addMinutes(30),
            fn() => self::build($user)
        );
    }

    public static function flush(User $user): void
    {
        Cache::forget("permissions:{$user->id}:{$user->school_id}");
    }

    private static function build(User $user): array
    {
        // montar array de permissões por módulo
        return [
            'content.view' => in_array($user->role, ['teacher', 'school_admin', 'super_admin']),
            'content.edit' => in_array($user->role, ['school_admin', 'super_admin']),
            'students.view' => in_array($user->role, ['teacher', 'school_admin', 'super_admin', 'guardian']),
            // ... demais permissões
        ];
    }
}
```

---

### ✅ GATE 0.4 — Testes de RBAC

```php
it('student cannot access teacher routes', function () {
    $student = User::factory()->create(['role' => 'student']);
    $this->actingAs($student)->get('/teacher/classes')
         ->assertStatus(403);
});

it('school scope prevents cross-school data access', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $userA = User::factory()->create(['school_id' => $schoolA->id, 'role' => 'teacher']);

    // Criar dado da escola B
    $studentB = Student::factory()->create(['school_id' => $schoolB->id]);

    // Usuário da escola A não pode ver aluno da escola B
    $this->actingAs($userA);
    expect(Student::find($studentB->id))->toBeNull();
});

it('super_admin bypasses school scope', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $schoolB = School::factory()->create();
    $student = Student::factory()->create(['school_id' => $schoolB->id]);

    $this->actingAs($admin);
    expect(Student::find($student->id))->not->toBeNull();
});

it('permission cache is invalidated on role change', function () {
    $user = User::factory()->create(['role' => 'student']);
    PermissionCache::get($user); // popula cache

    $user->update(['role' => 'teacher']);
    PermissionCache::flush($user);

    $perms = PermissionCache::get($user);
    expect($perms['content.view'])->toBeTrue();
});
```

**Critérios de aprovação:**
- [ ] Aluno recebe 403 em rotas de professor/admin
- [ ] School scope bloqueia dados de outra escola
- [ ] Super admin vê dados de todas as escolas
- [ ] Cache de permissões é invalidado ao mudar role

---

## Etapa 0.5 — PWA Shell e Frontend Base

### Configurar Vite com PWA

```js
// vite.config.js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
    plugins: [
        laravel({ input: ['resources/js/app.js'], refresh: true }),
        vue(),
        VitePWA({
            registerType: 'autoUpdate',
            manifest: {
                name: 'Estudos BNCC',
                short_name: 'BNCC',
                theme_color: '#1565C0',
                icons: [
                    { src: '/icons/icon-192.png', sizes: '192x192', type: 'image/png' },
                    { src: '/icons/icon-512.png', sizes: '512x512', type: 'image/png' },
                ],
            },
            workbox: {
                runtimeCaching: [
                    {
                        urlPattern: /^https:\/\/.*\/api\//,
                        handler: 'NetworkFirst',
                        options: { cacheName: 'api-cache', expiration: { maxAgeSeconds: 300 } },
                    },
                ],
            },
        }),
    ],
})
```

### Layout base Vue/Inertia

```
resources/js/
├── app.js              (entry point Inertia)
├── Layouts/
│   ├── AppLayout.vue   (aluno: topbar + bottomnav)
│   ├── AdminLayout.vue (professor/escola)
│   └── GuestLayout.vue (auth pages)
├── Pages/
│   ├── Auth/
│   │   ├── Login.vue
│   │   ├── Register.vue
│   │   └── VerifyEmail.vue
│   └── Dashboard/
│       └── Index.vue
└── Components/
    └── UI/             (botões, cards, inputs base)
```

---

### ✅ GATE 0.5 — Testes de PWA e Frontend

```bash
# Build de produção sem erros
npm run build

# Lighthouse PWA score (Chrome DevTools)
# Mínimo aceitável: PWA score >= 80
```

**Critérios de aprovação:**
- [ ] `npm run build` sem erros ou warnings críticos
- [ ] App instalável (manifest + service worker válidos)
- [ ] Páginas de auth renderizam corretamente via Inertia
- [ ] Navegação entre Login → Register → Verify funciona
- [ ] Console do browser sem erros de JS

---

> 🎉 **Fase 0 concluída!** Base sólida: auth completo, RBAC testado, multi-tenant seguro, PWA instalável. Agora sim: features.

---

---

# Fase 1 — MVP Core

> **Meta:** loop principal de estudo funcionando — trilha → lição → XP → revisão.  
> **Duração estimada:** 6–10 semanas  
> **Regra:** cada etapa tem seu próprio conjunto de migrations. Nunca misturar migrations de domínios diferentes no mesmo commit.

---

## Etapa 1.1 — Estrutura BNCC (Content)

### Ordem de migrations

```
1. grades
2. subjects
3. bncc_skills
```

### Migration: `grades`

```php
Schema::create('grades', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // "3º Ano EF", "1º Ano EM"
    $table->string('code')->unique(); // "3EF", "1EM"
    $table->string('stage'); // fundamental | medio
    $table->unsignedTinyInteger('order'); // para ordenação
    $table->timestamps();

    $table->index(['stage', 'order']);
});
```

### Migration: `subjects`

```php
Schema::create('subjects', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // "Matemática"
    $table->string('slug')->unique(); // "matematica"
    $table->string('icon')->nullable(); // emoji ou nome de ícone
    $table->string('color', 7)->nullable(); // #hex
    $table->timestamps();
});
```

### Migration: `bncc_skills`

```php
Schema::create('bncc_skills', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique(); // "EF06MA01"
    $table->text('description');
    $table->foreignId('grade_id')->constrained()->restrictOnDelete();
    $table->foreignId('subject_id')->constrained()->restrictOnDelete();
    $table->string('thematic_unit')->nullable();
    $table->string('knowledge_object')->nullable();
    $table->unsignedTinyInteger('version')->default(1);
    $table->boolean('active')->default(true);
    $table->timestamps();

    $table->index(['grade_id', 'subject_id']);
    $table->index(['code', 'active']);
});
```

> ⚠️ **Use `restrictOnDelete()`** em `bncc_skills` — não pode deletar grade/subject se tiver skill associada.

### Filament Resources

```bash
php artisan make:filament-resource Grade --generate
php artisan make:filament-resource Subject --generate
php artisan make:filament-resource BnccSkill --generate
```

### Import CSV

```php
// app/Domain/Content/Actions/ImportBnccSkills.php
class ImportBnccSkills
{
    public function handle(string $filePath): ImportResult
    {
        $rows = SimpleExcelReader::create($filePath)->getRows();
        $imported = 0;
        $errors = [];

        foreach ($rows as $row) {
            try {
                BnccSkill::updateOrCreate(
                    ['code' => $row['code']],
                    [
                        'description' => $row['description'],
                        'grade_id' => Grade::where('code', $row['grade'])->value('id'),
                        'subject_id' => Subject::where('slug', $row['subject'])->value('id'),
                        'thematic_unit' => $row['thematic_unit'] ?? null,
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Linha {$row['code']}: {$e->getMessage()}";
            }
        }

        return new ImportResult($imported, $errors);
    }
}
```

---

### ✅ GATE 1.1 — Testes de BNCC

```php
it('bncc skill code must be unique', function () {
    BnccSkill::factory()->create(['code' => 'EF06MA01']);
    expect(fn() => BnccSkill::factory()->create(['code' => 'EF06MA01']))
        ->toThrow(QueryException::class);
});

it('cannot delete grade with associated skills', function () {
    $grade = Grade::factory()->has(BnccSkill::factory()->count(3))->create();
    expect(fn() => $grade->delete())->toThrow(QueryException::class);
});

it('imports bncc skills from csv', function () {
    // usar fixture CSV de teste
    $action = app(ImportBnccSkills::class);
    $result = $action->handle(base_path('tests/fixtures/bncc_sample.csv'));
    expect($result->imported)->toBeGreaterThan(0);
    expect($result->errors)->toBeEmpty();
});

it('filament admin can list bncc skills', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $this->actingAs($admin)->get('/admin/bncc-skills')->assertOk();
});
```

**Critérios de aprovação:**
- [ ] Código BNCC único enforçado no banco
- [ ] `restrictOnDelete` funciona em grade e subject
- [ ] Import CSV sem erros com dados reais BNCC
- [ ] Filament lista, cria e edita skills corretamente

---

## Etapa 1.2 — Trilhas e Nós (Path)

### Ordem de migrations

```
1. paths          (depende de grades + subjects)
2. path_nodes     (depende de paths)
```

### Migration: `paths`

```php
Schema::create('paths', function (Blueprint $table) {
    $table->id();
    $table->foreignId('grade_id')->constrained()->restrictOnDelete();
    $table->foreignId('subject_id')->constrained()->restrictOnDelete();
    $table->string('title');
    $table->boolean('published')->default(false);
    $table->timestamps();

    $table->unique(['grade_id', 'subject_id']); // uma trilha por série+matéria
    $table->index('published');
});
```

### Migration: `path_nodes`

```php
Schema::create('path_nodes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('path_id')->constrained()->cascadeOnDelete();
    $table->unsignedSmallInteger('order');
    $table->string('title');
    $table->enum('node_type', ['lesson', 'boss'])->default('lesson');
    $table->json('skill_ids'); // array de bncc_skill ids
    $table->boolean('published')->default(false);
    $table->timestamps();

    $table->unique(['path_id', 'order']); // evitar nós na mesma posição
    $table->index(['path_id', 'order']);
});
```

> 💡 `skill_ids` como JSON é intencional no MVP — evita tabela pivot que complica queries de desbloqueio.

### Lógica de desbloqueio

```php
// app/Domain/Content/Services/PathProgressService.php
class PathProgressService
{
    public function getNodeStatus(PathNode $node, Student $student): string
    {
        // Primeiro nó sempre desbloqueado
        if ($node->order === 1) return 'unlocked';

        $previousNode = PathNode::where('path_id', $node->path_id)
            ->where('order', $node->order - 1)
            ->first();

        $completed = LessonRun::where('student_id', $student->id)
            ->whereIn('lesson_id', $previousNode->lessons->pluck('id'))
            ->where('score', '>=', 70) // mínimo 70% para desbloquear
            ->exists();

        return $completed ? 'unlocked' : 'locked';
    }
}
```

---

### ✅ GATE 1.2 — Testes de Path

```php
it('only one path per grade and subject', function () {
    $grade = Grade::factory()->create();
    $subject = Subject::factory()->create();
    Path::factory()->create(['grade_id' => $grade->id, 'subject_id' => $subject->id]);

    expect(fn() => Path::factory()->create(['grade_id' => $grade->id, 'subject_id' => $subject->id]))
        ->toThrow(QueryException::class);
});

it('first node is always unlocked', function () {
    $node = PathNode::factory()->create(['order' => 1]);
    $student = Student::factory()->create();
    $service = app(PathProgressService::class);

    expect($service->getNodeStatus($node, $student))->toBe('unlocked');
});

it('second node locked until first completed', function () {
    $path = Path::factory()->has(PathNode::factory()->count(2))->create();
    $student = Student::factory()->create();
    $service = app(PathProgressService::class);

    $node2 = $path->nodes->sortBy('order')->nth(2);
    expect($service->getNodeStatus($node2, $student))->toBe('locked');
});
```

**Critérios de aprovação:**
- [ ] Constraint unique de grade+subject funciona
- [ ] Nó 1 sempre desbloqueado
- [ ] Nó 2+ bloqueado sem conclusão anterior
- [ ] Cascade delete destrói nós ao deletar path

---

## Etapa 1.3 — Questões e Banco de Exercícios

### Ordem de migrations

```
1. lessons        (depende de path_nodes)
2. questions      (depende de bncc_skills)
3. lesson_questions  (pivot: lessons ↔ questions)
```

### Migration: `lessons`

```php
Schema::create('lessons', function (Blueprint $table) {
    $table->id();
    $table->foreignId('node_id')->constrained('path_nodes')->cascadeOnDelete();
    $table->string('title');
    $table->unsignedTinyInteger('interaction_count')->default(10);
    $table->unsignedTinyInteger('difficulty')->default(2); // 1–5
    $table->boolean('published')->default(false);
    $table->timestamps();

    $table->index('node_id');
});
```

### Migration: `questions`

```php
Schema::create('questions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('skill_id')->constrained('bncc_skills')->restrictOnDelete();
    $table->enum('type', ['multiple_choice', 'true_false', 'fill_blank', 'order_steps', 'drag_drop', 'short_answer']);
    $table->unsignedTinyInteger('difficulty')->default(2); // 1–5
    $table->text('prompt'); // enunciado
    $table->json('options')->nullable(); // alternativas (MC, drag_drop)
    $table->text('correct_answer'); // resposta correta
    $table->text('explanation')->nullable(); // explicação do erro
    $table->enum('status', ['draft', 'reviewed', 'published'])->default('draft');
    $table->boolean('ai_generated')->default(false);
    $table->unsignedInteger('avg_time_ms')->default(0); // telemetria
    $table->decimal('error_rate', 5, 4)->default(0); // telemetria
    $table->timestamps();
    $table->softDeletes();

    $table->index(['skill_id', 'status', 'difficulty']);
    $table->index(['type', 'status']);
});
```

### Migration: `lesson_questions` (pivot)

```php
Schema::create('lesson_questions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
    $table->foreignId('question_id')->constrained()->restrictOnDelete();
    $table->unsignedTinyInteger('order');
    $table->timestamps();

    $table->unique(['lesson_id', 'question_id']);
    $table->index(['lesson_id', 'order']);
});
```

> ⚠️ `restrictOnDelete` em `questions` → não deletar questão que está em lição publicada.

---

### ✅ GATE 1.3 — Testes de Questões

```php
it('question requires valid type', function () {
    expect(fn() => Question::factory()->create(['type' => 'invalid_type']))
        ->toThrow(QueryException::class);
});

it('published question cannot be hard deleted', function () {
    $q = Question::factory()->create(['status' => 'published']);
    $q->delete(); // soft delete
    expect(Question::withTrashed()->find($q->id))->not->toBeNull();
    expect(Question::find($q->id))->toBeNull();
});

it('question in published lesson cannot be deleted', function () {
    $lesson = Lesson::factory()->create(['published' => true]);
    $question = Question::factory()->create(['status' => 'published']);
    $lesson->questions()->attach($question->id, ['order' => 1]);

    expect(fn() => $question->forceDelete())->toThrow(QueryException::class);
});

it('difficulty must be between 1 and 5', function () {
    expect(fn() => Question::factory()->create(['difficulty' => 6]))
        ->toThrow(\Exception::class);
});
```

**Critérios de aprovação:**
- [ ] Tipo inválido rejeitado pelo banco
- [ ] Soft delete funciona em questions
- [ ] `restrictOnDelete` protege questão em lição publicada
- [ ] Options JSON salvo e recuperado corretamente

---

## Etapa 1.4 — LessonPlayer (Engine de Lição)

### Migrations

```
1. lesson_runs    (depende de students + lessons)
2. attempts       (depende de students + questions + lesson_runs)
```

### Migration: `lesson_runs`

```php
Schema::create('lesson_runs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->foreignId('lesson_id')->constrained()->restrictOnDelete();
    $table->timestamp('started_at')->useCurrent();
    $table->timestamp('finished_at')->nullable();
    $table->unsignedTinyInteger('score')->default(0); // 0–100
    $table->unsignedSmallInteger('xp_earned')->default(0);
    $table->unsignedTinyInteger('correct_count')->default(0);
    $table->unsignedTinyInteger('total_count')->default(0);
    $table->timestamps();

    $table->index(['student_id', 'lesson_id']);
    $table->index(['student_id', 'started_at']);
});
```

### Migration: `attempts`

```php
Schema::create('attempts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->foreignId('question_id')->constrained()->restrictOnDelete();
    $table->foreignId('run_id')->nullable()->constrained('lesson_runs')->nullOnDelete();
    $table->boolean('correct');
    $table->unsignedInteger('time_ms'); // tempo de resposta em ms
    $table->string('given_answer')->nullable(); // resposta dada pelo aluno
    $table->timestamps();

    // índice composto para queries de telemetria e revisão
    $table->index(['student_id', 'question_id', 'created_at']);
    $table->index(['student_id', 'correct', 'created_at']);
});
```

### LessonService (backend)

```php
// app/Domain/Gameplay/Services/LessonService.php
class LessonService
{
    public function start(Student $student, Lesson $lesson): LessonRun
    {
        return LessonRun::create([
            'student_id' => $student->id,
            'lesson_id' => $lesson->id,
            'started_at' => now(),
        ]);
    }

    public function answer(LessonRun $run, Question $question, string $answer, int $timeMs): Attempt
    {
        $correct = $this->checkAnswer($question, $answer);

        $attempt = Attempt::create([
            'student_id' => $run->student_id,
            'question_id' => $question->id,
            'run_id' => $run->id,
            'correct' => $correct,
            'time_ms' => $timeMs,
            'given_answer' => $answer,
        ]);

        // Atualizar telemetria da questão (async)
        UpdateQuestionMetrics::dispatch($question->id);

        return $attempt;
    }

    public function finish(LessonRun $run): LessonRun
    {
        $attempts = $run->attempts;
        $correct = $attempts->where('correct', true)->count();
        $total = $attempts->count();
        $score = $total > 0 ? round(($correct / $total) * 100) : 0;
        $xp = $this->calculateXP($score, $run->started_at);

        $run->update([
            'finished_at' => now(),
            'score' => $score,
            'correct_count' => $correct,
            'total_count' => $total,
            'xp_earned' => $xp,
        ]);

        // Triggers em cascade
        AwardXP::dispatch($run->student_id, $xp, 'lesson', $run->id);
        UpdateMastery::dispatch($run->student_id, $run->lesson->skill_ids);
        UpdateStreak::dispatch($run->student_id);

        return $run->refresh();
    }

    private function checkAnswer(Question $question, string $answer): bool
    {
        return match($question->type) {
            'multiple_choice', 'true_false' => $answer === $question->correct_answer,
            'fill_blank' => strtolower(trim($answer)) === strtolower(trim($question->correct_answer)),
            default => false, // drag_drop, order_steps: validação no frontend + confirmação
        };
    }

    private function calculateXP(int $score, Carbon $startedAt): int
    {
        $base = match(true) {
            $score >= 90 => 30,
            $score >= 70 => 20,
            $score >= 50 => 10,
            default => 5,
        };

        // bônus de velocidade (terminou em menos de 5 min)
        $speedBonus = now()->diffInMinutes($startedAt) < 5 ? 5 : 0;

        return $base + $speedBonus;
    }
}
```

### Componente LessonPlayer (Vue)

```
resources/js/Pages/Lesson/
├── Player.vue           (orquestrador: carrega questões, controla progresso)
├── QuestionCard.vue     (renderiza questão por tipo)
├── OptionButton.vue     (botão de alternativa com estados)
├── TimerChip.vue        (cronômetro por questão)
├── Hearts.vue           (vidas em modo desafio)
├── ProgressBar.vue      (0/10 questões)
└── FeedbackOverlay.vue  (correto/errado com animação)
```

> 💡 **Player.vue mantém estado local:** questões carregadas, índice atual, tentativas pendentes (para offline sync).

---

### ✅ GATE 1.4 — Testes de LessonPlayer

```php
// Backend
it('calculates score correctly', function () {
    $run = LessonRun::factory()->create();
    Attempt::factory()->count(8)->create(['run_id' => $run->id, 'correct' => true]);
    Attempt::factory()->count(2)->create(['run_id' => $run->id, 'correct' => false]);

    $service = app(LessonService::class);
    $finished = $service->finish($run);

    expect($finished->score)->toBe(80);
    expect($finished->xp_earned)->toBeGreaterThan(0);
});

it('awards xp after lesson completion', function () {
    Queue::fake();
    $run = LessonRun::factory()->create();
    $service = app(LessonService::class);
    $service->finish($run);

    Queue::assertPushed(AwardXP::class);
    Queue::assertPushed(UpdateMastery::class);
    Queue::assertPushed(UpdateStreak::class);
});

it('fill_blank answer is case insensitive', function () {
    $question = Question::factory()->create([
        'type' => 'fill_blank',
        'correct_answer' => 'Fotossíntese',
    ]);
    $service = app(LessonService::class);

    // deve aceitar variações de capitalização
    $run = LessonRun::factory()->create();
    $attempt = $service->answer($run, $question, 'fotossíntese', 3000);
    expect($attempt->correct)->toBeTrue();
});
```

```js
// Playwright E2E
test('completes lesson flow from start to finish', async ({ page }) => {
    await page.goto('/lesson/1/play');
    
    // responder 10 questões
    for (let i = 0; i < 10; i++) {
        await page.click('[data-testid="option-a"]');
        await page.click('[data-testid="btn-confirm"]');
        await page.waitForSelector('[data-testid="feedback-overlay"]');
        await page.click('[data-testid="btn-next"]');
    }
    
    // tela de resultado deve aparecer
    await expect(page.locator('[data-testid="lesson-result"]')).toBeVisible();
    await expect(page.locator('[data-testid="xp-earned"]')).toContainText('XP');
});
```

**Critérios de aprovação:**
- [ ] Score calculado corretamente (acertos/total × 100)
- [ ] XP, mastery e streak disparados via queue após finish
- [ ] Fill_blank aceita variações de capitalização
- [ ] E2E: lição completa do início ao fim sem erro

---

## Etapa 1.5 — Gamificação Core

### Migrations

```
1. xp_transactions   (depende de students)
2. streaks           (depende de students)
3. badges            (independente)
4. student_badges    (depende de students + badges)
5. daily_missions    (independente)
6. student_missions  (depende de students + daily_missions)
```

### Migration: `xp_transactions`

```php
Schema::create('xp_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->unsignedSmallInteger('amount');
    $table->string('reason'); // lesson | streak_bonus | challenge | badge
    $table->string('reference_type')->nullable(); // LessonRun | Badge
    $table->unsignedBigInteger('reference_id')->nullable();
    $table->timestamp('created_at')->useCurrent();

    // NUNCA adicionar updated_at — ledger é imutável
    $table->index(['student_id', 'created_at']);
});
```

### Migration: `streaks`

```php
Schema::create('streaks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->unsignedSmallInteger('current')->default(0);
    $table->unsignedSmallInteger('best')->default(0);
    $table->date('last_activity_date')->nullable();
    $table->timestamp('freeze_used_at')->nullable(); // 1 freeze/semana
    $table->timestamps();

    $table->unique('student_id'); // um streak por aluno
});
```

### StreakService

```php
// app/Domain/Gameplay/Services/StreakService.php
class StreakService
{
    public function update(Student $student): Streak
    {
        $streak = Streak::firstOrCreate(['student_id' => $student->id]);
        $today = today();
        $yesterday = today()->subDay();

        if ($streak->last_activity_date === null) {
            // primeiro estudo
            $streak->update(['current' => 1, 'last_activity_date' => $today, 'best' => 1]);
        } elseif ($streak->last_activity_date->equalTo($today)) {
            // já estudou hoje — não incrementar
        } elseif ($streak->last_activity_date->equalTo($yesterday)) {
            // consecutivo
            $newCurrent = $streak->current + 1;
            $streak->update([
                'current' => $newCurrent,
                'last_activity_date' => $today,
                'best' => max($streak->best, $newCurrent),
            ]);
        } else {
            // quebrou — verificar freeze disponível
            $canFreeze = $this->canUseFreeze($streak);
            if ($canFreeze) {
                $streak->update(['freeze_used_at' => now()]);
                // mantém streak mas não incrementa
            } else {
                $streak->update(['current' => 1, 'last_activity_date' => $today]);
            }
        }

        return $streak->refresh();
    }

    private function canUseFreeze(Streak $streak): bool
    {
        if (!$streak->freeze_used_at) return false;
        // freeze disponível se não usou na semana atual
        return $streak->freeze_used_at->lt(now()->startOfWeek());
    }
}
```

---

### ✅ GATE 1.5 — Testes de Gamificação

```php
it('streak increments on consecutive days', function () {
    $student = Student::factory()->create();
    $service = app(StreakService::class);

    // Dia 1
    Carbon::setTestNow(today());
    $service->update($student);
    expect($student->streak->current)->toBe(1);

    // Dia 2
    Carbon::setTestNow(today()->addDay());
    $service->update($student);
    expect($student->streak->fresh()->current)->toBe(2);
});

it('streak resets after missing a day without freeze', function () {
    $student = Student::factory()->create();
    $streak = Streak::factory()->create([
        'student_id' => $student->id,
        'current' => 10,
        'last_activity_date' => today()->subDays(2), // 2 dias atrás
        'freeze_used_at' => now()->subDays(3), // freeze já usado essa semana
    ]);

    $service = app(StreakService::class);
    $service->update($student);
    expect($streak->fresh()->current)->toBe(1);
});

it('xp_transactions table is append-only (no updates)', function () {
    $tx = XpTransaction::factory()->create(['amount' => 20]);
    // tentativa de update deve ser tratada como erro na aplicação
    expect(fn() => $tx->update(['amount' => 100]))->not->toThrow(\Exception::class);
    // mas registrar que nunca devemos fazer isso via test de auditoria
    expect(XpTransaction::find($tx->id)->amount)->toBe(100); // se passar, adicionar Observer que bloqueia
});

it('student total xp is sum of all transactions', function () {
    $student = Student::factory()->create();
    XpTransaction::factory()->count(5)->create([
        'student_id' => $student->id,
        'amount' => 20,
    ]);
    $total = XpTransaction::where('student_id', $student->id)->sum('amount');
    expect($total)->toBe(100);
});
```

**Critérios de aprovação:**
- [ ] Streak incrementa em dias consecutivos
- [ ] Streak reseta após 2+ dias sem freeze
- [ ] Freeze funciona (1 por semana)
- [ ] XP calculado corretamente via SUM das transactions
- [ ] Best streak sempre >= current streak

---

## Etapa 1.6 — Revisão Inteligente (Spaced Repetition)

### Migrations

```
1. mastery      (depende de students + bncc_skills)
```

### Migration: `mastery`

```php
Schema::create('mastery', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->foreignId('skill_id')->constrained('bncc_skills')->restrictOnDelete();
    $table->unsignedTinyInteger('mastery_score')->default(0); // 0–100
    $table->timestamp('last_seen_at')->nullable();
    $table->timestamp('next_review_at')->nullable();
    $table->unsignedTinyInteger('interval_days')->default(1);
    $table->unsignedTinyInteger('consecutive_correct')->default(0);
    $table->timestamps();

    $table->unique(['student_id', 'skill_id']);
    $table->index(['student_id', 'next_review_at']); // query mais frequente
});
```

### MasteryService (algoritmo SM-2 adaptado)

```php
// app/Domain/Gameplay/Services/MasteryService.php
class MasteryService
{
    // Intervalos em dias: 1 → 3 → 7 → 14 → 30
    private const INTERVALS = [1, 3, 7, 14, 30];

    public function update(Student $student, int $skillId, bool $correct): Mastery
    {
        $mastery = Mastery::firstOrCreate(
            ['student_id' => $student->id, 'skill_id' => $skillId],
            ['mastery_score' => 0, 'interval_days' => 1, 'consecutive_correct' => 0]
        );

        if ($correct) {
            $consecutive = $mastery->consecutive_correct + 1;
            $newScore = min(100, $mastery->mastery_score + 10);
            $newInterval = $this->nextInterval($mastery->interval_days, $consecutive);
        } else {
            $consecutive = 0;
            $newScore = max(0, $mastery->mastery_score - 15);
            $newInterval = 1; // voltar ao início
        }

        $mastery->update([
            'mastery_score' => $newScore,
            'consecutive_correct' => $consecutive,
            'interval_days' => $newInterval,
            'last_seen_at' => now(),
            'next_review_at' => now()->addDays($newInterval),
        ]);

        return $mastery->refresh();
    }

    public function getDueReviews(Student $student, int $limit = 5): Collection
    {
        return Mastery::where('student_id', $student->id)
            ->where('next_review_at', '<=', now())
            ->where('mastery_score', '<', 90) // não revisar o que já dominou
            ->orderBy('next_review_at')
            ->limit($limit)
            ->with('skill')
            ->get();
    }

    private function nextInterval(int $current, int $consecutive): int
    {
        // avançar na sequência de intervalos com base em acertos consecutivos
        $index = array_search($current, self::INTERVALS) ?: 0;
        $nextIndex = min($consecutive >= 2 ? $index + 1 : $index, count(self::INTERVALS) - 1);
        return self::INTERVALS[$nextIndex];
    }
}
```

---

### ✅ GATE 1.6 — Testes de Spaced Repetition

```php
it('mastery score increases on correct answer', function () {
    $student = Student::factory()->create();
    $skill = BnccSkill::factory()->create();
    $service = app(MasteryService::class);

    $mastery = $service->update($student, $skill->id, correct: true);
    expect($mastery->mastery_score)->toBe(10);
    expect($mastery->interval_days)->toBe(1);
    expect($mastery->next_review_at)->toBeAfter(now());
});

it('mastery score decreases on wrong answer', function () {
    $student = Student::factory()->create();
    $skill = BnccSkill::factory()->create();
    Mastery::factory()->create([
        'student_id' => $student->id,
        'skill_id' => $skill->id,
        'mastery_score' => 50,
        'interval_days' => 14,
    ]);

    $service = app(MasteryService::class);
    $mastery = $service->update($student, $skill->id, correct: false);

    expect($mastery->mastery_score)->toBe(35);
    expect($mastery->interval_days)->toBe(1); // volta ao início
    expect($mastery->consecutive_correct)->toBe(0);
});

it('interval advances after 2 consecutive correct answers', function () {
    $student = Student::factory()->create();
    $skill = BnccSkill::factory()->create();
    $service = app(MasteryService::class);

    $service->update($student, $skill->id, correct: true); // 1 consecutivo
    $mastery = $service->update($student, $skill->id, correct: true); // 2 consecutivos

    expect($mastery->interval_days)->toBe(3); // 1 → 3
});

it('getDueReviews returns only overdue skills', function () {
    $student = Student::factory()->create();
    // skill vencida (deveria ter sido revisada ontem)
    Mastery::factory()->create([
        'student_id' => $student->id,
        'next_review_at' => now()->subDay(),
        'mastery_score' => 50,
    ]);
    // skill não vencida
    Mastery::factory()->create([
        'student_id' => $student->id,
        'next_review_at' => now()->addDays(5),
        'mastery_score' => 50,
    ]);

    $service = app(MasteryService::class);
    $due = $service->getDueReviews($student);

    expect($due)->toHaveCount(1);
});
```

**Critérios de aprovação:**
- [ ] Score aumenta em acerto, diminui em erro
- [ ] Intervalo avança após 2 acertos consecutivos
- [ ] Intervalo reseta para 1 após erro
- [ ] `getDueReviews` retorna apenas habilidades vencidas

---

## Etapa 1.7 — Ranking e Ligas

### Migrations

```
1. league_snapshots   (tabela para auditoria semanal)
```

### Migration: `league_snapshots`

```php
Schema::create('league_snapshots', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->string('school_id')->nullable();
    $table->string('class_id')->nullable();
    $table->string('league'); // bronze | silver | gold | platinum
    $table->unsignedInteger('weekly_xp');
    $table->unsignedSmallInteger('rank_position');
    $table->unsignedSmallInteger('week'); // número da semana ISO
    $table->unsignedSmallInteger('year');
    $table->timestamps();

    $table->index(['year', 'week', 'league']);
    $table->index(['student_id', 'year', 'week']);
});
```

### LeagueService (com Redis sorted sets)

```php
// app/Domain/Gameplay/Services/LeagueService.php
class LeagueService
{
    public function addXP(Student $student, int $xp): void
    {
        $key = $this->weeklyKey($student->school_id);
        Redis::zincrby($key, $xp, $student->id);
        Redis::expire($key, 60 * 60 * 24 * 14); // manter por 14 dias
    }

    public function getLeaderboard(int $schoolId, int $limit = 20): array
    {
        $key = $this->weeklyKey($schoolId);
        $members = Redis::zrevrange($key, 0, $limit - 1, 'WITHSCORES');
        // retorna array [student_id => score, ...]
        return $members;
    }

    public function snapshotAndReset(): void
    {
        // Job semanal: persistir no MySQL e zerar Redis
        $schools = School::where('active', true)->get();
        foreach ($schools as $school) {
            $key = $this->weeklyKey($school->id);
            $scores = Redis::zrevrange($key, 0, -1, 'WITHSCORES');
            $this->persistSnapshot($school->id, $scores);
            Redis::del($key);
        }
    }

    private function weeklyKey(int $schoolId): string
    {
        $week = now()->weekOfYear;
        $year = now()->year;
        return "league:school:{$schoolId}:week:{$year}:{$week}";
    }
}
```

### Job semanal

```php
// app/Domain/Gameplay/Jobs/RecomputeWeeklyLeagues.php
class RecomputeWeeklyLeagues implements ShouldQueue
{
    public function handle(LeagueService $service): void
    {
        $service->snapshotAndReset();
        Log::info('Weekly leagues recomputed and reset');
    }
}

// Agendar no Kernel:
$schedule->job(new RecomputeWeeklyLeagues)->weeklyOn(0, '23:59'); // domingo meia-noite
```

---

### ✅ GATE 1.7 — Testes de Ranking

```php
it('redis leaderboard returns correct order', function () {
    $school = School::factory()->create();
    $students = Student::factory()->count(3)->create(['school_id' => $school->id]);
    $service = app(LeagueService::class);

    $service->addXP($students[0], 100);
    $service->addXP($students[1], 200);
    $service->addXP($students[2], 150);

    $leaderboard = $service->getLeaderboard($school->id);

    // student[1] deve ser o primeiro
    expect(array_key_first($leaderboard))->toBe((string)$students[1]->id);
});

it('weekly snapshot persists data to mysql', function () {
    Queue::fake();
    $school = School::factory()->create();
    $student = Student::factory()->create(['school_id' => $school->id]);
    $service = app(LeagueService::class);

    $service->addXP($student, 500);
    $service->snapshotAndReset();

    expect(LeagueSnapshot::where('student_id', $student->id)->exists())->toBeTrue();
});

it('redis key is cleared after snapshot', function () {
    $school = School::factory()->create();
    $service = app(LeagueService::class);
    $student = Student::factory()->create(['school_id' => $school->id]);
    $service->addXP($student, 100);

    $service->snapshotAndReset();

    $leaderboard = $service->getLeaderboard($school->id);
    expect($leaderboard)->toBeEmpty();
});
```

**Critérios de aprovação:**
- [ ] Redis retorna ranking na ordem correta
- [ ] Snapshot persiste dados no MySQL
- [ ] Chave Redis zerada após snapshot
- [ ] Job agendado no Kernel funciona

---

## Etapa 1.8 — Gestão Escolar (Turmas e Tarefas)

### Migrations

```
1. classes            (depende de schools + grades)
2. class_students     (depende de classes + students)
3. assignments        (depende de classes + users)
4. assignment_items   (depende de assignments + path_nodes/lessons)
```

### Migration: `classes`

```php
Schema::create('classes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('grade_id')->constrained()->restrictOnDelete();
    $table->string('name'); // "Turma A", "6º B"
    $table->unsignedSmallInteger('year');
    $table->boolean('active')->default(true);
    $table->timestamps();

    $table->index(['school_id', 'grade_id', 'year']);
});
```

### Migration: `class_students`

```php
Schema::create('class_students', function (Blueprint $table) {
    $table->id();
    $table->foreignId('class_id')->constrained()->cascadeOnDelete();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->timestamp('enrolled_at')->useCurrent();
    $table->timestamps();

    $table->unique(['class_id', 'student_id']);
    $table->index('class_id');
});
```

### Migration: `assignments`

```php
Schema::create('assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('class_id')->constrained()->cascadeOnDelete();
    $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
    $table->enum('type', ['nodes', 'lesson', 'simulation']);
    $table->string('title');
    $table->text('description')->nullable();
    $table->timestamp('due_at');
    $table->json('config')->nullable(); // configurações do simulado
    $table->timestamps();

    $table->index(['class_id', 'due_at']);
});
```

---

### ✅ GATE 1.8 — Testes Escolares

```php
it('student can only be in a class of their school', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $class = Classes::factory()->create(['school_id' => $schoolA->id]);
    $student = Student::factory()->create(['school_id' => $schoolB->id]);

    // A policy deve bloquear isso
    $user = User::factory()->create(['role' => 'teacher', 'school_id' => $schoolA->id]);
    $this->actingAs($user)
         ->post("/classes/{$class->id}/students", ['student_id' => $student->id])
         ->assertStatus(403);
});

it('teacher can only manage their own school classes', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $classB = Classes::factory()->create(['school_id' => $schoolB->id]);
    $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $schoolA->id]);

    $this->actingAs($teacher)->get("/classes/{$classB->id}")->assertStatus(403);
});

it('assignment due_at must be in the future', function () {
    $class = Classes::factory()->create();
    $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $class->school_id]);

    $this->actingAs($teacher)->post('/assignments', [
        'class_id' => $class->id,
        'type' => 'nodes',
        'title' => 'Tarefa',
        'due_at' => now()->subDay()->toDateTimeString(), // no passado
    ])->assertSessionHasErrors('due_at');
});
```

**Critérios de aprovação:**
- [ ] Aluno de escola diferente não pode entrar na turma
- [ ] Professor não vê turmas de outras escolas
- [ ] Due_at no passado rejeitado na validação

---

## Etapa 1.9 — Dashboards

> Esta etapa é principalmente de **frontend** — sem novas migrations.  
> Usa dados das etapas anteriores via queries/controllers.

### Controllers a criar

```php
// app/Http/Controllers/Dashboard/
├── StudentDashboardController.php   // streak, XP, próximas revisões, trilha
├── GuardianDashboardController.php  // progresso filho, alertas
├── TeacherDashboardController.php   // turma, tarefas, alunos em risco
└── SchoolDashboardController.php    // visão consolidada
```

### Queries críticas (otimizar com índices já criados)

```php
// Alunos em risco (não estudam há X dias)
Student::whereHas('streak', function ($q) {
    $q->where('last_activity_date', '<', now()->subDays(3));
})->orWhereDoesntHave('streak')->get();

// Habilidades com mastery crítico (< 30) por turma
Mastery::whereIn('student_id', $classStudentIds)
    ->where('mastery_score', '<', 30)
    ->with('skill')
    ->groupBy('skill_id')
    ->selectRaw('skill_id, AVG(mastery_score) as avg_mastery, COUNT(*) as students_count')
    ->orderBy('avg_mastery')
    ->get();
```

---

### ✅ GATE 1.9 — Testes de Dashboard

```php
it('guardian only sees their own children', function () {
    $guardian = User::factory()->create(['role' => 'guardian']);
    $myStudent = Student::factory()->create();
    $otherStudent = Student::factory()->create();

    $guardian->studentsGuarded()->attach($myStudent->id);

    $this->actingAs($guardian)->get('/dashboard')
         ->assertSee($myStudent->name)
         ->assertDontSee($otherStudent->name);
});

it('teacher dashboard shows at-risk students', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $class = Classes::factory()->create(['school_id' => $teacher->school_id]);

    // aluno em risco: não estuda há 5 dias
    $atRisk = Student::factory()->create(['school_id' => $teacher->school_id]);
    Streak::factory()->create([
        'student_id' => $atRisk->id,
        'last_activity_date' => now()->subDays(5),
    ]);
    $class->students()->attach($atRisk->id);

    $this->actingAs($teacher)->get('/teacher/dashboard')
         ->assertSee('alunos em risco')
         ->assertSee($atRisk->name);
});
```

**Critérios de aprovação:**
- [ ] Responsável vê apenas seus filhos
- [ ] Alunos em risco aparecem no dashboard do professor
- [ ] Dashboard carrega em < 500ms (verificar no Telescope)
- [ ] Sem N+1 queries (verificar no Telescope)

---

## Etapa 1.10 — IA: Geração e Explicação

### Migration: `ai_jobs`

```php
Schema::create('ai_jobs', function (Blueprint $table) {
    $table->id();
    $table->string('type'); // generate_questions | explain_error | validate_batch
    $table->foreignId('skill_id')->nullable()->constrained('bncc_skills')->nullOnDelete();
    $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
    $table->json('config')->nullable(); // parâmetros do job
    $table->unsignedInteger('prompt_tokens')->default(0);
    $table->unsignedInteger('result_tokens')->default(0);
    $table->string('model')->nullable();
    $table->text('error')->nullable();
    $table->unsignedTinyInteger('questions_generated')->default(0);
    $table->timestamp('started_at')->nullable();
    $table->timestamp('finished_at')->nullable();
    $table->timestamps();

    $table->index(['status', 'type']);
    $table->index(['skill_id', 'status']);
});
```

### GenerateQuestionsJob

```php
// app/Domain/AI/Jobs/GenerateQuestionsForSkill.php
class GenerateQuestionsForSkill implements ShouldQueue
{
    public int $timeout = 300;
    public int $tries = 2;

    public function __construct(
        private readonly int $skillId,
        private readonly int $count = 100,
        private readonly string $model = 'deepseek/deepseek-chat'
    ) {}

    public function handle(AIService $ai): void
    {
        $skill = BnccSkill::with(['grade', 'subject'])->findOrFail($this->skillId);
        $aiJob = AiJob::create([
            'type' => 'generate_questions',
            'skill_id' => $this->skillId,
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $questions = $ai->generateQuestions($skill, $this->count, $this->model);

            foreach ($questions as $q) {
                Question::create([
                    'skill_id' => $this->skillId,
                    'type' => $q['type'],
                    'difficulty' => $q['difficulty'],
                    'prompt' => $q['prompt'],
                    'options' => $q['options'] ?? null,
                    'correct_answer' => $q['correct_answer'],
                    'explanation' => $q['explanation'],
                    'status' => 'draft', // curadoria manual antes de publicar
                    'ai_generated' => true,
                ]);
            }

            $aiJob->update([
                'status' => 'done',
                'questions_generated' => count($questions),
                'finished_at' => now(),
            ]);
        } catch (\Exception $e) {
            $aiJob->update(['status' => 'failed', 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
```

### Prompt template

```php
// app/Domain/AI/Prompts/GenerateQuestionsPrompt.php
class GenerateQuestionsPrompt
{
    public static function build(BnccSkill $skill, int $count): string
    {
        $grade = $skill->grade->name;
        $ageRange = match($skill->grade->stage) {
            'fundamental' => $skill->grade->order <= 5 ? '8–10 anos' : '11–14 anos',
            'medio' => '15–18 anos',
        };

        return <<<PROMPT
        Você é um especialista em educação brasileira e BNCC.
        
        Gere {$count} questões de múltipla escolha para:
        - Série: {$grade}
        - Faixa etária: {$ageRange}
        - Disciplina: {$skill->subject->name}
        - Habilidade BNCC: {$skill->code} — {$skill->description}
        
        REGRAS OBRIGATÓRIAS:
        1. Linguagem adequada para {$ageRange}
        2. Sem pegadinhas ou ambiguidade
        3. 4 alternativas (A, B, C, D)
        4. Explicação clara do erro em 1–2 frases
        5. Dificuldade variada: 30% fácil (1-2), 50% médio (3), 20% difícil (4-5)
        
        Responda APENAS em JSON válido no formato:
        [{"type":"multiple_choice","difficulty":2,"prompt":"...","options":{"A":"...","B":"...","C":"...","D":"..."},"correct_answer":"A","explanation":"..."}]
        PROMPT;
    }
}
```

---

### ✅ GATE 1.10 — Testes de IA

```php
it('generates questions as draft status', function () {
    Queue::fake();
    $skill = BnccSkill::factory()->create();

    GenerateQuestionsForSkill::dispatch($skill->id, 10);
    Queue::assertPushed(GenerateQuestionsForSkill::class);
});

it('ai job records token usage', function () {
    Http::fake(['*' => Http::response([
        'content' => [['text' => json_encode([
            ['type' => 'multiple_choice', 'difficulty' => 2,
             'prompt' => 'Questão?', 'options' => ['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd'],
             'correct_answer' => 'A', 'explanation' => 'Explicação.']
        ])]],
        'usage' => ['input_tokens' => 500, 'output_tokens' => 300],
    ])]);

    $skill = BnccSkill::factory()->create();
    $job = new GenerateQuestionsForSkill($skill->id, 1);
    $job->handle(app(AIService::class));

    $aiJob = AiJob::latest()->first();
    expect($aiJob->status)->toBe('done');
    expect($aiJob->questions_generated)->toBe(1);
    expect(Question::where('skill_id', $skill->id)->where('status', 'draft')->count())->toBe(1);
});

it('ai job marks failed on api error', function () {
    Http::fake(['*' => Http::response([], 500)]);
    $skill = BnccSkill::factory()->create();

    try {
        $job = new GenerateQuestionsForSkill($skill->id, 1);
        $job->handle(app(AIService::class));
    } catch (\Exception $e) {}

    expect(AiJob::latest()->first()->status)->toBe('failed');
});
```

**Critérios de aprovação:**
- [ ] Questões geradas com status `draft`
- [ ] AiJob registra tokens e status
- [ ] Falha na API marca job como `failed` sem corromper banco
- [ ] Curadoria no Filament: lista questões draft para revisão

---

> 🎉 **Fase 1 concluída!** Loop principal funcionando: trilha → lição → XP → streak → revisão → ranking. Dashboards para todos os perfis. IA gerando conteúdo em lote.

---

---

# Fase 2 — Qualidade e Escala

> **Meta:** expandir tipos de questão, adicionar loja, tutor IA e otimizar infra.  
> **Duração estimada:** 4–8 semanas  
> **Regra:** toda nova feature passa por teste antes de ir para produção.

---

## Etapa 2.1 — Tipos de Questão Avançados

### O que adicionar

Suporte a `drag_drop` e `order_steps` no `QuestionCard.vue`.  
**Backend não precisa de nova migration** — os tipos já estão no enum e o `options` JSON acomoda os dados.

```php
// Estrutura JSON para drag_drop:
// options: {"items": ["item1","item2","item3"], "targets": ["alvo1","alvo2","alvo3"]}
// correct_answer: {"item1":"alvo1","item2":"alvo2","item3":"alvo3"}

// Estrutura para order_steps:
// options: {"steps": ["passo3","passo1","passo2"]}
// correct_answer: ["passo1","passo2","passo3"]
```

### Verificação de resposta para novos tipos

```php
// No LessonService::checkAnswer
'drag_drop' => $this->checkDragDrop($question, $answer),
'order_steps' => $this->checkOrderSteps($question, $answer),

private function checkDragDrop(Question $q, string $answer): bool
{
    $given = json_decode($answer, true);
    $correct = json_decode($q->correct_answer, true);
    return $given === $correct;
}

private function checkOrderSteps(Question $q, string $answer): bool
{
    $given = json_decode($answer, true);
    $correct = json_decode($q->correct_answer, true);
    return $given === $correct;
}
```

---

### ✅ GATE 2.1

```php
it('drag_drop answer validates correctly', function () {
    $q = Question::factory()->create([
        'type' => 'drag_drop',
        'correct_answer' => json_encode(['item1' => 'alvo1', 'item2' => 'alvo2']),
    ]);
    $run = LessonRun::factory()->create();
    $service = app(LessonService::class);

    $attempt = $service->answer($run, $q, json_encode(['item1' => 'alvo1', 'item2' => 'alvo2']), 5000);
    expect($attempt->correct)->toBeTrue();
});

// E2E Playwright: arrastar item para target e verificar feedback
```

---

## Etapa 2.2 — Loja e Avatares

### Migrations

```
1. shop_items      (independente)
2. student_items   (depende de students + shop_items)
```

```php
Schema::create('shop_items', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('type'); // avatar | skin | theme | frame
    $table->unsignedSmallInteger('price_gems');
    $table->string('asset_url');
    $table->boolean('active')->default(true);
    $table->timestamps();
});

Schema::create('student_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->foreignId('shop_item_id')->constrained()->restrictOnDelete();
    $table->boolean('equipped')->default(false);
    $table->timestamp('purchased_at')->useCurrent();

    $table->unique(['student_id', 'shop_item_id']);
});
```

---

### ✅ GATE 2.2

```php
it('student cannot buy item they already own', function () {
    $student = Student::factory()->create();
    $item = ShopItem::factory()->create(['price_gems' => 100]);
    StudentItem::factory()->create(['student_id' => $student->id, 'shop_item_id' => $item->id]);

    $service = app(ShopService::class);
    expect(fn() => $service->purchase($student, $item))->toThrow(\Exception::class, 'já possui');
});

it('purchase deducts gems from student', function () {
    // gems são calculados via xp_transactions com reason='gems_earned'
    // deduções via reason='shop_purchase'
    $student = Student::factory()->create();
    GemTransaction::factory()->create(['student_id' => $student->id, 'amount' => 200]);

    $item = ShopItem::factory()->create(['price_gems' => 100]);
    $service = app(ShopService::class);
    $service->purchase($student, $item);

    $gems = GemTransaction::where('student_id', $student->id)->sum('amount');
    expect($gems)->toBe(100);
});
```

---

## Etapa 2.3 — Tutor IA (Chat)

### Migration: `tutor_messages`

```php
Schema::create('tutor_messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->foreignId('question_id')->nullable()->constrained()->nullOnDelete();
    $table->enum('role', ['user', 'assistant']);
    $table->text('content');
    $table->string('model')->nullable();
    $table->unsignedSmallInteger('tokens')->default(0);
    $table->boolean('flagged')->default(false); // moderação
    $table->timestamps();

    $table->index(['student_id', 'created_at']);
});
```

### Moderação

```php
// app/Domain/AI/Services/ModerationService.php
class ModerationService
{
    private const BLOCKED_TOPICS = ['violência', 'drogas', 'conteúdo adulto'];

    public function isSafe(string $message, Student $student): bool
    {
        // Verificar tópicos bloqueados
        foreach (self::BLOCKED_TOPICS as $topic) {
            if (str_contains(strtolower($message), $topic)) {
                return false;
            }
        }

        // Limite de mensagens por sessão (por idade)
        $limit = $student->age < 13 ? 10 : 20;
        $todayCount = TutorMessage::where('student_id', $student->id)
            ->whereDate('created_at', today())
            ->count();

        return $todayCount < $limit;
    }
}
```

---

### ✅ GATE 2.3

```php
it('tutor blocks inappropriate content', function () {
    $student = Student::factory()->create();
    $service = app(ModerationService::class);
    expect($service->isSafe('como usar drogas', $student))->toBeFalse();
});

it('tutor respects daily message limit for under 13', function () {
    $student = Student::factory()->create(['birth_date' => now()->subYears(11)]);
    TutorMessage::factory()->count(10)->create([
        'student_id' => $student->id,
        'role' => 'user',
        'created_at' => today(),
    ]);

    $service = app(ModerationService::class);
    expect($service->isSafe('explica fração', $student))->toBeFalse();
});
```

---

## Etapa 2.4 — Relatórios PDF

> Sem migrations novas — usa dados existentes.

```bash
composer require barryvdh/laravel-dompdf
```

```php
// app/Domain/Analytics/Services/ReportService.php
class ReportService
{
    public function generateClassReport(Classes $class, string $period): string
    {
        $data = [
            'class' => $class->load('students', 'grade'),
            'period' => $period,
            'avg_mastery' => $this->avgMasteryBySubject($class),
            'at_risk_students' => $this->atRiskStudents($class),
            'top_skills' => $this->topAndBottomSkills($class),
        ];

        $pdf = Pdf::loadView('reports.class', $data);
        $path = "reports/class-{$class->id}-{$period}.pdf";
        Storage::put($path, $pdf->output());

        return $path;
    }
}
```

---

### ✅ GATE 2.4

```php
it('pdf report generates without error', function () {
    $class = Classes::factory()
        ->has(Student::factory()->count(5))
        ->create();

    $service = app(ReportService::class);
    $path = $service->generateClassReport($class, 'trimestre-1');

    expect(Storage::exists($path))->toBeTrue();
    expect(Storage::size($path))->toBeGreaterThan(1000); // pelo menos 1kb
});
```

---

## Etapa 2.5 — Otimizações de Infra

> Sem migrations. Trabalho de performance e observabilidade.

### Checklist de otimização

```php
// 1. Eager loading — eliminar N+1 identificados no Telescope
// ANTES:
$students = Student::all();
foreach ($students as $s) { $s->streak; } // N+1!

// DEPOIS:
$students = Student::with('streak', 'mastery')->get();

// 2. Cache de queries pesadas
$report = Cache::remember("report:class:{$classId}", 3600, fn() => ...);

// 3. Índices — revisar EXPLAIN das queries mais lentas no Telescope
// 4. Horizon: separar pools por prioridade
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-default' => ['queue' => ['default'], 'processes' => 3],
        'supervisor-ai'      => ['queue' => ['ai-generation'], 'processes' => 2],
        'supervisor-reports' => ['queue' => ['reports'], 'processes' => 1],
    ],
]
```

---

### ✅ GATE 2.5 — Testes de Performance

```bash
# k6 load test: 500 alunos simultâneos fazendo lição
k6 run --vus 500 --duration 60s tests/k6/lesson_flow.js

# Critérios:
# p95 response time < 500ms
# Error rate < 1%
# Horizon queues: 0 jobs com mais de 30s de espera
```

**Critérios de aprovação:**
- [ ] p95 < 500ms com 500 usuários simultâneos
- [ ] Nenhuma query N+1 no Telescope em produção
- [ ] Cache hit rate > 80% para relatórios
- [ ] Horizon pools sem acúmulo de jobs

---

> 🎉 **Fase 2 concluída!** Produto completo, escalável e testado em carga.

---

---

# Fase 3 — Premium

> **Meta:** funcionalidades premium e diferencias competitivos.  
> **Duração:** contínuo — implementar por demanda de mercado.

## Etapa 3.1 — 2FA

```php
// Já preparado no Fortify — apenas habilitar:
// config/fortify.php
Features::twoFactorAuthentication(['confirm' => true, 'confirmPassword' => true]),
```

---

## Etapa 3.2 — Push Notifications

```bash
npm install web-push
```

### Migration: `push_subscriptions`

```php
Schema::create('push_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->text('endpoint');
    $table->string('p256dh');
    $table->string('auth');
    $table->timestamps();
    $table->unique('endpoint');
});
```

---

## Etapa 3.3 — Trilhas ENEM/Vestibular

```php
// Reaproveitar toda a estrutura de paths/nodes/questions
// Adicionar campo no paths:
Schema::table('paths', function (Blueprint $table) {
    $table->string('path_type')->default('regular')->after('published');
    // regular | enem | vestibular_fuvest | vestibular_unicamp
    $table->index('path_type');
});
```

---

## ✅ GATE Final — Checklist de Produção

Antes de qualquer release em produção:

```bash
# 1. Testes completos
php artisan test --coverage --min=80

# 2. Análise estática
./vendor/bin/phpstan analyse --level=6

# 3. Auditoria de segurança
php artisan security:check  # composer audit

# 4. Migrate em staging primeiro
php artisan migrate --pretend  # preview das migrations
php artisan migrate             # executar em staging
php artisan test                # rodar testes contra staging

# 5. Load test
k6 run tests/k6/full_flow.js

# 6. Backup antes de migrar produção
mysqldump -u root -p bncc_prod > backup_$(date +%Y%m%d).sql

# 7. Migrar produção com zero-downtime
php artisan down --retry=60
php artisan migrate
php artisan up

# 8. Verificar Sentry — zero erros novos em 10 min
# 9. Verificar Horizon — filas processando normalmente
# 10. Smoke test manual: registro → lição → XP → dashboard
```

---

## Regras de Ouro (nunca violar)

1. **Nunca** fazer `migrate:fresh` em produção — apenas `migrate`
2. **Nunca** misturar migrations de domínios diferentes no mesmo PR
3. **Sempre** criar Factory e Seeder junto com cada migration
4. **Sempre** rodar `php artisan test` antes de abrir PR
5. **Nunca** usar `DB::raw()` sem bindings parametrizados
6. **Sempre** fazer backup antes de migrations destrutivas (DROP, ALTER)
7. **Nunca** deletar `xp_transactions` — ledger é imutável
8. **Sempre** testar em staging antes de produção
9. **Nunca** commitar `.env` ou chaves de API
10. **Sempre** passar pelo gate de testes antes de avançar de etapa

---

*Última atualização: gerado com base no planejamento completo da Plataforma Gamificada BNCC.*
