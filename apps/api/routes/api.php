<?php

use App\Http\Controllers\AdminSongController;
use App\Http\Controllers\RecordingController;
use App\Http\Controllers\RevenueCatController;
use App\Http\Controllers\SongController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['status' => 'ok']);
Route::get('/v1/songs', [SongController::class, 'index']);
Route::get('/v1/songs/{songId}', [SongController::class, 'show'])->whereNumber('songId');
Route::get('/v1/songs/{songId}/stream', [SongController::class, 'stream'])->whereNumber('songId')->name('songs.stream');
Route::post('/v1/songs/{songId}/recordings', [RecordingController::class, 'store'])->whereNumber('songId');
Route::get('/v1/recordings/{recordingId}', [RecordingController::class, 'show'])->whereNumber('recordingId');
Route::post('/v1/webhooks/revenuecat', RevenueCatController::class);
Route::middleware('admin.token')->prefix('/v1/admin')->group(function () {
    Route::get('/songs', [AdminSongController::class, 'index']);
    Route::post('/songs', [AdminSongController::class, 'store']);
    Route::post('/songs/{songId}/assets', [AdminSongController::class, 'upload'])->whereNumber('songId');
    Route::post('/songs/{songId}/jobs', [AdminSongController::class, 'enqueue'])->whereNumber('songId');
    Route::get('/jobs/{jobId}', [AdminSongController::class, 'job'])->whereNumber('jobId');
    Route::get('/songs/{songId}/timeline', [AdminSongController::class, 'timeline'])->whereNumber('songId');
    Route::put('/songs/{songId}/timeline', [AdminSongController::class, 'updateTimeline'])->whereNumber('songId');
    Route::post('/songs/{songId}/publish', [AdminSongController::class, 'publish'])->whereNumber('songId');
});
