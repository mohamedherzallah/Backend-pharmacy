<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\{AddressController,
    AuthController,
    CategoryController,
    CustomerMedicineController,
    MedicineController,
    PharmacyController,
    PharmacyMedicineController,
    CartController,
    CheckoutController,
    OrderController,
    PrescriptionController,
    ConversationController,
    MessageController,
    UserController,
    PaymentController,
    PaymentCardController,
    ProfileController,
    FavoriteController};
/*
|--------------------------------------------------------------------------
| Auth Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class,'logout']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

    Route::post('pharmacy/register', [AuthController::class, 'registerPharmacy']);

});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/user/change-password', [AuthController::class, 'changePassword']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});




/*
|--------------------------------------------------------------------------
| Public Resources
|--------------------------------------------------------------------------
*/
Route::get('categories', [CategoryController::class,'index']);
Route::get('categories/{id}', [CategoryController::class,'show']);

Route::get('medicines', [MedicineController::class,'index']);
Route::get('medicines/{id}', [MedicineController::class,'show']);
Route::get('pharmacies/{id}/medicines', [MedicineController::class,'byPharmacy']);//??????

Route::get('pharmacies/{id}', [PharmacyController::class, 'show']);


Route::get('medicines/{id}/pharmacies', [MedicineController::class, 'getPharmacies']);


Route::get('pharmacies', [PharmacyController::class,'index']);
Route::get('pharmacy/{id}', [PharmacyController::class,'show']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Sanctum Token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

        // ========== Favorites Routes ==========
        Route::get('favorites', [FavoriteController::class, 'index']);                      // عرض جميع المفضلات
        Route::post('favorites', [FavoriteController::class, 'store']);                     // إضافة إلى المفضلة
        Route::get('favorites/check/{medicineId}', [FavoriteController::class, 'check']);   // التحقق من المفضلة
        Route::delete('favorites/{medicineId}', [FavoriteController::class, 'destroy']);    // حذف من المفضلة

//    Route::put('pharmacy/profile', [PharmacyController::class, 'updatePharmacyProfile']);
    Route::get('profile', [ProfileController::class, 'getProfile']);   // عرض أي بروفايل
    Route::put('profile', [ProfileController::class, 'updateProfile']);
    Route::post('profile/logo', [ProfileController::class, 'updatePharmacyLogo']
    )->name('pharmacy.profile.logo');

    // ========== Payment Routes ==========
    // Moved behind auth:sanctum (was public - any anonymous caller could
    // pay/mark-as-paid any order_id).
    Route::post('payment/pay', [PaymentController::class, 'pay']);

    // ========== Payment Card Routes ==========
    // PaymentCardController existed but had no routes registered at all,
    // so every call from the frontend's paymentCardAPI (getCards, addCard,
    // setDefault, deleteCard) 404'd and the "Payment Cards" page was
    // entirely non-functional.
    Route::prefix('payment-cards')->group(function () {
        Route::get('/', [PaymentCardController::class, 'index']);
        Route::post('/', [PaymentCardController::class, 'store']);
        Route::post('{id}/set-default', [PaymentCardController::class, 'setDefault']);
        Route::delete('{id}', [PaymentCardController::class, 'destroy']);
    });

    /*
    |--------------------- Pharmacy Management ---------------------
    */
    Route::prefix('pharmacy')->group(function () {
        Route::get('medicines',  [PharmacyMedicineController::class,'index']);
        Route::post('medicines', [PharmacyMedicineController::class,'store']);
        Route::delete('medicines/{Id}', [PharmacyMedicineController::class,'destroy']);
        // PharmacyController::stats() existed but had no route - the
        // pharmacy dashboard had no way to fetch medicine/order counts.
        Route::get('stats', [PharmacyController::class, 'stats']);
    });

// (المفروض هيك )للمستخدمين (العملاء) - بدون تسجيل دخول


    /*
    |-------------------------- Cart --------------------------
    */
    Route::prefix('cart')->group(function () {
        Route::get('/',    [CartController::class,'index']);
        Route::post('add',           [CartController::class,'add']);
        Route::post('item/{id}',     [CartController::class,'updateItem']);
        Route::delete('item/{id}',   [CartController::class,'remove']);
        Route::post('clear',         [CartController::class,'clear']);
    });
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
    /*
    |------------------- Checkout & Orders -------------------
    */
    Route::post('checkout', [CheckoutController::class,'checkout']);
    Route::apiResource('orders', OrderController::class)->only([
        'index', 'show', 'update', 'destroy'
    ]);

    /*
    |---------------------- Prescriptions ----------------------
    */
    Route::post('prescriptions', [PrescriptionController::class,'store']);
    Route::get('prescriptions',  [PrescriptionController::class,'index']);

    /*
    |-------------------------- Chat ---------------------------
    */
    Route::prefix('chat')->group(function () {
        Route::get('conversations', [ConversationController::class,'index']);
        Route::post('conversations', [ConversationController::class,'store']);

        Route::get('conversations/{id}/messages',  [MessageController::class,'index']);
        Route::post('conversations/{id}/messages', [MessageController::class,'store']);
    });

});

Route::prefix('customer')->group(function () {
    // 1. البحث عن دواء في كل الصيدليات
    Route::get('medicines/search', [CustomerMedicineController::class, 'search']);

    // 2. أدوية صيدلية معينة
    Route::get('pharmacies/{pharmacy}/medicines', [CustomerMedicineController::class, 'pharmacyMedicines']);

    // 3. الأدوية حسب التصنيف
    Route::get('categories/{category}/medicines', [CustomerMedicineController::class, 'medicinesByCategory']);
});
