<?php

use App\Http\Controllers\Admin\AuctionController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ManageController;
use App\Http\Controllers\Admin\MatchController;
use App\Http\Controllers\FantasyController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::controller(PublicController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/register', 'registerForm')->name('register');
    Route::post('/register', 'register')->middleware('throttle:10,1');
    Route::get('/players', 'players')->name('players');
    Route::get('/teams', 'teams')->name('teams');
    Route::get('/teams/{team}', 'team')->name('team');
    Route::get('/auction', 'auction')->name('auction');
    Route::get('/auction/data', 'auctionLive')->name('auction.data');
    Route::get('/live', 'live')->name('live');
    Route::get('/live/data', 'liveData')->name('live.data');
    Route::get('/matches', 'matches')->name('matches');
    Route::get('/matches/{fixture}', 'match')->name('match');
    Route::get('/matches/{fixture}/data', 'matchLive')->name('match.data');
    Route::get('/points-table', 'points')->name('points');
    Route::get('/{page}', 'page')->whereIn('page', ['about', 'contact', 'terms', 'privacy', 'refunds'])->name('page');
});

Route::controller(FantasyController::class)->prefix('cric11')->name('cric11.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/profile', 'saveProfile')->name('profile');
    Route::get('/match/{fixture}', 'match')->name('match');
    Route::get('/match/{fixture}/data', 'matchData')->name('match.data');
    Route::get('/match/{fixture}/create', 'create')->name('create');
    Route::post('/match/{fixture}/store', 'store')->middleware('throttle:15,1')->name('store');
    Route::get('/entry/{entry}', 'entry')->name('entry');
    Route::get('/entry/{entry}/edit', 'edit')->name('edit');
    Route::put('/entry/{entry}', 'update')->name('update');
});

Route::get('/admin/login', [AuthController::class, 'show'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::controller(ManageController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('settings', 'settings')->name('settings');
        Route::post('settings', 'saveSettings')->name('settings.save');
        Route::get('players', 'players')->name('players');
        Route::get('players/export', 'exportPlayers')->name('players.export');
        Route::get('players/{player}/edit', 'editPlayer')->name('players.edit');
        Route::put('players/{player}', 'updatePlayer')->name('players.update');
        Route::post('players/{player}/status', 'playerStatus')->name('players.status');
        Route::get('players/{player}/payment', 'paymentProof')->name('players.payment');
        Route::delete('players/{player}', 'deletePlayer')->name('players.delete');
        Route::get('teams', 'teams')->name('teams');
        Route::post('teams/{team?}', 'saveTeam')->name('teams.save');
        Route::delete('teams/{team}', 'deleteTeam')->name('teams.delete');
    });

    Route::controller(AuctionController::class)->group(function () {
        Route::get('auction', 'index')->name('auction');
        Route::post('auction/start', 'start')->name('auction.start');
        Route::post('auction/bid', 'bid')->name('auction.bid');
        Route::post('auction/undo', 'undoBid')->name('auction.undo');
        Route::post('auction/sold', 'sold')->name('auction.sold');
        Route::post('auction/unsold', 'unsold')->name('auction.unsold');
        Route::post('auction/revert/{player}', 'revert')->name('auction.revert');
    });

    Route::controller(MatchController::class)->prefix('matches')->group(function () {
        Route::get('/', 'index')->name('matches');
        Route::post('save/{fixture?}', 'save')->name('matches.save');
        Route::delete('{fixture}', 'delete')->name('matches.delete');
        Route::get('{fixture}/score', 'score')->name('score');
        Route::post('{fixture}/toss', 'toss')->name('score.toss');
        Route::post('{fixture}/ball', 'ball')->name('score.ball');
        Route::post('{fixture}/undo', 'undo')->name('score.undo');
        Route::post('{fixture}/end-innings', 'endInning')->name('score.end');
        Route::post('{fixture}/second-innings', 'secondInning')->name('score.second');
        Route::post('{fixture}/result', 'result')->name('score.result');
    });
});
