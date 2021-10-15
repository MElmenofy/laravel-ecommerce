<?php
use App\Http\Controllers\Frontend;
use App\Http\Controllers\Backend;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Meneses\LaravelMpdf\Facades\LaravelMpdf as PDF;
use App\Notifications\Frontend\Customer\OrderThanksNotification;
//test pdf
Route::get('/test-pdf', function (){
//    $data = [
//        'invoice' => '123'
//    ];
    $order = \App\Models\Order::with('products', 'user', 'payment_method')->find(1);
    $data = $order->toArray();
    $data['currency_symbol'] = $order->currency == 'USD' ? '$' : $order->currency;
    $pdf = PDF::loadView('layouts.invoice', $data);
//    return $pdf->stream('document.pdf');
    $saved_file = storage_path('app/pdf/files/'. $data['ref_id'] .'.pdf');
    $pdf->save($saved_file);
    $customer = \App\Models\User::find($order->user_id);
    $customer->notify(new OrderThanksNotification($order, $saved_file));
    return 'email sent';
});


// FRONTEND
Route::get('/', [Frontend\FrontendController::class, 'index'])->name('frontend.index');
Route::get('/shop/{slug?}', [Frontend\FrontendController::class, 'shop'])->name('frontend.shop');
Route::get('/shop/tags/{slug}', [Frontend\FrontendController::class, 'shop_tag'])->name('frontend.shop_tag');
Route::get('/product/{slug?}', [Frontend\FrontendController::class, 'product'])->name('frontend.product');
Route::get('/cart', [Frontend\FrontendController::class, 'cart'])->name('frontend.cart');
Route::get('/wishlist', [Frontend\FrontendController::class, 'wishlist'])->name('frontend.wishlist');


Route::group(['middleware' => ['Roles', 'role:customer']], function (){
    Route::get('/dashboard', [Frontend\CustomerController::class, 'dashboard'])->name('customer.dashboard');
    Route::get('/profile', [Frontend\CustomerController::class, 'profile'])->name('customer.profile');
    Route::patch('/profile', [Frontend\CustomerController::class, 'update_profile'])->name('customer.update_profile');
    Route::get('/profile/remove-image', [Frontend\CustomerController::class, 'remove_profile_image'])->name('customer.remove_profile_image');
    Route::get('/addresses', [Frontend\CustomerController::class, 'addresses'])->name('customer.addresses');
    Route::get('/orders', [Frontend\CustomerController::class, 'orders'])->name('customer.orders');

    Route::group(['middleware' => 'CheckCart'], function (){
        Route::get('/checkout/{order_id}/cancelled', [Frontend\PaymentController::class, 'cancelled'])->name('checkout.cancel');
        Route::post('/checkout/payment', [Frontend\PaymentController::class, 'checkout_now'])->name('checkout.payment');
        Route::get('/checkout/{order_id}/completed', [Frontend\PaymentController::class, 'completed'])->name('checkout.complete');
        Route::get('/checkout/webhook/{order?}/{env?}', [Frontend\PaymentController::class, 'webhook'])->name('checkout.webhook.ipn');
        Route::get('/checkout', [Frontend\PaymentController::class, 'checkout'])->name('frontend.checkout');

    });


});

Auth::routes(['verify' => true]);
// BACKEND - ADMIN
Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
Route::group(['middleware' => 'guest'], function () {
    Route::get('/login', [Backend\BackendController::class, 'login'])->name('login');
    Route::get('/forgot-password', [Backend\BackendController::class, 'forgot_password'])->name('forgot_password');
});

Route::group(['middleware' => ['Roles', 'role:admin|supervisor']], function (){
    Route::get('/', [Backend\BackendController::class, 'index'])->name('index_route');
    Route::get('/index', [Backend\BackendController::class, 'index'])->name('index');
    Route::get('/account_settings', [Backend\BackendController::class, 'account_settings'])->name('account_settings');
    Route::post('/admin/remove-image', [Backend\BackendController::class, 'remove_image'])->name('remove_image');
    Route::patch('/account_settings', [Backend\BackendController::class, 'update_account_settings'])->name('update_account_settings');
    // PRODUCT CATEGORY
    Route::post('/product_categories/remove-image', [Backend\ProductCategoriesController::class, 'remove_image'])->name('product_categories.remove_image');
    Route::resource('product_categories', Backend\ProductCategoriesController::class);
    // PRODUCT
    Route::post('/products/remove-image', [Backend\ProductController::class, 'remove_image'])->name('product.remove_image');
    Route::resource('products', Backend\ProductController::class);
    // TAG
    Route::resource('tags', Backend\TagController::class);
    // Product Coupon
    Route::resource('product_coupons', Backend\ProductCouponController::class);
    // Product Reviews
    Route::resource('product_reviews', Backend\ProductReviewController::class);
    // Customers
    Route::post('/customers/remove-image', [Backend\CustomerController::class, 'remove_image'])->name('customers.remove_image');
    Route::get('/customers/get_customers', [Backend\CustomerController::class, 'get_customers'])->name('customers.get_customers');
    Route::resource('customers', Backend\CustomerController::class);
    Route::resource('customer_addresses', Backend\CustomerAddressController::class);
    // supervisor
    Route::post('/supervisor/remove-image', [Backend\SupervisorController::class, 'remove_image'])->name('supervisor.remove_image');
    Route::resource('supervisors', Backend\SupervisorController::class);
    // Orders
    Route::resource('orders', Backend\OrderController::class);
    // countries
    Route::resource('countries', Backend\CountryController::class);
    // STATES
    Route::get('states/get_states', [Backend\StateController::class, 'get_states'])->name('states/get_states');
    Route::resource('states', Backend\StateController::class);
    // CITY
    Route::get('cities/get_cities', [Backend\CityController::class, 'get_cities'])->name('cities/get_cities');
    Route::resource('cities', Backend\CityController::class);
    // SHIPPING COMPANY
    Route::resource('shipping_companies', Backend\ShippingCompanyController::class);
    // PaymentMethod
    Route::resource('payment_methods', Backend\PaymentMethodController::class);

});

});
//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
