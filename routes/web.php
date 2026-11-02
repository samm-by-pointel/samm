<?php

use App\Modules\Audit\Http\Controllers\AuditLogController;
use App\Modules\Cases\Http\Controllers\CaseAssignmentController;
use App\Modules\Cases\Http\Controllers\CaseController;
use App\Modules\Cases\Http\Controllers\CasePriorityController;
use App\Modules\Cases\Http\Controllers\CaseStatusController;
use App\Modules\Cases\Http\Controllers\DashboardController;
use App\Modules\Cases\Http\Controllers\InternalNoteController;
use App\Modules\Core\Http\Controllers\LocaleController;
use App\Modules\IdentityVault\Http\Controllers\IdentityRevealController;
use App\Modules\Messaging\Http\Controllers\PublicMessageController;
use App\Modules\Messaging\Http\Controllers\StaffMessageController;
use App\Modules\Organizations\Http\Controllers\EmergencyContactController;
use App\Modules\Organizations\Http\Controllers\OrganizationLogoController;
use App\Modules\Organizations\Http\Controllers\OrganizationSettingsController;
use App\Modules\Reporting\Http\Controllers\ReportCategoryController;
use App\Modules\Reporting\Http\Controllers\ReportWizardController;
use App\Modules\Tracking\Http\Controllers\TrackingController;
use App\Modules\Tracking\Http\Middleware\EnsureTrackingSession;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public.home')->name('public.home');

Route::post('/langue', [LocaleController::class, 'update'])->name('locale.update');
Route::get('/logo', [OrganizationLogoController::class, 'show'])->name('organization.logo');

Route::prefix('signaler')->name('report.')->middleware('throttle:public-form')->group(function (): void {
    Route::get('/', [ReportWizardController::class, 'start'])->name('start');
    Route::get('/confirmation', [ReportWizardController::class, 'confirmation'])->name('confirmation');
    Route::post('/abandonner', [ReportWizardController::class, 'abandon'])->name('abandon');
    Route::post('/transmettre', [ReportWizardController::class, 'submit'])->middleware('throttle:public-submission')->name('submit');
    Route::get('/{step}', [ReportWizardController::class, 'show'])->name('step.show');
    Route::post('/{step}', [ReportWizardController::class, 'store'])->name('step.store');
});

Route::prefix('suivre')->name('tracking.')->middleware('throttle:public-form')->group(function (): void {
    Route::get('/', [TrackingController::class, 'showLogin'])->name('login');
    Route::post('/', [TrackingController::class, 'authenticate'])->middleware('throttle:public-tracking')->name('authenticate');
    Route::post('/quitter', [TrackingController::class, 'logout'])->name('logout');

    Route::middleware(EnsureTrackingSession::class)->group(function (): void {
        Route::get('/dossier', [TrackingController::class, 'show'])->name('show');
        Route::post('/dossier/messages', [PublicMessageController::class, 'store'])->name('messages.store');
    });
});

Route::middleware('auth')->prefix('gestion')->name('manage.')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/journal', [AuditLogController::class, 'index'])->middleware('can:audit.view')->name('audit.index');
    Route::get('/dossiers', [CaseController::class, 'index'])->name('cases.index');
    Route::get('/dossiers/{case}', [CaseController::class, 'show'])->name('cases.show');
    Route::post('/dossiers/{case}/identite', [IdentityRevealController::class, 'store'])->name('cases.identity.reveal');
    Route::post('/dossiers/{case}/affectation', [CaseAssignmentController::class, 'store'])->name('cases.assignment.store');
    Route::post('/dossiers/{case}/statut', [CaseStatusController::class, 'store'])->name('cases.status.store');
    Route::post('/dossiers/{case}/priorite', [CasePriorityController::class, 'store'])->name('cases.priority.store');
    Route::post('/dossiers/{case}/notes', [InternalNoteController::class, 'store'])->name('cases.notes.store');
    Route::post('/dossiers/{case}/messages', [StaffMessageController::class, 'store'])->name('cases.messages.store');

    Route::middleware('can:settings.manage')->prefix('organisation')->name('organization.')->group(function (): void {
        Route::get('/', [OrganizationSettingsController::class, 'edit'])->name('edit');
        Route::put('/', [OrganizationSettingsController::class, 'update'])->name('update');
        Route::post('/urgences', [EmergencyContactController::class, 'store'])->name('emergency.store');
        Route::put('/urgences/{contact}', [EmergencyContactController::class, 'update'])->name('emergency.update');
        Route::delete('/urgences/{contact}', [EmergencyContactController::class, 'destroy'])->name('emergency.destroy');
        Route::post('/categories', [ReportCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [ReportCategoryController::class, 'update'])->name('categories.update');
    });
});
