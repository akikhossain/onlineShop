<?php

use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\Admin\DiscountCodeController;
use App\Http\Controllers\admin\HomeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\ProductImageController;
use App\Http\Controllers\admin\SettingController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\admin\SubCategoryController;
use App\Http\Controllers\admin\TempImagesController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\front\AuthController;
use App\Http\Controllers\front\CartController;
use App\Http\Controllers\front\FrontController;
use App\Http\Controllers\front\ShopController;
use App\Http\Controllers\ProductSubCategoryController;
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

// Route::get('/', function () {
//     return view('welcome');
// });
// Route::get('/test', function () {
//     orderEmail(13);
// });

// Frontend
Route::get('/', [FrontController::class, 'index'])->name('front.home');
Route::get('/shop/{categorySlug?}/{subCategorySlug?}', [ShopController::class, 'index'])->name('front.shop');
Route::get('/product/{slug}', [ShopController::class, 'product'])->name('front.product');
Route::get('/cart', [CartController::class, 'cart'])->name('front.cart');
Route::post('/add-to-cart', [CartController::class, 'addToCart'])->name('front.addToCart');
Route::post('/update-cart', [CartController::class, 'updateCart'])->name('front.updateCart');
Route::post('/delete-item', [CartController::class, 'deleteItem'])->name('front.deleteItem');
Route::get('/checkOut', [CartController::class, 'checkOut'])->name('front.checkOut');
Route::post('/process-checkout', [CartController::class, 'processCheckout'])->name('front.processCheckout');
Route::get('/thanks/{orderId}', [CartController::class, 'thankyou'])->name('front.thankyou');
Route::post('/get-order-summery', [CartController::class, 'getOrderSummery'])->name('front.getOrderSummery');
Route::post('/apply-discount', [CartController::class, 'applyDiscount'])->name('front.applyDiscount');
Route::post('/remove-discount', [CartController::class, 'removeCoupon'])->name('front.removeCoupon');
Route::post('/add-to-wishlist', [FrontController::class, 'addToWishList'])->name('front.addToWishList');
Route::get('/page/{slug}', [FrontController::class, 'page'])->name('front.page');
Route::post('/send-contact-mail', [FrontController::class, 'sentContactMail'])->name('front.sentContactMail');
Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('front.forgotPassword');
Route::post('/forgot-password', [AuthController::class, 'processForgotPassword'])->name('front.processForgotPassword');
Route::get('/reset-password/{token}', [AuthController::class, 'resetPassword'])->name('front.resetPassword');
Route::post('/reset-password', [AuthController::class, 'processResetPassword'])->name('front.processResetPassword');
Route::post('/save-rating/{productId}', [ShopController::class, 'saveRating'])->name('front.saveRating');





// Account
Route::group(['prefix' => 'account'], function () {
    Route::group(['middleware' => 'guest'], function () {
        Route::get('/login', [AuthController::class, 'login'])->name('account.login');
        Route::post('/login', [AuthController::class, 'authenticate'])->name('account.authenticate');
        Route::get('/register', [AuthController::class, 'register'])->name('account.register');
        Route::post('/process-register', [AuthController::class, 'processRegister'])->name('account.processRegister');
    });
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/profile', [AuthController::class, 'profile'])->name('account.profile');
        Route::post('/update-profile', [AuthController::class, 'updateProfile'])->name('account.updateProfile');
        Route::post('/update-address', [AuthController::class, 'updateAddress'])->name('account.updateAddress');
        Route::get('/my-orders', [AuthController::class, 'orders'])->name('account.orders');
        Route::get('/order-detail/{orderId}', [AuthController::class, 'orderDetail'])->name('account.orderDetail');
        Route::get('/wishlist', [AuthController::class, 'wishlist'])->name('account.wishlist');
        Route::post('/remove-wishlist', [AuthController::class, 'removeWishlist'])->name('account.removeWishlist');
        Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('account.showChangePassword');
        Route::post('/process-change-password', [AuthController::class, 'changePassword'])->name('account.changePassword');
        Route::get('/logout', [AuthController::class, 'logout'])->name('account.logout');
    });
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
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.delete');

        // Sub Category
        Route::get('/sub-categories/list', [SubCategoryController::class, 'index'])->name('sub-categories.list');
        Route::get('/sub-categories/create', [SubCategoryController::class, 'create'])->name('sub-categories.create');
        Route::post('/sub-categories', [SubCategoryController::class, 'store'])->name('sub-categories.store');
        Route::get('/sub-categories/{subCategory}/edit', [SubCategoryController::class, 'edit'])->name('sub-categories.edit');
        Route::post('/sub-categories/{subCategory}', [SubCategoryController::class, 'update'])->name('sub-categories.update');
        Route::delete('/sub-categories/{subCategory}', [SubCategoryController::class, 'destroy'])->name('sub-categories.delete');


        // Brand
        Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
        Route::get('/brands/list', [BrandController::class, 'index'])->name('brands.list');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
        Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
        Route::post('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.delete');


        // Product
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::get('/products/list', [ProductController::class, 'index'])->name('products.list');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::post('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.delete');
        Route::get('/get-products', [ProductController::class, 'getProducts'])->name('products.getProducts');
        Route::get('/product-ratings', [ProductController::class, 'productRating'])->name('products.productRating');
        Route::get('/change-rating', [ProductController::class, 'changeRatingStatus'])->name('products.changeRatingStatus');


        // product-sub-category
        Route::get('/product-subCategories', [ProductSubCategoryController::class, 'index'])->name('product-subCategories.list');

        // product image
        Route::post('/product-images/update', [ProductImageController::class, 'update'])->name('product-images.update');
        Route::delete('/product-images', [ProductImageController::class, 'destroy'])->name('product-images.delete');

        // Shipping
        Route::get('/shipping/create', [ShippingController::class, 'create'])->name('shipping.create');
        Route::get('/shipping/{id}', [ShippingController::class, 'edit'])->name('shipping.edit');
        Route::post('/shipping', [ShippingController::class, 'store'])->name('shipping.store');
        Route::put('/shipping/{id}', [ShippingController::class, 'update'])->name('shipping.update');
        Route::delete('/shipping/{id}', [ShippingController::class, 'destroy'])->name('shipping.delete');

        // Discount Code
        Route::get('/coupon/list', [DiscountCodeController::class, 'index'])->name('coupon.list');
        Route::get('/coupon/create', [DiscountCodeController::class, 'create'])->name('coupon.create');
        Route::post('/coupon', [DiscountCodeController::class, 'store'])->name('coupon.store');
        Route::get('/coupon/{id}', [DiscountCodeController::class, 'edit'])->name('coupon.edit');
        Route::put('/coupon/{id}', [DiscountCodeController::class, 'update'])->name('coupon.update');
        Route::delete('/coupon/{id}', [DiscountCodeController::class, 'destroy'])->name('coupon.delete');


        // Order Routes
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.list');
        Route::get('/orders/{id}', [OrderController::class, 'detail'])->name('orders.detail');
        Route::post('/orders/change-status/{id}', [OrderController::class, 'changeStatusForm'])->name('orders.changeStatusForm');
        Route::post('/orders/send-email/{id}', [OrderController::class, 'sendInvoiceEmail'])->name('orders.sendInvoiceEmail');

        // User Routes
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/list', [UserController::class, 'index'])->name('users.list');
        Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.delete');

        // Pages Routes
        Route::get('/pages', [PageController::class, 'index'])->name('pages.list');
        Route::get('/pages/create', [PageController::class, 'create'])->name('pages.create');
        Route::post('/pages', [PageController::class, 'store'])->name('pages.store');
        Route::get('/pages/{id}', [PageController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{id}', [PageController::class, 'update'])->name('pages.update');
        Route::delete('/pages/{id}', [PageController::class, 'destroy'])->name('pages.delete');

        // Setting Routes
        Route::get('/change-password', [SettingController::class, 'showChangePassword'])->name('admin.showChangePassword');
        Route::post('/process-change-password', [SettingController::class, 'changePassword'])->name('admin.changePassword');

        // temp-image
        Route::post('/upload-temp-image', [TempImagesController::class, 'create'])->name('temp-images.create');
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
