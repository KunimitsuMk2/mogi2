<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;

// メール認証関連のルート
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware(['auth'])->name('verification.notice');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/', [ItemController::class, 'index'])->name('products.index');
Route::get('/item/{item}', [ItemController::class, 'showItem'])->name('products.item');
Route::get('/mypage',[ProfileController::class,'showMypage'])->name('mypage')->middleware('auth');
Route::get('/mypage/profile/edit', [ProfileController::class, 'edit'])
    ->name('mypage.profile.edit')
    ->middleware('auth');
    
Route::put('/mypage/profile/update', [ProfileController::class, 'update'])
    ->name('mypage.profile.update')
    ->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/purchase/{item}', [PurchaseController::class, 'confirm'])->name('products.confirm');
    Route::post('/purchase/{item}/complete', [PurchaseController::class, 'complete'])->name('purchase.complete');
    Route::get('/purchase/{item}/completed', [PurchaseController::class, 'completed'])->name('purchases.completed');
    Route::get('/purchase/{item}/success', [PurchaseController::class, 'success'])->name('purchase.success');
    Route::get('/purchase/{item}/checkout/success', [PurchaseController::class, 'checkoutSuccess'])->name('purchase.checkout.success');
});

// 住所変更関連ルート
Route::middleware(['auth'])->group(function () {
    Route::get('/address/edit', [ProfileController::class, 'editAddress'])->name('address.edit');
    Route::post('/address/update', [ProfileController::class, 'updateAddress'])->name('address.update');
});

// 商品出品関連のルート
Route::middleware(['auth'])->group(function () {
    Route::get('/sell', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
});

// コメント関連のルート
Route::middleware(['auth'])->group(function () {
    Route::post('/item/{item}/comment', [CommentController::class, 'store'])->name('comments.store');
});

// お気に入り関連のルート
Route::middleware(['auth'])->group(function () {
    Route::post('/item/{item}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
});