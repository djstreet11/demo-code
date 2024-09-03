<?php

use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentDeliveryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapXmlController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TermsOfUseController;
use App\Http\Controllers\ThankYouController;

use App\Http\Controllers\PartnersController;
use App\Http\Controllers\TechSupportController;
use App\Http\Controllers\VacanciesController;
use App\Http\Controllers\ShippingAndPaymentController;

use App\Http\Controllers\WarrantyAndExchangeController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

require __DIR__.'/dashboard.php';


Route::get('/sitemap.xml', [SitemapXmlController::class, 'index']);
Route::get('/not-found', [ErrorController::class, 'notFound'])->name('404');

Route::name('cart.')->prefix('cart')->controller(CartController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('add', 'add')->name('add');
    Route::delete('destroy/{product}', 'destroy')->name('destroy');
    Route::get('update', 'update')->name('update');
});

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');

Route::name('page.')->controller(PageController::class)->group(function () {
    Route::get('/page/{slug}', 'index')->name('index')->where('slug', '[A-Za-z0-9\-]+');
});

Route::name('category.')->controller(CategoryController::class)->group(function () {
    Route::get('/{slug}', 'index')->name('index')->where('slug', '[A-Za-z0-9\-]+');
});


Route::name('product.')->controller(ProductController::class)->group(function () {
    Route::get('/{category_slug}/{product_slug}', 'index')->name('index')->where([
        'category_slug' => '[A-Za-z0-9\-]+',
        'product_slug'  => '[A-Za-z0-9\-]+',
    ]);
});