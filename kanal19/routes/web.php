<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\OfferController;
use Illuminate\Support\Facades\Route;

// Strona główna i oferty – dostępne dla wszystkich
Route::get('/', [OfferController::class, 'index'])->name('home');
Route::get('/oferty/{offer}', [OfferController::class, 'show'])->name('offers.show');
Route::post('/jezyk', [AuthController::class, 'setLanguage'])->name('language');

// Logowanie i rejestracja
Route::middleware('guest')->group(function () {
    Route::get('/logowanie', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/logowanie', [AuthController::class, 'login'])->middleware('throttle:20,1');
    Route::get('/rejestracja', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/rejestracja', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/wyloguj', [AuthController::class, 'logout'])->name('logout');
    Route::get('/weryfikacja', [AuthController::class, 'showVerify'])->name('verify.show');
    Route::post('/weryfikacja', [AuthController::class, 'verify'])->middleware('throttle:10,1')->name('verify');
    Route::post('/weryfikacja/nowy-kod', [AuthController::class, 'resend'])->middleware('throttle:5,1')->name('verify.resend');
});

// Kierowca
Route::middleware(['auth', 'phone', 'role:driver'])->group(function () {
    Route::get('/kierowca', [DriverController::class, 'profile'])->name('driver.profile');
    Route::post('/kierowca/baza', [DriverController::class, 'toggleDatabase'])->name('driver.database');
    Route::post('/kierowca/zaproszenia/{invitation}', [DriverController::class, 'acceptInvitation'])->name('driver.invitation.accept');
    Route::post('/oferty/{offer}/aplikuj', [OfferController::class, 'apply'])->middleware('throttle:20,1')->name('offers.apply');
});

// Firma
Route::middleware(['auth', 'phone', 'role:company'])->prefix('firma')->name('company.')->group(function () {
    Route::get('/oferty', [CompanyController::class, 'offers'])->name('offers');
    Route::get('/oferty/nowa', [CompanyController::class, 'create'])->name('offers.create');
    Route::post('/oferty', [CompanyController::class, 'store'])->name('offers.store');
    Route::get('/oferty/{offer}/edytuj', [CompanyController::class, 'edit'])->name('offers.edit');
    Route::put('/oferty/{offer}', [CompanyController::class, 'update'])->name('offers.update');
    Route::get('/oferty/{offer}/podglad', [CompanyController::class, 'preview'])->name('offers.preview');
    Route::post('/oferty/{offer}/publikuj', [CompanyController::class, 'publish'])->name('offers.publish');
    Route::get('/oferty/{offer}/kandydaci', [CompanyController::class, 'applications'])->name('offers.applications');
    Route::get('/kierowcy', [CompanyController::class, 'drivers'])->name('drivers');
    Route::post('/kierowcy/{driver}/zapros', [CompanyController::class, 'invite'])->middleware('throttle:30,1')->name('drivers.invite');
});

// Reklamodawca – na razie strona informacyjna; panel kampanii to kolejny etap
Route::middleware(['auth', 'phone', 'role:advertiser'])->get('/reklamodawca', fn () => view('advertiser.index'))->name('advertiser.index');

// Administrator
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('/uzytkownicy/{user}/blokada', [AdminController::class, 'toggleBlock'])->name('users.block');
    Route::get('/oferty/{offer}/edytuj', [AdminController::class, 'editOffer'])->name('offers.edit');
    Route::put('/oferty/{offer}', [AdminController::class, 'updateOffer'])->name('offers.update');
    Route::delete('/oferty/{offer}', [AdminController::class, 'destroyOffer'])->name('offers.destroy');
});
