<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdvertiserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CbController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

// Strona główna i oferty – dostępne dla wszystkich
Route::get('/', [OfferController::class, 'index'])->name('home');
Route::get('/oferty/{offer}', [OfferController::class, 'show'])->name('offers.show');
// Giełda pojazdów – lista i ogłoszenia dla wszystkich
Route::get('/pojazdy', [VehicleController::class, 'index'])->name('vehicles.index');
Route::get('/pojazdy/{vehicle}', [VehicleController::class, 'show'])->whereNumber('vehicle')->name('vehicles.show');

// Mapa kierowców i SOS
Route::get('/mapa', [MapController::class, 'index'])->name('map.index');
Route::get('/mapa/dane', [MapController::class, 'data'])->name('map.data');
Route::get('/sos', [MapController::class, 'sos'])->name('map.sos');
Route::view('/app', 'app')->name('app');
Route::view('/kalkulator', 'calculator')->name('calculator');
Route::middleware(['auth', 'phone'])->group(function () {
    Route::post('/mapa/zgloszenia', [MapController::class, 'store'])->middleware('throttle:15,1')->name('map.store');
    Route::post('/mapa/sos/{report}/pomoc', [MapController::class, 'help'])->middleware('throttle:30,1')->name('map.help');
    Route::post('/mapa/zgloszenia/{report}/zamknij', [MapController::class, 'close'])->name('map.close');
});

// CB Kanał 19 – tekstowe radio kierowców
Route::get('/cb', [CbController::class, 'index'])->name('cb.index');
Route::get('/cb/wiadomosci', [CbController::class, 'messages'])->middleware('throttle:60,1')->name('cb.messages');
Route::middleware(['auth', 'phone'])->group(function () {
    Route::post('/cb/wiadomosci', [CbController::class, 'store'])->middleware('throttle:6,1')->name('cb.store');
    Route::post('/cb/wiadomosci/{message}/zglos', [CbController::class, 'report'])->middleware('throttle:20,1')->name('cb.report');
    Route::delete('/cb/wiadomosci/{message}', [CbController::class, 'destroy'])->name('cb.destroy');
});

// Aktualności i poradniki
Route::get('/aktualnosci', [PostController::class, 'index'])->name('posts.index');
Route::get('/aktualnosci/{post}', [PostController::class, 'show'])->whereNumber('post')->name('posts.show');

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
Route::middleware(['auth', 'phone', 'role:advertiser'])->prefix('reklamodawca')->name('advertiser.')->group(function () {
    Route::get('/', [AdvertiserController::class, 'index'])->name('index');
    Route::get('/kampanie/nowa', [AdvertiserController::class, 'create'])->name('campaigns.create');
    Route::post('/kampanie', [AdvertiserController::class, 'store'])->name('campaigns.store');
    Route::post('/kampanie/{campaign}/akceptuj', [AdvertiserController::class, 'accept'])->name('campaigns.accept');
});

// Edycja kampanii – reklamodawca (do akceptacji) i administrator (zawsze)
Route::middleware(['auth', 'phone'])->prefix('kampanie')->name('advertiser.campaigns.')->group(function () {
    Route::get('/{campaign}', [AdvertiserController::class, 'edit'])->name('edit');
    Route::put('/{campaign}', [AdvertiserController::class, 'update'])->name('update');
    Route::post('/{campaign}/jezyk/{locale}', [AdvertiserController::class, 'saveCreative'])->name('creative');
    Route::get('/{campaign}/podglad', [AdvertiserController::class, 'preview'])->name('preview');
});
Route::get('/artykul/{creative}', [AdvertiserController::class, 'article'])->name('article');

// Administrator
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/wpisy', [PostController::class, 'admin'])->name('posts.admin');
    Route::get('/wpisy/nowy', [PostController::class, 'create'])->name('posts.create');
    Route::post('/wpisy', [PostController::class, 'store'])->name('posts.store');
    Route::get('/wpisy/{post}/edytuj', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/wpisy/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/wpisy/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/kampanie/{campaign}/wlacz', [AdminController::class, 'toggleCampaign'])->name('campaigns.toggle');
    Route::post('/uzytkownicy/{user}/blokada', [AdminController::class, 'toggleBlock'])->name('users.block');
    Route::get('/oferty/{offer}/edytuj', [AdminController::class, 'editOffer'])->name('offers.edit');
    Route::put('/oferty/{offer}', [AdminController::class, 'updateOffer'])->name('offers.update');
    Route::delete('/oferty/{offer}', [AdminController::class, 'destroyOffer'])->name('offers.destroy');
});

// Sprzedaż pojazdu – każde konto z potwierdzonym telefonem
Route::middleware(['auth', 'phone'])->group(function () {
    Route::get('/sprzedaj-pojazd', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/pojazdy', [VehicleController::class, 'store'])->middleware('throttle:20,1')->name('vehicles.store');
    Route::get('/moje-pojazdy', [VehicleController::class, 'mine'])->name('vehicles.mine');
    Route::get('/pojazdy/{vehicle}/edytuj', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::put('/pojazdy/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::get('/pojazdy/{vehicle}/podglad', [VehicleController::class, 'preview'])->name('vehicles.preview');
    Route::post('/pojazdy/{vehicle}/publikuj', [VehicleController::class, 'publish'])->name('vehicles.publish');
    Route::delete('/pojazdy/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
});
