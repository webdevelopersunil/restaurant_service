<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use App\Http\Controllers\API;
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
/*
normal api_token
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/


Route::get('type-list',[API\CommanController::class,'getTypeList']);
Route::get('get-cuisines',[API\CommanController::class,'getCuisines']);
Route::get('services',[API\ServiceController::class,'getServices']);
Route::get('cuisines',[API\CommanController::class,'getCuisines']);

Route::get('countries',[ API\CommanController::class, 'getCountryList' ]);
Route::get('states',[ API\CommanController::class, 'getStateList']);
Route::post('city-list',[ API\CommanController::class, 'getCityList' ]);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Restaurant Registration
Route::post('restaurant/register',[App\Http\Controllers\API\Restaurant\RestaurantController::class, 'register']);
// Technician Registration
Route::post('technician/register',[App\Http\Controllers\API\Provider\ProviderController::class, 'register']);

Route::post('login',[API\User\UserController::class,'login']);

Route::post('forgot-password',[ API\User\UserController::class,'forgotPassword']);
Route::post('verify-otp', [ API\User\UserController::class, 'verifyOtp' ] );
Route::post('create-new-password', [ API\User\UserController::class, 'createNewPassword' ] );


// ---------------Old-Routes---------------
Route::post('social-login',[ API\User\UserController::class, 'socialLogin' ]);
Route::post('contact-us', [ API\User\UserController::class, 'contactUs' ] );
Route::get('user-detail',[API\User\UserController::class, 'userDetail']);
Route::get('user-list',[API\User\UserController::class, 'userList']);
// ---------------Old-Routes---------------


Route::group(['prefix' => 'restaurant','middleware' => ['auth:sanctum','role.validate:restaurant']], function () {

    Route::get('dashboard',[API\Restaurant\DashboardController::class,'index']);

    //Update Restaurant Profile Detail
    Route::post('profile',[API\Restaurant\RestaurantController::class,'updateProfile']);
    Route::get('profile',[API\Restaurant\RestaurantController::class,'profile']);

    //Restaurant Jobs Applications Management
    Route::post('job',[API\Restaurant\RestaurantJobController::class,'jobPost']);
    Route::get('job/{uuid}',[API\Restaurant\RestaurantJobController::class,'jobDetail']);
    Route::post('job/cancel',[API\Restaurant\RestaurantJobController::class,'jobCancel']);
    Route::get('jobs',[API\Restaurant\RestaurantJobController::class,'jobs']);
    Route::get('job/applications/{uuid}',[API\Restaurant\RestaurantJobController::class,'getJobApplications']);


    Route::get('profile',[API\Restaurant\RestaurantController::class,'profile']);//Equipment Manage
    Route::resource('equipment', App\Http\Controllers\API\EquipmentAPIController::class);
    Route::resource('maintanance_frequencies', App\Http\Controllers\API\MaintananceFrequencyAPIController::class);

    // Company Subscription
    Route::post('plan/subscription',[App\Http\Controllers\API\StripeController::class,'craeteSubscription']);

    //Sending offer to Provider applied job application
    Route::post('send/offer',[API\Restaurant\RestaurantJobController::class,'sendOffer']);
    Route::post('refer',[API\Restaurant\RestaurantController::class,'refer']);

});

Route::group(['prefix' => 'technician','middleware' => ['auth:sanctum','role.validate:provider']], function () {

    //Update Technician Profile Detail
    Route::post('profile',[API\Provider\ProviderController::class,'updateProfile']);
    Route::get('profile',[API\Provider\ProviderController::class,'profile']);

    //Jobs API Technician
    Route::get('jobs',[API\Provider\ProviderJobController::class,'jobs']);
    Route::get('job/{uuid}',[API\Provider\ProviderJobController::class,'jobDetail']);

    Route::get('dashboard',[API\Provider\DashboardController::class,'index']);

    Route::resource('equipment/request', App\Http\Controllers\API\PartRequestAPIController::class);

    //Provider Payment Methods
    Route::resource('provider_payment_methods', App\Http\Controllers\API\ProviderPaymentMethodAPIController::class);

    Route::group(['prefix' => 'job'], function () {

        //Jobs Applications Management
        Route::post('application',[API\Provider\ProviderJobController::class,'applyJob']);
        Route::post('application/accept',[API\Provider\ProviderJobController::class,'applicationAccept']);

    });

    Route::get('bookings',[API\Provider\ProviderJobController::class,'bookingList']);

    //Booking Management
    Route::post('start/tracking',[API\Provider\BookingController::class,'startTracking']);

    //Manage Work - Log
    Route::post('work-log',[API\Provider\BookingController::class,'workLog']);

    Route::post('booking/invoice',[API\Provider\BookingController::class,'bookingInvoice']);
});



Route::group(['middleware' => ['auth:sanctum']], function () {

    //For File Uploading
    Route::post('file-upload',[API\FileUploadingController::class,'upload']);
    Route::post('delete/file',[API\FileUploadingController::class,'deleteFile']);

    Route::post('/static-data', API\StaticDataController::class);

    Route::post('service',[API\ServiceController::class, 'create']);
    Route::get('logout',[ API\User\UserController::class, 'logout' ]);
    Route::post('save-provider-bank',[API\User\UserController::class,'saveProviderBank']);
    Route::post('notification-list',[API\NotificationController::class,'notificationList']);

    Route::post('user-update-status',[API\User\UserController::class, 'userStatusUpdate']);
    Route::post('change-password',[API\User\UserController::class, 'changePassword']);
    Route::post('delete-user-account',[API\User\UserController::class, 'deleteUserAccount']);
    Route::post('delete-account',[API\User\UserController::class, 'deleteAccount']);

    Route::resource('features', App\Http\Controllers\API\FeatureAPIController::class);
    Route::get('plans',[ App\Http\Controllers\API\PlansController::class,'index']);

    Route::post('ratingreview', [API\RatingReviewController::class, 'store']);

});
