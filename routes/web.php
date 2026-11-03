<?php

use App\Http\Controllers\BorrowingRecordController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DigitalFileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\PublicBorrowingController;
use App\Http\Controllers\PublicCategoryController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\PublicNotificationController;
use App\Http\Controllers\PublicOwnerRequestController;
use App\Http\Controllers\PublicResourceController;
use App\Http\Controllers\RegisteredMemberController;
use App\Http\Controllers\ResourceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicHomeController::class, 'index'])->name('public.home');

Route::get('/browse', [PublicResourceController::class, 'index'])->name('public.resources.index');
Route::get('/browse/categories', [PublicCategoryController::class, 'index'])->name('public.categories.index');
Route::get('/browse/{resource}', [PublicResourceController::class, 'show'])
    ->whereNumber('resource')
    ->name('public.resources.show');
Route::get('/browse/{resource}/files/{digitalFile}/download', [PublicResourceController::class, 'download'])
    ->whereNumber(['resource', 'digitalFile'])
    ->middleware(['auth', 'student.active'])
    ->name('public.resources.download');

Route::middleware(['auth', 'student.active'])->group(function () {
    Route::get('/browse/{resource}/request', [PublicBorrowingController::class, 'requestForm'])
        ->whereNumber('resource')
        ->name('public.borrowings.request');
    Route::post('/browse/{resource}/request', [PublicBorrowingController::class, 'store'])
        ->whereNumber('resource')
        ->name('public.borrowings.store');
    Route::get('/my-requests', [PublicBorrowingController::class, 'index'])
        ->name('public.borrowings.index');
    Route::delete('/my-requests/{borrowing}', [PublicBorrowingController::class, 'cancel'])
        ->whereNumber('borrowing')
        ->name('public.borrowings.cancel');
    Route::get('/incoming-requests', [PublicOwnerRequestController::class, 'index'])
        ->name('public.owner-requests.index');
    Route::patch('/incoming-requests/{borrowing}', [PublicOwnerRequestController::class, 'update'])
        ->whereNumber('borrowing')
        ->name('public.owner-requests.update');
    Route::get('/account/notifications', [PublicNotificationController::class, 'index'])
        ->name('public.notifications.index');
    Route::patch('/account/notifications/{notification}/read', [PublicNotificationController::class, 'markAsRead'])
        ->whereNumber('notification')
        ->name('public.notifications.read');
    Route::patch('/account/notifications/read-all', [PublicNotificationController::class, 'markAllAsRead'])
        ->name('public.notifications.read-all');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'student.active'])->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('programs', ProgramController::class)->except('show');
    Route::resource('resources', ResourceController::class);

    Route::get('digital-files/{digital_file}/download', [DigitalFileController::class, 'download'])
        ->name('digital-files.download');
    Route::resource('digital-files', DigitalFileController::class);

    Route::get('borrowing-records', [BorrowingRecordController::class, 'index'])->name('borrowing-records.index');
    Route::get('borrowing-records/{borrowingRecord}', [BorrowingRecordController::class, 'show'])
        ->whereNumber('borrowingRecord')
        ->name('borrowing-records.show');
    Route::patch('borrowing-records/{borrowingRecord}/approve', [BorrowingRecordController::class, 'approve'])
        ->whereNumber('borrowingRecord')
        ->name('borrowing-records.approve');
    Route::patch('borrowing-records/{borrowingRecord}/reject', [BorrowingRecordController::class, 'reject'])
        ->whereNumber('borrowingRecord')
        ->name('borrowing-records.reject');
    Route::patch('borrowing-records/{borrowingRecord}/picked-up', [BorrowingRecordController::class, 'pickedUp'])
        ->whereNumber('borrowingRecord')
        ->name('borrowing-records.picked-up');
    Route::patch('borrowing-records/{borrowingRecord}/returned', [BorrowingRecordController::class, 'returned'])
        ->whereNumber('borrowingRecord')
        ->name('borrowing-records.returned');

    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::resource('notifications', NotificationController::class);
});

Route::middleware(['auth', 'admin'])
    ->prefix('administration')
    ->name('admin.')
    ->group(function () {
        Route::get('members', [RegisteredMemberController::class, 'index'])
            ->name('members.index');
    });

require __DIR__.'/auth.php';
