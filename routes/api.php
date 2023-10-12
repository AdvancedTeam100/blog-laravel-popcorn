<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    Route::post('login', 'App\Http\Controllers\AuthController@login');
    Route::post('register', 'App\Http\Controllers\AuthController@register');
    Route::post('logout', 'App\Http\Controllers\AuthController@logout');
    Route::post('refresh', 'App\Http\Controllers\AuthController@refresh');
    Route::get('user-profile', 'App\Http\Controllers\AuthController@userProfile');

    Route::group(['middleware' => 'role:1'], function () {
        Route::get('admin/get_admin', [App\Http\Controllers\AdminController::class, 'getAllAdmin'])->name('admin.getadmin');
        Route::post('admin/register_admin', [App\Http\Controllers\AdminController::class, 'registerAdmin'])->name('admin.registeradmin');
        Route::post('admin/update_admin', [App\Http\Controllers\AdminController::class, 'updateAdmin'])->name('admin.updateadmin');
        Route::post('admin/delete_admin', [App\Http\Controllers\AdminController::class, 'deleteAdmin'])->name('admin.deleteadmin');
        
        Route::get('admin/get_user', [App\Http\Controllers\AdminController::class, 'getAllUser'])->name('admin.getuser');
        Route::post('admin/register_user', [App\Http\Controllers\AdminController::class, 'registerUser'])->name('admin.registeruser');
        Route::post('admin/update_user', [App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.updateuser');
        Route::post('admin/delete_user', [App\Http\Controllers\AdminController::class, 'deleteUser'])->name('admin.deleteuser');
    });
    
    Route::group(['middleware' => 'role:2'], function () {
        Route::get('user/get_user', [App\Http\Controllers\UserController::class, 'getAllUser'])->name('user.getuser');
        Route::post('user/register_user', [App\Http\Controllers\UserController::class, 'registerUser'])->name('user.registeruser');
        Route::post('user/update_user', [App\Http\Controllers\UserController::class, 'updateUser'])->name('user.updateuser');
        Route::post('user/delete_user', [App\Http\Controllers\UserController::class, 'deleteUser'])->name('user.deleteuser');
    });
    
    Route::group(['middleware' => 'role:3'], function () {
        // Routes accessible only to user
    });
});

// Route::get('articles', [App\Http\Controllers\ArticleController::class, 'index'])->name('articles');

Route::get('articles', 'App\Http\Controllers\ArticleController@index');
Route::get('articles/{article}', 'App\Http\Controllers\ArticleController@show');
Route::post('articles', 'App\Http\Controllers\ArticleController@store');
Route::put('articles/{article}', 'App\Http\Controllers\ArticleController@update');
Route::delete('articles/{article}', 'App\Http\Controllers\ArticleController@delete');
