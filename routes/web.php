<?php

use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::get('admin/login', [AdminLoginController::class, 'adminLogin'])->name('admin.login');

Route::group(['prefix' => 'admin'], function () {

    Route::group(['middleware' => 'admin.guest'], function () {

        Route::get('/login', [AdminLoginController::class, 'adminLogin'])->name('admin.login');
        Route::post('/authenticate', [AdminLoginController::class, 'authenticate'])->name('admin.authenticate');
    });

    Route::group(['middleware' => 'admin.auth'], function () {

        Route::get('/dashboard', [HomeController::class, 'homeDashboard'])->name('admin.dashboard');
        Route::get('/logout', [HomeController::class, 'logout'])->name('admin.logout');

        // Category
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::get('/categories/list', [CategoryController::class, 'index'])->name('categories.list');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');


        Route::get('/getSlug', function (Request $request) {

            $slug = '';

            if (!empty($request->title)) {
                $slug = Illuminate\Support\Str::slug($request->title);
            }

            return response()->json([
                'status' => true,
                'slug' => $slug

            ]);
        })->name('getSlug');
    });
});
