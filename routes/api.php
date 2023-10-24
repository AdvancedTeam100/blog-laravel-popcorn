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
    Route::get('user-profile', 'App\Http\Controllers\AuthController@userProfile');

    Route::group(['middleware' => 'role:1'], function () {
        Route::get('leader/index', [App\Http\Controllers\AdminController::class, 'getLeaders']);
        Route::get('leader/{id}', [App\Http\Controllers\AdminController::class, 'getLeaderById']);

        Route::post('leader/create', [App\Http\Controllers\AdminController::class, 'addLeader']);
        Route::post('leader/update/{id}', [App\Http\Controllers\AdminController::class, 'updateLeader']);
        Route::delete('leader/{id}', [App\Http\Controllers\AdminController::class, 'deleteLeader']);


    });
        
    Route::group(['middleware' => 'role:2'], function () {

        Route::get('user/index', [App\Http\Controllers\UserController::class, 'getUsers']);
        Route::get('user/groupusers', [App\Http\Controllers\UserController::class, 'getGroupUsers']);
        Route::get('user/categories', [App\Http\Controllers\UserController::class, 'getCategoriesForUser']);
        Route::get('user/group/getall', [App\Http\Controllers\UserController::class, 'getAllGroups']);

        Route::get('user/{id}', [App\Http\Controllers\UserController::class, 'getUserById']);
        Route::post('user/create', [App\Http\Controllers\UserController::class, 'addUser']);
        Route::post('user/update/{id}', [App\Http\Controllers\UserController::class, 'updateUser']);
        Route::delete('user/{id}', [App\Http\Controllers\UserController::class, 'deleteUser']);


        // Route:

        //category
        Route::post('/category/create', [App\Http\Controllers\CategoryController::class, 'addCategory']);
        Route::delete('/category/{id}', [App\Http\Controllers\CategoryController::class, 'deleteCategory']);
        Route::post('/category/update/{id}', [App\Http\Controllers\CategoryController::class, 'updateCategory']);

        
        //genre
        Route::post('/genre/create', [App\Http\Controllers\GenreController::class, 'addGenre']);
        Route::delete('/genre/{id}', [App\Http\Controllers\GenreController::class, 'deleteGenre']);
        Route::post('/genre/update/{id}', [App\Http\Controllers\GenreController::class, 'updateGenre']);

        //blog
        Route::post('blog/create', [App\Http\Controllers\PostController::class, 'createBlog'])->name('blog.create');
        Route::get('blog', [App\Http\Controllers\PostController::class, 'getAllBlogs'])->name('blog.index');
    });

    Route::group(['middleware' => 'role:3'], function () {
        Route::post('refresh', 'App\Http\Controllers\AuthController@refresh');
        Route::get('user-profile', 'App\Http\Controllers\AuthController@userProfile');

        Route::get('/category/all', [App\Http\Controllers\CategoryController::class, 'getAllCategories']);
        Route::get('/category/index/{group_id}', [App\Http\Controllers\CategoryController::class, 'getCategories']);
        Route::get('/genre/index/{category_id}', [App\Http\Controllers\GenreController::class, 'getGenres']);
        Route::get('/genre/all', [App\Http\Controllers\GenreController::class, 'getAllGenres']);
        Route::get('/genre/allbygroup', [App\Http\Controllers\GenreController::class, 'getGenresByGroup']);

        Route::get('blog/{group_id}', [App\Http\Controllers\PostController::class, 'getBlogs']);
        Route::get('blog/show/{id}', [App\Http\Controllers\PostController::class, 'show']);

    });
    
});

// Route::get('articles', [App\Http\Controllers\ArticleController::class, 'index'])->name('articles');

Route::get('articles', 'App\Http\Controllers\ArticleController@index');
Route::get('articles/{article}', 'App\Http\Controllers\ArticleController@show');
Route::post('articles', 'App\Http\Controllers\ArticleController@store');
Route::put('articles/{article}', 'App\Http\Controllers\ArticleController@update');
Route::delete('articles/{article}', 'App\Http\Controllers\ArticleController@delete');

Route::get('/images/{path}', function ($path) {
    $filePath = public_path('upload/images/' . $path);
    return response()->file($filePath);
});