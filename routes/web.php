<?php

use App\Http\Controllers\AI\TutorChatController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\StudentProfileController;
use App\Http\Controllers\Dashboard\TeacherDashboardController;
use App\Http\Controllers\Gameplay\LeagueController;
use App\Http\Controllers\Gameplay\ShopController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PathController;
use App\Http\Controllers\PushSubscriptionController;
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

Route::middleware('auth')->group(function (): void {
    Route::get('/email/verify', fn () => Inertia::render('Auth/VerifyEmail'))->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request): RedirectResponse {
        $request->fulfill();

        return redirect('/dashboard');
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
    Route::get('/media/student-avatars/{filename}', [StudentProfileController::class, 'avatar'])
        ->where('filename', '[A-Za-z0-9._-]+')
        ->name('profile.avatar');
    Route::get('/ranking', [LeagueController::class, 'index'])->name('league.index');
    Route::get('/loja', [ShopController::class, 'index'])->name('shop.index');
    Route::post('/loja/comprar', [ShopController::class, 'purchase'])->name('shop.purchase');
    Route::post('/loja/equipar', [ShopController::class, 'equip'])->name('shop.equip');
    Route::post('/loja/vidas/comprar', [ShopController::class, 'buyLife'])->name('shop.buy-life');
    Route::get('/tutor', [TutorChatController::class, 'index'])->name('tutor.index');
    Route::post('/tutor/mensagens', [TutorChatController::class, 'store'])->name('tutor.store');
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
