<?php

use App\Http\Controllers\AI\TutorChatController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\StudentProfileController;
use App\Http\Controllers\Dashboard\TeacherDashboardController;
use App\Http\Controllers\Enem\EnemPracticeController;
use App\Http\Controllers\Gameplay\LeagueController;
use App\Http\Controllers\Gameplay\ShopController;
use App\Http\Controllers\Guardian\GuardianController;
use App\Http\Controllers\Guardian\GuardianStudentController;
use App\Http\Controllers\Guardian\GuardianStudentCreateController;
use App\Http\Controllers\Guardian\GuardianTutorController;
use App\Http\Controllers\Jogos\JogosController;
use App\Http\Controllers\Jogos\QuizMestreController;
use App\Http\Controllers\Jogos\SudokuController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PathController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\SelectRoleController;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn (): RedirectResponse => redirect('/dashboard'));

Route::middleware('guest')->group(function (): void {
    Route::get('/auth/google', [SocialAuthController::class, 'redirect'])->name('oauth.google.redirect');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'callback'])->name('oauth.google.callback');
});

Route::get('/selecionar-perfil', [SelectRoleController::class, 'create'])->name('select-role');
Route::post('/selecionar-perfil', [SelectRoleController::class, 'store'])->name('select-role.store');

Route::middleware('auth')->group(function (): void {
    Route::get('/email/verify', fn () => Inertia::render('Auth/VerifyEmail'))->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request): RedirectResponse {
        $request->fulfill();
        $role = $request->user()?->role;

        return redirect($role === 'guardian' ? '/responsavel' : '/dashboard');
    })->middleware(['signed', 'throttle:email-verification'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request): RedirectResponse {
        $request->user()->sendEmailVerificationNotification();

        return back();
    })->middleware('throttle:email-verification')->name('verification.send');

    Route::post('/logout', function (Request $request): RedirectResponse {
        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    })->name('logout');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/perfil', [StudentProfileController::class, 'show'])->name('profile.show');
    Route::post('/perfil/serie', [StudentProfileController::class, 'updateGrade'])->name('profile.update-grade');
    Route::post('/perfil/avatar', [StudentProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
    Route::post('/perfil/avatar/pessoal', [StudentProfileController::class, 'usePersonalAvatar'])->name('profile.use-personal-avatar');
    Route::get('/media/student-avatars/{filename}', [StudentProfileController::class, 'avatar'])
        ->where('filename', '[A-Za-z0-9._-]+')
        ->name('profile.avatar');
    Route::get('/media/shop-avatars/{filename}', [ShopController::class, 'avatar'])
        ->where('filename', '[A-Za-z0-9._-]+')
        ->name('shop.avatar');
    Route::get('/ranking', [LeagueController::class, 'index'])->name('league.index');
    Route::get('/loja', [ShopController::class, 'index'])->name('shop.index');
    Route::post('/loja/comprar', [ShopController::class, 'purchase'])->name('shop.purchase');
    Route::post('/loja/equipar', [ShopController::class, 'equip'])->name('shop.equip');
    Route::post('/loja/vidas/comprar', [ShopController::class, 'buyLife'])->name('shop.buy-life');
    Route::get('/tutor', [TutorChatController::class, 'index'])->name('tutor.index');
    Route::post('/tutor/mensagens', [TutorChatController::class, 'store'])->name('tutor.store');
    Route::get('/enem', [EnemPracticeController::class, 'index'])->name('enem.index');
    Route::post('/enem/iniciar', [EnemPracticeController::class, 'start'])->name('enem.start');
    Route::get('/enem/questoes/{question}', [EnemPracticeController::class, 'play'])->name('enem.play');
    Route::post('/enem/questoes/{question}/responder', [EnemPracticeController::class, 'answer'])->name('enem.answer');
    Route::get('/jogos', [JogosController::class, 'index'])->name('jogos.index');
    Route::get('/jogos/quiz-mestre', [QuizMestreController::class, 'lobby'])->name('quiz-mestre.lobby');
    Route::post('/jogos/quiz-mestre/sessoes', [QuizMestreController::class, 'start'])->name('quiz-mestre.start');
    Route::get('/jogos/quiz-mestre/sessoes/{session}', [QuizMestreController::class, 'play'])->name('quiz-mestre.play');
    Route::post('/jogos/quiz-mestre/sessoes/{session}/responder', [QuizMestreController::class, 'submit'])->name('quiz-mestre.submit');
    Route::get('/jogos/quiz-mestre/sessoes/{session}/resultado', [QuizMestreController::class, 'result'])->name('quiz-mestre.result');
    Route::get('/jogos/sudoku', [SudokuController::class, 'lobby'])->name('sudoku.lobby');
    Route::get('/jogos/sudoku/dificuldade', [SudokuController::class, 'difficulty'])->name('sudoku.difficulty');
    Route::post('/jogos/sudoku/sessoes', [SudokuController::class, 'start'])->name('sudoku.start');
    Route::get('/jogos/sudoku/sessoes/{session}', [SudokuController::class, 'play'])->name('sudoku.play');
    Route::post('/jogos/sudoku/sessoes/{session}/movimentos', [SudokuController::class, 'submitMove'])->name('sudoku.move');
    Route::get('/jogos/sudoku/sessoes/{session}/resultado', [SudokuController::class, 'result'])->name('sudoku.result');
    Route::get('/teacher/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');

    Route::middleware('role:teacher,school_admin,super_admin')->group(function (): void {
        Route::get('/teacher/classes', fn () => response()->json(['ok' => true]));
    });

    // Push Notifications
    Route::get('/push/vapid-public-key', [PushSubscriptionController::class, 'vapidPublicKey'])->name('push.vapid');
    Route::post('/push/subscriptions', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::delete('/push/subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

    Route::get('/classes/{schoolClass}', [ClassController::class, 'show'])->name('classes.show');
    Route::post('/classes/{schoolClass}/students', [ClassController::class, 'addStudent'])->name('classes.students.add');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');

    // Trilhas
    Route::get('/trilhas', [PathController::class, 'index'])->name('paths.index');
    Route::get('/trilhas/{path}', [PathController::class, 'show'])->name('paths.show');

    // Aulas (LessonPlayer)
    Route::get('/aulas/{lesson}/jogar', [LessonController::class, 'play'])->name('lessons.play');
    Route::post('/runs/{lessonRun}/responder', [LessonController::class, 'answer'])->name('lessons.answer');
    Route::post('/runs/{lessonRun}/finalizar', [LessonController::class, 'finish'])->name('lessons.finish');
});

// Painel dos Responsáveis (Pais/Guardiões)
Route::middleware(['auth', 'verified', 'role:guardian'])->prefix('responsavel')->name('guardian.')->group(function (): void {
    Route::get('/', [GuardianController::class, 'index'])->name('dashboard');
    Route::get('/adicionar-filho', [GuardianStudentCreateController::class, 'create'])->name('student.create');
    Route::post('/adicionar-filho', [GuardianStudentCreateController::class, 'store'])->name('student.store');
    Route::get('/{student}', [GuardianStudentController::class, 'show'])->name('student.show');
    Route::get('/{student}/conversas', [GuardianTutorController::class, 'index'])->name('student.tutor');
});
