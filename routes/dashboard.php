<?php

use App\Http\Controllers\Dashboard\AttributeController as DashboardAttributeController;
use App\Http\Controllers\Dashboard\AuthController;
use App\Http\Controllers\Dashboard\CategoryController as DashboardCategoryController;
use App\Http\Controllers\Dashboard\CustomerController as DashboardCustomerController;
use App\Http\Controllers\Dashboard\HomeController as DashboardHomeController;
use App\Http\Controllers\Dashboard\OrderController as DashboardOrderController;
use App\Http\Controllers\Dashboard\PageController as DashboardPageController;
use App\Http\Controllers\Dashboard\ProductController as DashboardProductController;
use App\Http\Controllers\Dashboard\Settings\PaymentController as DashboardPaymentController;
use App\Http\Controllers\Dashboard\Settings\ShippingController as DashboardShippingController;
use App\Http\Controllers\Dashboard\SettingsController as DashboardSettingsController;
use App\Http\Controllers\Dashboard\StoreController as DashboardStoreController;
use App\Http\Controllers\Dashboard\TagController as DashboardTagController;
use App\Http\Controllers\Dashboard\UnitController as DashboardUnitController;
use App\Http\Controllers\Dashboard\UserController as DashboardUserController;
use App\Http\Controllers\Dashboard\VariationController as DashboardVariationController;
use App\Http\Controllers\Dashboard\PartnersController as DashboardPartnersController;
use App\Http\Controllers\Dashboard\FaqController as DashboardFaqController;
use App\Http\Controllers\Dashboard\VacancyController as DashboardVacancyController;
use App\Http\Controllers\Dashboard\ReviewController as DashboardReviewController;


Route::middleware('admin:admin')->name('dashboard.')->prefix('dashboard')->group(function () {


    Route::name('product.')->prefix('products')->controller(DashboardProductController::class)->group(function () {
        include __DIR__.'/crudRoutes.php';
    });

    Route::name('setting.')->prefix('settings')->controller(DashboardSettingsController::class)->group(function () {
        Route::get('/main', 'main')->name('main');
        Route::get('/home_page', 'home_page')->name('home_page');
        Route::get('/warranty_and_exchange_page', 'warranty_and_exchange_page')->name('warranty_and_exchange_page');
        Route::get('/seo', 'seo')->name('seo');
    });
});
