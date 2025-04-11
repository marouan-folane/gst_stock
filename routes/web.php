<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\SensibleCategoryController;

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

// Authentication routes
Auth::routes();

// Redirect root to login if not authenticated
Route::get('/', function () {
    return redirect()->route('login');
});

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Products
    Route::resource('products', ProductController::class);
    Route::get('/products/expiring/soon', [ProductController::class, 'expiringSoon'])->name('products.expiring');
    
    // Categories
    Route::resource('categories', CategoryController::class);
    
    // Suppliers
    Route::resource('suppliers', SupplierController::class);
    
    // Customers
    Route::resource('customers', CustomerController::class);
    
    // Stock Management
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/products/{product}/stock/add', [StockController::class, 'add'])->name('products.stock.create');
    Route::post('/products/{product}/stock/add', [StockController::class, 'store'])->name('stock.store');
    Route::get('/products/{product}/stock/remove', [StockController::class, 'remove'])->name('products.stock.remove');
    Route::post('/products/{product}/stock/remove', [StockController::class, 'destroy'])->name('stock.destroy');
    Route::get('/products/{product}/stock/history', [StockController::class, 'history'])->name('products.stock.history');
    
    // New unified stock management routes
    Route::get('/products/{product}/stock/adjust', [StockController::class, 'createStock'])->name('products.stock.create');
    Route::post('/products/{product}/stock/adjust', [StockController::class, 'storeStock'])->name('products.stock.store');
    
    // Reports
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::match(['get', 'post'], '/reports/generate', [ReportsController::class, 'generate'])->name('reports.generate');
    Route::match(['get', 'post'], '/reports/download', [ReportsController::class, 'download'])->name('reports.download.post');
    Route::get('/reports/download/{reportId?}', [ReportsController::class, 'download'])->name('reports.download');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/notifications', [ProfileController::class, 'updateNotificationSettings'])->name('profile.notifications');
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    
    // Sensible Categories
    Route::resource('sensible-categories', SensibleCategoryController::class);
    Route::put('/sensible-categories/{sensibleCategory}/toggle-active', [SensibleCategoryController::class, 'toggleActive'])
        ->name('sensible-categories.toggle-active');
    Route::get('/sensible-categories/{sensibleCategory}/test-notification', [SensibleCategoryController::class, 'testNotification'])
        ->name('sensible-categories.test-notification');
    
    // Admin only routes
    Route::middleware(['can:manage-users'])->group(function () {
        // User Management
        Route::resource('users', UserController::class);
        
        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        
        // Email Notification Settings
        Route::get('/settings/notifications', [SettingController::class, 'showNotifications'])->name('settings.notifications');
        Route::put('/settings/notifications', [SettingController::class, 'updateNotifications'])->name('settings.notifications.update');
        Route::post('/settings/notifications/test', [SettingController::class, 'testEmail'])->name('settings.notifications.test');
        
        // SMS Management - Admin only
        Route::get('/sms', [SmsController::class, 'showSmsForm'])->name('sms.form');
        Route::post('/sms/send-direct', [SmsController::class, 'sendDirectSms'])->name('sms.send-direct');
        Route::post('/sms/send-alert', [SmsController::class, 'sendAlertSms'])->name('sms.send-alert');
        Route::post('/sms/send-bulk', [SmsController::class, 'sendBulkSms'])->name('sms.send-bulk');
        
        // New SMS routes with InfoBip and MSG91
        Route::get('/sms/new', [SmsController::class, 'index'])->name('sms.index');
        Route::post('/sms/send', [SmsController::class, 'send'])->name('sms.send');
        Route::post('/sms/send-to-user', [SmsController::class, 'sendToUser'])->name('sms.send-to-user');
    });

    // Activity routes
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');

    // Sales routes
    Route::resource('sales', SaleController::class);
    Route::get('sales/{sale}/pdf', [SaleController::class, 'generatePdf'])->name('sales.pdf');
    Route::get('sales/{sale}/payments/create', [PaymentController::class, 'create'])->name('sales.payments.create');

    // Payment routes
    Route::resource('payments', PaymentController::class);

    // Alerts
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::post('/alerts/{id}/mark-as-read', [AlertController::class, 'markAsRead'])->name('alerts.mark-as-read');
    Route::post('/alerts/mark-all-as-read', [AlertController::class, 'markAllAsRead'])->name('alerts.mark-all-as-read');
    Route::get('/alerts/check/expiring', [AlertController::class, 'checkExpiringProducts'])->name('alerts.check.expiring');
    Route::get('/alerts/check/low-stock', [AlertController::class, 'checkLowStockProducts'])->name('alerts.check.low-stock');
    Route::get('/alerts/check/out-of-stock', [AlertController::class, 'checkOutOfStockProducts'])->name('alerts.check.out-of-stock');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/stock', [HomeController::class, 'index'])->name('home');

