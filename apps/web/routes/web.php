<?php

use App\Http\Controllers\SongController;
use App\Http\Controllers\PlayController;
use App\Http\Controllers\RecordingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\KaraokeController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//Route::get('/', function () {
Route::get('/', [App\Http\Controllers\TopController::class, 'index'])->name('welcome');
//  return view('welcome');
//});
Route::get('/list', [App\Http\Controllers\ListController::class, 'index'])->name('list');
Route::post('/songs/save-recording', [PlayController::class, 'saveRecording'])->name('songs.saveRecording');

Route::middleware(['auth'])->group(function () {
    Route::middleware('admin')->group(function () {
        Route::get('/admin/karaoke', [KaraokeController::class, 'index'])->name('admin.karaoke.index');
        Route::post('/admin/karaoke', [KaraokeController::class, 'store'])->name('admin.karaoke.store');
        Route::get('/admin/karaoke/{songId}/review', [KaraokeController::class, 'review'])->name('admin.karaoke.review');
        Route::put('/admin/karaoke/{songId}/timeline', [KaraokeController::class, 'updateTimeline'])->name('admin.karaoke.timeline');
        Route::post('/admin/karaoke/{songId}/publish', [KaraokeController::class, 'publish'])->name('admin.karaoke.publish');
    });
    Route::get('/songs', [SongController::class, 'index'])->name('songs.index');
    Route::get('/songs/play/{song}', [PlayController::class, 'show'])->name('songs.play');
    Route::post('/songs/score', [PlayController::class, 'saveScore'])->name('songs.saveScore');
    Route::get('/user/scores', [UserController::class, 'scores'])->name('user.scores');
    Route::get('/recordings', [RecordingController::class, 'index'])->name('recordings.index');
    Route::delete('/recordings/{recording}', [RecordingController::class, 'destroy'])->name('recordings.destroy');
    Route::get('/recordings/{recording}/download', [RecordingController::class, 'download'])->name('recordings.download');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
