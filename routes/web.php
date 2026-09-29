<?php
use App\Http\Controllers\{AuthController,AttendanceController,AdminController};
use Illuminate\Support\Facades\Route;
Route::redirect('/','/dashboard');
Route::middleware('guest')->group(function () { Route::get('/login',[AuthController::class,'form'])->name('login'); Route::post('/login',[AuthController::class,'login'])->middleware('throttle:5,1')->name('login.submit'); });
Route::middleware('auth')->group(function () {
    Route::post('/logout',[AuthController::class,'logout'])->name('logout');
    Route::get('/dashboard',[AttendanceController::class,'dashboard'])->name('dashboard');
    Route::post('/attendance',[AttendanceController::class,'store'])->middleware('throttle:5,1')->name('attendance.store');
    Route::get('/history',[AttendanceController::class,'history'])->name('history');
    Route::get('/attendance/{attendance}/photo',[AttendanceController::class,'photo'])->name('attendance.photo');
    Route::prefix('admin')->middleware('admin')->name('admin.')->group(function () {
        Route::get('/',[AdminController::class,'dashboard'])->name('dashboard');
        Route::get('/users',[AdminController::class,'users'])->name('users');
        Route::post('/users',[AdminController::class,'saveUser'])->name('users.create');
        Route::put('/users/{user}',[AdminController::class,'saveUser'])->name('users.update');
        Route::patch('/users/{user}/toggle',[AdminController::class,'toggle'])->name('users.toggle');
        Route::get('/attendance',[AdminController::class,'attendance'])->name('attendance');
        Route::get('/report',[AdminController::class,'report'])->name('report');
        Route::get('/locations',[AdminController::class,'locations'])->name('locations');
        Route::post('/locations',[AdminController::class,'saveLocation'])->name('locations.save');
    });
});
