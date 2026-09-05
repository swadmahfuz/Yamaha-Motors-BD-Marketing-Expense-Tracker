<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MonthlyBudgetController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\BudgetRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HomDashboardController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/install', [InstallController::class, 'show'])->name('install.show');
Route::post('/install', [InstallController::class, 'run'])->name('install.run');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/requests', [BudgetRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [BudgetRequestController::class, 'create'])->name('requests.create');
    Route::get('/requests/{request}', [BudgetRequestController::class, 'show'])->name('requests.show');
    Route::get('/requests/{request}/edit', [BudgetRequestController::class, 'edit'])->name('requests.edit');

    Route::get('/requests/{request}/actuals/create', function (\App\Models\BudgetRequest $request) {
        return view('actuals.create', ['budgetRequest' => $request]);
    })->name('actuals.create');

    Route::post('/requests/{request}/cancel', function (\App\Models\BudgetRequest $request, \App\Services\ApprovalService $approvalService) {
        abort_unless(auth()->user()->can('cancel', $request), 403);
        $approvalService->cancel($request, auth()->user());

        return back()->with('success', 'Request cancelled and commitment released.');
    })->name('requests.cancel');

    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('/approvals/{budgetRequest}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{budgetRequest}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');

    Route::middleware('role:super_admin')->prefix('super-admin')->group(function () {
        Route::get('/backdates', [SuperAdminController::class, 'backdates'])->name('super-admin.backdates');
        Route::post('/backdates/{budgetRequest}/clear', [SuperAdminController::class, 'clear'])->name('super-admin.clear');
        Route::post('/backdates/{budgetRequest}/reject', [SuperAdminController::class, 'reject'])->name('super-admin.reject');
    });

    Route::middleware('role:head_of_marketing|admin|super_admin')->group(function () {
        Route::get('/hom', HomDashboardController::class)->name('hom.dashboard');
        Route::get('/export/requests', [ExportController::class, 'requests'])->name('export.requests');
    });

    Route::middleware('role:admin|super_admin|head_of_marketing')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/chains', [UserController::class, 'chains'])->name('users.chains');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

        Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::patch('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::patch('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');

        Route::get('/budgets', [MonthlyBudgetController::class, 'index'])->name('budgets.index');
        Route::post('/budgets', [MonthlyBudgetController::class, 'store'])->name('budgets.store');

        Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
    });

    Route::get('/notifications', function () {
        $notifications = auth()->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    })->name('notifications.index');

    Route::post('/notifications/{id}/read', function (string $id) {
        auth()->user()->notifications()->where('id', $id)->first()?->markAsRead();

        return back();
    })->name('notifications.read');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
